/**
 * Alumni Routes - CW2
 * Base: /api/alumni
 * Requires: Bearer token with read:alumni scope
 */
const express = require('express');
const router = express.Router();
const { bearerAuth } = require('../middleware/bearerAuth');
const { requireScope } = require('../middleware/scopeMiddleware');
const alumniController = require('../controllers/alumniController');

router.use(bearerAuth);

/**
 * @swagger
 * /api/alumni:
 *   get:
 *     summary: Get all alumni with filters
 *     tags: [Alumni]
 *     security:
 *       - bearerAuth: []
 *     parameters:
 *       - in: query
 *         name: programme
 *         schema:
 *           type: string
 *       - in: query
 *         name: graduation_year
 *         schema:
 *           type: integer
 *       - in: query
 *         name: industry
 *         schema:
 *           type: string
 *       - in: query
 *         name: country
 *         schema:
 *           type: string
 *       - in: query
 *         name: page
 *         schema:
 *           type: integer
 *       - in: query
 *         name: limit
 *         schema:
 *           type: integer
 */
router.get('/',    requireScope('read:alumni'), alumniController.getAlumni);

/**
 * @swagger
 * /api/alumni/{id}:
 *   get:
 *     summary: Get single alumni profile
 *     tags: [Alumni]
 *     security:
 *       - bearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 */
router.get('/:id', requireScope('read:alumni'), alumniController.getAlumniById);

module.exports = router;