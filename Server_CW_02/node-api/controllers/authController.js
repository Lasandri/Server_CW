// node-api/controllers/authController.js
// UPDATE register and login to use university_users table from CW1

const bcrypt = require('bcryptjs');
const crypto = require('crypto');
const jwt    = require('jsonwebtoken');
const pool   = require('../config/database');
const { sendVerificationEmail, sendPasswordResetEmail } = require('../services/emailService');
require('dotenv').config();

/**
 * POST /api/auth/register
 * Register university staff in CW1's university_users table
 */
async function register(req, res) {
    const connection = await pool.getConnection();
    try {
        const { full_name, email, password } = req.body;

        await connection.beginTransaction();

        // Check in university_users (CW1 table for university staff)
        const [existing] = await connection.execute(
            'SELECT id FROM university_users WHERE email = ?',
            [email]
        );

        if (existing.length > 0) {
            await connection.rollback();
            return res.status(409).json({
                success: false,
                message: 'An account with this email already exists.'
            });
        }

        const saltRounds       = 12;
        const hashedPassword   = await bcrypt.hash(password, saltRounds);
        const verificationToken = crypto.randomBytes(32).toString('hex');
        const tokenExpires      = new Date();
        tokenExpires.setHours(tokenExpires.getHours() + 24);

        // Split full_name into first and last
        const nameParts  = full_name.trim().split(' ');
        const first_name = nameParts[0];
        const last_name  = nameParts.slice(1).join(' ') || '';

        // Insert into CW1's university_users table
        const [result] = await connection.execute(
            `INSERT INTO university_users
             (email, password_hash, first_name, last_name,
              role, is_verified, verification_token, verification_expires)
             VALUES (?, ?, ?, ?, 'staff', 0, ?, ?)`,
            [email, hashedPassword, first_name, last_name,
             verificationToken, tokenExpires]
        );

        await connection.commit();

        try {
            await sendVerificationEmail(email, full_name, verificationToken);
        } catch (emailError) {
            console.error('Email failed:', emailError.message);
        }

        return res.status(201).json({
            success: true,
            message: 'Registration successful! Please check your email to verify your account.',
            data: { userId: result.insertId, email }
        });

    } catch (error) {
        await connection.rollback();
        console.error('Registration error:', error);
        return res.status(500).json({
            success: false,
            message: 'Registration failed. Please try again.'
        });
    } finally {
        connection.release();
    }
}

/**
 * GET /api/auth/verify-email/:token
 */
async function verifyEmail(req, res) {
    try {
        const { token } = req.params;

        const [rows] = await pool.execute(
            `SELECT id, email, is_verified
             FROM university_users
             WHERE verification_token = ?
             AND verification_expires > NOW()`,
            [token]
        );

        if (rows.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Invalid or expired verification link.'
            });
        }

        if (rows[0].is_verified) {
            return res.status(200).json({
                success: true,
                message: 'Email already verified. You can log in.'
            });
        }

        await pool.execute(
            `UPDATE university_users
             SET is_verified = 1,
                 verification_token = NULL,
                 verification_expires = NULL,
                 updated_at = NOW()
             WHERE id = ?`,
            [rows[0].id]
        );

        return res.status(200).json({
            success: true,
            message: 'Email verified successfully! You can now log in.'
        });

    } catch (error) {
        console.error('Verify email error:', error);
        return res.status(500).json({ success: false, message: 'Verification failed.' });
    }
}

/**
 * POST /api/auth/resend-verification
 */
async function resendVerification(req, res) {
    try {
        const { email } = req.body;

        const [rows] = await pool.execute(
            'SELECT id, first_name, last_name, is_verified FROM university_users WHERE email = ?',
            [email]
        );

        if (rows.length > 0 && !rows[0].is_verified) {
            const newToken  = crypto.randomBytes(32).toString('hex');
            const newExpiry = new Date();
            newExpiry.setHours(newExpiry.getHours() + 24);

            await pool.execute(
                `UPDATE university_users
                 SET verification_token = ?, verification_expires = ?
                 WHERE id = ?`,
                [newToken, newExpiry, rows[0].id]
            );

            const fullName = rows[0].first_name + ' ' + rows[0].last_name;
            await sendVerificationEmail(email, fullName, newToken);
        }

        return res.status(200).json({
            success: true,
            message: 'If your email exists and is unverified, a new link has been sent.'
        });

    } catch (error) {
        console.error('Resend verification error:', error);
        return res.status(500).json({ success: false, message: 'Failed to resend.' });
    }
}

/**
 * POST /api/auth/login
 * Authenticate against CW1's university_users table
 */
async function login(req, res) {
    try {
        const { email, password } = req.body;

        const [rows] = await pool.execute(
            `SELECT id, first_name, last_name, email,
                    password_hash, is_verified, role
             FROM university_users WHERE email = ?`,
            [email]
        );

        if (rows.length === 0) {
            return res.status(401).json({
                success: false,
                message: 'Invalid email or password.'
            });
        }

        const user = rows[0];

        const passwordMatch = await bcrypt.compare(password, user.password_hash);
        if (!passwordMatch) {
            return res.status(401).json({
                success: false,
                message: 'Invalid email or password.'
            });
        }

        if (!user.is_verified) {
            return res.status(403).json({
                success: false,
                message: 'Please verify your email before logging in.',
                action: 'VERIFY_EMAIL'
            });
        }

        const token = jwt.sign(
            { userId: user.id, email: user.email, role: user.role },
            process.env.JWT_SECRET,
            { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
        );

        // Update last login
        await pool.execute(
            'UPDATE university_users SET last_login = NOW() WHERE id = ?',
            [user.id]
        );

        // Log to user_sessions (CW1 table)
        const ipAddress = req.ip || '';
        const userAgent = req.headers['user-agent'] || '';

        // await pool.execute(
        //     `INSERT INTO user_sessions (user_id, ip_address, user_agent, login_at, is_active)
        //      VALUES (?, ?, ?, NOW(), 1)`,
        //     [user.id, ipAddress.substring(0, 45), userAgent.substring(0, 255)]
        // );

        return res.status(200).json({
            success: true,
            message: 'Login successful.',
            data: {
                token,
                user: {
                    id:         user.id,
                    full_name:  user.first_name + ' ' + user.last_name,
                    email:      user.email,
                    role:       user.role
                }
            }
        });

    } catch (error) {
        console.error('Login error:', error);
        return res.status(500).json({ success: false, message: 'Login failed.' });
    }
}

/**
 * POST /api/auth/logout
 */
async function logout(req, res) {
    try {
        if (req.user) {
            // Update session logout time in CW1
            await pool.execute(
                `UPDATE user_sessions
                 SET logout_at = NOW(), is_active = 0
                 WHERE user_id = ? AND is_active = 1`,
                [req.user.id]
            );
        }
        return res.status(200).json({ success: true, message: 'Logged out successfully.' });
    } catch (error) {
        return res.status(500).json({ success: false, message: 'Logout failed.' });
    }
}

/**
 * POST /api/auth/forgot-password
 */
async function forgotPassword(req, res) {
    try {
        const { email } = req.body;

        const [rows] = await pool.execute(
            'SELECT id, first_name, last_name, is_verified FROM university_users WHERE email = ?',
            [email]
        );

        if (rows.length > 0 && rows[0].is_verified) {
            const resetToken  = crypto.randomBytes(32).toString('hex');
            const resetExpiry = new Date();
            resetExpiry.setHours(resetExpiry.getHours() + 1);

            await pool.execute(
                `UPDATE university_users
                 SET reset_token = ?, reset_expires = ?
                 WHERE id = ?`,
                [resetToken, resetExpiry, rows[0].id]
            );

            const fullName = rows[0].first_name + ' ' + rows[0].last_name;
            try {
                await sendPasswordResetEmail(email, fullName, resetToken);
            } catch (e) {
                console.error('Reset email failed:', e.message);
            }
        }

        return res.status(200).json({
            success: true,
            message: 'If an account exists with this email, a reset link has been sent.'
        });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Request failed.' });
    }
}

/**
 * POST /api/auth/reset-password/:token
 */
async function resetPassword(req, res) {
    try {
        const { token }    = req.params;
        const { password } = req.body;

        const [rows] = await pool.execute(
            `SELECT id FROM university_users
             WHERE reset_token = ? AND reset_expires > NOW()`,
            [token]
        );

        if (rows.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Invalid or expired reset link.'
            });
        }

        const hashedPassword = await bcrypt.hash(password, 12);

        await pool.execute(
            `UPDATE university_users
             SET password_hash = ?,
                 reset_token = NULL,
                 reset_expires = NULL,
                 updated_at = NOW()
             WHERE id = ?`,
            [hashedPassword, rows[0].id]
        );

        return res.status(200).json({
            success: true,
            message: 'Password reset successful. You can now log in.'
        });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Reset failed.' });
    }
}

/**
 * GET /api/auth/me
 */
async function getProfile(req, res) {
    try {
        const [rows] = await pool.execute(
            `SELECT id, first_name, last_name, email, role,
                    is_verified, last_login, created_at
             FROM university_users WHERE id = ?`,
            [req.user.id]
        );

        if (rows.length === 0) {
            return res.status(404).json({ success: false, message: 'User not found.' });
        }

        const user = {
            ...rows[0],
            full_name: rows[0].first_name + ' ' + rows[0].last_name
        };

        return res.json({ success: true, data: user });

    } catch (error) {
        return res.status(500).json({ success: false, message: 'Failed to get profile.' });
    }
}

module.exports = {
    register,
    verifyEmail,
    resendVerification,
    login,
    logout,
    forgotPassword,
    resetPassword,
    getProfile
};