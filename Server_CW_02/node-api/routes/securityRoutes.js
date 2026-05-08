// node-api/routes/securityRoutes.js

const express = require('express');
const router  = express.Router();
const securityController = require('../controllers/securityController');
const { verifyToken, verifyApiKey } = require('../middleware/authMiddleware');

// ── API Key Management ────────────────────────────────────────────────────────

// Get all API keys (admin only)
router.get('/keys', verifyApiKey('write:admin'), securityController.getAllKeys);

// Get single key details
router.get('/keys/:id', verifyApiKey('write:admin'), securityController.getKeyById);

// Create new API key
router.post('/keys', verifyApiKey('write:admin'), securityController.createKey);

// Toggle key active/inactive
router.put('/keys/:id/toggle', verifyApiKey('write:admin'), securityController.toggleKey);

// Delete API key
router.delete('/keys/:id', verifyApiKey('write:admin'), securityController.deleteKey);

// ── Usage Statistics ──────────────────────────────────────────────────────────

// Get overall usage stats
router.get('/stats', verifyApiKey('write:admin'), securityController.getUsageStats);

// Get usage stats for specific key
router.get('/stats/:key_id', verifyApiKey('write:admin'), securityController.getKeyStats);

// Get login logs
router.get('/login-logs', verifyApiKey('write:admin'), securityController.getLoginLogs);

// Get recent API access logs
router.get('/access-logs', verifyApiKey('write:admin'), securityController.getAccessLogs);

// ── AR App Endpoint (mobile_ar permission only) ───────────────────────────────
router.get('/alumni-of-day', verifyApiKey('read:alumni_of_day'), securityController.getAlumniOfDay);

module.exports = router;