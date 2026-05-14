// node-api/routes/alumniRoutes.js
// REPLACE ENTIRE FILE

const express = require('express');
const router  = express.Router();

const alumniController      = require('../controllers/alumniController');
const { verifyApiKey }      = require('../middleware/authMiddleware');

// Get alumni list with filters
router.get('/', verifyApiKey('read:alumni'), alumniController.getAlumni);

// Get filter options (programmes, years, sectors)
router.get('/filters', verifyApiKey('read:alumni'), alumniController.getFilterOptions);

// Get single alumni by ID
router.get('/:id', verifyApiKey('read:alumni'), alumniController.getAlumniById);

module.exports = router;