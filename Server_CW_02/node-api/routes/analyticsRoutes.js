/**
 * Analytics Routes - CW2
 * Base: /api/analytics
 * Requires: Bearer token with read:analytics scope
 */
const express = require('express');
const router = express.Router();
const { bearerAuth } = require('../middleware/bearerAuth');
const { blockArAppFromAnalytics } = require('../middleware/scopeMiddleware');
const { analyticsLimiter } = require('../middleware/rateLimiter');
const analyticsController = require('../controllers/analyticsController');

// Apply auth + scope check + rate limit
router.use(bearerAuth);
router.use(blockArAppFromAnalytics);
router.use(analyticsLimiter);

/**
 * @swagger
 * /api/analytics/overview:
 *   get:
 *     summary: Dashboard overview statistics
 *     tags: [Analytics]
 *     security:
 *       - bearerAuth: []
 */
router.get('/overview',              analyticsController.getOverview);

/**
 * @swagger
 * /api/analytics/employment:
 *   get:
 *     summary: Employment by industry sector
 *     tags: [Analytics]
 *     security:
 *       - bearerAuth: []
 */
router.get('/employment',            analyticsController.getEmployment);

/**
 * @swagger
 * /api/analytics/skills-gap:
 *   get:
 *     summary: Skills gap analysis
 *     tags: [Analytics]
 *     security:
 *       - bearerAuth: []
 */
router.get('/skills-gap',            analyticsController.getSkillsGap);

/**
 * @swagger
 * /api/analytics/job-titles:
 *   get:
 *     summary: Most common job titles
 *     tags: [Analytics]
 *     security:
 *       - bearerAuth: []
 */
router.get('/job-titles',            analyticsController.getJobTitles);

/**
 * @swagger
 * /api/analytics/employers:
 *   get:
 *     summary: Top employers
 *     tags: [Analytics]
 *     security:
 *       - bearerAuth: []
 */
router.get('/employers',             analyticsController.getEmployers);

/**
 * @swagger
 * /api/analytics/geographic:
 *   get:
 *     summary: Geographic distribution
 *     tags: [Analytics]
 *     security:
 *       - bearerAuth: []
 */
router.get('/geographic',            analyticsController.getGeographic);

/**
 * @swagger
 * /api/analytics/certification-trends:
 *   get:
 *     summary: Certification trends over time
 *     tags: [Analytics]
 *     security:
 *       - bearerAuth: []
 */
router.get('/certification-trends',  analyticsController.getCertificationTrends);

module.exports = router;