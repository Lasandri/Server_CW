// node-api/controllers/securityController.js

const pool   = require('../config/database');
const crypto = require('crypto');

/**
 * GET /api/security/keys
 * Get all API keys with usage stats
 */
async function getAllKeys(req, res) {
    try {
        const [keys] = await pool.execute(
            `SELECT id, key_name, 
                    CONCAT(LEFT(api_key, 12), '...') as api_key_preview,
                    client_type, permissions, is_active,
                    usage_count, last_used_at, expires_at, created_at
             FROM api_keys
             ORDER BY created_at DESC`
        );

        // Parse permissions JSON for each key
        const keysData = keys.map(k => ({
            ...k,
            permissions: JSON.parse(k.permissions)
        }));

        return res.json({ success: true, data: keysData });

    } catch (error) {
        console.error('Get all keys error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch keys' });
    }
}

/**
 * GET /api/security/keys/:id
 */
async function getKeyById(req, res) {
    try {
        const [rows] = await pool.execute(
            `SELECT id, key_name, api_key, client_type, permissions,
                    is_active, usage_count, last_used_at, expires_at, created_at
             FROM api_keys WHERE id = ?`,
            [req.params.id]
        );

        if (rows.length === 0) {
            return res.status(404).json({ success: false, message: 'Key not found' });
        }

        const key = { ...rows[0], permissions: JSON.parse(rows[0].permissions) };
        return res.json({ success: true, data: key });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed to fetch key' });
    }
}

/**
 * POST /api/security/keys
 * Create a new scoped API key
 * Body: { key_name, client_type, permissions, expires_at }
 */
async function createKey(req, res) {
    try {
        const { key_name, client_type, permissions, expires_at } = req.body;

        if (!key_name || !client_type || !permissions) {
            return res.status(400).json({
                success: false,
                message: 'key_name, client_type and permissions are required'
            });
        }

        // Validate client type
        const validTypes = ['analytics_dashboard', 'mobile_ar', 'admin'];
        if (!validTypes.includes(client_type)) {
            return res.status(400).json({
                success: false,
                message: 'Invalid client_type. Must be: analytics_dashboard, mobile_ar, or admin'
            });
        }

        // Validate permissions match client type
        const allowedPerms = {
            analytics_dashboard: ['read:alumni', 'read:analytics', 'read:bidding'],
            mobile_ar:           ['read:alumni_of_day'],
            admin:               ['read:alumni', 'read:analytics', 'read:bidding',
                                  'read:alumni_of_day', 'write:admin']
        };

        const invalidPerms = permissions.filter(p => !allowedPerms[client_type].includes(p));
        if (invalidPerms.length > 0) {
            return res.status(400).json({
                success: false,
                message: `Invalid permissions for ${client_type}: ${invalidPerms.join(', ')}`,
                allowed_permissions: allowedPerms[client_type]
            });
        }

        // Generate cryptographically random API key
        const newApiKey = `${client_type.substring(0, 4)}_${crypto.randomBytes(24).toString('hex')}`;

        await pool.execute(
            `INSERT INTO api_keys 
             (key_name, api_key, client_type, permissions, expires_at)
             VALUES (?, ?, ?, ?, ?)`,
            [
                key_name,
                newApiKey,
                client_type,
                JSON.stringify(permissions),
                expires_at || null
            ]
        );

        return res.status(201).json({
            success: true,
            message: 'API key created successfully',
            data: {
                api_key:     newApiKey,  // only shown ONCE at creation
                key_name:    key_name,
                client_type: client_type,
                permissions: permissions
            },
            warning: 'Save this API key now. It will not be shown again in full.'
        });

    } catch (error) {
        console.error('Create key error:', error);
        return res.status(500).json({ success: false, message: 'Failed to create key' });
    }
}

/**
 * PUT /api/security/keys/:id/toggle
 * Enable or disable an API key
 */
async function toggleKey(req, res) {
    try {
        const [rows] = await pool.execute(
            'SELECT id, key_name, is_active FROM api_keys WHERE id = ?',
            [req.params.id]
        );

        if (rows.length === 0) {
            return res.status(404).json({ success: false, message: 'Key not found' });
        }

        const newStatus = rows[0].is_active ? 0 : 1;

        await pool.execute(
            'UPDATE api_keys SET is_active = ? WHERE id = ?',
            [newStatus, req.params.id]
        );

        return res.json({
            success: true,
            message: `API key "${rows[0].key_name}" ${newStatus ? 'activated' : 'deactivated'}`,
            is_active: newStatus
        });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed to toggle key' });
    }
}

/**
 * DELETE /api/security/keys/:id
 */
async function deleteKey(req, res) {
    try {
        const [rows] = await pool.execute(
            'SELECT id, key_name FROM api_keys WHERE id = ?',
            [req.params.id]
        );

        if (rows.length === 0) {
            return res.status(404).json({ success: false, message: 'Key not found' });
        }

        await pool.execute('DELETE FROM api_keys WHERE id = ?', [req.params.id]);

        return res.json({
            success: true,
            message: `API key "${rows[0].key_name}" deleted`
        });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed to delete key' });
    }
}

/**
 * GET /api/security/stats
 * Overall usage statistics
 */
async function getUsageStats(req, res) {
    try {
        // Total requests per key
        const [keyStats] = await pool.execute(
            `SELECT ak.id, ak.key_name, ak.client_type,
                    ak.usage_count, ak.last_used_at,
                    ak.is_active,
                    COUNT(ul.id) as log_count,
                    SUM(CASE WHEN ul.response_status = 200 THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN ul.response_status = 403 THEN 1 ELSE 0 END) as forbidden_count,
                    SUM(CASE WHEN ul.response_status = 401 THEN 1 ELSE 0 END) as unauthorized_count
             FROM api_keys ak
             LEFT JOIN api_usage_logs ul ON ak.id = ul.api_key_id
             GROUP BY ak.id
             ORDER BY ak.usage_count DESC`
        );

        // Requests per hour (last 24h)
        const [hourlyStats] = await pool.execute(
            `SELECT HOUR(accessed_at) as hour,
                    COUNT(*) as requests
             FROM api_usage_logs
             WHERE accessed_at >= NOW() - INTERVAL 24 HOUR
             GROUP BY HOUR(accessed_at)
             ORDER BY hour ASC`
        );

        // Most accessed endpoints
        const [topEndpoints] = await pool.execute(
            `SELECT endpoint, method, COUNT(*) as hits,
                    AVG(response_status) as avg_status
             FROM api_usage_logs
             GROUP BY endpoint, method
             ORDER BY hits DESC
             LIMIT 10`
        );

        // Login statistics
        const [loginStats] = await pool.execute(
            `SELECT action, COUNT(*) as count,
                    MAX(login_at) as last_occurrence
             FROM login_logs
             GROUP BY action`
        );

        // Total counts
        const [totals] = await pool.execute(
            `SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN response_status = 200 THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN response_status = 403 THEN 1 ELSE 0 END) as forbidden,
                SUM(CASE WHEN response_status = 401 THEN 1 ELSE 0 END) as unauthorized
             FROM api_usage_logs`
        );

        return res.json({
            success: true,
            data: {
                key_stats:     keyStats.map(k => ({
                    ...k,
                    success_rate: k.log_count > 0
                        ? ((k.success_count / k.log_count) * 100).toFixed(1) + '%'
                        : 'N/A'
                })),
                hourly_stats:  hourlyStats,
                top_endpoints: topEndpoints,
                login_stats:   loginStats,
                totals:        totals[0]
            }
        });

    } catch (error) {
        console.error('Get stats error:', error);
        return res.status(500).json({ success: false, message: 'Failed to fetch stats' });
    }
}

/**
 * GET /api/security/stats/:key_id
 * Usage stats for a specific API key
 */
async function getKeyStats(req, res) {
    try {
        const { key_id } = req.params;

        const [keyInfo] = await pool.execute(
            `SELECT id, key_name, client_type, permissions,
                    usage_count, last_used_at, is_active, created_at
             FROM api_keys WHERE id = ?`,
            [key_id]
        );

        if (keyInfo.length === 0) {
            return res.status(404).json({ success: false, message: 'Key not found' });
        }

        // Recent access logs for this key
        const [logs] = await pool.execute(
            `SELECT endpoint, method, response_status,
                    ip_address, accessed_at
             FROM api_usage_logs
             WHERE api_key_id = ?
             ORDER BY accessed_at DESC
             LIMIT 50`,
            [key_id]
        );

        // Endpoint breakdown
        const [endpoints] = await pool.execute(
            `SELECT endpoint, COUNT(*) as hits,
                    AVG(response_status) as avg_status
             FROM api_usage_logs
             WHERE api_key_id = ?
             GROUP BY endpoint
             ORDER BY hits DESC`,
            [key_id]
        );

        return res.json({
            success: true,
            data: {
                key_info:  {
                    ...keyInfo[0],
                    permissions: JSON.parse(keyInfo[0].permissions)
                },
                logs:      logs,
                endpoints: endpoints
            }
        });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed to fetch key stats' });
    }
}

/**
 * GET /api/security/login-logs
 * View user login history
 */
async function getLoginLogs(req, res) {
    try {
        const limit = parseInt(req.query.limit) || 50;

        const [logs] = await pool.execute(
            `SELECT ll.id, ll.action, ll.ip_address,
                    ll.login_at, ll.user_agent,
                    u.full_name, u.email
             FROM login_logs ll
             LEFT JOIN users u ON ll.user_id = u.id
             ORDER BY ll.login_at DESC
             LIMIT ?`,
            [limit]
        );

        return res.json({ success: true, data: logs });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed to fetch login logs' });
    }
}

/**
 * GET /api/security/access-logs
 * Recent API access logs
 */
async function getAccessLogs(req, res) {
    try {
        const limit = parseInt(req.query.limit) || 100;

        const [logs] = await pool.execute(
            `SELECT ul.id, ul.endpoint, ul.method,
                    ul.response_status, ul.ip_address,
                    ul.accessed_at,
                    ak.key_name, ak.client_type
             FROM api_usage_logs ul
             JOIN api_keys ak ON ul.api_key_id = ak.id
             ORDER BY ul.accessed_at DESC
             LIMIT ?`,
            [limit]
        );

        return res.json({ success: true, data: logs });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed to fetch access logs' });
    }
}

/**
 * GET /api/security/alumni-of-day
 * Mobile AR App endpoint - only accessible with read:alumni_of_day permission
 * Analytics Dashboard CANNOT access this
 */
async function getAlumniOfDay(req, res) {
    try {
        // Only mobile_ar client type can reach here
        // verifyApiKey middleware already enforced read:alumni_of_day permission

        const [featured] = await pool.execute(
            `SELECT a.id, a.full_name, a.programme,
                    a.graduation_year, a.job_title,
                    a.employer, a.industry_sector,
                    a.location_city, a.location_country,
                    a.skills, a.certifications
             FROM alumni a
             WHERE a.is_featured = 1
             AND a.profile_visible = 1
             ORDER BY RAND()
             LIMIT 1`
        );

        if (featured.length === 0) {
            return res.json({
                success: true,
                data: null,
                message: 'No featured alumni for today'
            });
        }

        const alumni = {
            ...featured[0],
            skills:         featured[0].skills ? JSON.parse(featured[0].skills) : [],
            certifications: featured[0].certifications ? JSON.parse(featured[0].certifications) : []
        };

        return res.json({
            success: true,
            data: alumni,
            accessed_by: req.clientType  // shows which client accessed it
        });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed to fetch alumni of day' });
    }
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