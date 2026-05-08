/**
 * Analytics Controller - CW2
 * All data comes from the same alumni_platform database as CW1
 */

const pool = require('../config/database');

// ================================================================
// OVERVIEW
// ================================================================
exports.getOverview = async (req, res) => {
    try {
        const [[totalAlumni]] = await pool.query(
            'SELECT COUNT(*) as count FROM users WHERE is_active = 1'
        );
        const [[completeProfiles]] = await pool.query(
            'SELECT COUNT(*) as count FROM alumni_profiles WHERE is_profile_complete = 1'
        );
        const [[totalCerts]] = await pool.query(
            'SELECT COUNT(*) as count FROM certifications'
        );
        const [[totalDegrees]] = await pool.query(
            'SELECT COUNT(*) as count FROM degrees'
        );
        const [[totalJobs]] = await pool.query(
            'SELECT COUNT(*) as count FROM employment_history'
        );
        const [[totalCourses]] = await pool.query(
            'SELECT COUNT(*) as count FROM professional_courses'
        );
        const [[totalLicences]] = await pool.query(
            'SELECT COUNT(*) as count FROM licences'
        );
        const [[newThisMonth]] = await pool.query(
            `SELECT COUNT(*) as count FROM users
             WHERE MONTH(created_at) = MONTH(NOW())
             AND YEAR(created_at) = YEAR(NOW())`
        );

        res.status(200).json({
            status: 'success',
            data: {
                total_alumni:        totalAlumni.count,
                complete_profiles:   completeProfiles.count,
                total_certifications: totalCerts.count,
                total_degrees:       totalDegrees.count,
                total_jobs:          totalJobs.count,
                total_courses:       totalCourses.count,
                total_licences:      totalLicences.count,
                new_this_month:      newThisMonth.count,
            },
        });
    } catch (err) {
        console.error('Overview error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

// ================================================================
// EMPLOYMENT BY INDUSTRY
// ================================================================
exports.getEmployment = async (req, res) => {
    try {
        const { programme, year_from, year_to } = req.query;

        // By company
        const [byCompany] = await pool.query(
            `SELECT company_name,
                    COUNT(*) as employee_count,
                    SUM(CASE WHEN is_current=1 THEN 1 ELSE 0 END) as current_count
             FROM employment_history
             GROUP BY company_name
             ORDER BY employee_count DESC
             LIMIT 20`
        );

        // Current vs past
        const [[currentVsPast]] = await pool.query(
            `SELECT
                SUM(CASE WHEN is_current=1 THEN 1 ELSE 0 END) as current_jobs,
                SUM(CASE WHEN is_current=0 THEN 1 ELSE 0 END) as past_jobs
             FROM employment_history`
        );

        // Jobs by year
        const [jobsByYear] = await pool.query(
            `SELECT YEAR(start_date) as year, COUNT(*) as count
             FROM employment_history
             WHERE start_date IS NOT NULL
             AND YEAR(start_date) >= 2015
             GROUP BY YEAR(start_date)
             ORDER BY year ASC`
        );

        res.status(200).json({
            status: 'success',
            data: {
                by_company: byCompany,
                current_vs_past: currentVsPast,
                jobs_by_year: jobsByYear,
            },
        });
    } catch (err) {
        console.error('Employment error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

// ================================================================
// SKILLS GAP
// ================================================================
exports.getSkillsGap = async (req, res) => {
    try {
        const [topCerts] = await pool.query(
            `SELECT certification_name, issuing_organization,
                    COUNT(*) as count
             FROM certifications
             GROUP BY certification_name, issuing_organization
             ORDER BY count DESC
             LIMIT 15`
        );

        const [topCourses] = await pool.query(
            `SELECT course_name, provider, COUNT(*) as count
             FROM professional_courses
             GROUP BY course_name, provider
             ORDER BY count DESC
             LIMIT 15`
        );

        const [topLicences] = await pool.query(
            `SELECT licence_name, issuing_body, COUNT(*) as count
             FROM licences
             GROUP BY licence_name, issuing_body
             ORDER BY count DESC
             LIMIT 10`
        );

        const [certsByYear] = await pool.query(
            `SELECT YEAR(completion_date) as year, COUNT(*) as count
             FROM certifications
             WHERE completion_date IS NOT NULL
             AND YEAR(completion_date) >= 2018
             GROUP BY YEAR(completion_date)
             ORDER BY year ASC`
        );

        const [coursesByYear] = await pool.query(
            `SELECT YEAR(completion_date) as year, COUNT(*) as count
             FROM professional_courses
             WHERE completion_date IS NOT NULL
             AND YEAR(completion_date) >= 2018
             GROUP BY YEAR(completion_date)
             ORDER BY year ASC`
        );

        const [topOrgs] = await pool.query(
            `SELECT issuing_organization as organization,
                    COUNT(*) as count
             FROM certifications
             GROUP BY issuing_organization
             ORDER BY count DESC
             LIMIT 10`
        );

        res.status(200).json({
            status: 'success',
            data: {
                top_certifications: topCerts,
                top_courses:        topCourses,
                top_licences:       topLicences,
                certification_trend: certsByYear,
                course_trend:       coursesByYear,
                top_organizations:  topOrgs,
            },
        });
    } catch (err) {
        console.error('Skills gap error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

// ================================================================
// JOB TITLES
// ================================================================
exports.getJobTitles = async (req, res) => {
    try {
        const limit = parseInt(req.query.limit) || 15;

        const [allTitles] = await pool.query(
            `SELECT job_title, COUNT(*) as count,
                    SUM(CASE WHEN is_current=1 THEN 1 ELSE 0 END) as current_count
             FROM employment_history
             GROUP BY job_title
             ORDER BY count DESC
             LIMIT ?`,
            [limit]
        );

        const [currentTitles] = await pool.query(
            `SELECT job_title, COUNT(*) as count
             FROM employment_history
             WHERE is_current = 1
             GROUP BY job_title
             ORDER BY count DESC
             LIMIT ?`,
            [limit]
        );

        res.status(200).json({
            status: 'success',
            data: {
                all_time:     allTitles,
                current_only: currentTitles,
            },
        });
    } catch (err) {
        console.error('Job titles error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

// ================================================================
// TOP EMPLOYERS
// ================================================================
exports.getEmployers = async (req, res) => {
    try {
        const limit = parseInt(req.query.limit) || 10;

        const [employers] = await pool.query(
            `SELECT company_name,
                    COUNT(DISTINCT user_id) as alumni_count,
                    COUNT(*) as total_roles,
                    SUM(CASE WHEN is_current=1 THEN 1 ELSE 0 END) as current_employees
             FROM employment_history
             GROUP BY company_name
             ORDER BY alumni_count DESC
             LIMIT ?`,
            [limit]
        );

        res.status(200).json({
            status: 'success',
            data: { top_employers: employers },
        });
    } catch (err) {
        console.error('Employers error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

// ================================================================
// GEOGRAPHIC
// ================================================================
exports.getGeographic = async (req, res) => {
    try {
        const [byCountry] = await pool.query(
            `SELECT country, COUNT(*) as count
             FROM alumni_profiles
             WHERE country IS NOT NULL AND country != ''
             GROUP BY country
             ORDER BY count DESC
             LIMIT 20`
        );

        const [byCity] = await pool.query(
            `SELECT city, country, COUNT(*) as count
             FROM alumni_profiles
             WHERE city IS NOT NULL AND city != ''
             GROUP BY city, country
             ORDER BY count DESC
             LIMIT 20`
        );

        const [ukVsIntl] = await pool.query(
            `SELECT
                CASE
                    WHEN country IN ('United Kingdom','UK','England',
                                     'Scotland','Wales','Northern Ireland')
                    THEN 'UK'
                    WHEN country IS NULL OR country = '' THEN 'Unknown'
                    ELSE 'International'
                END as region,
                COUNT(*) as count
             FROM alumni_profiles
             GROUP BY region`
        );

        res.status(200).json({
            status: 'success',
            data: {
                by_country:          byCountry,
                by_city:             byCity,
                uk_vs_international: ukVsIntl,
            },
        });
    } catch (err) {
        console.error('Geographic error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};

// ================================================================
// CERTIFICATION TRENDS
// ================================================================
exports.getCertificationTrends = async (req, res) => {
    try {
        const [monthlyTrend] = await pool.query(
            `SELECT DATE_FORMAT(completion_date,'%Y-%m') as month,
                    COUNT(*) as certifications_count
             FROM certifications
             WHERE completion_date >= DATE_SUB(NOW(), INTERVAL 24 MONTH)
             GROUP BY DATE_FORMAT(completion_date,'%Y-%m')
             ORDER BY month ASC`
        );

        const [courseTrend] = await pool.query(
            `SELECT DATE_FORMAT(completion_date,'%Y-%m') as month,
                    COUNT(*) as courses_count
             FROM professional_courses
             WHERE completion_date >= DATE_SUB(NOW(), INTERVAL 24 MONTH)
             GROUP BY DATE_FORMAT(completion_date,'%Y-%m')
             ORDER BY month ASC`
        );

        const [programmes] = await pool.query(
            `SELECT field_of_study as programme,
                    COUNT(DISTINCT user_id) as alumni_count
             FROM degrees
             GROUP BY field_of_study
             ORDER BY alumni_count DESC
             LIMIT 15`
        );

        res.status(200).json({
            status: 'success',
            data: {
                monthly_certifications: monthlyTrend,
                monthly_courses:        courseTrend,
                degree_programmes:      programmes,
            },
        });
    } catch (err) {
        console.error('Trends error:', err);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
};