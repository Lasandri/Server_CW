/**
 * Automated Winner Selection - Cron Job
 * 
 * Runs daily at 6 PM (18:00) to:
 *   1. Select the highest bidder for tomorrow
 *   2. Mark their bid as "won"
 *   3. Mark all other bids as "lost"
 *   4. Create daily_winners record
 *   5. Send email notifications
 * 
 * Setup (Linux/Mac):
 *   crontab -e
 *   0 18 * * * /usr/bin/node /path/to/node-api/cron/selectWinner.js
 * 
 * Setup (Windows Task Scheduler):
 *   Create task → Trigger: Daily at 6:00 PM
 *   Action: node C:\path\to\node-api\cron\selectWinner.js
 * 
 * Manual run: node cron/selectWinner.js
 */

const mysql = require('mysql2/promise');
const nodemailer = require('nodemailer');
require('dotenv').config({ path: __dirname + '/../.env' });

// Database connection
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT || 3306,
});

// Email transporter
const transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port: parseInt(process.env.SMTP_PORT),
    auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
    },
});

/**
 * Get tomorrow's date in YYYY-MM-DD format.
 */
function getTomorrow() {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    return d.toISOString().split('T')[0];
}

/**
 * Check monthly win count for a user.
 */
async function getMonthlyWins(userId) {
    const now = new Date();
    const startDate = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
    const endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0)
        .toISOString().split('T')[0];

    const [rows] = await pool.query(
        `SELECT COUNT(*) as wins FROM daily_winners 
         WHERE user_id = ? AND display_date >= ? AND display_date <= ?`,
        [userId, startDate, endDate]
    );

    return rows[0].wins;
}

/**
 * Check if user has event bonus this month.
 */
async function hasEventBonus(userId) {
    const now = new Date();
    const [rows] = await pool.query(
        `SELECT COUNT(*) as count FROM event_participation 
         WHERE user_id = ? AND event_month = ? AND event_year = ? AND is_verified = 1`,
        [userId, now.getMonth() + 1, now.getFullYear()]
    );

    return rows[0].count > 0;
}

/**
 * Send notification email.
 */
async function sendEmail(toEmail, firstName, subject, message) {
    try {
        await transporter.sendMail({
            from: `"Alumni Influencers" <${process.env.EMAIL_FROM}>`,
            to: toEmail,
            subject: subject,
            html: `
                <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
                    <h2>${subject}</h2>
                    <p>Hello ${firstName},</p>
                    <p>${message}</p>
                    <hr style="border:1px solid #eee;">
                    <p style="color:#999;font-size:12px;">Alumni Influencers - University of Eastminster</p>
                </div>
            `,
        });
        console.log(`  📧 Email sent to ${toEmail}`);
    } catch (err) {
        console.error(`  ❌ Failed to email ${toEmail}:`, err.message);
    }
}

/**
 * Main winner selection process.
 */
async function selectWinner() {
    const tomorrow = getTomorrow();
    console.log(`\n========================================`);
    console.log(`🏆 Winner Selection - ${new Date().toISOString()}`);
    console.log(`📅 Selecting winner for: ${tomorrow}`);
    console.log(`========================================\n`);

    try {
        // 1. Check if winner already selected
        const [existing] = await pool.query(
            'SELECT id FROM daily_winners WHERE display_date = ?',
            [tomorrow]
        );

        if (existing.length > 0) {
            console.log('⚠️  Winner already selected for this date. Exiting.');
            return;
        }

        // 2. Get all active bids for tomorrow (highest first)
        const [bids] = await pool.query(
            `SELECT b.*, u.first_name, u.last_name, u.email 
             FROM bids b
             JOIN users u ON u.id = b.user_id
             WHERE b.bid_date = ? 
             AND b.status IN ('pending', 'winning', 'losing')
             ORDER BY b.amount DESC, b.created_at ASC`,
            [tomorrow]
        );

        if (bids.length === 0) {
            console.log('ℹ️  No bids for tomorrow. No winner selected.');
            return;
        }

        console.log(`📊 Total active bids: ${bids.length}`);

        // 3. Find eligible winner (check monthly limits)
        let winnerBid = null;

        for (const bid of bids) {
            const monthlyWins = await getMonthlyWins(bid.user_id);
            const eventBonus = await hasEventBonus(bid.user_id);
            const maxWins = 3 + (eventBonus ? 1 : 0);

            console.log(`  Checking: ${bid.first_name} ${bid.last_name} - £${bid.amount} (${monthlyWins}/${maxWins} monthly wins)`);

            if (monthlyWins < maxWins) {
                winnerBid = bid;
                break;
            } else {
                console.log(`  ⚠️  Monthly limit reached - skipping`);
            }
        }

        if (!winnerBid) {
            console.log('❌ All bidders have reached monthly limits. No winner.');
            return;
        }

        console.log(`\n🎉 WINNER: ${winnerBid.first_name} ${winnerBid.last_name} with £${winnerBid.amount}\n`);

        // 4. Mark winning bid
        await pool.query(
            "UPDATE bids SET status = 'won', updated_at = NOW() WHERE id = ?",
            [winnerBid.id]
        );

        // 5. Mark all other bids as lost
        await pool.query(
            `UPDATE bids SET status = 'lost', updated_at = NOW() 
             WHERE bid_date = ? AND id != ? AND status != 'cancelled'`,
            [tomorrow, winnerBid.id]
        );

        // 6. Deactivate previous winners
        await pool.query(
            "UPDATE daily_winners SET is_active = 0 WHERE is_active = 1"
        );

        // 7. Create winner record
        await pool.query(
            `INSERT INTO daily_winners (user_id, bid_id, display_date, winning_amount, is_active, created_at)
             VALUES (?, ?, ?, ?, 1, NOW())`,
            [winnerBid.user_id, winnerBid.id, tomorrow, winnerBid.amount]
        );

        // 8. Create notifications
        // Winner notification
        await pool.query(
            `INSERT INTO bid_notifications (user_id, bid_id, message, type, created_at)
             VALUES (?, ?, ?, 'winner', NOW())`,
            [winnerBid.user_id, winnerBid.id,
             `🎉 Congratulations! You are Alumni of the Day for ${tomorrow}!`]
        );

        // Send winner email
        await sendEmail(
            winnerBid.email,
            winnerBid.first_name,
            '🎉 You are Alumni of the Day!',
            `Your bid of £${parseFloat(winnerBid.amount).toFixed(2)} won! Your profile will be featured on ${tomorrow}.`
        );

        // Notify and email losers
        for (const bid of bids) {
            if (bid.id !== winnerBid.id) {
                await pool.query(
                    `INSERT INTO bid_notifications (user_id, bid_id, message, type, created_at)
                     VALUES (?, ?, ?, 'loser', NOW())`,
                    [bid.user_id, bid.id,
                     `Your bid for ${tomorrow} was not successful. Try again tomorrow!`]
                );

                await sendEmail(
                    bid.email,
                    bid.first_name,
                    'Bid Result - Alumni of the Day',
                    `Your bid of £${parseFloat(bid.amount).toFixed(2)} for ${tomorrow} was not successful. Better luck next time!`
                );
            }
        }

        console.log('✅ Winner selection complete!\n');

    } catch (error) {
        console.error('❌ Error during winner selection:', error);
    } finally {
        await pool.end();
    }
}

// Run the selection
selectWinner();