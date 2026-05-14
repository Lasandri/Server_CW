// node-api/routes/authRoutes.js
// REPLACE ENTIRE FILE

const express = require('express');
const router  = express.Router();

const authController = require('../controllers/authController');
const { verifyToken } = require('../middleware/authMiddleware');
const {
    validateRegistration,
    validateLogin,
    validateForgotPassword,
    validateResetPassword
} = require('../middleware/validationMiddleware');

// ── Auth Routes ───────────────────────────────────────────────────────────────

// Register new user
router.post('/register', validateRegistration, authController.register);

// Verify email with token
router.get('/verify-email/:token', authController.verifyEmail);

// Resend verification email
router.post('/resend-verification', authController.resendVerification);

// Login
router.post('/login', validateLogin, authController.login);

// Logout
router.post('/logout', verifyToken, authController.logout);

// Forgot password
router.post('/forgot-password', validateForgotPassword, authController.forgotPassword);

// Reset password
router.post('/reset-password/:token', validateResetPassword, authController.resetPassword);

// Get current user profile
router.get('/me', verifyToken, authController.getProfile);

module.exports = router;
