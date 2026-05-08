/**
 * Authentication Middleware
 * 
 * Verifies JWT bearer tokens for protected API routes.
 * Extracts user data from the token and attaches to req.user.
 * 
 * Usage: Add as middleware to any route that requires authentication.
 * Header format: Authorization: Bearer <token>
 */

const jwt = require('jsonwebtoken');
require('dotenv').config();

function authenticateToken(req, res, next) {
    // Extract token from Authorization header
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // "Bearer TOKEN"

    if (!token) {
        return res.status(401).json({
            status: 'error',
            message: 'Access denied. No authentication token provided.',
        });
    }

    try {
        // Verify and decode the JWT token
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        req.user = decoded; // Attach user data to request object
        next();
    } catch (error) {
        if (error.name === 'TokenExpiredError') {
            return res.status(401).json({
                status: 'error',
                message: 'Authentication token has expired. Please log in again.',
            });
        }
        return res.status(403).json({
            status: 'error',
            message: 'Invalid authentication token.',
        });
    }
}

module.exports = { authenticateToken };