// node-api/controllers/alumniController.js
// REPLACE ENTIRE FILE
// Now reads from CW1's actual tables

const pool = require('../config/database');

/**
 * GET /api/alumni
 * Get alumni list from CW1 database
 * Joins: users + alumni_profiles + degrees + employment_history
 */
async function getAlumni(req, res) {
    try {
        const {
            programme,
            graduation_year,
            industry_sector,
            search,
            page  = 1,
            limit = 12
        } = req.query;

        // Build WHERE conditions using CW1 table structure
        let conditions = [
            'u.is_active = 1',
            'u.is_email_verified = 1',
            'ap.is_profile_complete = 1'
        ];
        let params = [];

        // Filter by programme (from degrees table)
        if (programme && programme !== 'all') {
            conditions.push('d.programme = ?');
            params.push(programme);
        }

        // Filter by graduation year (from degrees table)
        if (graduation_year && graduation_year !== 'all') {
            conditions.push('d.graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        // Filter by industry sector (from employment_history)
        if (industry_sector && industry_sector !== 'all') {
            conditions.push('eh.industry_sector = ?');
            params.push(industry_sector);
        }

        // Search by name, job title, company
        if (search) {
            conditions.push(
                '(u.first_name LIKE ? OR u.last_name LIKE ? OR eh.job_title LIKE ? OR eh.company_name LIKE ?)'
            );
            const s = `%${search}%`;
            params.push(s, s, s, s);
        }

        const whereClause = 'WHERE ' + conditions.join(' AND ');

        // Count total matching alumni
        const [countResult] = await pool.execute(
            `SELECT COUNT(DISTINCT u.id) as total
             FROM users u
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             LEFT JOIN degrees d ON u.id = d.user_id
             LEFT JOIN employment_history eh ON u.id = eh.user_id AND eh.is_current = 1
             ${whereClause}`,
            params
        );

        const total  = countResult[0].total;
        const offset = (parseInt(page) - 1) * parseInt(limit);

        // Get alumni with all related data
        const [rows] = await pool.execute(
            `SELECT DISTINCT
                u.id,
                u.first_name,
                u.last_name,
                CONCAT(u.first_name, ' ', u.last_name) as full_name,
                u.email,
                ap.city             as location_city,
                ap.country          as location_country,
                ap.profile_image,
                ap.linkedin_url,
                d.programme,
                d.graduation_year,
                d.university_name,
                d.field_of_study,
                eh.job_title,
                eh.company_name     as employer,
                eh.industry_sector,
                eh.industry         as industry,
                eh.location         as work_location
             FROM users u
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             LEFT JOIN degrees d ON u.id = d.user_id
             LEFT JOIN employment_history eh ON u.id = eh.user_id AND eh.is_current = 1
             ${whereClause}
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?`,
            [...params, parseInt(limit), offset]
        );

        // For each alumni, get their certifications and courses
        const alumniWithDetails = await Promise.all(rows.map(async (alumni) => {
            // Get certifications
            const [certs] = await pool.execute(
                `SELECT certification_name FROM certifications
                 WHERE user_id = ? LIMIT 5`,
                [alumni.id]
            );

            // Get professional courses (post-grad learning)
            const [courses] = await pool.execute(
                `SELECT course_name FROM professional_courses
                 WHERE user_id = ? LIMIT 5`,
                [alumni.id]
            );

            // Get licences
            const [licences] = await pool.execute(
                `SELECT licence_name FROM licences
                 WHERE user_id = ? LIMIT 3`,
                [alumni.id]
            );

            return {
                ...alumni,
                certifications: certs.map(c => c.certification_name),
                courses:        courses.map(c => c.course_name),
                licences:       licences.map(l => l.licence_name),
                // Combine courses + certs as "skills" for charts
                skills:         [
                    ...courses.map(c => c.course_name),
                    ...certs.map(c => c.certification_name)
                ].slice(0, 8)
            };
        }));

        return res.json({
            success: true,
            data:    alumniWithDetails,
            pagination: {
                total,
                page:        parseInt(page),
                limit:       parseInt(limit),
                total_pages: Math.ceil(total / parseInt(limit))
            }
        });

    } catch (error) {
        console.error('Get alumni error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch alumni',
            error:   error.message
        });
    }
}

/**
 * GET /api/alumni/filters
 * Get all unique filter options from CW1 data
 */
async function getFilterOptions(req, res) {
    try {
        // Distinct programmes from CW1 degrees table
        const [programmes] = await pool.execute(
            `SELECT DISTINCT programme 
             FROM degrees 
             WHERE programme IS NOT NULL AND programme != ''
             ORDER BY programme`
        );

        // Distinct graduation years
        const [years] = await pool.execute(
            `SELECT DISTINCT graduation_year 
             FROM degrees 
             WHERE graduation_year IS NOT NULL
             ORDER BY graduation_year DESC`
        );

        // Distinct industry sectors from employment_history
        const [sectors] = await pool.execute(
            `SELECT DISTINCT industry_sector 
             FROM employment_history 
             WHERE industry_sector IS NOT NULL AND industry_sector != ''
             ORDER BY industry_sector`
        );

        return res.json({
            success: true,
            data: {
                programmes:       programmes.map(r => r.programme),
                graduation_years: years.map(r => r.graduation_year),
                industry_sectors: sectors.map(r => r.industry_sector)
            }
        });

    } catch (error) {
        console.error('Get filter options error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch filter options'
        });
    }
}

/**
 * GET /api/alumni/:id
 * Get single alumni full profile from CW1
 */
async function getAlumniById(req, res) {
    try {
        const { id } = req.params;

        // Main profile data
        const [rows] = await pool.execute(
            `SELECT
                u.id,
                u.first_name,
                u.last_name,
                CONCAT(u.first_name, ' ', u.last_name) as full_name,
                u.email,
                ap.phone,
                ap.city            as location_city,
                ap.country         as location_country,
                ap.profile_image,
                ap.linkedin_url,
                ap.biography
             FROM users u
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             WHERE u.id = ? AND u.is_active = 1`,
            [id]
        );

        if (rows.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Alumni not found'
            });
        }

        const alumni = rows[0];

        // Get all degrees
        const [degrees] = await pool.execute(
            `SELECT degree_title, field_of_study, programme,
                    university_name, graduation_year, grade
             FROM degrees WHERE user_id = ?
             ORDER BY graduation_year DESC`,
            [id]
        );

        // Get all employment history
        const [employment] = await pool.execute(
            `SELECT company_name, job_title, industry_sector,
                    industry, location, start_date, end_date, is_current
             FROM employment_history WHERE user_id = ?
             ORDER BY is_current DESC, start_date DESC`,
            [id]
        );

        // Get certifications
        const [certifications] = await pool.execute(
            `SELECT certification_name, issuing_organization,
                    completion_date, expiry_date
             FROM certifications WHERE user_id = ?
             ORDER BY completion_date DESC`,
            [id]
        );

        // Get professional courses
        const [courses] = await pool.execute(
            `SELECT course_name, provider, completion_date, duration
             FROM professional_courses WHERE user_id = ?
             ORDER BY completion_date DESC`,
            [id]
        );

        // Get licences
        const [licences] = await pool.execute(
            `SELECT licence_name, issuing_body, issue_date, expiry_date
             FROM licences WHERE user_id = ?`,
            [id]
        );

        return res.json({
            success: true,
            data: {
                ...alumni,
                degrees,
                employment,
                certifications,
                courses,
                licences,
                // Latest degree info for quick access
                programme:       degrees[0]?.programme       || null,
                graduation_year: degrees[0]?.graduation_year || null,
                university:      degrees[0]?.university_name || null,
                // Current job for quick access
                job_title:       employment.find(e => e.is_current)?.job_title    || null,
                employer:        employment.find(e => e.is_current)?.company_name || null,
                industry_sector: employment.find(e => e.is_current)?.industry_sector || null
            }
        });

    } catch (error) {
        console.error('Get alumni by id error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch alumni'
        });
    }
}

module.exports = { getAlumni, getFilterOptions, getAlumniById };