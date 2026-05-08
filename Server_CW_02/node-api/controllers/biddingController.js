// node-api/controllers/biddingController.js

const pool = require('../config/database');

/**
 * GET /api/bidding/features
 * Get all open bid features for current month
 */
async function getFeatures(req, res) {
    try {
        const currentMonth = new Date().getMonth() + 1;
        const currentYear  = new Date().getFullYear();

        const [features] = await pool.execute(
            `SELECT id, feature_name, description, bid_month, 
                    bid_year, status, winner_selected_at
             FROM bid_features
             WHERE bid_month = ? AND bid_year = ?
             ORDER BY id ASC`,
            [currentMonth, currentYear]
        );

        return res.json({
            success: true,
            data: features,
            current_month: currentMonth,
            current_year: currentYear
        });

    } catch (error) {
        console.error('Get features error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch features'
        });
    }
}

/**
 * GET /api/bidding/my-bids/:alumni_id
 * Get bids placed by this alumni - WITHOUT showing highest bid
 * This is the BLIND part - they see their own bid amount only
 */
async function getMyBids(req, res) {
    try {
        const { alumni_id } = req.params;
        const currentMonth  = new Date().getMonth() + 1;
        const currentYear   = new Date().getFullYear();

        const [bids] = await pool.execute(
            `SELECT b.id, b.bid_amount, b.status, b.is_winner,
                    b.bid_month, b.bid_year, b.updated_at,
                    bf.feature_name, bf.description, bf.status as feature_status
             FROM bids b
             JOIN bid_features bf ON b.feature_id = bf.id
             WHERE b.alumni_id = ?
             AND b.bid_month = ? AND b.bid_year = ?
             ORDER BY b.created_at DESC`,
            [alumni_id, currentMonth, currentYear]
        );

        // BLIND BIDDING: Do NOT include highest bid or other bids
        // Only return this alumni's own bid amount
        const safeBids = bids.map(bid => ({
            id:             bid.id,
            bid_amount:     bid.bid_amount,  // own amount only
            status:         bid.status,
            is_winner:      bid.is_winner,
            bid_month:      bid.bid_month,
            bid_year:       bid.bid_year,
            updated_at:     bid.updated_at,
            feature_name:   bid.feature_name,
            description:    bid.description,
            feature_status: bid.feature_status
            // NOTE: highest_bid is intentionally omitted - blind bidding
        }));

        return res.json({
            success: true,
            data: safeBids
        });

    } catch (error) {
        console.error('Get my bids error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch bids'
        });
    }
}

/**
 * GET /api/bidding/limit/:alumni_id
 * Get monthly bid limit status for alumni
 */
async function getMonthlyLimit(req, res) {
    try {
        const { alumni_id }  = req.params;
        const currentMonth   = new Date().getMonth() + 1;
        const currentYear    = new Date().getFullYear();

        // Get or create limit record
        const [existing] = await pool.execute(
            `SELECT * FROM monthly_bid_limits
             WHERE alumni_id = ? AND bid_month = ? AND bid_year = ?`,
            [alumni_id, currentMonth, currentYear]
        );

        if (existing.length === 0) {
            // No bids yet this month
            return res.json({
                success: true,
                data: {
                    bids_used:    0,
                    max_bids:     3,
                    bids_remaining: 3,
                    can_bid:      true
                }
            });
        }

        const limit = existing[0];
        return res.json({
            success: true,
            data: {
                bids_used:      limit.bids_used,
                max_bids:       limit.max_bids,
                bids_remaining: limit.max_bids - limit.bids_used,
                can_bid:        limit.bids_used < limit.max_bids
            }
        });

    } catch (error) {
        console.error('Get limit error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch bid limit'
        });
    }
}

/**
 * POST /api/bidding/place
 * Place a new blind bid on a feature
 * Body: { alumni_id, feature_id, bid_amount }
 */
async function placeBid(req, res) {
    const connection = await pool.getConnection();

    try {
        const { alumni_id, feature_id, bid_amount } = req.body;
        const currentMonth = new Date().getMonth() + 1;
        const currentYear  = new Date().getFullYear();

        // Validate inputs
        if (!alumni_id || !feature_id || !bid_amount) {
            return res.status(400).json({
                success: false,
                message: 'alumni_id, feature_id and bid_amount are required'
            });
        }

        if (parseFloat(bid_amount) <= 0) {
            return res.status(400).json({
                success: false,
                message: 'Bid amount must be greater than zero'
            });
        }

        await connection.beginTransaction();

        // 1. Check feature exists and is open
        const [features] = await connection.execute(
            `SELECT * FROM bid_features
             WHERE id = ? AND status = 'open'
             AND bid_month = ? AND bid_year = ?`,
            [feature_id, currentMonth, currentYear]
        );

        if (features.length === 0) {
            await connection.rollback();
            return res.status(400).json({
                success: false,
                message: 'Feature not available for bidding this month'
            });
        }

        // 2. Check if alumni already bid on this feature
        const [existingBid] = await connection.execute(
            `SELECT id FROM bids
             WHERE alumni_id = ? AND feature_id = ?`,
            [alumni_id, feature_id]
        );

        if (existingBid.length > 0) {
            await connection.rollback();
            return res.status(400).json({
                success: false,
                message: 'You have already placed a bid on this feature. Use update to increase your bid.'
            });
        }

        // 3. Check monthly bid limit (max 3 per month)
        const [limitRows] = await connection.execute(
            `SELECT * FROM monthly_bid_limits
             WHERE alumni_id = ? AND bid_month = ? AND bid_year = ?`,
            [alumni_id, currentMonth, currentYear]
        );

        if (limitRows.length > 0 && limitRows[0].bids_used >= limitRows[0].max_bids) {
            await connection.rollback();
            return res.status(400).json({
                success: false,
                message: 'Monthly bid limit reached. You can only bid on 3 features per month.',
                limit_info: {
                    bids_used: limitRows[0].bids_used,
                    max_bids:  limitRows[0].max_bids
                }
            });
        }

        // 4. Place the bid
        await connection.execute(
            `INSERT INTO bids 
             (alumni_id, feature_id, bid_amount, bid_month, bid_year, status)
             VALUES (?, ?, ?, ?, ?, 'active')`,
            [alumni_id, feature_id, parseFloat(bid_amount), currentMonth, currentYear]
        );

        // 5. Update monthly limit counter
        await connection.execute(
            `INSERT INTO monthly_bid_limits 
             (alumni_id, bid_month, bid_year, bids_used, max_bids)
             VALUES (?, ?, ?, 1, 3)
             ON DUPLICATE KEY UPDATE bids_used = bids_used + 1`,
            [alumni_id, currentMonth, currentYear]
        );

        await connection.commit();

        // Get updated limit info
        const [updatedLimit] = await pool.execute(
            `SELECT * FROM monthly_bid_limits
             WHERE alumni_id = ? AND bid_month = ? AND bid_year = ?`,
            [alumni_id, currentMonth, currentYear]
        );

        return res.status(201).json({
            success: true,
            message: 'Bid placed successfully! Winner will be selected at midnight.',
            data: {
                bid_amount:      parseFloat(bid_amount),
                feature_name:    features[0].feature_name,
                bids_remaining:  3 - updatedLimit[0].bids_used
                // NOTE: we do NOT reveal position or other bid amounts
            }
        });

    } catch (error) {
        await connection.rollback();
        console.error('Place bid error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to place bid'
        });
    } finally {
        connection.release();
    }
}

/**
 * PUT /api/bidding/update/:bid_id
 * Update existing bid - INCREASE ONLY
 * Body: { alumni_id, new_amount }
 */
async function updateBid(req, res) {
    const connection = await pool.getConnection();

    try {
        const { bid_id }    = req.params;
        const { alumni_id, new_amount } = req.body;

        if (!new_amount || parseFloat(new_amount) <= 0) {
            return res.status(400).json({
                success: false,
                message: 'New bid amount must be greater than zero'
            });
        }

        await connection.beginTransaction();

        // Get existing bid
        const [bids] = await connection.execute(
            `SELECT b.*, bf.status as feature_status, bf.feature_name
             FROM bids b
             JOIN bid_features bf ON b.feature_id = bf.id
             WHERE b.id = ? AND b.alumni_id = ?`,
            [bid_id, alumni_id]
        );

        if (bids.length === 0) {
            await connection.rollback();
            return res.status(404).json({
                success: false,
                message: 'Bid not found or does not belong to you'
            });
        }

        const bid = bids[0];

        // Check feature is still open
        if (bid.feature_status !== 'open') {
            await connection.rollback();
            return res.status(400).json({
                success: false,
                message: 'Bidding is closed for this feature. Winner has been selected.'
            });
        }

        // INCREASE ONLY enforcement
        if (parseFloat(new_amount) <= parseFloat(bid.bid_amount)) {
            await connection.rollback();
            return res.status(400).json({
                success: false,
                message: `New bid must be higher than your current bid of £${bid.bid_amount}`,
                current_bid: bid.bid_amount
            });
        }

        // Update the bid
        await connection.execute(
            `UPDATE bids SET bid_amount = ?, updated_at = NOW()
             WHERE id = ? AND alumni_id = ?`,
            [parseFloat(new_amount), bid_id, alumni_id]
        );

        await connection.commit();

        return res.json({
            success: true,
            message: 'Bid updated successfully!',
            data: {
                old_amount:   parseFloat(bid.bid_amount),
                new_amount:   parseFloat(new_amount),
                feature_name: bid.feature_name
                // NOTE: we still do NOT reveal highest bid - blind bidding
            }
        });

    } catch (error) {
        await connection.rollback();
        console.error('Update bid error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to update bid'
        });
    } finally {
        connection.release();
    }
}

/**
 * GET /api/bidding/results/:alumni_id
 * Get win/lose results for alumni bids
 * Only shows AFTER winner selection - reveals if they won or lost
 */
async function getBidResults(req, res) {
    try {
        const { alumni_id } = req.params;

        const [results] = await pool.execute(
            `SELECT b.id, b.status, b.is_winner, b.bid_month, b.bid_year,
                    bf.feature_name, bf.status as feature_status,
                    bf.winner_selected_at
             FROM bids b
             JOIN bid_features bf ON b.feature_id = bf.id
             WHERE b.alumni_id = ?
             AND bf.status = 'winner_selected'
             ORDER BY b.bid_year DESC, b.bid_month DESC`,
            [alumni_id]
        );

        // Show win/lose but NEVER show bid amounts of others
        const safeResults = results.map(r => ({
            id:                  r.id,
            feature_name:        r.feature_name,
            status:              r.is_winner ? 'WON' : 'LOST',
            is_winner:           r.is_winner,
            bid_month:           r.bid_month,
            bid_year:            r.bid_year,
            winner_selected_at:  r.winner_selected_at
            // NOTE: winning amount not revealed - blind bidding integrity
        }));

        return res.json({
            success: true,
            data: safeResults
        });

    } catch (error) {
        console.error('Get results error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch results'
        });
    }
}

/**
 * POST /api/bidding/select-winners
 * Automated midnight winner selection
 * Selects highest bid per feature, marks profile as featured
 * Called by cron job at midnight
 */
async function selectWinners(req, res) {
    const connection = await pool.getConnection();

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

        if (features.length === 0) {
            await connection.rollback();
            return res.json({
                success: true,
                message: 'No open features found for this month',
                winners: []
            });
        }

        const winners = [];

        for (const feature of features) {
            // Find highest bid for this feature (blind - no one sees this until now)
            const [topBids] = await connection.execute(
                `SELECT b.*, a.full_name as alumni_name
                 FROM bids b
                 JOIN alumni a ON b.alumni_id = a.id
                 WHERE b.feature_id = ? AND b.status = 'active'
                 ORDER BY b.bid_amount DESC
                 LIMIT 1`,
                [feature.id]
            );

            if (topBids.length === 0) {
                // No bids for this feature - close it without winner
                await connection.execute(
                    `UPDATE bid_features
                     SET status = 'winner_selected', winner_selected_at = NOW()
                     WHERE id = ?`,
                    [feature.id]
                );
                continue;
            }

            const winnerBid = topBids[0];

            // Mark winning bid
            await connection.execute(
                `UPDATE bids SET status = 'won', is_winner = 1
                 WHERE id = ?`,
                [winnerBid.id]
            );

            // Mark all losing bids for this feature
            await connection.execute(
                `UPDATE bids SET status = 'lost', is_winner = 0
                 WHERE feature_id = ? AND id != ?`,
                [feature.id, winnerBid.id]
            );

            // Update feature with winner and close it
            await connection.execute(
                `UPDATE bid_features
                 SET status = 'winner_selected',
                     winner_alumni_id = ?,
                     winner_selected_at = NOW()
                 WHERE id = ?`,
                [winnerBid.alumni_id, feature.id]
            );

            // Mark alumni profile as featured
            await connection.execute(
                `UPDATE alumni SET is_featured = 1 WHERE id = ?`,
                [winnerBid.alumni_id]
            );

            // Remove featured status from previous winners who didn't win this time
            await connection.execute(
                `UPDATE alumni SET is_featured = 0
                 WHERE id IN (
                     SELECT DISTINCT b2.alumni_id
                     FROM bids b2
                     WHERE b2.feature_id = ? AND b2.alumni_id != ?
                 )`,
                [feature.id, winnerBid.alumni_id]
            );

            winners.push({
                feature_name:   feature.feature_name,
                winner_name:    winnerBid.alumni_name,
                winner_id:      winnerBid.alumni_id
                // NOTE: winning amount NOT included in response
            });

            console.log(`✅ Winner selected for "${feature.feature_name}": ${winnerBid.alumni_name}`);
        }

        await connection.commit();

        return res.json({
            success: true,
            message: `Winners selected for ${winners.length} features`,
            winners: winners,
            selected_at: new Date().toISOString()
        });

    } catch (error) {
        await connection.rollback();
        console.error('Select winners error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to select winners'
        });
    } finally {
        connection.release();
    }
}

/**
 * GET /api/bidding/featured
 * Get featured alumni (winners to display in AR app and dashboard)
 */
async function getFeaturedAlumni(req, res) {
    try {
        const [alumni] = await pool.execute(
            `SELECT a.id, a.full_name, a.programme, a.graduation_year,
                    a.industry_sector, a.job_title, a.employer,
                    a.location_city, a.location_country,
                    a.skills, a.certifications,
                    bf.feature_name, bf.winner_selected_at
             FROM alumni a
             JOIN bid_features bf ON bf.winner_alumni_id = a.id
             WHERE a.is_featured = 1 AND bf.status = 'winner_selected'
             AND bf.bid_month = MONTH(NOW()) AND bf.bid_year = YEAR(NOW())
             ORDER BY bf.winner_selected_at DESC`
        );

        const featured = alumni.map(a => ({
            ...a,
            skills:         a.skills ? JSON.parse(a.skills) : [],
            certifications: a.certifications ? JSON.parse(a.certifications) : []
        }));

        return res.json({
            success: true,
            data: featured
        });

    } catch (error) {
        console.error('Get featured error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to fetch featured alumni'
        });
    }
}

module.exports = {
    getFeatures,
    getMyBids,
    getMonthlyLimit,
    placeBid,
    updateBid,
    getBidResults,
    selectWinners,
    getFeaturedAlumni
};