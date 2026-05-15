// node-api/controllers/analyticsController.js
// REPLACE ENTIRE FILE

const pool = require('../config/database');

/**
 * Build base WHERE clause from filters
 */
function buildFilters(programme, graduation_year) {
    let conditions = [
        'u.is_active = 1',
        'u.is_email_verified = 1'
    ];
    let params = [];

    if (programme && programme !== 'all') {
        conditions.push('d.programme = ?');
        params.push(programme);
    }

    if (graduation_year && graduation_year !== 'all') {
        conditions.push('d.graduation_year = ?');
        params.push(parseInt(graduation_year));
    }

    return { conditions, params };
}

/**
 * GET /api/analytics/overview
 */
async function getOverview(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        // Total verified alumni
        const [totalResult] = await pool.execute(
            `SELECT COUNT(DISTINCT u.id) as total
             FROM users u
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             WHERE u.is_active = 1 AND u.is_email_verified = 1`
        );

        // Total distinct industry sectors
        const [sectorResult] = await pool.execute(
            `SELECT COUNT(DISTINCT eh.industry_sector) as count
             FROM employment_history eh
             WHERE eh.industry_sector IS NOT NULL AND eh.industry_sector != ''`
        );

        // Total distinct programmes
        const [programmeResult] = await pool.execute(
            `SELECT COUNT(DISTINCT d.programme) as count
             FROM degrees d
             WHERE d.programme IS NOT NULL AND d.programme != ''`
        );

        // Total distinct graduation years
        const [yearResult] = await pool.execute(
            `SELECT COUNT(DISTINCT d.graduation_year) as count
             FROM degrees d
             WHERE d.graduation_year IS NOT NULL`
        );

        // Total certifications earned post-grad
        const [certResult] = await pool.execute(
            `SELECT COUNT(*) as count FROM certifications`
        );

        // Total professional courses completed
        const [courseResult] = await pool.execute(
            `SELECT COUNT(*) as count FROM professional_courses`
        );

        return res.json({
            success: true,
            data: {
                total_alumni:     totalResult[0].total,
                total_sectors:    sectorResult[0].count,
                total_programmes: programmeResult[0].count,
                total_years:      yearResult[0].count,
                total_certs:      certResult[0].count,
                total_courses:    courseResult[0].count
            }
        });

    } catch (error) {
        console.error('Overview error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch overview' });
    }
}

/**
 * GET /api/analytics/employment-by-sector
 * Uses CW1 employment_history.industry_sector
 */
async function getEmploymentBySector(req, res) {
    try {
        const { programme, graduation_year } = req.query;
        const { conditions, params } = buildFilters(programme, graduation_year);

        let query = `
            SELECT eh.industry_sector, COUNT(DISTINCT u.id) as count
            FROM users u
            LEFT JOIN degrees d ON u.id = d.user_id
            JOIN employment_history eh ON u.id = eh.user_id AND eh.is_current = 1
            WHERE u.is_active = 1
            AND eh.industry_sector IS NOT NULL
            AND eh.industry_sector != ''
        `;

        if (programme && programme !== 'all') {
            query += ' AND d.programme = ?';
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            query += ' AND d.graduation_year = ?';
            params.push(parseInt(graduation_year));
        }

        query += ' GROUP BY eh.industry_sector ORDER BY count DESC';

        const [rows] = await pool.execute(query, params);

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Employment by sector error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch data' });
    }
}

/**
 * GET /api/analytics/skills-gap
 * Uses CW1 professional_courses (what alumni self-learned post-grad)
 * This is the CURRICULUM GAP - courses they had to take themselves
 */
async function getSkillsGap(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let query = `
            SELECT pc.course_name as skill, COUNT(*) as count
            FROM professional_courses pc
            JOIN users u ON pc.user_id = u.id
        `;

        let params = [];
        let conditions = ['u.is_active = 1'];

        if (programme && programme !== 'all') {
            query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            if (!query.includes('JOIN degrees')) {
                query += ' JOIN degrees d ON u.id = d.user_id';
            }
            conditions.push('d.graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        query += ' WHERE ' + conditions.join(' AND ');
        query += ' GROUP BY pc.course_name ORDER BY count DESC LIMIT 15';

        const [rows] = await pool.execute(query, params);

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Skills gap error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch skills data' });
    }
}

/**
 * GET /api/analytics/top-employers
 * Uses CW1 employment_history.company_name
 */
async function getTopEmployers(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let query = `
            SELECT eh.company_name as employer, COUNT(DISTINCT u.id) as count
            FROM employment_history eh
            JOIN users u ON eh.user_id = u.id
        `;

        let params = [];
        let conditions = ['u.is_active = 1', 'eh.is_current = 1',
                         'eh.company_name IS NOT NULL'];

        if (programme && graduation_year) {
            query += ' JOIN degrees d ON u.id = d.user_id';
        }
        if (programme && programme !== 'all') {
            if (!query.includes('JOIN degrees')) query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            if (!query.includes('JOIN degrees')) query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        query += ' WHERE ' + conditions.join(' AND ');
        query += ' GROUP BY eh.company_name ORDER BY count DESC LIMIT 10';

        const [rows] = await pool.execute(query, params);

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Top employers error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch employers' });
    }
}

/**
 * GET /api/analytics/job-titles
 * Uses CW1 employment_history.job_title
 */
async function getTopJobTitles(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let query = `
            SELECT eh.job_title, COUNT(DISTINCT u.id) as count
            FROM employment_history eh
            JOIN users u ON eh.user_id = u.id
        `;

        let params = [];
        let conditions = ['u.is_active = 1', 'eh.is_current = 1',
                         'eh.job_title IS NOT NULL'];

        if (programme && programme !== 'all') {
            query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            if (!query.includes('JOIN degrees')) query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        query += ' WHERE ' + conditions.join(' AND ');
        query += ' GROUP BY eh.job_title ORDER BY count DESC LIMIT 10';

        const [rows] = await pool.execute(query, params);

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Job titles error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch job titles' });
    }
}

/**
 * GET /api/analytics/geographic
 * Uses CW1 alumni_profiles city + country
 */
async function getGeographic(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let query = `
            SELECT ap.city as location_city,
                   ap.country as location_country,
                   COUNT(DISTINCT u.id) as count
            FROM alumni_profiles ap
            JOIN users u ON ap.user_id = u.id
        `;

        let params = [];
        let conditions = ['u.is_active = 1',
                         'ap.city IS NOT NULL', 'ap.city != ""'];

        if (programme && programme !== 'all') {
            query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            if (!query.includes('JOIN degrees')) query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        query += ' WHERE ' + conditions.join(' AND ');
        query += ' GROUP BY ap.city, ap.country ORDER BY count DESC LIMIT 10';

        const [rows] = await pool.execute(query, params);

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Geographic error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch geographic data' });
    }
}

/**
 * GET /api/analytics/graduation-trends
 * Uses CW1 degrees.graduation_year
 */
async function getGraduationTrends(req, res) {
    try {
        const { programme } = req.query;

        let query = `
            SELECT d.graduation_year, COUNT(DISTINCT d.user_id) as count
            FROM degrees d
            JOIN users u ON d.user_id = u.id
            WHERE u.is_active = 1
            AND d.graduation_year IS NOT NULL
        `;

        let params = [];

        if (programme && programme !== 'all') {
            query += ' AND d.programme = ?';
            params.push(programme);
        }

        query += ' GROUP BY d.graduation_year ORDER BY d.graduation_year ASC';

        const [rows] = await pool.execute(query, params);

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Graduation trends error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch trends' });
    }
}

/**
 * GET /api/analytics/certifications
 * Uses CW1 certifications table
 */
async function getCertifications(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let query = `
            SELECT c.certification_name as cert,
                   c.issuing_organization as provider,
                   COUNT(*) as count
            FROM certifications c
            JOIN users u ON c.user_id = u.id
        `;

        let params = [];
        let conditions = ['u.is_active = 1', 'c.certification_name IS NOT NULL'];

        if (programme && programme !== 'all') {
            query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            if (!query.includes('JOIN degrees')) query += ' JOIN degrees d ON u.id = d.user_id';
            conditions.push('d.graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        query += ' WHERE ' + conditions.join(' AND ');
        query += ' GROUP BY c.certification_name, c.issuing_organization';
        query += ' ORDER BY count DESC LIMIT 10';

        const [rows] = await pool.execute(query, params);

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Certifications error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch certifications' });
    }
}

/**
 * GET /api/analytics/courses
 * Uses CW1 professional_courses - what alumni self-learned
 */
async function getCourses(req, res) {
    try {
        const [rows] = await pool.execute(
            `SELECT pc.course_name, pc.provider,
                    COUNT(*) as count
             FROM professional_courses pc
             JOIN users u ON pc.user_id = u.id
             WHERE u.is_active = 1
             GROUP BY pc.course_name, pc.provider
             ORDER BY count DESC
             LIMIT 10`
        );

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Courses error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch courses' });
    }
}

module.exports = {
    getOverview,
    getEmploymentBySector,
    getSkillsGap,
    getTopEmployers,
    getTopJobTitles,
    getGeographic,
    getGraduationTrends,
    getCertifications,
    getCourses
};