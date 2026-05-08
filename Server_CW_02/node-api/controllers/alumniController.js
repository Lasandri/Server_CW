/**
 * Alumni Controller - CW2
 * View alumni with filters
 */
const pool = require('../config/database');

// ================================================================
// GET ALL ALUMNI (with filters)
// ================================================================
exports.getAlumni = async (req, res) => {
    try {
        const {
            programme,
            graduation_year,
            industry,
            country,
            page  = 1,
            limit = 20,
        } = req.query;

        const offset = (parseInt(page) - 1) * parseInt(limit);
        const conditions = [];
        const params = [];

        if (programme) {
            conditions.push('d.field_of_study LIKE ?');
            params.push(`%${programme}%`);
        }
        if (graduation_year) {
            conditions.push('YEAR(d.completion_date) = ?');
            params.push(parseInt(graduation_year));
        }
        if (industry) {
            conditions.push('eh.company_name LIKE ?');
            params.push(`%${industry}%`);
        }
        if (country) {
            conditions.push('ap.country LIKE ?');
            params.push(`%${country}%`);
        }

        const where = conditions.length
            ? 'WHERE ' + conditions.join(' AND ')
            : '';

        const [alumni] = await pool.query(
            `SELECT DISTINCT
                u.id, u.first_name, u.last_name, u.email,
                ap.biography, ap.linkedin_url, ap.profile_image,
                ap.city, ap.country,
                d.field_of_study   as programme,
                d.university_name,
                YEAR(d.completion_date) as graduation_year,
                eh.job_title       as current_job_title,
                eh.company_name    as current_company
             FROM users u
             LEFT JOIN alumni_profiles ap ON ap.user_id = u.id
             LEFT JOIN degrees d         ON d.user_id   = u.id
             LEFT JOIN employment_history eh
                ON eh.user_id = u.id AND eh.is_current = 1
             ${where}
             GROUP BY u.id
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?`,
            [...params, parseInt(limit), offset]
        );

        const [[{ total }]] = await pool.query(
            `SELECT COUNT(DISTINCT u.id) as total
             FROM users u
             LEFT JOIN alumni_profiles ap ON ap.user_id = u.id
             LEFT JOIN degrees d         ON d.user_id   = u.id
             LEFT JOIN employment_history eh ON eh.user_id = u.id
             ${where}`,
            params
        );

        // Get unique programmes for filter dropdown
        const [programmes] = await pool.query(
            `SELECT DISTINCT field_of_study as programme
             FROM degrees
             WHERE field_of_study IS NOT NULL
             ORDER BY field_of_study ASC`
        );

        // Get unique countries
        const [countries] = await pool.query(
            `SELECT DISTINCT country
             FROM alumni_profiles
             WHERE country IS NOT NULL AND country != ''
             ORDER BY country ASC`
        );

        // Get unique graduation years
        const [gradYears] = await pool.query(
            `SELECT DISTINCT YEAR(completion_date) as year
             FROM degrees
             WHERE completion_date IS NOT NULL
             ORDER BY year DESC`
        );

        res.status(200).json({
            status: 'success',
            data: {
                alumni,
                pagination: {
                    total,
                    page:        parseInt(page),
                    limit:       parseInt(limit),
                    total_pages: Math.ceil(total / parseInt(limit)),
                },
                filters: {
                    programmes:       programmes.map(p => p.programme),
                    countries:        countries.map(c => c.country),
                    graduation_years: gradYears.map(y => y.year),
                },
                filters_applied: { programme, graduation_year, industry, country },
            },
        });

    } catch (err) {
        console.error('Alumni list error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

// ================================================================
// GET SINGLE ALUMNI
// ================================================================
exports.getAlumniById = async (req, res) => {
    try {
        const userId = parseInt(req.params.id);

        const [users] = await pool.query(
            `SELECT u.id, u.first_name, u.last_name,
                    u.email, u.created_at,
                    ap.biography, ap.linkedin_url,
                    ap.profile_image, ap.city, ap.country
             FROM users u
             LEFT JOIN alumni_profiles ap ON ap.user_id = u.id
             WHERE u.id = ? AND u.is_active = 1`,
            [userId]
        );

        if (users.length === 0) {
            return res.status(404).json({
                status: 'error',
                message: 'Alumni not found.',
            });
        }

        const [degrees] = await pool.query(
            'SELECT * FROM degrees WHERE user_id = ? ORDER BY completion_date DESC',
            [userId]
        );
        const [certs] = await pool.query(
            'SELECT * FROM certifications WHERE user_id = ? ORDER BY completion_date DESC',
            [userId]
        );
        const [licences] = await pool.query(
            'SELECT * FROM licences WHERE user_id = ? ORDER BY issue_date DESC',
            [userId]
        );
        const [courses] = await pool.query(
            'SELECT * FROM professional_courses WHERE user_id = ? ORDER BY completion_date DESC',
            [userId]
        );
        const [employment] = await pool.query(
            'SELECT * FROM employment_history WHERE user_id = ? ORDER BY start_date DESC',
            [userId]
        );

        res.status(200).json({
            status: 'success',
            data: {
                user:        users[0],
                degrees,
                certifications: certs,
                licences,
                courses,
                employment,
            },
        });

    } catch (err) {
        console.error('Single alumni error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};