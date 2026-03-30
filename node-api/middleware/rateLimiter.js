/**
 * Rate Limiter Middleware
 * 
 * Protects authentication endpoints from brute-force attacks.
 * Different limits for different endpoint sensitivities:
 *   - Login: 5 attempts per 15 minutes
 *   - Registration: 3 per hour
 *   - Password reset: 3 per hour
 *   - General API: 100 per 15 minutes
 */

const rateLimit = require('express-rate-limit');

// General API rate limiter - 100 requests per 15 minutes
const generalLimiter = rateLimit({
    windowMs: 15 * 60 * 1000,  // 15 minutes
    max: 100,
    message: {
        status: 429,
        error: 'Too many requests. Please try again later.',
    },
    standardHeaders: true,
    legacyHeaders: false,
});

// Login rate limiter - 5 attempts per 15 minutes (per IP)
const loginLimiter = rateLimit({
    windowMs: 15 * 60 * 1000,
    max: 5,
    message: {
        status: 429,
        error: 'Too many login attempts. Please try again in 15 minutes.',
    },
    standardHeaders: true,
    legacyHeaders: false,
});

// Registration rate limiter - 3 per hour
const registerLimiter = rateLimit({
    windowMs: 60 * 60 * 1000,
    max: 3,
    message: {
        status: 429,
        error: 'Too many registration attempts. Please try again later.',
    },
    standardHeaders: true,
    legacyHeaders: false,
});

// Password reset rate limiter - 3 per hour
const passwordResetLimiter = rateLimit({
    windowMs: 60 * 60 * 1000,
    max: 3,
    message: {
        status: 429,
        error: 'Too many password reset requests. Please try again later.',
    },
    standardHeaders: true,
    legacyHeaders: false,
});

module.exports = {
    generalLimiter,
    loginLimiter,
    registerLimiter,
    passwordResetLimiter,
};