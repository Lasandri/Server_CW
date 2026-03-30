const express = require('express');
const crypto = require('crypto');
const bcrypt = require('bcryptjs');
const router = express.Router();
const pool = require('../config/database');
const { bearerAuth, requireScope } = require('../middleware/bearerAuth');
const { loginLimiter } = require('../middleware/rateLimiter');

/**
 * @swagger
 * /client/authenticate:
 *   post:
 *     summary: Authenticate with API key to get bearer token
 *     tags: [Authentication]
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/AuthRequest'
 *     responses:
 *       200:
 *         description: Authentication successful
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/AuthResponse'
 *       400:
 *         description: Bad request
 *       401:
 *         description: Invalid credentials
 */
router.post('/client/authenticate', loginLimiter, async (req, res) => {
    try {
        // ⚠️ Check if body exists
        if (!req.body) {
            return res.status(400).json({
                status: 'error',
                message: 'Request body is required. Set Content-Type: application/json',
            });
        }

        const { api_key, api_secret } = req.body;

        // ⚠️ Validate required fields
        if (!api_key || !api_secret) {
            return res.status(400).json({
                status: 'error',
                message: 'api_key and api_secret are required.',
                received: req.body, // Debug: show what was received
            });
        }

        const [keys] = await pool.query(
            'SELECT * FROM api_keys WHERE api_key = ?',
            [api_key]
        );

        if (keys.length === 0) {
            return res.status(401).json({
                status: 'error',
                message: 'Invalid API key.',
            });
        }

        const keyRecord = keys[0];

        if (!keyRecord.is_active || keyRecord.is_revoked) {
            return res.status(401).json({
                status: 'error',
                message: 'API key has been revoked or deactivated.',
            });
        }

        const secretValid = await bcrypt.compare(api_secret, keyRecord.api_secret);

        if (!secretValid) {
            return res.status(401).json({
                status: 'error',
                message: 'Invalid API secret.',
            });
        }

        const rawToken = crypto.randomBytes(32).toString('hex');
        const hashedToken = crypto.createHash('sha256').update(rawToken).digest('hex');
        const expiresAt = new Date(Date.now() + 24 * 60 * 60 * 1000);

        await pool.query(
            `UPDATE api_keys SET bearer_token = ?, bearer_token_expires = ?, 
             last_used_at = NOW(), updated_at = NOW() WHERE id = ?`,
            [hashedToken, expiresAt, keyRecord.id]
        );

        await pool.query(
            `INSERT INTO api_key_auth_logs (api_key_id, event_type, ip_address, user_agent, details, created_at)
             VALUES (?, 'login', ?, ?, 'Bearer token generated', NOW())`,
            [keyRecord.id, req.ip, req.get('User-Agent') || '']
        );

        res.status(200).json({
            status: 'success',
            message: 'Authentication successful.',
            data: {
                bearer_token: rawToken,
                token_type: 'Bearer',
                expires_in: 86400,
                expires_at: expiresAt.toISOString(),
                scopes: keyRecord.scopes.split(','),
                client_name: keyRecord.client_name,
            },
        });

    } catch (error) {
        console.error('Client auth error:', error);
        res.status(500).json({ 
            status: 'error', 
            message: 'Server error.',
            error: error.message 
        });
    }
});

// Apply bearer auth to /v1 routes
router.use('/v1', bearerAuth);

/**
 * @swagger
 * /v1/health:
 *   get:
 *     summary: API health check
 *     tags: [System]
 *     security:
 *       - bearerAuth: []
 *     responses:
 *       200:
 *         description: API is running
 */
router.get('/v1/health', (req, res) => {
    res.status(200).json({
        status: 'success',
        message: 'API is running.',
        client: req.apiKey ? req.apiKey.client_name : 'Unknown',
        timestamp: new Date().toISOString(),
    });
});

/**
 * @swagger
 * /v1/alumni-of-the-day:
 *   get:
 *     summary: Get today's featured alumnus
 *     tags: [Alumni]
 *     security:
 *       - bearerAuth: []
 *     responses:
 *       200:
 *         description: Alumni data retrieved
 *       404:
 *         description: No alumni selected
 */
router.get('/v1/alumni-of-the-day', requireScope('read'), async (req, res) => {
    try {
        const [winners] = await pool.query(
            `SELECT dw.*, u.first_name, u.last_name, u.email,
                    p.biography, p.linkedin_url, p.profile_image, p.city, p.country
             FROM daily_winners dw
             JOIN users u ON u.id = dw.user_id
             LEFT JOIN alumni_profiles p ON p.user_id = dw.user_id
             WHERE dw.is_active = 1
             ORDER BY dw.display_date DESC
             LIMIT 1`
        );

        if (winners.length === 0) {
            return res.status(404).json({
                status: 'error',
                message: 'No Alumni of the Day selected yet.',
            });
        }

        const winner = winners[0];

        const [degrees] = await pool.query(
            'SELECT degree_title, field_of_study, university_name, degree_url, completion_date, grade FROM degrees WHERE user_id = ?',
            [winner.user_id]
        );

        const [certifications] = await pool.query(
            'SELECT certification_name, issuing_organization, certification_url, completion_date FROM certifications WHERE user_id = ?',
            [winner.user_id]
        );

        const [licences] = await pool.query(
            'SELECT licence_name, issuing_body, licence_url, issue_date FROM licences WHERE user_id = ?',
            [winner.user_id]
        );

        const [courses] = await pool.query(
            'SELECT course_name, provider, course_url, completion_date FROM professional_courses WHERE user_id = ?',
            [winner.user_id]
        );

        const [employment] = await pool.query(
            'SELECT job_title, company_name, start_date, end_date, is_current FROM employment_history WHERE user_id = ?',
            [winner.user_id]
        );

        res.status(200).json({
            status: 'success',
            data: {
                display_date: winner.display_date,
                alumni: {
                    name: `${winner.first_name} ${winner.last_name}`,
                    biography: winner.biography,
                    linkedin_url: winner.linkedin_url,
                    profile_image: winner.profile_image,
                    city: winner.city,
                    country: winner.country,
                },
                degrees,
                certifications,
                licences,
                courses,
                employment,
            },
        });

    } catch (error) {
        console.error('Alumni of the day error:', error);
        res.status(500).json({ status: 'error', message: 'Server error.' });
    }
});

module.exports = router;