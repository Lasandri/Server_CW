// node-api/routes/biddingRoutes.js

const express = require('express');
const router  = express.Router();
const biddingController = require('../controllers/biddingController');
const { verifyApiKey }  = require('../middleware/authMiddleware');

// All bidding routes require read:alumni permission
// (alumni place bids on their own profiles)

// Get available features to bid on
router.get('/features', verifyApiKey('read:alumni'), biddingController.getFeatures);

// Get my bids (for logged in alumni)
router.get('/my-bids/:alumni_id', verifyApiKey('read:alumni'), biddingController.getMyBids);

// Get monthly limit status
router.get('/limit/:alumni_id', verifyApiKey('read:alumni'), biddingController.getMonthlyLimit);

// Place a new bid
router.post('/place', verifyApiKey('read:alumni'), biddingController.placeBid);

// Update existing bid (increase only)
router.put('/update/:bid_id', verifyApiKey('read:alumni'), biddingController.updateBid);

// Get bid result (win/lose) for alumni
router.get('/results/:alumni_id', verifyApiKey('read:alumni'), biddingController.getBidResults);

// Admin: trigger winner selection manually (for testing)
router.post('/select-winners', verifyApiKey('read:analytics'), biddingController.selectWinners);

// Get featured alumni (winners displayed)
router.get('/featured', verifyApiKey('read:alumni'), biddingController.getFeaturedAlumni);

module.exports = router;