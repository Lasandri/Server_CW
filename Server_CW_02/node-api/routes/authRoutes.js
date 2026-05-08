// node-api/routes/authRoutes.js

const express = require('express');
const router = express.Router();

const authController = require('../controllers/authController');
const { verifyToken } = require('../middleware/authMiddleware');
const {
    validateRegistration,
    validateLogin,
    validateForgotPassword,
    validateResetPassword
} = require('../middleware/validationMiddleware');

// ── Auth Routes (NO rate limiting) ───────────────────────────────────────────

// Register new user
router.post('/register', validateRegistration, authController.register);

// Verify email with token from email link
router.get('/verify-email/:token', authController.verifyEmail);

// Resend verification email
router.post('/resend-verification', authController.resendVerification);

// Login
router.post('/login', validateLogin, authController.login);

// Logout (requires valid token)
router.post('/logout', verifyToken, authController.logout);

// Request password reset email
router.post('/forgot-password', validateForgotPassword, authController.forgotPassword);

// Submit new password with reset token
router.post('/reset-password/:token', validateResetPassword, authController.resetPassword);

// All routes require read:alumni permission
router.get('/', verifyApiKey('read:alumni'), alumniController.getAlumni);
router.get('/filters', verifyApiKey('read:alumni'), alumniController.getFilterOptions);
router.get('/:id', verifyApiKey('read:alumni'), alumniController.getAlumniById);

// Get current user profile (protected)
router.get('/me', verifyToken, authController.getProfile);

module.exports = router;