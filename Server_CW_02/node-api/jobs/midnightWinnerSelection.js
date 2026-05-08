// node-api/jobs/midnightWinnerSelection.js

const pool = require('../config/database');
require('dotenv').config();

/**
 * Automated midnight winner selection
 * Runs at 00:00 every day
 * Selects highest bidder for each open feature
 */
async function runMidnightSelection() {
    const connection = await pool.getConnection();

    console.log('\n🌙 Midnight Winner Selection Starting...');
    console.log('Time:', new Date().toLocaleString());

    try {
        await connection.beginTransaction();

        const currentMonth = new Date().getMonth() + 1;
        const currentYear  = new Date().getFullYear();

        // Get all open features for current month
        const [features] = await connection.execute(
            `SELECT * FROM bid_features
             WHERE bid_month = ? AND bid_year = ? AND status = 'open'`,
            [currentMonth, currentYear]
        );

        console.log(`📋 Found ${features.length} open features`);

        if (features.length === 0) {
            console.log('ℹ️  No open features. Exiting.');
            await connection.rollback();
            return;
        }

        let winnersSelected = 0;

        for (const feature of features) {
            console.log(`\n🎯 Processing: ${feature.feature_name}`);

            // Find highest bidder
            const [topBids] = await connection.execute(
                `SELECT b.*, a.full_name
                 FROM bids b
                 JOIN alumni a ON b.alumni_id = a.id
                 WHERE b.feature_id = ? AND b.status = 'active'
                 ORDER BY b.bid_amount DESC
                 LIMIT 1`,
                [feature.id]
            );

            if (topBids.length === 0) {
                console.log('   ⚠️  No bids. Closing without winner.');
                await connection.execute(
                    `UPDATE bid_features
                     SET status = 'winner_selected',
                         winner_selected_at = NOW()
                     WHERE id = ?`,
                    [feature.id]
                );
                continue;
            }

            const winner = topBids[0];
            console.log(`   🏆 Winner: ${winner.full_name}`);

            // Mark winning bid
            await connection.execute(
                `UPDATE bids SET status = 'won', is_winner = 1, updated_at = NOW()
                 WHERE id = ?`,
                [winner.id]
            );

            // Mark losing bids
            await connection.execute(
                `UPDATE bids SET status = 'lost', is_winner = 0, updated_at = NOW()
                 WHERE feature_id = ? AND id != ? AND status = 'active'`,
                [feature.id, winner.id]
            );

            // Update feature record
            await connection.execute(
                `UPDATE bid_features
                 SET status = 'winner_selected',
                     winner_alumni_id = ?,
                     winner_selected_at = NOW()
                 WHERE id = ?`,
                [winner.alumni_id, feature.id]
            );

            // Mark profile as featured
            await connection.execute(
                `UPDATE alumni SET is_featured = 1 WHERE id = ?`,
                [winner.alumni_id]
            );

            winnersSelected++;
        }

        // Create next month's features automatically
        const nextMonth = currentMonth === 12 ? 1 : currentMonth + 1;
        const nextYear  = currentMonth === 12 ? currentYear + 1 : currentYear;

        const [existingNext] = await connection.execute(
            `SELECT id FROM bid_features
             WHERE bid_month = ? AND bid_year = ?`,
            [nextMonth, nextYear]
        );

        if (existingNext.length === 0) {
            await connection.execute(
                `INSERT INTO bid_features (feature_name, description, bid_month, bid_year, status)
                 VALUES
                 (?, ?, ?, ?, 'open'),
                 (?, ?, ?, ?, 'open'),
                 (?, ?, ?, ?, 'open')`,
                [
                    'Homepage Featured Spot 1',
                    'Prime homepage placement - top of featured alumni section',
                    nextMonth, nextYear,
                    'Homepage Featured Spot 2',
                    'Second homepage featured placement',
                    nextMonth, nextYear,
                    'Newsletter Feature',
                    'Featured in university monthly newsletter',
                    nextMonth, nextYear
                ]
            );
            console.log(`\n📅 Created features for next month (${nextMonth}/${nextYear})`);
        }

        await connection.commit();
        console.log(`\n✅ Done! ${winnersSelected} winners selected.`);

    } catch (error) {
        await connection.rollback();
        console.error('❌ Midnight selection failed:', error.message);
    } finally {
        connection.release();
    }
}

module.exports = { runMidnightSelection };