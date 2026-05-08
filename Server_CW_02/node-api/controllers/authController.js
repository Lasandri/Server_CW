/**
 * Auth Controller - CW2 University Dashboard
 * 
 * POST /api/auth/register      - Register university staff
 * POST /api/auth/login         - Login + JWT
 * POST /api/auth/verify-email  - Verify email token
 * POST /api/auth/forgot-password
 * POST /api/auth/reset-password
 * POST /api/auth/logout
 * GET  /api/auth/me
 */

const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const crypto = require('crypto');
const { validationResult } = require('express-validator');
const pool = require('../config/database');
const {
    sendVerificationEmail,
    sendPasswordResetEmail
} = require('../utils/emailService');
require('dotenv').config();

const ALLOWED_DOMAINS = (
    process.env.ALLOWED_DOMAINS || 'eastminster.ac.uk'
).split(',');

const BCRYPT_ROUNDS = 12;
const MAX_ATTEMPTS = 5;
const LOCKOUT_MINUTES = 15;

/**
 * Check university email domain
 */
function isUniversityEmail(email) {
    const domain = email.split('@')[1]?.toLowerCase();
    return ALLOWED_DOMAINS.map(d => d.trim()).includes(domain);
}

/**
 * Password strength check
 */
function isStrongPassword(password) {
    return password.length >= 8
        && /[A-Z]/.test(password)
        && /[a-z]/.test(password)
        && /[0-9]/.test(password)
        && /[^A-Za-z0-9]/.test(password);
}

function generateToken() {
    return crypto.randomBytes(32).toString('hex');
}

function hashToken(token) {
    return crypto.createHash('sha256').update(token).digest('hex');
}

// ================================================================
// REGISTER
// ================================================================
exports.register = async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                status: 'error',
                errors: errors.array(),
            });
        }

        const { first_name, last_name, email, password } = req.body;
        const cleanEmail = email.trim().toLowerCase();

        // University domain check
        if (!isUniversityEmail(cleanEmail)) {
            return res.status(400).json({
                status: 'error',
                message: 'Registration requires a valid university email.',
            });
        }

        // Password strength
        if (!isStrongPassword(password)) {
            return res.status(400).json({
                status: 'error',
                message: 'Password must have uppercase, lowercase, digit, special character (min 8 chars).',
            });
        }

        // Check duplicate
        const [existing] = await pool.query(
            'SELECT id FROM users WHERE email = ?',
            [cleanEmail]
        );

        if (existing.length > 0) {
            return res.status(409).json({
                status: 'error',
                message: 'Email already registered.',
            });
        }

        // Hash password
        const passwordHash = await bcrypt.hash(password, BCRYPT_ROUNDS);

        // Verification token
        const rawToken = generateToken();
        const hashedToken = hashToken(rawToken);
        const tokenExpiry = new Date(Date.now() + 24 * 60 * 60 * 1000);

        // Insert user
        const [result] = await pool.query(
            `INSERT INTO users 
             (first_name, last_name, email, password_hash,
              email_verification_token, email_verification_expires,
              created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())`,
            [first_name.trim(), last_name.trim(), cleanEmail,
             passwordHash, hashedToken, tokenExpiry]
        );

        // Send verification email
        await sendVerificationEmail(cleanEmail, first_name.trim(), rawToken);

        res.status(201).json({
            status: 'success',
            message: 'Registration successful! Check your email to verify.',
            data: {
                user_id: result.insertId,
                email: cleanEmail,
            },
        });

    } catch (error) {
        console.error('Register error:', error);
        res.status(500).json({
            status: 'error',
            message: 'Registration failed.',
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
                message: 'Token required.',
            });
        }

        const hashed = hashToken(token);

        const [users] = await pool.query(
            `SELECT id FROM users
             WHERE email_verification_token = ?
             AND email_verification_expires > NOW()`,
            [hashed]
        );

        if (users.length === 0) {
            return res.status(400).json({
                status: 'error',
                message: 'Invalid or expired verification link.',
            });
        }

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
            message: 'Email verified! You can now log in.',
        });

    } catch (error) {
        console.error('Verify email error:', error);
        res.status(500).json({ status: 'error', message: 'Verification failed.' });
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

        const [users] = await pool.query(
            'SELECT * FROM users WHERE email = ?',
            [cleanEmail]
        );

        if (users.length === 0) {
            return res.status(401).json({
                status: 'error',
                message: 'Invalid email or password.',
            });
        }

        const user = users[0];

        // Check lockout
        if (user.locked_until && new Date(user.locked_until) > new Date()) {
            return res.status(423).json({
                status: 'error',
                message: `Account locked until ${new Date(user.locked_until).toLocaleTimeString()}.`,
            });
        }

        // Verify password
        const valid = await bcrypt.compare(password, user.password_hash);
        if (!valid) {
            const attempts = (user.login_attempts || 0) + 1;
            const update = { login_attempts: attempts };
            if (attempts >= MAX_ATTEMPTS) {
                update.locked_until = new Date(
                    Date.now() + LOCKOUT_MINUTES * 60 * 1000
                );
            }
            await pool.query(
                'UPDATE users SET login_attempts=?, locked_until=? WHERE id=?',
                [update.login_attempts, update.locked_until || null, user.id]
            );
            return res.status(401).json({
                status: 'error',
                message: 'Invalid email or password.',
            });
        }

        // Email verified?
        if (!user.is_email_verified) {
            return res.status(403).json({
                status: 'error',
                message: 'Please verify your email first.',
            });
        }

        // Active?
        if (!user.is_active) {
            return res.status(403).json({
                status: 'error',
                message: 'Account deactivated.',
            });
        }

        // Reset attempts
        await pool.query(
            `UPDATE users SET
             login_attempts = 0,
             locked_until = NULL,
             last_login_at = NOW(),
             updated_at = NOW()
             WHERE id = ?`,
            [user.id]
        );

        // Generate JWT
        const token = jwt.sign(
            {
                user_id: user.id,
                email: user.email,
                first_name: user.first_name,
                last_name: user.last_name,
            },
            process.env.JWT_SECRET,
            { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
        );

        // Log session
        await pool.query(
            `INSERT INTO user_sessions
             (user_id, session_id, ip_address, user_agent, login_at, is_active)
             VALUES (?, ?, ?, ?, NOW(), 1)`,
            [user.id, token.slice(-32), req.ip, req.get('User-Agent') || '']
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
        res.status(500).json({ status: 'error', message: 'Login failed.' });
    }
};

// ================================================================
// LOGOUT
// ================================================================
exports.logout = async (req, res) => {
    try {
        if (req.user?.user_id) {
            await pool.query(
                `UPDATE user_sessions
                 SET is_active = 0, logout_at = NOW()
                 WHERE user_id = ? AND is_active = 1`,
                [req.user.user_id]
            );
        }
        res.status(200).json({
            status: 'success',
            message: 'Logged out successfully.',
        });
    } catch (error) {
        res.status(500).json({ status: 'error', message: 'Logout failed.' });
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
                message: 'Email required.',
            });
        }

        const cleanEmail = email.trim().toLowerCase();
        const [users] = await pool.query(
            'SELECT id, first_name FROM users WHERE email = ?',
            [cleanEmail]
        );

        if (users.length > 0) {
            const rawToken = generateToken();
            const hashed = hashToken(rawToken);
            const expiry = new Date(Date.now() + 60 * 60 * 1000);

            await pool.query(
                `UPDATE users SET
                 password_reset_token = ?,
                 password_reset_expires = ?,
                 updated_at = NOW()
                 WHERE id = ?`,
                [hashed, expiry, users[0].id]
            );

            await sendPasswordResetEmail(
                cleanEmail, users[0].first_name, rawToken
            );
        }

        res.status(200).json({
            status: 'success',
            message: 'If that email is registered, a reset link has been sent.',
        });

    } catch (error) {
        console.error('Forgot password error:', error);
        res.status(500).json({ status: 'error', message: 'Request failed.' });
    }
};

// ================================================================
// RESET PASSWORD
// ================================================================
exports.resetPassword = async (req, res) => {
    try {
        const { token, password, password_confirm } = req.body;

        if (!token || !password || !password_confirm) {
            return res.status(400).json({
                status: 'error',
                message: 'All fields required.',
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
                message: 'Password too weak.',
            });
        }

        const hashed = hashToken(token);
        const [users] = await pool.query(
            `SELECT id FROM users
             WHERE password_reset_token = ?
             AND password_reset_expires > NOW()`,
            [hashed]
        );

        if (users.length === 0) {
            return res.status(400).json({
                status: 'error',
                message: 'Reset link invalid or expired.',
            });
        }

        const newHash = await bcrypt.hash(password, BCRYPT_ROUNDS);

        await pool.query(
            `UPDATE users SET
             password_hash = ?,
             password_reset_token = NULL,
             password_reset_expires = NULL,
             login_attempts = 0,
             locked_until = NULL,
             updated_at = NOW()
             WHERE id = ?`,
            [newHash, users[0].id]
        );

        res.status(200).json({
            status: 'success',
            message: 'Password reset successfully.',
        });

    } catch (error) {
        console.error('Reset password error:', error);
        res.status(500).json({ status: 'error', message: 'Reset failed.' });
    }
};

// ================================================================
// GET ME
// ================================================================
exports.getMe = async (req, res) => {
    try {
        const [users] = await pool.query(
            `SELECT id, first_name, last_name, email,
                    is_email_verified, last_login_at, created_at
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
        res.status(500).json({ status: 'error', message: 'Error.' });
    }
};