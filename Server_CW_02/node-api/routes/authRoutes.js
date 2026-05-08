/**
 * Auth Routes - CW2
 * Base: /api/auth
 */
const express = require('express');
const { body } = require('express-validator');
const router = express.Router();
const authController = require('../controllers/authController');
const { authenticateToken } = require('../middleware/authMiddleware');
const {
    loginLimiter,
    registerLimiter,
    passwordResetLimiter
} = require('../middleware/rateLimiter');

// Register
router.post('/register', registerLimiter, [
    body('first_name').trim().notEmpty().isLength({ min: 2, max: 100 }),
    body('last_name').trim().notEmpty().isLength({ min: 2, max: 100 }),
    body('email').trim().notEmpty().isEmail().normalizeEmail(),
    body('password').notEmpty().isLength({ min: 8 }),
    body('password_confirm').notEmpty()
        .custom((val, { req }) => val === req.body.password)
        .withMessage('Passwords do not match.'),
], authController.register);

// Login
router.post('/login', loginLimiter, [
    body('email').trim().notEmpty().isEmail(),
    body('password').notEmpty(),
], authController.login);

// Verify email
router.post('/verify-email', authController.verifyEmail);

// Forgot password
router.post('/forgot-password', passwordResetLimiter, [
    body('email').trim().notEmpty().isEmail(),
], authController.forgotPassword);

// Reset password
router.post('/reset-password', authController.resetPassword);

// Protected
router.post('/logout', authenticateToken, authController.logout);
router.get('/me', authenticateToken, authController.getMe);

module.exports = router;