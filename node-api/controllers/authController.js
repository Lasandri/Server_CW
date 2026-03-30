/**
 * Auth Controller - Node.js API
 * 
 * Handles authentication-related API endpoints:
 *   POST /api/auth/register      - Register new alumni
 *   POST /api/auth/login         - Login and receive JWT
 *   POST /api/auth/verify-email  - Verify email with token
 *   POST /api/auth/forgot-password - Request password reset
 *   POST /api/auth/reset-password  - Reset password with token
 *   POST /api/auth/logout        - Logout (invalidate session)
 *   GET  /api/auth/me            - Get current user profile (protected)
 * 
 * Security: bcrypt hashing, JWT tokens, input validation,
 *           rate limiting, brute-force protection
 */

const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const crypto = require('crypto');
const { validationResult } = require('express-validator');
const pool = require('../config/database');
const { sendVerificationEmail, sendPasswordResetEmail } = require('../utils/emailService');
require('dotenv').config();

// Allowed university email domains
const ALLOWED_DOMAINS = (process.env.ALLOWED_DOMAINS || 'eastminster.ac.uk').split(',');

// Constants for security
const BCRYPT_SALT_ROUNDS = 12;
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES = 15;

/**
 * Validate that email belongs to an allowed university domain.
 * @param {string} email 
 * @returns {boolean}
 */
function isUniversityEmail(email) {
    const domain = email.split('@')[1]?.toLowerCase();
    return ALLOWED_DOMAINS.includes(domain);
}

/**
 * Validate password strength requirements.
 * Must have: uppercase, lowercase, digit, special character, min 8 chars.
 * @param {string} password 
 * @returns {boolean}
 */
function isStrongPassword(password) {
    return password.length >= 8
        && /[A-Z]/.test(password)
        && /[a-z]/.test(password)
        && /[0-9]/.test(password)
        && /[^A-Za-z0-9]/.test(password);
}

/**
 * Generate a cryptographically secure random token.
 * @returns {string} 64-character hex string
 */
function generateSecureToken() {
    return crypto.randomBytes(32).toString('hex');
}

/**
 * Hash a token with SHA-256 for secure storage.
 * (Raw token sent to user; only hash stored in DB.)
 * @param {string} token 
 * @returns {string}
 */
function hashToken(token) {
    return crypto.createHash('sha256').update(token).digest('hex');
}

// ================================================================
// REGISTER
// ================================================================
exports.register = async (req, res) => {
    try {
        // Check validation errors from express-validator
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                status: 'error',
                errors: errors.array(),
            });
        }

        const { first_name, last_name, email, password } = req.body;
        const cleanEmail = email.trim().toLowerCase();

        // 1. Validate university email domain
        if (!isUniversityEmail(cleanEmail)) {
            return res.status(400).json({
                status: 'error',
                message: 'Registration requires a valid university email address (e.g., @eastminster.ac.uk).',
            });
        }

        // 2. Validate password strength
        if (!isStrongPassword(password)) {
            return res.status(400).json({
                status: 'error',
                message: 'Password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character (min 8 characters).',
            });
        }

        // 3. Check for duplicate email
        const [existingUsers] = await pool.query(
            'SELECT id FROM users WHERE email = ?',
            [cleanEmail]
        );

        if (existingUsers.length > 0) {
            return res.status(409).json({
                status: 'error',
                message: 'An account with this email address already exists.',
            });
        }

        // 4. Hash password with bcrypt (12 salt rounds)
        const passwordHash = await bcrypt.hash(password, BCRYPT_SALT_ROUNDS);

        // 5. Generate email verification token
        const rawToken = generateSecureToken();
        const hashedToken = hashToken(rawToken);
        const tokenExpiry = new Date(Date.now() + 24 * 60 * 60 * 1000); // 24 hours

        // 6. Insert user into database
        const [result] = await pool.query(
            `INSERT INTO users (first_name, last_name, email, password_hash, 
             email_verification_token, email_verification_expires, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())`,
            [first_name.trim(), last_name.trim(), cleanEmail, passwordHash, hashedToken, tokenExpiry]
        );

        // 7. Send verification email
        await sendVerificationEmail(cleanEmail, first_name.trim(), rawToken);

        res.status(201).json({
            status: 'success',
            message: 'Registration successful! Please check your email to verify your account.',
            data: {
                user_id: result.insertId,
                email: cleanEmail,
            },
        });

    } catch (error) {
        console.error('Registration error:', error);
        res.status(500).json({
            status: 'error',
            message: 'An internal server error occurred during registration.',
        });
    }
};

// ================================================================
// VERIFY EMAIL
// ================================================================
exports.verifyEmail = async (req, res) => {
    try {
        const { token } = req.body;

        if (!token) {
            return res.status(400).json({
                status: 'error',
                message: 'Verification token is required.',
            });
        }

        const hashedToken = hashToken(token);

        // Find user with matching, non-expired token
        const [users] = await pool.query(
            `SELECT id FROM users 
             WHERE email_verification_token = ? 
             AND email_verification_expires > NOW()`,
            [hashedToken]
        );

        if (users.length === 0) {
            return res.status(400).json({
                status: 'error',
                message: 'Verification link is invalid or has expired.',
            });
        }

        // Mark as verified and clear token (single-use)
        await pool.query(
            `UPDATE users SET 
             is_email_verified = 1, 
             email_verification_token = NULL, 
             email_verification_expires = NULL,
             updated_at = NOW()
             WHERE id = ?`,
            [users[0].id]
        );

        res.status(200).json({
            status: 'success',
            message: 'Email verified successfully! You can now log in.',
        });

    } catch (error) {
        console.error('Email verification error:', error);
        res.status(500).json({
            status: 'error',
            message: 'An error occurred during email verification.',
        });
    }
};

// ================================================================
// LOGIN
// ================================================================
exports.login = async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                status: 'error',
                errors: errors.array(),
            });
        }

        const { email, password } = req.body;
        const cleanEmail = email.trim().toLowerCase();

        // 1. Find user by email
        const [users] = await pool.query(
            'SELECT * FROM users WHERE email = ?',
            [cleanEmail]
        );

        if (users.length === 0) {
            // Generic message to prevent email enumeration
            return res.status(401).json({
                status: 'error',
                message: 'Invalid email or password.',
            });
        }

        const user = users[0];

        // 2. Check account lockout
        if (user.locked_until && new Date(user.locked_until) > new Date()) {
            return res.status(423).json({
                status: 'error',
                message: `Account is temporarily locked. Try again after ${new Date(user.locked_until).toLocaleTimeString()}.`,
            });
        }

        // 3. Verify password
        const isPasswordValid = await bcrypt.compare(password, user.password_hash);

        if (!isPasswordValid) {
            // Increment failed attempts
            const newAttempts = (user.login_attempts || 0) + 1;
            const updateData = { login_attempts: newAttempts };

            if (newAttempts >= MAX_LOGIN_ATTEMPTS) {
                updateData.locked_until = new Date(Date.now() + LOCKOUT_MINUTES * 60 * 1000);
            }

            await pool.query(
                'UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?',
                [updateData.login_attempts, updateData.locked_until || null, user.id]
            );

            return res.status(401).json({
                status: 'error',
                message: 'Invalid email or password.',
            });
        }

        // 4. Check email verification
        if (!user.is_email_verified) {
            return res.status(403).json({
                status: 'error',
                message: 'Please verify your email address before logging in.',
                email_verified: false,
            });
        }

        // 5. Check account active
        if (!user.is_active) {
            return res.status(403).json({
                status: 'error',
                message: 'Your account has been deactivated.',
            });
        }

        // 6. Successful login - reset attempts
        await pool.query(
            `UPDATE users SET 
             login_attempts = 0, locked_until = NULL, 
             last_login_at = NOW(), updated_at = NOW() 
             WHERE id = ?`,
            [user.id]
        );

        // 7. Generate JWT token
        const jwtPayload = {
            user_id: user.id,
            email: user.email,
            first_name: user.first_name,
            last_name: user.last_name,
        };

        const token = jwt.sign(jwtPayload, process.env.JWT_SECRET, {
            expiresIn: process.env.JWT_EXPIRES_IN || '24h',
        });

        // 8. Log the session for auditing
        await pool.query(
            `INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, login_at, is_active) 
             VALUES (?, ?, ?, ?, NOW(), 1)`,
            [user.id, token.slice(-32), req.ip, req.get('User-Agent') || 'Unknown']
        );

        res.status(200).json({
            status: 'success',
            message: `Welcome back, ${user.first_name}!`,
            data: {
                token,
                token_type: 'Bearer',
                expires_in: process.env.JWT_EXPIRES_IN || '24h',
                user: {
                    id: user.id,
                    first_name: user.first_name,
                    last_name: user.last_name,
                    email: user.email,
                },
            },
        });

    } catch (error) {
        console.error('Login error:', error);
        res.status(500).json({
            status: 'error',
            message: 'An internal server error occurred during login.',
        });
    }
};

// ================================================================
// LOGOUT
// ================================================================
exports.logout = async (req, res) => {
    try {
        // Mark user sessions as inactive
        if (req.user && req.user.user_id) {
            await pool.query(
                `UPDATE user_sessions SET is_active = 0, logout_at = NOW() 
                 WHERE user_id = ? AND is_active = 1`,
                [req.user.user_id]
            );
        }

        res.status(200).json({
            status: 'success',
            message: 'Logged out successfully.',
        });

    } catch (error) {
        console.error('Logout error:', error);
        res.status(500).json({
            status: 'error',
            message: 'An error occurred during logout.',
        });
    }
};

// ================================================================
// FORGOT PASSWORD
// ================================================================
exports.forgotPassword = async (req, res) => {
    try {
        const { email } = req.body;

        if (!email) {
            return res.status(400).json({
                status: 'error',
                message: 'Email address is required.',
            });
        }

        const cleanEmail = email.trim().toLowerCase();

        const [users] = await pool.query(
            'SELECT id, first_name FROM users WHERE email = ?',
            [cleanEmail]
        );

        if (users.length > 0) {
            const user = users[0];

            // Generate reset token (1 hour expiry)
            const rawToken = generateSecureToken();
            const hashedToken = hashToken(rawToken);
            const tokenExpiry = new Date(Date.now() + 60 * 60 * 1000); // 1 hour

            // Save hashed token to DB
            await pool.query(
                `UPDATE users SET 
                 password_reset_token = ?, 
                 password_reset_expires = ?,
                 updated_at = NOW()
                 WHERE id = ?`,
                [hashedToken, tokenExpiry, user.id]
            );

            // Send reset email
            await sendPasswordResetEmail(cleanEmail, user.first_name, rawToken);
        }

        // Always return same response to prevent email enumeration
        res.status(200).json({
            status: 'success',
            message: 'If that email is registered, a password reset link has been sent.',
        });

    } catch (error) {
        console.error('Forgot password error:', error);
        res.status(500).json({
            status: 'error',
            message: 'An error occurred. Please try again.',
        });
    }
};

// ================================================================
// RESET PASSWORD
// ================================================================
exports.resetPassword = async (req, res) => {
    try {
        const { token, password, password_confirm } = req.body;

        // Validate inputs
        if (!token || !password || !password_confirm) {
            return res.status(400).json({
                status: 'error',
                message: 'Token, password, and password confirmation are required.',
            });
        }

        if (password !== password_confirm) {
            return res.status(400).json({
                status: 'error',
                message: 'Passwords do not match.',
            });
        }

        if (!isStrongPassword(password)) {
            return res.status(400).json({
                status: 'error',
                message: 'Password must contain uppercase, lowercase, digit, and special character (min 8 chars).',
            });
        }

        // Verify token
        const hashedToken = hashToken(token);
        const [users] = await pool.query(
            `SELECT id FROM users 
             WHERE password_reset_token = ? 
             AND password_reset_expires > NOW()`,
            [hashedToken]
        );

        if (users.length === 0) {
            return res.status(400).json({
                status: 'error',
                message: 'Password reset link is invalid or has expired.',
            });
        }

        // Hash new password and update
        const newPasswordHash = await bcrypt.hash(password, BCRYPT_SALT_ROUNDS);

        await pool.query(
            `UPDATE users SET 
             password_hash = ?,
             password_reset_token = NULL,
             password_reset_expires = NULL,
             login_attempts = 0,
             locked_until = NULL,
             updated_at = NOW()
             WHERE id = ?`,
            [newPasswordHash, users[0].id]
        );

        res.status(200).json({
            status: 'success',
            message: 'Password has been reset successfully. You can now log in.',
        });

    } catch (error) {
        console.error('Reset password error:', error);
        res.status(500).json({
            status: 'error',
            message: 'An error occurred during password reset.',
        });
    }
};

// ================================================================
// GET CURRENT USER (protected route)
// ================================================================
exports.getMe = async (req, res) => {
    try {
        const [users] = await pool.query(
            `SELECT id, first_name, last_name, email, is_email_verified, 
                    last_login_at, created_at 
             FROM users WHERE id = ?`,
            [req.user.user_id]
        );

        if (users.length === 0) {
            return res.status(404).json({
                status: 'error',
                message: 'User not found.',
            });
        }

        res.status(200).json({
            status: 'success',
            data: { user: users[0] },
        });

    } catch (error) {
        console.error('Get user error:', error);
        res.status(500).json({
            status: 'error',
            message: 'An error occurred.',
        });
    }
};