// node-api/routes/analyticsRoutes.js
// REPLACE ENTIRE FILE

const express = require('express');
const router  = express.Router();

const analyticsController   = require('../controllers/analyticsController');
const { verifyApiKey }      = require('../middleware/authMiddleware');

// Overview stats
router.get('/overview',            verifyApiKey('read:analytics'), analyticsController.getOverview);

// Employment by sector
router.get('/employment-by-sector', verifyApiKey('read:analytics'), analyticsController.getEmploymentBySector);

// Skills gap analysis
router.get('/skills-gap',          verifyApiKey('read:analytics'), analyticsController.getSkillsGap);

// Top employers
router.get('/top-employers',       verifyApiKey('read:analytics'), analyticsController.getTopEmployers);

// Most common job titles
router.get('/job-titles',          verifyApiKey('read:analytics'), analyticsController.getTopJobTitles);

// Geographic distribution
router.get('/geographic',          verifyApiKey('read:analytics'), analyticsController.getGeographic);

// Graduation year trends
router.get('/graduation-trends',   verifyApiKey('read:analytics'), analyticsController.getGraduationTrends);

// Certifications
router.get('/certifications',      verifyApiKey('read:analytics'), analyticsController.getCertifications);

module.exports = router;