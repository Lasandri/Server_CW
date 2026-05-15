// node-api/controllers/securityController.js
// REPLACE ENTIRE FILE
// Uses CW1's university_api_keys and university_api_key_secrets tables

const pool   = require('../config/database');
const crypto = require('crypto');

/**
 * GET /api/security/keys
 * Get university API keys from CW1 university_api_keys table
 */
async function getAllKeys(req, res) {
    try {
        const [keys] = await pool.execute(
            `SELECT
                uak.id,
                uak.key_hash,
                uak.key_prefix,
                uak.client_name,
                uak.client_type,
                uak.permissions,
                uak.is_active,
                uak.is_revoked,
                uak.total_requests,
                uak.rate_limit,
                uak.last_used_at,
                uak.expires_at,
                uak.created_at,
                uu.first_name,
                uu.last_name,
                uu.email
             FROM university_api_keys uak
             LEFT JOIN university_users uu ON uak.user_id = uu.id
             ORDER BY uak.created_at DESC`
        );

        const keysData = keys.map(k => ({
            ...k,
            permissions: k.permissions
                ? (typeof k.permissions === 'string' ? JSON.parse(k.permissions) : k.permissions)
                : []
        }));

        return res.json({ success: true, data: keysData });

    } catch (error) {
        console.error('Get all keys error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch keys' });
    }
}

/**
 * GET /api/security/stats
 * Usage statistics from CW1 api_usage_logs
 */
async function getUsageStats(req, res) {
    try {
        // Stats from CW1 api_usage_logs table
        const [totalStats] = await pool.execute(
            `SELECT
                COUNT(*) as total_requests,
                SUM(CASE WHEN response_code = 200 THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN response_code = 403 THEN 1 ELSE 0 END) as forbidden,
                SUM(CASE WHEN response_code = 401 THEN 1 ELSE 0 END) as unauthorized
             FROM api_usage_logs`
        );

        // Per key usage
        const [keyStats] = await pool.execute(
            `SELECT
                ak.client_name,
                ak.client_type,
                ak.total_requests,
                ak.last_used_at,
                ak.is_active,
                COUNT(ul.id) as log_count
             FROM university_api_keys ak
             LEFT JOIN api_usage_logs ul ON ak.id = ul.api_key_id
             GROUP BY ak.id
             ORDER BY ak.total_requests DESC`
        );

        // Hourly stats last 24h from api_key_auth_logs
        const [hourlyStats] = await pool.execute(
            `SELECT HOUR(created_at) as hour, COUNT(*) as requests
             FROM api_key_auth_logs
             WHERE created_at >= NOW() - INTERVAL 24 HOUR
             GROUP BY HOUR(created_at)
             ORDER BY hour ASC`
        );

        // Top endpoints from api_usage_logs
        const [topEndpoints] = await pool.execute(
            `SELECT endpoint, method, COUNT(*) as hits
             FROM api_usage_logs
             GROUP BY endpoint, method
             ORDER BY hits DESC
             LIMIT 10`
        );

        return res.json({
            success: true,
            data: {
                totals:        totalStats[0],
                key_stats:     keyStats,
                hourly_stats:  hourlyStats,
                top_endpoints: topEndpoints
            }
        });

    } catch (error) {
        console.error('Get stats error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch stats' });
    }
}

/**
 * GET /api/security/login-logs
 * From CW1 user_sessions table
 */
async function getLoginLogs(req, res) {
    try {
        const limit = parseInt(req.query.limit) || 50;

        const [logs] = await pool.execute(
            `SELECT
                us.id,
                us.ip_address,
                us.user_agent,
                us.login_at,
                us.logout_at,
                us.is_active,
                CONCAT(u.first_name, ' ', u.last_name) as full_name,
                u.email
             FROM user_sessions us
             JOIN users u ON us.user_id = u.id
             ORDER BY us.login_at DESC
             LIMIT ?`,
            [limit]
        );

        return res.json({ success: true, data: logs });

    } catch (error) {
        console.error('Login logs error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch login logs' });
    }
}

/**
 * GET /api/security/access-logs
 * From CW1 api_key_auth_logs
 */
async function getAccessLogs(req, res) {
    try {
        const limit = parseInt(req.query.limit) || 100;

        const [logs] = await pool.execute(
            `SELECT
                al.id,
                al.success,
                al.ip_address,
                al.created_at,
                uak.client_name,
                uak.client_type
             FROM api_key_auth_logs al
             LEFT JOIN university_api_keys uak ON al.api_key_id = uak.id
             ORDER BY al.created_at DESC
             LIMIT ?`,
            [limit]
        );

        return res.json({ success: true, data: logs });

    } catch (error) {
        console.error('Access logs error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch access logs' });
    }
}

/**
 * GET /api/security/alumni-of-day
 * AR App endpoint - reads from CW1 daily_winners
 */
async function getAlumniOfDay(req, res) {
    try {
        const today = new Date().toISOString().split('T')[0];

        const [rows] = await pool.execute(
            `SELECT
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as full_name,
                ap.city, ap.country, ap.profile_image,
                d.programme, d.graduation_year,
                eh.job_title, eh.company_name as employer,
                eh.industry_sector,
                dw.display_date
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

        return res.json({
            success: true,
            data: rows[0] || null,
            accessed_by: req.clientType
        });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed' });
    }
}

// Keep create/toggle/delete using CW2's own api_keys table for CW2 auth
async function createKey(req, res) {
    return res.json({ success: false, message: 'Manage API keys in CW1 Developer section' });
}

async function toggleKey(req, res) {
    return res.json({ success: false, message: 'Manage API keys in CW1 Developer section' });
}

async function deleteKey(req, res) {
    return res.json({ success: false, message: 'Manage API keys in CW1 Developer section' });
}

async function getKeyById(req, res) {
    return res.json({ success: false, message: 'Use getAllKeys endpoint' });
}

async function getKeyStats(req, res) {
    return res.json({ success: false, message: 'Use getUsageStats endpoint' });
}

module.exports = {
    getAllKeys,
    getKeyById,
    createKey,
    toggleKey,
    deleteKey,
    getUsageStats,
    getKeyStats,
    getLoginLogs,
    getAccessLogs,
    getAlumniOfDay
};