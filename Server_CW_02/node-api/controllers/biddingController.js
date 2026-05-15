// node-api/controllers/biddingController.js
// REPLACE ENTIRE FILE
// Now uses CW1's actual bids and daily_winners tables

const pool = require('../config/database');

/**
 * GET /api/bidding/featured
 * Get today's winner from CW1 daily_winners table
 */
async function getFeaturedAlumni(req, res) {
    try {
        // CW1 has daily_winners table with user_id, bid_id, display_date
        const [winners] = await pool.execute(
            `SELECT
                dw.id,
                dw.display_date,
                dw.winning_amount,
                u.id as alumni_id,
                CONCAT(u.first_name, ' ', u.last_name) as full_name,
                u.email,
                ap.city            as location_city,
                ap.country         as location_country,
                ap.profile_image,
                ap.linkedin_url,
                d.programme,
                d.graduation_year,
                d.university_name,
                eh.job_title,
                eh.company_name    as employer,
                eh.industry_sector
             FROM daily_winners dw
             JOIN users u ON dw.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             LEFT JOIN degrees d ON u.id = d.user_id
             LEFT JOIN employment_history eh ON u.id = eh.user_id AND eh.is_current = 1
             WHERE dw.is_active = 1
             ORDER BY dw.display_date DESC
             LIMIT 5`
        );

        const featured = await Promise.all(winners.map(async (w) => {
            const [certs] = await pool.execute(
                'SELECT certification_name FROM certifications WHERE user_id = ? LIMIT 5',
                [w.alumni_id]
            );
            const [courses] = await pool.execute(
                'SELECT course_name FROM professional_courses WHERE user_id = ? LIMIT 5',
                [w.alumni_id]
            );

            return {
                ...w,
                certifications: certs.map(c => c.certification_name),
                skills:         courses.map(c => c.course_name)
            };
        }));

        return res.json({ success: true, data: featured });

    } catch (error) {
        console.error('Get featured error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch featured alumni' });
    }
}

/**
 * GET /api/bidding/stats
 * Bidding statistics from CW1 bids table
 */
async function getBiddingStats(req, res) {
    try {
        // Total bids
        const [totalBids] = await pool.execute(
            'SELECT COUNT(*) as total FROM bids'
        );

        // Bids by status
        const [byStatus] = await pool.execute(
            `SELECT status, COUNT(*) as count
             FROM bids GROUP BY status`
        );

        // Recent bids
        const [recentBids] = await pool.execute(
            `SELECT b.id, b.amount, b.status, b.bid_date,
                    CONCAT(u.first_name, ' ', u.last_name) as alumni_name
             FROM bids b
             JOIN users u ON b.user_id = u.id
             ORDER BY b.created_at DESC
             LIMIT 10`
        );

        // Total winners
        const [winners] = await pool.execute(
            'SELECT COUNT(*) as total FROM daily_winners WHERE is_active = 1'
        );

        return res.json({
            success: true,
            data: {
                total_bids:   totalBids[0].total,
                by_status:    byStatus,
                recent_bids:  recentBids,
                total_winners: winners[0].total
            }
        });

    } catch (error) {
        console.error('Bidding stats error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch bidding stats' });
    }
}

/**
 * GET /api/bidding/alumni-of-day
 * Today's featured alumni from CW1 daily_winners
 */
async function getAlumniOfDay(req, res) {
    try {
        const today = new Date().toISOString().split('T')[0];

        const [rows] = await pool.execute(
            `SELECT
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as full_name,
                u.email,
                ap.city, ap.country, ap.profile_image, ap.biography,
                d.programme, d.graduation_year,
                eh.job_title, eh.company_name as employer, eh.industry_sector,
                dw.winning_amount, dw.display_date
             FROM daily_winners dw
             JOIN users u ON dw.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             LEFT JOIN degrees d ON u.id = d.user_id
             LEFT JOIN employment_history eh ON u.id = eh.user_id AND eh.is_current = 1
             WHERE dw.is_active = 1
             AND DATE(dw.display_date) = ?
             LIMIT 1`,
            [today]
        );

        if (rows.length === 0) {
            // Fall back to most recent winner if no winner today
            const [latest] = await pool.execute(
                `SELECT
                    u.id,
                    CONCAT(u.first_name, ' ', u.last_name) as full_name,
                    ap.city, ap.country, ap.profile_image,
                    d.programme, d.graduation_year,
                    eh.job_title, eh.company_name as employer
                 FROM daily_winners dw
                 JOIN users u ON dw.user_id = u.id
                 LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
                 LEFT JOIN degrees d ON u.id = d.user_id
                 LEFT JOIN employment_history eh ON u.id = eh.user_id AND eh.is_current = 1
                 WHERE dw.is_active = 1
                 ORDER BY dw.display_date DESC
                 LIMIT 1`
            );

            return res.json({
                success: true,
                data: latest[0] || null,
                note: 'Showing most recent winner'
            });
        }

        return res.json({ success: true, data: rows[0] });

    } catch (error) {
        console.error('Alumni of day error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch alumni of day' });
    }
}

module.exports = {
    getFeaturedAlumni,
    getBiddingStats,
    getAlumniOfDay
};