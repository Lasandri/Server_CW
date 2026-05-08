/**
 * Bearer Token Authentication Middleware for Node.js API
 * 
 * Validates bearer tokens issued by the CI3 system.
 * Logs every API request for usage tracking.
 * Checks rate limits per API key.
 * 
 * Usage: app.use('/api/v1', bearerAuth);
 * Header: Authorization: Bearer <token>
 */

const crypto = require('crypto');
const pool = require('../config/database');

async function bearerAuth(req, res, next) {
    const startTime = Date.now();

    // Extract bearer token from Authorization header
    const authHeader = req.headers['authorization'];

    if (!authHeader || !authHeader.startsWith('Bearer ')) {
        return res.status(401).json({
            status: 'error',
            message: 'Missing or invalid Authorization header. Use: Bearer <token>',
        });
    }

    const token = authHeader.split(' ')[1];

    if (!token) {
        return res.status(401).json({
            status: 'error',
            message: 'Bearer token is required.',
        });
    }

    try {
        // Hash the token to compare with DB
        const hashedToken = crypto.createHash('sha256').update(token).digest('hex');

        // Find matching active, non-expired token
        const [keys] = await pool.query(
            `SELECT ak.*, u.first_name, u.last_name, u.email
             FROM api_keys ak
             JOIN users u ON u.id = ak.user_id
             WHERE ak.bearer_token = ?
             AND ak.bearer_token_expires > NOW()
             AND ak.is_active = 1
             AND ak.is_revoked = 0`,
            [hashedToken]
        );

        if (keys.length === 0) {
            return res.status(401).json({
                status: 'error',
                message: 'Invalid or expired bearer token. Please re-authenticate.',
            });
        }

        const apiKey = keys[0];

        // Check rate limit
        const [rateLimitCheck] = await pool.query(
            `SELECT COUNT(*) as count FROM api_usage_logs 
             WHERE api_key_id = ? 
             AND requested_at > DATE_SUB(NOW(), INTERVAL ? SECOND)`,
            [apiKey.id, apiKey.rate_limit_window]
        );

        if (rateLimitCheck[0].count >= apiKey.rate_limit) {
            // Log the rate-limited request
            await pool.query(
                `INSERT INTO api_usage_logs (api_key_id, endpoint, method, ip_address, user_agent, response_code, response_time_ms, requested_at)
                 VALUES (?, ?, ?, ?, ?, 429, ?, NOW())`,
                [apiKey.id, req.originalUrl, req.method, req.ip, req.get('User-Agent') || '', Date.now() - startTime]
            );

            return res.status(429).json({
                status: 'error',
                message: 'Rate limit exceeded.',
                retry_after: apiKey.rate_limit_window,
            });
        }

        // Attach API key info to request
        req.apiKey = {
            id: apiKey.id,
            user_id: apiKey.user_id,
            client_name: apiKey.client_name,
            scopes: apiKey.scopes.split(','),
            first_name: apiKey.first_name,
            last_name: apiKey.last_name,
        };

        // Update usage count
        await pool.query(
            `UPDATE api_keys SET total_requests = total_requests + 1, last_used_at = NOW() WHERE id = ?`,
            [apiKey.id]
        );

        // Log the request (after response using res.on finish)
        res.on('finish', async () => {
            try {
                await pool.query(
                    `INSERT INTO api_usage_logs (api_key_id, endpoint, method, ip_address, user_agent, response_code, response_time_ms, requested_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())`,
                    [apiKey.id, req.originalUrl, req.method, req.ip, req.get('User-Agent') || '', res.statusCode, Date.now() - startTime]
                );
            } catch (err) {
                console.error('Failed to log API usage:', err.message);
            }
        });

        next();

    } catch (error) {
        console.error('Bearer auth error:', error);
        return res.status(500).json({
            status: 'error',
            message: 'Authentication error.',
        });
    }
}

/**
 * Check if request has a specific scope.
 */
function requireScope(scope) {
    return (req, res, next) => {
        if (!req.apiKey || !req.apiKey.scopes.includes(scope)) {
            return res.status(403).json({
                status: 'error',
                message: `Insufficient permissions. Required scope: ${scope}`,
            });
        }
        next();
    };
}

module.exports = { bearerAuth, requireScope };