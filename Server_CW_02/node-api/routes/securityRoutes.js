// node-api/routes/securityRoutes.js
// REPLACE ENTIRE FILE

const express = require('express');
const router  = express.Router();

const securityController = require('../controllers/securityController');
const { verifyApiKey }   = require('../middleware/authMiddleware');

// ── API Key Management (admin only) ──────────────────────────────────────────

// Get all API keys
router.get('/keys',               verifyApiKey('write:admin'), securityController.getAllKeys);

// Get single key
router.get('/keys/:id',           verifyApiKey('write:admin'), securityController.getKeyById);

// Create new API key
router.post('/keys',              verifyApiKey('write:admin'), securityController.createKey);

// Toggle key active/inactive
router.put('/keys/:id/toggle',    verifyApiKey('write:admin'), securityController.toggleKey);

// Delete API key
router.delete('/keys/:id',        verifyApiKey('write:admin'), securityController.deleteKey);

// ── Usage Statistics (admin only) ─────────────────────────────────────────────

// Overall stats
router.get('/stats',              verifyApiKey('write:admin'), securityController.getUsageStats);

// Stats for specific key
router.get('/stats/:key_id',      verifyApiKey('write:admin'), securityController.getKeyStats);

// Login logs
router.get('/login-logs',         verifyApiKey('write:admin'), securityController.getLoginLogs);

// Access logs
router.get('/access-logs',        verifyApiKey('write:admin'), securityController.getAccessLogs);

// ── AR App Endpoint (mobile_ar only) ─────────────────────────────────────────

// Only read:alumni_of_day permission can access this
// Analytics Dashboard key CANNOT access this endpoint
router.get('/alumni-of-day',      verifyApiKey('read:alumni_of_day'), securityController.getAlumniOfDay);

module.exports = router;



