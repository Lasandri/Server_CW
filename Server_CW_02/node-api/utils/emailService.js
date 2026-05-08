/**
 * Email Service Module
 * 
 * Provides email sending functionality using Nodemailer.
 * Supports HTML emails for verification and password reset.
 * Uses SMTP transport (configurable for Mailtrap/production).
 */

const nodemailer = require('nodemailer');
require('dotenv').config();

// Create reusable SMTP transporter
const transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port: parseInt(process.env.SMTP_PORT),
    auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
    },
});

/**
 * Send email verification link to newly registered user.
 * 
 * @param {string} toEmail - Recipient email address
 * @param {string} firstName - Recipient's first name
 * @param {string} token - Raw verification token
 */
async function sendVerificationEmail(toEmail, firstName, token) {
    const verificationUrl = `${process.env.CI_APP_URL}/verify-email?token=${token}`;

    const mailOptions = {
        from: `"Alumni Influencers Platform" <${process.env.EMAIL_FROM}>`,
        to: toEmail,
        subject: 'Verify Your Email Address - Alumni Influencers',
        html: `
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #333;">Welcome to Alumni Influencers, ${firstName}!</h2>
                <p>Thank you for registering. Please verify your email address by clicking the button below:</p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="${verificationUrl}" 
                       style="background-color: #4CAF50; color: white; padding: 14px 28px; 
                              text-decoration: none; border-radius: 6px; font-weight: bold;">
                        Verify Email Address
                    </a>
                </p>
                <p>Or copy and paste this URL into your browser:</p>
                <p style="word-break: break-all; color: #666;">${verificationUrl}</p>
                <p><strong>⏰ This link expires in 24 hours.</strong></p>
                <hr style="border: 1px solid #eee; margin: 20px 0;">
                <p style="color: #999; font-size: 12px;">
                    If you did not create this account, please ignore this email.<br>
                    Alumni Influencers Platform - University of Eastminster
                </p>
            </div>
        `,
    };

    try {
        await transporter.sendMail(mailOptions);
        console.log(`📧 Verification email sent to ${toEmail}`);
    } catch (error) {
        console.error('❌ Failed to send verification email:', error.message);
        throw error;
    }
}

/**
 * Send password reset link to user.
 * 
 * @param {string} toEmail - Recipient email address
 * @param {string} firstName - Recipient's first name
 * @param {string} token - Raw reset token
 */
async function sendPasswordResetEmail(toEmail, firstName, token) {
    const resetUrl = `${process.env.CI_APP_URL}/reset-password?token=${token}`;

    const mailOptions = {
        from: `"Alumni Influencers Platform" <${process.env.EMAIL_FROM}>`,
        to: toEmail,
        subject: 'Password Reset Request - Alumni Influencers',
        html: `
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #333;">Password Reset Request</h2>
                <p>Hello ${firstName},</p>
                <p>We received a request to reset your password. Click the button below:</p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="${resetUrl}" 
                       style="background-color: #2196F3; color: white; padding: 14px 28px; 
                              text-decoration: none; border-radius: 6px; font-weight: bold;">
                        Reset Password
                    </a>
                </p>
                <p>Or copy and paste this URL:</p>
                <p style="word-break: break-all; color: #666;">${resetUrl}</p>
                <p><strong>⏰ This link expires in 1 hour.</strong></p>
                <hr style="border: 1px solid #eee; margin: 20px 0;">
                <p style="color: #999; font-size: 12px;">
                    If you didn't request this, please ignore this email.<br>
                    Alumni Influencers Platform - University of Eastminster
                </p>
            </div>
        `,
    };

    try {
        await transporter.sendMail(mailOptions);
        console.log(`📧 Password reset email sent to ${toEmail}`);
    } catch (error) {
        console.error('❌ Failed to send reset email:', error.message);
        throw error;
    }
}

module.exports = { sendVerificationEmail, sendPasswordResetEmail };