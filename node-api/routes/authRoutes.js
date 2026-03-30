/**
 * Authentication API Routes
 * 
 * Base path: /api/auth
 * 
 * Public routes:
 *   POST /register        - Register new alumni
 *   POST /login           - Login and get JWT
 *   POST /verify-email    - Verify email with token
 *   POST /forgot-password - Request password reset
 *   POST /reset-password  - Reset password
 * 
 * Protected routes (require JWT):
 *   POST /logout          - Logout
 *   GET  /me              - Get current user
 */

const express = require('express');
const { body } = require('express-validator');
const router = express.Router();

const authController = require('../controllers/authController');
const { authenticateToken } = require('../middleware/authMiddleware');
const { loginLimiter, registerLimiter, passwordResetLimiter } = require('../middleware/rateLimiter');

// ---- Registration (rate limited: 3/hour) ----
router.post(
    '/register',
    registerLimiter,
    [
        body('first_name')
            .trim()
            .notEmpty().withMessage('First name is required.')
            .isLength({ min: 2, max: 100 }).withMessage('First name must be 2-100 characters.')
            .matches(/^[a-zA-Z\s]+$/).withMessage('First name may only contain letters and spaces.'),
        body('last_name')
            .trim()
            .notEmpty().withMessage('Last name is required.')
            .isLength({ min: 2, max: 100 }).withMessage('Last name must be 2-100 characters.')
            .matches(/^[a-zA-Z\s]+$/).withMessage('Last name may only contain letters and spaces.'),
        body('email')
            .trim()
            .notEmpty().withMessage('Email is required.')
            .isEmail().withMessage('Please provide a valid email address.')
            .normalizeEmail(),
        body('password')
            .notEmpty().withMessage('Password is required.')
            .isLength({ min: 8 }).withMessage('Password must be at least 8 characters.'),
        body('password_confirm')
            .notEmpty().withMessage('Password confirmation is required.')
            .custom((value, { req }) => value === req.body.password)
            .withMessage('Passwords do not match.'),
    ],
    authController.register
);

// ---- Login (rate limited: 5 per 15 min) ----
router.post(
    '/login',
    loginLimiter,
    [
        body('email')
            .trim()
            .notEmpty().withMessage('Email is required.')
            .isEmail().withMessage('Please provide a valid email.'),
        body('password')
            .notEmpty().withMessage('Password is required.'),
    ],
    authController.login
);

// ---- Email Verification ----
router.post('/verify-email', authController.verifyEmail);

// ---- Forgot Password (rate limited: 3/hour) ----
router.post(
    '/forgot-password',
    passwordResetLimiter,
    [
        body('email')
            .trim()
            .notEmpty().withMessage('Email is required.')
            .isEmail().withMessage('Please provide a valid email.'),
    ],
    authController.forgotPassword
);

// ---- Reset Password ----
router.post('/reset-password', authController.resetPassword);

// ---- Protected Routes ----
router.post('/logout', authenticateToken, authController.logout);
router.get('/me', authenticateToken, authController.getMe);

module.exports = router;