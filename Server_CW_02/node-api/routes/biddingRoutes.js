// node-api/routes/biddingRoutes.js
// REPLACE ENTIRE FILE

const express = require('express');
const router  = express.Router();

const biddingController = require('../controllers/biddingController');
const { verifyApiKey }  = require('../middleware/authMiddleware');

// Get available features this month
router.get('/features',           verifyApiKey('read:alumni'), biddingController.getFeatures);

// Get my bids (blind - own amount only)
router.get('/my-bids/:alumni_id', verifyApiKey('read:alumni'), biddingController.getMyBids);

// Get monthly limit status
router.get('/limit/:alumni_id',   verifyApiKey('read:alumni'), biddingController.getMonthlyLimit);

// Place new bid
router.post('/place',             verifyApiKey('read:alumni'), biddingController.placeBid);

// Update bid (increase only)
router.put('/update/:bid_id',     verifyApiKey('read:alumni'), biddingController.updateBid);

// Get bid results (win/lose)
router.get('/results/:alumni_id', verifyApiKey('read:alumni'), biddingController.getBidResults);

// Admin: trigger winner selection
router.post('/select-winners',    verifyApiKey('read:analytics'), biddingController.selectWinners);

// Get featured alumni (winners)
router.get('/featured',           verifyApiKey('read:alumni'), biddingController.getFeaturedAlumni);

module.exports = router;
