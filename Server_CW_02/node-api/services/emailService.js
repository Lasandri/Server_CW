// node-api/services/emailService.js

const nodemailer = require('nodemailer');
require('dotenv').config();

/**
 * Create Gmail transporter
 * Uses real Gmail SMTP - emails go to actual inboxes
 */
function createTransporter() {
    return nodemailer.createTransport({
        host: 'smtp.gmail.com',
        port: 587,
        secure: false,
        auth: {
            user: process.env.EMAIL_USER,
            pass: process.env.EMAIL_PASSWORD  // Gmail App Password (16 chars)
        },
        tls: {
            rejectUnauthorized: false
        }
    });
}

/**
 * Send email verification to newly registered user
 */
async function sendVerificationEmail(toEmail, fullName, token) {
    const transporter = createTransporter();

    const verifyUrl = `${process.env.APP_URL}/auth/verify_email/${token}`;

    console.log('📧 Sending verification email to:', toEmail);
    console.log('🔗 Verify URL:', verifyUrl);

    const mailOptions = {
        from: `"Alumni Analytics Dashboard" <${process.env.EMAIL_USER}>`,
        to: toEmail,
        subject: 'Verify Your Email - Alumni Analytics Dashboard',
        html: `
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="padding:40px 0;">
                        <table width="600" cellpadding="0" cellspacing="0"
                               style="background:#ffffff; border-radius:12px;
                                      box-shadow:0 4px 20px rgba(0,0,0,0.1);">

                            <!-- Header -->
                            <tr>
                                <td style="background:linear-gradient(135deg,#0f3460,#e94560);
                                           padding:35px 30px; text-align:center;
                                           border-radius:12px 12px 0 0;">
                                    <h1 style="color:white; margin:0; font-size:26px;
                                               font-weight:bold;">
                                        Alumni Analytics Dashboard
                                    </h1>
                                    <p style="color:rgba(255,255,255,0.85); margin:8px 0 0;
                                              font-size:15px;">
                                        University of Westminster
                                    </p>
                                </td>
                            </tr>

                            <!-- Body -->
                            <tr>
                                <td style="padding:40px 35px;">

                                    <h2 style="color:#0f3460; margin:0 0 15px;">
                                        Hello, ${fullName}! 👋
                                    </h2>

                                    <p style="color:#555; line-height:1.7; font-size:15px;">
                                        Thank you for registering with the 
                                        <strong>Alumni Analytics Dashboard</strong>. 
                                        You're almost ready to access university 
                                        alumni insights and analytics.
                                    </p>

                                    <p style="color:#555; line-height:1.7; font-size:15px;">
                                        Please verify your email address by clicking 
                                        the button below:
                                    </p>

                                    <!-- Verify Button -->
                                    <div style="text-align:center; margin:35px 0;">
                                        <a href="${verifyUrl}"
                                           style="background:linear-gradient(135deg,#0f3460,#e94560);
                                                  color:white;
                                                  padding:16px 45px;
                                                  text-decoration:none;
                                                  border-radius:8px;
                                                  font-size:16px;
                                                  font-weight:bold;
                                                  display:inline-block;
                                                  letter-spacing:0.5px;">
                                            ✅ Verify My Email Address
                                        </a>
                                    </div>

                                    <!-- Expiry Notice -->
                                    <div style="background:#fff3cd; border:1px solid #ffc107;
                                                border-radius:8px; padding:12px 15px;
                                                margin:20px 0; text-align:center;">
                                        <p style="margin:0; color:#856404; font-size:14px;">
                                            ⏰ This link expires in <strong>24 hours</strong>
                                        </p>
                                    </div>

                                    <hr style="border:none; border-top:1px solid #eee;
                                               margin:25px 0;">

                                    <!-- Fallback link -->
                                    <p style="color:#888; font-size:13px; margin:0 0 8px;">
                                        Button not working? Copy and paste this URL:
                                    </p>
                                    <p style="margin:0;">
                                        <a href="${verifyUrl}"
                                           style="color:#0f3460; font-size:12px;
                                                  word-break:break-all;">
                                            ${verifyUrl}
                                        </a>
                                    </p>

                                    <p style="color:#aaa; font-size:12px; margin-top:25px;">
                                        If you did not create an account, 
                                        you can safely ignore this email.
                                    </p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="background:#f8f9fa; padding:20px 35px;
                                           text-align:center;
                                           border-radius:0 0 12px 12px;
                                           border-top:1px solid #eee;">
                                    <p style="color:#aaa; font-size:12px; margin:0;">
                                        © 2025 Alumni Analytics Dashboard
                                        · University of Westminster
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        `
    };

    try {
        const info = await transporter.sendMail(mailOptions);
        console.log('✅ Verification email sent to:', toEmail);
        console.log('📨 Message ID:', info.messageId);
        return { success: true, messageId: info.messageId };
    } catch (error) {
        console.error('❌ Failed to send verification email:', error.message);
        throw error;
    }
}

/**
 * Send password reset email
 */
async function sendPasswordResetEmail(toEmail, fullName, token) {
    const transporter = createTransporter();

    const resetUrl = `${process.env.APP_URL}/auth/reset_password/${token}`;

    console.log('📧 Sending password reset email to:', toEmail);

    const mailOptions = {
        from: `"Alumni Analytics Dashboard" <${process.env.EMAIL_USER}>`,
        to: toEmail,
        subject: 'Password Reset - Alumni Analytics Dashboard',
        html: `
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="padding:40px 0;">
                        <table width="600" cellpadding="0" cellspacing="0"
                               style="background:#ffffff; border-radius:12px;
                                      box-shadow:0 4px 20px rgba(0,0,0,0.1);">

                            <!-- Header -->
                            <tr>
                                <td style="background:linear-gradient(135deg,#fd7e14,#e94560);
                                           padding:35px 30px; text-align:center;
                                           border-radius:12px 12px 0 0;">
                                    <h1 style="color:white; margin:0; font-size:26px;">
                                        Password Reset Request
                                    </h1>
                                    <p style="color:rgba(255,255,255,0.85); margin:8px 0 0;">
                                        Alumni Analytics Dashboard
                                    </p>
                                </td>
                            </tr>

                            <!-- Body -->
                            <tr>
                                <td style="padding:40px 35px;">
                                    <h2 style="color:#0f3460; margin:0 0 15px;">
                                        Hello, ${fullName}!
                                    </h2>

                                    <p style="color:#555; line-height:1.7; font-size:15px;">
                                        We received a request to reset your password 
                                        for the Alumni Analytics Dashboard.
                                    </p>

                                    <p style="color:#555; line-height:1.7; font-size:15px;">
                                        Click the button below to create a new password:
                                    </p>

                                    <!-- Reset Button -->
                                    <div style="text-align:center; margin:35px 0;">
                                        <a href="${resetUrl}"
                                           style="background:linear-gradient(135deg,#fd7e14,#e94560);
                                                  color:white; padding:16px 45px;
                                                  text-decoration:none; border-radius:8px;
                                                  font-size:16px; font-weight:bold;
                                                  display:inline-block;">
                                            🔑 Reset My Password
                                        </a>
                                    </div>

                                    <!-- Expiry -->
                                    <div style="background:#fff3cd; border:1px solid #ffc107;
                                                border-radius:8px; padding:12px 15px;
                                                margin:20px 0; text-align:center;">
                                        <p style="margin:0; color:#856404; font-size:14px;">
                                            ⏰ This link expires in <strong>1 hour</strong>
                                        </p>
                                    </div>

                                    <hr style="border:none; border-top:1px solid #eee;
                                               margin:25px 0;">

                                    <p style="color:#888; font-size:13px; margin:0 0 8px;">
                                        Button not working? Copy this URL:
                                    </p>
                                    <p style="margin:0;">
                                        <a href="${resetUrl}"
                                           style="color:#0f3460; font-size:12px;
                                                  word-break:break-all;">
                                            ${resetUrl}
                                        </a>
                                    </p>

                                    <p style="color:#aaa; font-size:12px; margin-top:25px;">
                                        If you did not request a password reset, 
                                        please ignore this email. Your password 
                                        will remain unchanged.
                                    </p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="background:#f8f9fa; padding:20px 35px;
                                           text-align:center;
                                           border-radius:0 0 12px 12px;
                                           border-top:1px solid #eee;">
                                    <p style="color:#aaa; font-size:12px; margin:0;">
                                        © 2025 Alumni Analytics Dashboard
                                        · University of Westminster
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        `
    };

    try {
        const info = await transporter.sendMail(mailOptions);
        console.log('✅ Reset email sent to:', toEmail);
        console.log('📨 Message ID:', info.messageId);
        return { success: true, messageId: info.messageId };
    } catch (error) {
        console.error('❌ Failed to send reset email:', error.message);
        throw error;
    }
}

module.exports = { sendVerificationEmail, sendPasswordResetEmail };