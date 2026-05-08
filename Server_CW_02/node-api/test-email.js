// node-api/test-email.js

require('dotenv').config();
const nodemailer = require('nodemailer');

console.log('\n=== Gmail Email Configuration Test ===');
console.log('HOST :', process.env.EMAIL_HOST);
console.log('PORT :', process.env.EMAIL_PORT);
console.log('USER :', process.env.EMAIL_USER);
console.log('PASS :', process.env.EMAIL_PASSWORD 
    ? `✅ Set (${process.env.EMAIL_PASSWORD.length} chars)` 
    : '❌ NOT SET');

async function test() {
    try {
        const transporter = nodemailer.createTransport({
            host: 'smtp.gmail.com',
            port: 587,
            secure: false,
            auth: {
                user: process.env.EMAIL_USER,
                pass: process.env.EMAIL_PASSWORD
            },
            tls: {
                rejectUnauthorized: false
            }
        });

        console.log('\n⏳ Connecting to Gmail SMTP...');
        await transporter.verify();
        console.log('✅ Gmail SMTP connected!\n');

        console.log('⏳ Sending test email...');
        const info = await transporter.sendMail({
            from: `"Alumni Dashboard" <${process.env.EMAIL_USER}>`,
            to: process.env.EMAIL_USER,
            subject: '✅ Email Working - Alumni Dashboard',
            html: `
                <div style="font-family:Arial; padding:30px; max-width:500px;">
                    <h2 style="color:#0f3460;">✅ Email is Working!</h2>
                    <p>Your Gmail SMTP is configured correctly.</p>
                    <p><strong>Time:</strong> ${new Date().toLocaleString()}</p>
                    <p>Registration and password reset emails will work.</p>
                </div>
            `
        });

        console.log('✅ Test email sent successfully!');
        console.log('📨 Message ID:', info.messageId);
        console.log('📬 Check your Gmail inbox:', process.env.EMAIL_USER);
        console.log('\n🎉 All good! Run: node server.js');

    } catch (error) {
        console.error('\n❌ Email test FAILED:', error.message);

        if (error.message.includes('Invalid login') || 
            error.message.includes('BadCredentials')) {
            console.error('\n💡 HOW TO FIX:');
            console.error('   1. Go to: https://myaccount.google.com/security');
            console.error('   2. Enable "2-Step Verification"');
            console.error('   3. Go to: https://myaccount.google.com/apppasswords');
            console.error('   4. Create App Password → name it "Alumni Dashboard"');
            console.error('   5. Copy 16-character code (no spaces)');
            console.error('   6. Update .env: EMAIL_PASSWORD=your16charcode');
            console.error('   7. Run test again: node test-email.js');
        }
    }
}

test();