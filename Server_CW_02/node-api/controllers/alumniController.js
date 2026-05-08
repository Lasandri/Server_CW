// node-api/controllers/alumniController.js

const pool = require('../config/database');

/**
 * GET /api/alumni
 * Get alumni list with filters
 * Query params: programme, graduation_year, industry_sector, page, limit
 */
async function getAlumni(req, res) {
    try {
        const {
            programme,
            graduation_year,
            industry_sector,
            search,
            page = 1,
            limit = 12
        } = req.query;

        // Build dynamic WHERE clause
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

        if (industry_sector && industry_sector !== 'all') {
            conditions.push('industry_sector = ?');
            params.push(industry_sector);
        }

        if (search) {
            conditions.push('(full_name LIKE ? OR job_title LIKE ? OR employer LIKE ?)');
            const searchTerm = `%${search}%`;
            params.push(searchTerm, searchTerm, searchTerm);
        }

        const whereClause = conditions.length > 0
            ? 'WHERE ' + conditions.join(' AND ')
            : '';

        // Count total for pagination
        const [countResult] = await pool.execute(
            `SELECT COUNT(*) as total FROM alumni ${whereClause}`,
            params
        );
        const total = countResult[0].total;

        // Get paginated results
        const offset = (parseInt(page) - 1) * parseInt(limit);
        const [rows] = await pool.execute(
            `SELECT id, full_name, programme, graduation_year, 
                    industry_sector, job_title, employer, 
                    location_city, location_country, skills, 
                    certifications, is_featured
             FROM alumni ${whereClause}
             ORDER BY is_featured DESC, graduation_year DESC
             LIMIT ? OFFSET ?`,
            [...params, parseInt(limit), offset]
        );

        // Parse JSON fields
        const alumni = rows.map(row => ({
            ...row,
            skills: row.skills ? JSON.parse(row.skills) : [],
            certifications: row.certifications ? JSON.parse(row.certifications) : []
        }));

        return res.json({
            success: true,
            data: alumni,
            pagination: {
                total,
                page: parseInt(page),
                limit: parseInt(limit),
                total_pages: Math.ceil(total / parseInt(limit))
            }
        });

    } catch (error) {
        console.error('Get alumni error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch alumni'
        });
    }
}

/**
 * GET /api/alumni/filters
 * Get all unique filter options
 */
async function getFilterOptions(req, res) {
    try {
        const [programmes] = await pool.execute(
            'SELECT DISTINCT programme FROM alumni WHERE profile_visible=1 ORDER BY programme'
        );

        const [years] = await pool.execute(
            'SELECT DISTINCT graduation_year FROM alumni WHERE profile_visible=1 ORDER BY graduation_year DESC'
        );

        const [sectors] = await pool.execute(
            'SELECT DISTINCT industry_sector FROM alumni WHERE profile_visible=1 ORDER BY industry_sector'
        );

        return res.json({
            success: true,
            data: {
                programmes: programmes.map(r => r.programme),
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
 * Get single alumni by ID
 */
async function getAlumniById(req, res) {
    try {
        const [rows] = await pool.execute(
            `SELECT id, full_name, programme, graduation_year,
                    industry_sector, job_title, employer,
                    location_city, location_country, skills, certifications
             FROM alumni WHERE id = ? AND profile_visible = 1`,
            [req.params.id]
        );

        if (rows.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Alumni not found'
            });
        }

        const alumni = {
            ...rows[0],
            skills: rows[0].skills ? JSON.parse(rows[0].skills) : [],
            certifications: rows[0].certifications ? JSON.parse(rows[0].certifications) : []
        };

        return res.json({ success: true, data: alumni });

    } catch (error) {
        console.error('Get alumni by id error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch alumni'
        });
    }
}

module.exports = { getAlumni, getFilterOptions, getAlumniById };