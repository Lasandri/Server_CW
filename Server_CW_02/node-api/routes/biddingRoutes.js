// node-api/routes/biddingRoutes.js

const express = require('express');
const router  = express.Router();

const biddingController = require('../controllers/biddingController');
const { verifyApiKey }  = require('../middleware/authMiddleware');

/**
 * GET /api/bidding/featured
 * Recent featured alumni from daily_winners
 */
router.get(
    '/featured',
    verifyApiKey('read:alumni'),
    biddingController.getFeaturedAlumni
);

/**
 * GET /api/bidding/stats
 * Overall bidding statistics
 */
router.get(
    '/stats',
    verifyApiKey('read:analytics'),
    biddingController.getBiddingStats
);

/**
 * GET /api/bidding/alumni-of-day
 * Today's featured alumni
 */
router.get(
    '/alumni-of-day',
    verifyApiKey('read:alumni'),
    biddingController.getAlumniOfDay
);

module.exports = router;