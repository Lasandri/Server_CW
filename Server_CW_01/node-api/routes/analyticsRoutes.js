const express = require('express');
const router  = express.Router();
const pool    = require('../config/database');
const { bearerAuth, requireScope } = require('../middleware/bearerAuth');
const { analyticsLimiter }         = require('../middleware/rateLimiter');

router.use(bearerAuth);
router.use(analyticsLimiter);

router.get('/dashboard-stats', requireScope('read:analytics'), async (req, res) => {
    try {
        const [
            [totalAlumni],
            [totalCerts],
            [totalCourses],
            [totalLicences],
            [activeBids],
            [totalWinners]
        ] = await Promise.all([
            pool.query('SELECT COUNT(*) AS count FROM users WHERE is_email_verified = 1'),
            pool.query(`SELECT COUNT(*) AS count FROM certifications c JOIN users u ON u.id = c.user_id WHERE u.is_email_verified = 1`),
            pool.query(`SELECT COUNT(*) AS count FROM professional_courses pc JOIN users u ON u.id = pc.user_id WHERE u.is_email_verified = 1`),
            pool.query(`SELECT COUNT(*) AS count FROM licences l JOIN users u ON u.id = l.user_id WHERE u.is_email_verified = 1`),
            pool.query(`SELECT COUNT(*) AS count FROM bids WHERE DATE(bid_date) = CURDATE()`),
            pool.query(`SELECT COUNT(*) AS count FROM daily_winners WHERE MONTH(display_date) = MONTH(CURDATE()) AND YEAR(display_date) = YEAR(CURDATE())`)
        ]);

        res.json({
            success: true,
            data: {
                total_alumni:         totalAlumni[0].count,
                total_certifications: totalCerts[0].count,
                total_courses:        totalCourses[0].count,
                total_licences:       totalLicences[0].count,
                active_bids_today:    activeBids[0].count,
                winners_this_month:   totalWinners[0].count
            }
        });
    } catch (err) {
        console.error('Dashboard stats error:', err.message);
        res.status(500).json({ success: false, message: 'Failed to fetch statistics' });
    }
});

router.get('/skills-gap', requireScope('read:analytics'), async (req, res) => {
    try {
        const { programme, graduation_year_from, graduation_year_to } = req.query;

        const conditions = ['u.is_email_verified = 1'];
        const params     = [];

        if (programme) {
            conditions.push('d.degree_name = ?');
            params.push(programme);
        }
        if (graduation_year_from) {
            const yr = parseInt(graduation_year_from);
            if (!isNaN(yr)) {
                conditions.push('YEAR(d.completion_date) >= ?');
                params.push(yr);
            }
        }
        if (graduation_year_to) {
            const yr = parseInt(graduation_year_to);
            if (!isNaN(yr)) {
                conditions.push('YEAR(d.completion_date) <= ?');
                params.push(yr);
            }
        }

        const whereClause = 'WHERE ' + conditions.join(' AND ');

        const [totalRows] = await pool.query(
            'SELECT COUNT(DISTINCT id) AS total FROM users WHERE is_email_verified = 1'
        );
        const totalAlumni = totalRows[0].total || 1;

        const [certRows] = await pool.query(`
            SELECT c.certification_name AS skill_name,
                   'certification' AS skill_type,
                   c.issuing_organization AS provider,
                   COUNT(DISTINCT u.id) AS alumni_count,
                   ROUND(COUNT(DISTINCT u.id) * 100.0 / ?, 1) AS percentage
            FROM certifications c
            JOIN users u ON u.id = c.user_id
            LEFT JOIN degrees d ON d.user_id = u.id
            ${whereClause}
            GROUP BY c.certification_name, c.issuing_organization
            ORDER BY alumni_count DESC
            LIMIT 20
        `, [totalAlumni, ...params]);

        const [courseRows] = await pool.query(`
            SELECT pc.course_name AS skill_name,
                   'course' AS skill_type,
                   pc.provider,
                   COUNT(DISTINCT u.id) AS alumni_count,
                   ROUND(COUNT(DISTINCT u.id) * 100.0 / ?, 1) AS percentage
            FROM professional_courses pc
            JOIN users u ON u.id = pc.user_id
            LEFT JOIN degrees d ON d.user_id = u.id
            ${whereClause}
            GROUP BY pc.course_name, pc.provider
            ORDER BY alumni_count DESC
            LIMIT 20
        `, [totalAlumni, ...params]);

        const [licenceRows] = await pool.query(`
            SELECT l.licence_name AS skill_name,
                   'licence' AS skill_type,
                   l.issuing_body AS provider,
                   COUNT(DISTINCT u.id) AS alumni_count,
                   ROUND(COUNT(DISTINCT u.id) * 100.0 / ?, 1) AS percentage
            FROM licences l
            JOIN users u ON u.id = l.user_id
            LEFT JOIN degrees d ON d.user_id = u.id
            ${whereClause}
            GROUP BY l.licence_name, l.issuing_body
            ORDER BY alumni_count DESC
            LIMIT 10
        `, [totalAlumni, ...params]);

        const allSkills = [...certRows, ...courseRows, ...licenceRows]
            .sort((a, b) => b.percentage - a.percentage)
            .map(s => ({
                ...s,
                gap_level: s.percentage >= 50 ? 'critical'
                         : s.percentage >= 25 ? 'significant'
                         : 'emerging'
            }));

        res.json({
            success: true,
            data: {
                skills:                allSkills,
                total_alumni_analysed: totalAlumni,
                filters_applied: { programme, graduation_year_from, graduation_year_to }
            }
        });
    } catch (err) {
        console.error('Skills gap error:', err.message);
        res.status(500).json({ success: false, message: 'Failed to fetch skills gap' });
    }
});

router.get('/employment-sectors', requireScope('read:analytics'), async (req, res) => {
    try {
        const conditions = ['u.is_email_verified = 1'];
        const params     = [];

        if (req.query.programme) {
            conditions.push('d.degree_name = ?');
            params.push(req.query.programme);
        }

        const [rows] = await pool.query(`
            SELECT COALESCE(eh.industry, 'Other') AS sector,
                   COUNT(DISTINCT u.id) AS alumni_count,
                   ROUND(
                       COUNT(DISTINCT u.id) * 100.0 /
                       (SELECT COUNT(DISTINCT id) FROM users WHERE is_email_verified = 1),
                       1
                   ) AS percentage
            FROM users u
            LEFT JOIN degrees d ON d.user_id = u.id
            LEFT JOIN employment_history eh ON eh.user_id = u.id
            WHERE ${conditions.join(' AND ')}
            GROUP BY eh.industry
            ORDER BY alumni_count DESC
            LIMIT 15
        `, params);

        res.json({ success: true, data: rows });
    } catch (err) {
        console.error('Employment sectors error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

router.get('/job-titles', requireScope('read:analytics'), async (req, res) => {
    try {
        const limit      = Math.min(Math.max(parseInt(req.query.limit) || 10, 1), 50);
        const conditions = ['u.is_email_verified = 1'];
        const params     = [];

        if (req.query.programme) {
            conditions.push('d.degree_name = ?');
            params.push(req.query.programme);
        }

        params.push(limit);

        const [rows] = await pool.query(`
            SELECT eh.job_title,
                   COUNT(DISTINCT u.id) AS alumni_count,
                   ROUND(
                       COUNT(DISTINCT u.id) * 100.0 /
                       (SELECT COUNT(DISTINCT id) FROM users WHERE is_email_verified = 1),
                       1
                   ) AS percentage
            FROM users u
            LEFT JOIN degrees d ON d.user_id = u.id
            JOIN employment_history eh ON eh.user_id = u.id
            WHERE ${conditions.join(' AND ')}
              AND eh.job_title IS NOT NULL
              AND eh.job_title != ''
            GROUP BY eh.job_title
            ORDER BY alumni_count DESC
            LIMIT ?
        `, params);

        res.json({ success: true, data: rows });
    } catch (err) {
        console.error('Job titles error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

router.get('/top-employers', requireScope('read:analytics'), async (req, res) => {
    try {
        const limit      = Math.min(Math.max(parseInt(req.query.limit) || 10, 1), 50);
        const conditions = ['u.is_email_verified = 1'];
        const params     = [];

        if (req.query.programme) {
            conditions.push('d.degree_name = ?');
            params.push(req.query.programme);
        }

        params.push(limit);

        const [rows] = await pool.query(`
            SELECT eh.employer_name AS employer,
                   COUNT(DISTINCT u.id) AS alumni_count
            FROM users u
            LEFT JOIN degrees d ON d.user_id = u.id
            JOIN employment_history eh ON eh.user_id = u.id
            WHERE ${conditions.join(' AND ')}
              AND eh.employer_name IS NOT NULL
              AND eh.employer_name != ''
            GROUP BY eh.employer_name
            ORDER BY alumni_count DESC
            LIMIT ?
        `, params);

        res.json({ success: true, data: rows });
    } catch (err) {
        console.error('Top employers error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

router.get('/certification-trends', requireScope('read:analytics'), async (req, res) => {
    try {
        const months     = Math.min(Math.max(parseInt(req.query.months) || 12, 3), 36);
        const conditions = [
            'u.is_email_verified = 1',
            'c.completion_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)'
        ];
        const params = [months];

        if (req.query.programme) {
            conditions.push('d.degree_name = ?');
            params.push(req.query.programme);
        }

        const [rows] = await pool.query(`
            SELECT DATE_FORMAT(c.completion_date, '%Y-%m') AS month,
                   c.certification_name,
                   c.issuing_organization,
                   COUNT(*) AS count
            FROM certifications c
            JOIN users u ON u.id = c.user_id
            LEFT JOIN degrees d ON d.user_id = u.id
            WHERE ${conditions.join(' AND ')}
            GROUP BY DATE_FORMAT(c.completion_date, '%Y-%m'), c.certification_name
            ORDER BY month ASC, count DESC
        `, params);

        const monthlyData = {};
        rows.forEach(row => {
            if (!monthlyData[row.month]) monthlyData[row.month] = [];
            monthlyData[row.month].push({
                name:  row.certification_name,
                org:   row.issuing_organization,
                count: row.count
            });
        });

        res.json({
            success: true,
            data: {
                monthly_breakdown: monthlyData,
                months_requested:  months,
                raw:               rows
            }
        });
    } catch (err) {
        console.error('Certification trends error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

router.get('/geographic-distribution', requireScope('read:analytics'), async (req, res) => {
    try {
        const conditions = ['u.is_email_verified = 1'];
        const params     = [];

        if (req.query.programme) {
            conditions.push('d.degree_name = ?');
            params.push(req.query.programme);
        }

        const [rows] = await pool.query(`
            SELECT COALESCE(
                       CONCAT(ap.city, ', ', ap.country),
                       ap.country,
                       ap.city,
                       'Unknown'
                   ) AS location,
                   ap.country,
                   ap.city,
                   COUNT(DISTINCT u.id) AS alumni_count
            FROM users u
            LEFT JOIN alumni_profiles ap ON ap.user_id = u.id
            LEFT JOIN degrees d ON d.user_id = u.id
            WHERE ${conditions.join(' AND ')}
            GROUP BY ap.city, ap.country
            ORDER BY alumni_count DESC
            LIMIT 30
        `, params);

        res.json({ success: true, data: rows });
    } catch (err) {
        console.error('Geographic error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

router.get('/alumni', requireScope('read:alumni'), async (req, res) => {
    try {
        const page   = Math.max(parseInt(req.query.page)  || 1, 1);
        const limit  = Math.min(Math.max(parseInt(req.query.limit) || 20, 1), 100);
        const offset = (page - 1) * limit;

        const conditions  = ['u.is_email_verified = 1'];
        const params      = [];
        const countParams = [];

        if (req.query.programme) {
            conditions.push('d.degree_name LIKE ?');
            params.push('%' + req.query.programme + '%');
            countParams.push('%' + req.query.programme + '%');
        }
        if (req.query.graduation_year) {
            const yr = parseInt(req.query.graduation_year);
            if (!isNaN(yr)) {
                conditions.push('YEAR(d.completion_date) = ?');
                params.push(yr);
                countParams.push(yr);
            }
        }
        if (req.query.industry) {
            conditions.push('eh.industry LIKE ?');
            params.push('%' + req.query.industry + '%');
            countParams.push('%' + req.query.industry + '%');
        }

        const whereClause = 'WHERE ' + conditions.join(' AND ');

        const [countRows] = await pool.query(`
            SELECT COUNT(DISTINCT u.id) AS total
            FROM users u
            LEFT JOIN degrees d ON d.user_id = u.id
            LEFT JOIN employment_history eh ON eh.user_id = u.id
            ${whereClause}
        `, countParams);

        const total = countRows[0].total;
        params.push(limit, offset);

        const [alumniRows] = await pool.query(`
            SELECT DISTINCT
                u.id, u.first_name, u.last_name,
                ap.biography, ap.linkedin_url, ap.profile_image,
                ap.city, ap.country,
                d.degree_name AS programme,
                d.institution,
                YEAR(d.completion_date) AS graduation_year,
                eh.job_title AS current_role,
                eh.employer_name AS current_employer,
                eh.industry AS industry_sector
            FROM users u
            LEFT JOIN alumni_profiles ap ON ap.user_id = u.id
            LEFT JOIN degrees d ON d.user_id = u.id
            LEFT JOIN employment_history eh ON eh.user_id = u.id
            ${whereClause}
            ORDER BY u.last_name ASC, u.first_name ASC
            LIMIT ? OFFSET ?
        `, params);

        res.json({
            success: true,
            data: {
                alumni: alumniRows,
                pagination: {
                    total,
                    page,
                    limit,
                    total_pages: Math.ceil(total / limit)
                }
            }
        });
    } catch (err) {
        console.error('Get alumni error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

router.get('/programmes', requireScope('read:alumni'), async (req, res) => {
    try {
        const [rows] = await pool.query(`
            SELECT DISTINCT d.degree_name AS programme
            FROM degrees d
            JOIN users u ON u.id = d.user_id
            WHERE u.is_email_verified = 1
              AND d.degree_name IS NOT NULL
              AND d.degree_name != ''
            ORDER BY d.degree_name ASC
        `);
        res.json({ success: true, data: rows.map(r => r.programme) });
    } catch (err) {
        console.error('Programmes error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

router.get('/industries', requireScope('read:alumni'), async (req, res) => {
    try {
        const [rows] = await pool.query(`
            SELECT DISTINCT eh.industry
            FROM employment_history eh
            JOIN users u ON u.id = eh.user_id
            WHERE u.is_email_verified = 1
              AND eh.industry IS NOT NULL
              AND eh.industry != ''
            ORDER BY eh.industry ASC
        `);
        res.json({ success: true, data: rows.map(r => r.industry) });
    } catch (err) {
        console.error('Industries error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

router.get('/api-usage', requireScope('read:analytics'), async (req, res) => {
    try {
        const days = Math.min(Math.max(parseInt(req.query.days) || 30, 1), 90);

        const [usageLogs] = await pool.query(`
            SELECT ak.client_name, ak.scopes,
                   COUNT(aal.id) AS total_requests,
                   MAX(aal.created_at) AS last_access
            FROM api_keys ak
            LEFT JOIN api_key_auth_logs aal
                   ON aal.api_key_id = ak.id
                  AND aal.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE ak.is_active = 1
            GROUP BY ak.id, ak.client_name, ak.scopes
            ORDER BY total_requests DESC
        `, [days]);

        const [recentActivity] = await pool.query(`
            SELECT ak.client_name, aal.event_type,
                   aal.ip_address, aal.details, aal.created_at
            FROM api_key_auth_logs aal
            JOIN api_keys ak ON ak.id = aal.api_key_id
            WHERE aal.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY aal.created_at DESC
            LIMIT 50
        `, [days]);

        res.json({
            success: true,
            data: {
                period_days:     days,
                usage_by_client: usageLogs,
                recent_activity: recentActivity
            }
        });
    } catch (err) {
        console.error('API usage error:', err.message);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

module.exports = router;