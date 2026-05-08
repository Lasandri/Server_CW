// node-api/middleware/authMiddleware.js

const jwt  = require('jsonwebtoken');
const pool = require('../config/database');
require('dotenv').config();

// ── Permission definitions per client type ────────────────────────────────────
const CLIENT_PERMISSIONS = {
    analytics_dashboard: ['read:alumni', 'read:analytics', 'read:bidding'],
    mobile_ar:           ['read:alumni_of_day'],
    admin:               ['read:alumni', 'read:analytics', 'read:bidding',
                          'read:alumni_of_day', 'write:admin']
};

// ── What each client CANNOT access ───────────────────────────────────────────
const CLIENT_RESTRICTIONS = {
    analytics_dashboard: ['read:alumni_of_day'],  // cannot access AR endpoints
    mobile_ar:           ['read:analytics', 'read:bidding'], // cannot access analytics
    admin:               []  // no restrictions
};

/**
 * Verify JWT token (for user sessions)
 */
async function verifyToken(req, res, next) {
    try {
        const authHeader = req.headers['authorization'];

        if (!authHeader || !authHeader.startsWith('Bearer ')) {
            return res.status(401).json({
                success: false,
                message: 'Access denied. No token provided.',
                code: 'NO_TOKEN'
            });
        }

        const token   = authHeader.split(' ')[1];
        const decoded = jwt.verify(token, process.env.JWT_SECRET);

        // Check user still exists and is active
        const [rows] = await pool.execute(
            `SELECT id, email, full_name, role, is_active, email_verified 
             FROM users WHERE id = ?`,
            [decoded.userId]
        );

        if (rows.length === 0 || !rows[0].is_active) {
            return res.status(401).json({
                success: false,
                message: 'User not found or deactivated.',
                code: 'USER_INVALID'
            });
        }

        req.user = rows[0];
        next();

    } catch (error) {
        if (error.name === 'TokenExpiredError') {
            return res.status(401).json({
                success: false,
                message: 'Session expired. Please log in again.',
                code: 'TOKEN_EXPIRED'
            });
        }
        return res.status(401).json({
            success: false,
            message: 'Invalid token.',
            code: 'TOKEN_INVALID'
        });
    }
}

/**
 * Verify API Key with permission scoping
 * @param {string} requiredPermission - e.g. "read:alumni"
 */
function verifyApiKey(requiredPermission) {
    return async (req, res, next) => {
        try {
            const authHeader = req.headers['authorization'];

            if (!authHeader || !authHeader.startsWith('Bearer ')) {
                return res.status(401).json({
                    success: false,
                    message: 'API key required. Format: Authorization: Bearer <api_key>',
                    code: 'NO_API_KEY'
                });
            }

            const apiKey = authHeader.split(' ')[1];

            // Look up API key in database
            const [rows] = await pool.execute(
                `SELECT * FROM api_keys 
                 WHERE api_key = ? AND is_active = 1`,
                [apiKey]
            );

            if (rows.length === 0) {
                // Log failed attempt
                console.warn(`⚠️  Invalid API key attempt: ${apiKey.substring(0, 10)}...`);
                return res.status(401).json({
                    success: false,
                    message: 'Invalid or inactive API key.',
                    code: 'INVALID_API_KEY'
                });
            }

            const keyData     = rows[0];
            const permissions = JSON.parse(keyData.permissions);

            // Check if key has expired
            if (keyData.expires_at && new Date(keyData.expires_at) < new Date()) {
                return res.status(401).json({
                    success: false,
                    message: 'API key has expired.',
                    code: 'KEY_EXPIRED'
                });
            }

            // ── SCOPE CHECK: Does this client have the required permission? ──
            if (requiredPermission && !permissions.includes(requiredPermission)) {

                // Check if this is a cross-client access attempt
                const restrictions = CLIENT_RESTRICTIONS[keyData.client_type] || [];
                const isCrossClient = restrictions.includes(requiredPermission);

                // Log the forbidden attempt
                await logApiUsage(
                    keyData.id,
                    req.path,
                    req.method,
                    403,
                    req.ip,
                    req.headers['user-agent']
                );

                if (isCrossClient) {
                    return res.status(403).json({
                        success: false,
                        message: `Access denied. The ${keyData.client_type.replace('_', ' ')} key cannot access this endpoint.`,
                        code: 'CROSS_CLIENT_FORBIDDEN',
                        your_permissions: permissions,
                        required_permission: requiredPermission,
                        your_client_type: keyData.client_type
                    });
                }

                return res.status(403).json({
                    success: false,
                    message: `Insufficient permissions. Required: ${requiredPermission}`,
                    code: 'INSUFFICIENT_PERMISSIONS',
                    your_permissions: permissions,
                    required_permission: requiredPermission
                });
            }

            // ── Update usage stats ───────────────────────────────────────────
            await pool.execute(
                `UPDATE api_keys 
                 SET usage_count = usage_count + 1, 
                     last_used_at = NOW() 
                 WHERE id = ?`,
                [keyData.id]
            );

            // ── Log the successful API access ────────────────────────────────
            await logApiUsage(
                keyData.id,
                req.path,
                req.method,
                200,
                req.ip,
                req.headers['user-agent']
            );

            // Attach key info to request for downstream use
            req.apiKey      = keyData;
            req.permissions = permissions;
            req.clientType  = keyData.client_type;

            next();

        } catch (error) {
            console.error('API key verification error:', error);
            return res.status(500).json({
                success: false,
                message: 'Authentication error.',
                code: 'AUTH_ERROR'
            });
        }
    };
}

/**
 * Log API usage to database
 */
async function logApiUsage(apiKeyId, endpoint, method, status, ip, userAgent) {
    try {
        await pool.execute(
            `INSERT INTO api_usage_logs 
             (api_key_id, endpoint, method, response_status, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)`,
            [
                apiKeyId,
                endpoint.substring(0, 255),
                method,
                status,
                (ip || '').substring(0, 45),
                (userAgent || '').substring(0, 255)
            ]
        );
    } catch (error) {
        console.error('Log API usage error:', error.message);
    }
}

module.exports = { verifyToken, verifyApiKey, logApiUsage };