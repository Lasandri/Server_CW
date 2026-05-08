// node-api/controllers/analyticsController.js

const pool = require('../config/database');

/**
 * GET /api/analytics/overview
 * Dashboard summary stats
 */
async function getOverview(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let conditions = ['profile_visible = 1'];
        let params = [];

        if (programme && programme !== 'all') {
            conditions.push('programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            conditions.push('graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        const where = 'WHERE ' + conditions.join(' AND ');

        const [totalResult] = await pool.execute(
            `SELECT COUNT(*) as total FROM alumni ${where}`, params
        );

        const [sectorResult] = await pool.execute(
            `SELECT COUNT(DISTINCT industry_sector) as count FROM alumni ${where}`, params
        );

        const [programmeResult] = await pool.execute(
            `SELECT COUNT(DISTINCT programme) as count FROM alumni ${where}`, params
        );

        const [yearResult] = await pool.execute(
            `SELECT COUNT(DISTINCT graduation_year) as count FROM alumni ${where}`, params
        );

        return res.json({
            success: true,
            data: {
                total_alumni: totalResult[0].total,
                total_sectors: sectorResult[0].count,
                total_programmes: programmeResult[0].count,
                total_years: yearResult[0].count
            }
        });

    } catch (error) {
        console.error('Overview error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch overview' });
    }
}

/**
 * GET /api/analytics/employment-by-sector
 * Alumni count per industry sector
 */
async function getEmploymentBySector(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let conditions = ['profile_visible = 1'];
        let params = [];

        if (programme && programme !== 'all') {
            conditions.push('programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            conditions.push('graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        const where = 'WHERE ' + conditions.join(' AND ');

        const [rows] = await pool.execute(
            `SELECT industry_sector, COUNT(*) as count
             FROM alumni ${where}
             GROUP BY industry_sector
             ORDER BY count DESC`,
            params
        );

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Employment by sector error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch data' });
    }
}

/**
 * GET /api/analytics/skills-gap
 * Most common post-graduation skills (curriculum gap analysis)
 */
async function getSkillsGap(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let conditions = ['profile_visible = 1', 'skills IS NOT NULL'];
        let params = [];

        if (programme && programme !== 'all') {
            conditions.push('programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            conditions.push('graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        const where = 'WHERE ' + conditions.join(' AND ');

        const [rows] = await pool.execute(
            `SELECT skills FROM alumni ${where}`, params
        );

        // Count skill frequency
        const skillCount = {};
        rows.forEach(row => {
            try {
                const skills = JSON.parse(row.skills);
                skills.forEach(skill => {
                    skillCount[skill] = (skillCount[skill] || 0) + 1;
                });
            } catch (e) {}
        });

        // Sort and get top 15
        const sorted = Object.entries(skillCount)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 15)
            .map(([skill, count]) => ({ skill, count }));

        return res.json({ success: true, data: sorted });

    } catch (error) {
        console.error('Skills gap error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch skills data' });
    }
}

/**
 * GET /api/analytics/top-employers
 */
async function getTopEmployers(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let conditions = ['profile_visible = 1'];
        let params = [];

        if (programme && programme !== 'all') {
            conditions.push('programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            conditions.push('graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        const where = 'WHERE ' + conditions.join(' AND ');

        const [rows] = await pool.execute(
            `SELECT employer, COUNT(*) as count
             FROM alumni ${where}
             GROUP BY employer
             ORDER BY count DESC
             LIMIT 10`,
            params
        );

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Top employers error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch employers' });
    }
}

/**
 * GET /api/analytics/job-titles
 */
async function getTopJobTitles(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let conditions = ['profile_visible = 1'];
        let params = [];

        if (programme && programme !== 'all') {
            conditions.push('programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            conditions.push('graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        const where = 'WHERE ' + conditions.join(' AND ');

        const [rows] = await pool.execute(
            `SELECT job_title, COUNT(*) as count
             FROM alumni ${where}
             GROUP BY job_title
             ORDER BY count DESC
             LIMIT 10`,
            params
        );

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Job titles error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch job titles' });
    }
}

/**
 * GET /api/analytics/geographic
 */
async function getGeographic(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let conditions = ['profile_visible = 1'];
        let params = [];

        if (programme && programme !== 'all') {
            conditions.push('programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            conditions.push('graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        const where = 'WHERE ' + conditions.join(' AND ');

        const [rows] = await pool.execute(
            `SELECT location_city, location_country, COUNT(*) as count
             FROM alumni ${where}
             GROUP BY location_city, location_country
             ORDER BY count DESC
             LIMIT 10`,
            params
        );

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Geographic error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch geographic data' });
    }
}

/**
 * GET /api/analytics/graduation-trends
 */
async function getGraduationTrends(req, res) {
    try {
        const { programme } = req.query;

        let conditions = ['profile_visible = 1'];
        let params = [];

        if (programme && programme !== 'all') {
            conditions.push('programme = ?');
            params.push(programme);
        }

        const where = 'WHERE ' + conditions.join(' AND ');

        const [rows] = await pool.execute(
            `SELECT graduation_year, COUNT(*) as count
             FROM alumni ${where}
             GROUP BY graduation_year
             ORDER BY graduation_year ASC`,
            params
        );

        return res.json({ success: true, data: rows });

    } catch (error) {
        console.error('Graduation trends error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch trends' });
    }
}

/**
 * GET /api/analytics/certifications
 */
async function getCertifications(req, res) {
    try {
        const { programme, graduation_year } = req.query;

        let conditions = ['profile_visible = 1', 'certifications IS NOT NULL'];
        let params = [];

        if (programme && programme !== 'all') {
            conditions.push('programme = ?');
            params.push(programme);
        }
        if (graduation_year && graduation_year !== 'all') {
            conditions.push('graduation_year = ?');
            params.push(parseInt(graduation_year));
        }

        const where = 'WHERE ' + conditions.join(' AND ');

        const [rows] = await pool.execute(
            `SELECT certifications FROM alumni ${where}`, params
        );

        const certCount = {};
        rows.forEach(row => {
            try {
                const certs = JSON.parse(row.certifications);
                certs.forEach(cert => {
                    certCount[cert] = (certCount[cert] || 0) + 1;
                });
            } catch (e) {}
        });

        const sorted = Object.entries(certCount)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 10)
            .map(([cert, count]) => ({ cert, count }));

        return res.json({ success: true, data: sorted });

    } catch (error) {
        console.error('Certifications error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch certifications' });
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
    getCertifications
};