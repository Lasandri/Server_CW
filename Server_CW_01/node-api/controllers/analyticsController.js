const pool = require('../config/database');

function buildDegreeJoin(filters, baseAlias) {
    let joins  = '';
    let wheres = [];

    if (filters.programme) {
        joins  += ` LEFT JOIN degrees d_prog ON d_prog.user_id = ${baseAlias}.id`;
        wheres.push(`d_prog.degree_name LIKE '%${filters.programme.replace(/'/g, "''")}%'`);
    }
    if (filters.graduation_year) {
        const alias = filters.programme ? 'd_prog' : 'd_year';
        if (!filters.programme) {
            joins += ` LEFT JOIN degrees d_year ON d_year.user_id = ${baseAlias}.id`;
        }
        wheres.push(`YEAR(${alias}.completion_date) = ${parseInt(filters.graduation_year)}`);
    }

    return { joins, wheres };
}

exports.getAlumni = async (req, res) => {
    try {
        const { programme, graduation_year, industry } = req.query;
        const filters = { programme, graduation_year, industry };

        let sql = `
            SELECT DISTINCT u.id, u.first_name, u.last_name, u.email,
                   ap.biography, ap.city, ap.country, ap.linkedin_url,
                   ap.profile_image, ap.is_profile_complete
            FROM users u
            LEFT JOIN alumni_profiles ap ON ap.user_id = u.id
        `;

        const wheres = ['u.is_active = 1'];
        const params = [];

        if (programme) {
            sql += ' LEFT JOIN degrees dp ON dp.user_id = u.id';
            sql += ' AND dp.degree_name LIKE ?';
            params.push('%' + programme + '%');
        }
        if (graduation_year) {
            const alias = programme ? 'dp' : 'dy';
            if (!programme) sql += ' LEFT JOIN degrees dy ON dy.user_id = u.id';
            wheres.push(`YEAR(${alias}.completion_date) = ?`);
            params.push(parseInt(graduation_year));
        }
        if (industry) {
            sql += ' LEFT JOIN employment_history ehi ON ehi.user_id = u.id';
            wheres.push('ehi.industry LIKE ?');
            params.push('%' + industry + '%');
        }

        sql += ' WHERE ' + wheres.join(' AND ') + ' ORDER BY u.first_name ASC';

        const [rows] = await pool.query(sql, params);

        res.status(200).json({
            status: 'success',
            count: rows.length,
            data: rows,
        });
    } catch (err) {
        console.error('getAlumni error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getAlumniById = async (req, res) => {
    try {
        const id = parseInt(req.params.id);

        const [users] = await pool.query(
            `SELECT u.id, u.first_name, u.last_name, u.email,
                    ap.biography, ap.city, ap.country, ap.linkedin_url, ap.profile_image
             FROM users u
             LEFT JOIN alumni_profiles ap ON ap.user_id = u.id
             WHERE u.id = ?`,
            [id]
        );

        if (users.length === 0) {
            return res.status(404).json({ status: 'error', message: 'Alumni not found.' });
        }

        const [degrees]  = await pool.query('SELECT * FROM degrees WHERE user_id = ?', [id]);
        const [certs]    = await pool.query('SELECT * FROM certifications WHERE user_id = ?', [id]);
        const [licences] = await pool.query('SELECT * FROM licences WHERE user_id = ?', [id]);
        const [courses]  = await pool.query('SELECT * FROM professional_courses WHERE user_id = ?', [id]);
        const [employment] = await pool.query('SELECT * FROM employment_history WHERE user_id = ? ORDER BY start_date DESC', [id]);

        res.status(200).json({
            status: 'success',
            data: {
                profile:        users[0],
                degrees,
                certifications: certs,
                licences,
                courses,
                employment,
            },
        });
    } catch (err) {
        console.error('getAlumniById error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getSkillsGap = async (req, res) => {
    try {
        const { programme, graduation_year } = req.query;

        const [certs] = await pool.query(
            `SELECT c.certification_name AS skill, COUNT(*) AS count
             FROM certifications c
             JOIN users u ON u.id = c.user_id
             GROUP BY c.certification_name
             ORDER BY count DESC LIMIT 15`
        );

        const [courses] = await pool.query(
            `SELECT pc.course_name AS skill, COUNT(*) AS count
             FROM professional_courses pc
             JOIN users u ON u.id = pc.user_id
             GROUP BY pc.course_name
             ORDER BY count DESC LIMIT 15`
        );

        const combined = {};
        [...certs, ...courses].forEach(r => {
            combined[r.skill] = (combined[r.skill] || 0) + parseInt(r.count);
        });

        const sorted = Object.entries(combined)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 15)
            .map(([skill, count]) => ({ skill, count }));

        res.status(200).json({ status: 'success', data: sorted });
    } catch (err) {
        console.error('getSkillsGap error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getEmployment = async (req, res) => {
    try {
        const [rows] = await pool.query(
            `SELECT eh.industry, COUNT(DISTINCT eh.user_id) AS count
             FROM employment_history eh
             WHERE eh.industry IS NOT NULL AND eh.industry != ''
             GROUP BY eh.industry
             ORDER BY count DESC LIMIT 12`
        );
        res.status(200).json({ status: 'success', data: rows });
    } catch (err) {
        console.error('getEmployment error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getJobTitles = async (req, res) => {
    try {
        const [rows] = await pool.query(
            `SELECT eh.job_title, COUNT(*) AS count
             FROM employment_history eh
             WHERE eh.job_title IS NOT NULL AND eh.job_title != ''
             GROUP BY eh.job_title
             ORDER BY count DESC LIMIT 10`
        );
        res.status(200).json({ status: 'success', data: rows });
    } catch (err) {
        console.error('getJobTitles error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getEmployers = async (req, res) => {
    try {
        const limit = parseInt(req.query.limit) || 10;
        const [rows] = await pool.query(
            `SELECT eh.employer_name, COUNT(*) AS count
             FROM employment_history eh
             WHERE eh.employer_name IS NOT NULL AND eh.employer_name != ''
             GROUP BY eh.employer_name
             ORDER BY count DESC LIMIT ?`,
            [limit]
        );
        res.status(200).json({ status: 'success', data: rows });
    } catch (err) {
        console.error('getEmployers error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getGeographic = async (req, res) => {
    try {
        const [rows] = await pool.query(
            `SELECT ap.country, ap.city, COUNT(DISTINCT u.id) AS count
             FROM alumni_profiles ap
             JOIN users u ON u.id = ap.user_id
             WHERE ap.country IS NOT NULL AND ap.country != ''
             GROUP BY ap.country
             ORDER BY count DESC LIMIT 20`
        );
        res.status(200).json({ status: 'success', data: rows });
    } catch (err) {
        console.error('getGeographic error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getCertTrends = async (req, res) => {
    try {
        const currentYear = new Date().getFullYear();
        const [rows] = await pool.query(
            `SELECT YEAR(c.completion_date) AS year, COUNT(*) AS count
             FROM certifications c
             WHERE c.completion_date IS NOT NULL
             AND YEAR(c.completion_date) >= ?
             GROUP BY YEAR(c.completion_date)
             ORDER BY year ASC`,
            [currentYear - 5]
        );
        res.status(200).json({ status: 'success', data: rows });
    } catch (err) {
        console.error('getCertTrends error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getTopCerts = async (req, res) => {
    try {
        const [rows] = await pool.query(
            `SELECT c.certification_name, c.issuing_organization, COUNT(*) AS count
             FROM certifications c
             GROUP BY c.certification_name
             ORDER BY count DESC LIMIT 10`
        );
        res.status(200).json({ status: 'success', data: rows });
    } catch (err) {
        console.error('getTopCerts error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getTopCourses = async (req, res) => {
    try {
        const [rows] = await pool.query(
            `SELECT pc.course_name, pc.provider, COUNT(*) AS count
             FROM professional_courses pc
             GROUP BY pc.course_name
             ORDER BY count DESC LIMIT 10`
        );
        res.status(200).json({ status: 'success', data: rows });
    } catch (err) {
        console.error('getTopCourses error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

exports.getSummary = async (req, res) => {
    try {
        const [[{ total_alumni }]] = await pool.query('SELECT COUNT(*) AS total_alumni FROM users');
        const [[{ employed }]] = await pool.query('SELECT COUNT(DISTINCT user_id) AS employed FROM employment_history');
        const [[{ total_certs }]] = await pool.query('SELECT COUNT(*) AS total_certs FROM certifications');
        const [[{ total_courses }]] = await pool.query('SELECT COUNT(*) AS total_courses FROM professional_courses');
        const [[{ complete_profiles }]] = await pool.query('SELECT COUNT(*) AS complete_profiles FROM alumni_profiles WHERE is_profile_complete = 1');

        res.status(200).json({
            status: 'success',
            data: {
                total_alumni,
                employed_alumni: employed,
                total_certs,
                total_courses,
                complete_profiles,
            },
        });
    } catch (err) {
        console.error('getSummary error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};