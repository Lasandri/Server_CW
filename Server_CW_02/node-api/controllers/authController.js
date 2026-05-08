// node-api/controllers/authController.js

const bcrypt = require('bcryptjs');
const crypto = require('crypto');
const jwt = require('jsonwebtoken');
const pool = require('../config/database');
const { sendVerificationEmail, sendPasswordResetEmail } = require('../services/emailService');
require('dotenv').config();

/**
 * POST /api/auth/register
 * Register a new university staff user
 */
async function register(req, res) {
    const connection = await pool.getConnection();
    
    try {
        const { full_name, email, password } = req.body;

        await connection.beginTransaction();

        // Check if email already exists in database
        const [existingUsers] = await connection.execute(
            'SELECT id FROM users WHERE email = ?',
            [email]
        );

        if (existingUsers.length > 0) {
            await connection.rollback();
            return res.status(409).json({
                success: false,
                message: 'An account with this email already exists.'
            });
        }

        // Hash password using bcrypt with 12 salt rounds (exceeds minimum 10)
        const saltRounds = 12;
        const hashedPassword = await bcrypt.hash(password, saltRounds);

        // Generate cryptographically random verification token
        const verificationToken = crypto.randomBytes(32).toString('hex');

        // Set token expiry to 24 hours from now
        const tokenExpires = new Date();
        tokenExpires.setHours(tokenExpires.getHours() + 24);

        // Insert new user into database
        const [result] = await connection.execute(
            `INSERT INTO users 
             (full_name, email, password, verification_token, verification_token_expires) 
             VALUES (?, ?, ?, ?, ?)`,
            [full_name, email, hashedPassword, verificationToken, tokenExpires]
        );

        await connection.commit();

        // Send verification email (non-blocking - don't fail registration if email fails)
        try {
            await sendVerificationEmail(email, full_name, verificationToken);
        } catch (emailError) {
            console.error('Email sending failed:', emailError.message);
            // Registration succeeds but note email issue in response
        }

        return res.status(201).json({
            success: true,
            message: 'Registration successful! Please check your email to verify your account.',
            data: {
                userId: result.insertId,
                email: email
            }
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
 * Verify user's email address using the token from the email link
 */
async function verifyEmail(req, res) {
    try {
        const { token } = req.params;

        if (!token || token.length < 10) {
            return res.status(400).json({
                success: false,
                message: 'Invalid verification token.'
            });
        }

        // Find user with matching token that hasn't expired
        const [rows] = await pool.execute(
            `SELECT id, email, email_verified 
             FROM users 
             WHERE verification_token = ? 
             AND verification_token_expires > NOW()`,
            [token]
        );

        if (rows.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Invalid or expired verification link. Please request a new one.'
            });
        }

        const user = rows[0];

        // Check if already verified
        if (user.email_verified) {
            return res.status(200).json({
                success: true,
                message: 'Email already verified. You can log in.'
            });
        }

        // Mark email as verified and clear the token (single-use)
        await pool.execute(
            `UPDATE users 
             SET email_verified = 1, 
                 verification_token = NULL, 
                 verification_token_expires = NULL,
                 updated_at = NOW()
             WHERE id = ?`,
            [user.id]
        );

        return res.status(200).json({
            success: true,
            message: 'Email verified successfully! You can now log in.'
        });

    } catch (error) {
        console.error('Email verification error:', error);
        return res.status(500).json({
            success: false,
            message: 'Verification failed. Please try again.'
        });
    }
}

/**
 * POST /api/auth/resend-verification
 * Resend verification email if token expired
 */
async function resendVerification(req, res) {
    try {
        const { email } = req.body;

        const [rows] = await pool.execute(
            'SELECT id, full_name, email_verified FROM users WHERE email = ?',
            [email]
        );

        // Always return success to prevent email enumeration attacks
        if (rows.length === 0 || rows[0].email_verified) {
            return res.status(200).json({
                success: true,
                message: 'If your email exists and is unverified, a new link has been sent.'
            });
        }

        const user = rows[0];

        // Generate new verification token
        const newToken = crypto.randomBytes(32).toString('hex');
        const newExpiry = new Date();
        newExpiry.setHours(newExpiry.getHours() + 24);

        await pool.execute(
            `UPDATE users 
             SET verification_token = ?, verification_token_expires = ? 
             WHERE id = ?`,
            [newToken, newExpiry, user.id]
        );

        await sendVerificationEmail(email, user.full_name, newToken);

        return res.status(200).json({
            success: true,
            message: 'A new verification email has been sent.'
        });

    } catch (error) {
        console.error('Resend verification error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to resend verification email.'
        });
    }
}

/**
 * POST /api/auth/login
 * Authenticate user and return JWT token
 */
async function login(req, res) {
    try {
        const { email, password } = req.body;

        // Find user by email
        const [rows] = await pool.execute(
            `SELECT id, full_name, email, password, 
                    email_verified, is_active, role 
             FROM users WHERE email = ?`,
            [email]
        );

        // Generic error to prevent user enumeration
        if (rows.length === 0) {
            return res.status(401).json({
                success: false,
                message: 'Invalid email or password.'
            });
        }

        const user = rows[0];

        // Check account is active
        if (!user.is_active) {
            return res.status(403).json({
                success: false,
                message: 'Your account has been deactivated. Please contact admin.'
            });
        }

        // Compare password with bcrypt hash
        const passwordMatch = await bcrypt.compare(password, user.password);

        if (!passwordMatch) {
            return res.status(401).json({
                success: false,
                message: 'Invalid email or password.'
            });
        }

        // Block login if email not verified
        if (!user.email_verified) {
            return res.status(403).json({
                success: false,
                message: 'Please verify your email address before logging in.',
                action: 'VERIFY_EMAIL'
            });
        }

        // Generate JWT token with user info
        const token = jwt.sign(
            {
                userId: user.id,
                email: user.email,
                role: user.role
            },
            process.env.JWT_SECRET,
            { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
        );

        // Log this login action for usage statistics
        const ipAddress = req.ip || req.connection.remoteAddress;
        const userAgent = req.headers['user-agent'] || '';

        await pool.execute(
            `INSERT INTO login_logs (user_id, ip_address, user_agent, action) 
             VALUES (?, ?, ?, 'login')`,
            [user.id, ipAddress.substring(0, 45), userAgent.substring(0, 255)]
        );

        return res.status(200).json({
            success: true,
            message: 'Login successful.',
            data: {
                token: token,
                user: {
                    id: user.id,
                    full_name: user.full_name,
                    email: user.email,
                    role: user.role
                }
            }
        });

    } catch (error) {
        console.error('Login error:', error);
        return res.status(500).json({
            success: false,
            message: 'Login failed. Please try again.'
        });
    }
}

/**
 * POST /api/auth/logout
 * Log the logout action (JWT is stateless, handled client-side)
 */
async function logout(req, res) {
    try {
        // Log logout event (user is attached by verifyToken middleware)
        if (req.user) {
            const ipAddress = req.ip || req.connection.remoteAddress;
            await pool.execute(
                `INSERT INTO login_logs (user_id, ip_address, action) 
                 VALUES (?, ?, 'logout')`,
                [req.user.id, ipAddress.substring(0, 45)]
            );
        }

        return res.status(200).json({
            success: true,
            message: 'Logged out successfully.'
        });

    } catch (error) {
        console.error('Logout error:', error);
        return res.status(500).json({
            success: false,
            message: 'Logout failed.'
        });
    }
}

/**
 * POST /api/auth/forgot-password
 * Send password reset email
 */
async function forgotPassword(req, res) {
    try {
        const { email } = req.body;

        // Find user - but always return same response (prevent email enumeration)
        const [rows] = await pool.execute(
            'SELECT id, full_name, email_verified FROM users WHERE email = ?',
            [email]
        );

        if (rows.length > 0 && rows[0].email_verified) {
            const user = rows[0];

            // Generate cryptographically secure reset token
            const resetToken = crypto.randomBytes(32).toString('hex');

            // Token expires in 1 hour
            const resetExpiry = new Date();
            resetExpiry.setHours(resetExpiry.getHours() + 1);

            // Save reset token to database
            await pool.execute(
                `UPDATE users 
                 SET reset_token = ?, reset_token_expires = ? 
                 WHERE id = ?`,
                [resetToken, resetExpiry, user.id]
            );

            // Send reset email
            try {
                await sendPasswordResetEmail(email, user.full_name, resetToken);
            } catch (emailError) {
                console.error('Reset email failed:', emailError.message);
            }
        }

        // Always return the same message regardless of whether email exists
        return res.status(200).json({
            success: true,
            message: 'If an account exists with this email, a password reset link has been sent.'
        });

    } catch (error) {
        console.error('Forgot password error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to process request. Please try again.'
        });
    }
}

/**
 * POST /api/auth/reset-password/:token
 * Reset password using the token from email
 */
async function resetPassword(req, res) {
    try {
        const { token } = req.params;
        const { password } = req.body;

        // Find user with valid (non-expired) reset token
        const [rows] = await pool.execute(
            `SELECT id, email 
             FROM users 
             WHERE reset_token = ? 
             AND reset_token_expires > NOW()`,
            [token]
        );

        if (rows.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Invalid or expired reset link. Please request a new password reset.'
            });
        }

        const user = rows[0];

        // Hash the new password
        const saltRounds = 12;
        const hashedPassword = await bcrypt.hash(password, saltRounds);

        // Update password and clear reset token (single-use enforcement)
        await pool.execute(
            `UPDATE users 
             SET password = ?, 
                 reset_token = NULL, 
                 reset_token_expires = NULL,
                 updated_at = NOW()
             WHERE id = ?`,
            [hashedPassword, user.id]
        );

        // Log the password reset action
        await pool.execute(
            `INSERT INTO login_logs (user_id, action) VALUES (?, 'password_reset')`,
            [user.id]
        );

        return res.status(200).json({
            success: true,
            message: 'Password reset successful. You can now log in with your new password.'
        });

    } catch (error) {
        console.error('Reset password error:', error);
        return res.status(500).json({
            success: false,
            message: 'Password reset failed. Please try again.'
        });
    }
}

/**
 * GET /api/auth/me
 * Get current authenticated user's profile
 */
async function getProfile(req, res) {
    try {
        const [rows] = await pool.execute(
            `SELECT id, full_name, email, role, email_verified, created_at 
             FROM users WHERE id = ?`,
            [req.user.id]
        );

        if (rows.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'User not found.'
            });
        }

        return res.status(200).json({
            success: true,
            data: rows[0]
        });

    } catch (error) {
        console.error('Get profile error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to retrieve profile.'
        });
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