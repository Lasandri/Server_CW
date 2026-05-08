// node-api/middleware/authMiddleware.js

const jwt = require('jsonwebtoken');
const pool = require('../config/database');
require('dotenv').config();

/**
 * Verify JWT token from Authorization header
 * Used to protect routes that require login
 */
async function verifyToken(req, res, next) {
    try {
        // Extract token from "Bearer <token>" header
        const authHeader = req.headers['authorization'];

        if (!authHeader || !authHeader.startsWith('Bearer ')) {
            return res.status(401).json({
                success: false,
                message: 'Access denied. No token provided.'
            });
        }

        const token = authHeader.split(' ')[1];

        // Verify and decode the JWT
        const decoded = jwt.verify(token, process.env.JWT_SECRET);

        // Check if user still exists and is active in DB
        const [rows] = await pool.execute(
            'SELECT id, email, full_name, role, is_active, email_verified FROM users WHERE id = ?',
            [decoded.userId]
        );

        if (rows.length === 0 || !rows[0].is_active) {
            return res.status(401).json({
                success: false,
                message: 'User no longer exists or has been deactivated.'
            });
        }

        // Attach user to request object for downstream use
        req.user = rows[0];
        next();

    } catch (error) {
        if (error.name === 'TokenExpiredError') {
            return res.status(401).json({
                success: false,
                message: 'Token expired. Please log in again.'
            });
        }
        return res.status(401).json({
            success: false,
            message: 'Invalid token.'
        });
    }
}

/**
 * Verify API key for client applications (bearer token scoping)
 * Checks permissions for specific endpoints
 * @param {string} requiredPermission - e.g. "read:alumni"
 */
function verifyApiKey(requiredPermission) {
    return async (req, res, next) => {
        try {
            const authHeader = req.headers['authorization'];

            if (!authHeader || !authHeader.startsWith('Bearer ')) {
                return res.status(401).json({
                    success: false,
                    message: 'API key required in Authorization header.'
                });
            }

            const apiKey = authHeader.split(' ')[1];

            // Look up the API key in database
            const [rows] = await pool.execute(
                `SELECT ak.*, u.email 
                 FROM api_keys ak 
                 JOIN users u ON ak.user_id = u.id 
                 WHERE ak.api_key = ? AND ak.is_active = 1`,
                [apiKey]
            );

            if (rows.length === 0) {
                return res.status(401).json({
                    success: false,
                    message: 'Invalid or inactive API key.'
                });
            }

            const keyData = rows[0];

            // Check expiry if set
            if (keyData.expires_at && new Date(keyData.expires_at) < new Date()) {
                return res.status(401).json({
                    success: false,
                    message: 'API key has expired.'
                });
            }

            // Parse permissions from JSON and check required permission
            const permissions = JSON.parse(keyData.permissions);

            if (requiredPermission && !permissions.includes(requiredPermission)) {
                return res.status(403).json({
                    success: false,
                    message: `Insufficient permissions. Required: ${requiredPermission}`
                });
            }

            // Update usage count and last used timestamp
            await pool.execute(
                'UPDATE api_keys SET usage_count = usage_count + 1, last_used_at = NOW() WHERE id = ?',
                [keyData.id]
            );

            // Log the endpoint access for usage statistics
            await pool.execute(
                `INSERT INTO api_usage_logs 
                 (api_key_id, endpoint, method, response_status) 
                 VALUES (?, ?, ?, ?)`,
                [keyData.id, req.path, req.method, 200]
            );

            // Attach key info to request
            req.apiKey = keyData;
            req.permissions = permissions;
            next();

        } catch (error) {
            console.error('API key verification error:', error);
            return res.status(500).json({
                success: false,
                message: 'Server error during authentication.'
            });
        }
    };
}

module.exports = { verifyToken, verifyApiKey };