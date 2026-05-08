// node-api/routes/analyticsRoutes.js

const express = require('express');
const router = express.Router();
const analyticsController = require('../controllers/analyticsController');
const { verifyApiKey } = require('../middleware/authMiddleware');

// All analytics routes require read:analytics permission
router.get('/overview', verifyApiKey('read:analytics'), analyticsController.getOverview);
router.get('/employment-by-sector', verifyApiKey('read:analytics'), analyticsController.getEmploymentBySector);
router.get('/skills-gap', verifyApiKey('read:analytics'), analyticsController.getSkillsGap);
router.get('/top-employers', verifyApiKey('read:analytics'), analyticsController.getTopEmployers);
router.get('/job-titles', verifyApiKey('read:analytics'), analyticsController.getTopJobTitles);
router.get('/geographic', verifyApiKey('read:analytics'), analyticsController.getGeographic);
router.get('/graduation-trends', verifyApiKey('read:analytics'), analyticsController.getGraduationTrends);
router.get('/certifications', verifyApiKey('read:analytics'), analyticsController.getCertifications);

module.exports = router;