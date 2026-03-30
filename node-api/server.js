/**
 * Alumni Influencers Platform - Node.js API Server
 * 
 * Entry point for the REST API layer.
 * Runs alongside CodeIgniter (PHP) which handles web views.
 * 
 * Features:
 *   - Express.js with security middleware (Helmet, CORS, rate limiting)
 *   - JWT-based authentication for API consumers
 *   - MySQL database (shared with CodeIgniter)
 *   - Swagger API documentation at /api-docs
 * 
 * Architecture:
 *   CodeIgniter (port 8080) → Web pages, sessions, CSRF
 *   Node.js (port 3000)     → REST API, JWT tokens, Swagger docs
 */

const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
const swaggerJsdoc = require('swagger-jsdoc');
const swaggerUi = require('swagger-ui-express');
require('dotenv').config();

const authRoutes = require('./routes/authRoutes');
const { generalLimiter } = require('./middleware/rateLimiter');

const app = express();
const PORT = process.env.PORT || 3000;

// ================================================================
// SECURITY MIDDLEWARE
// ================================================================

// Helmet: Sets various HTTP security headers
app.use(helmet());

// CORS: Allow CodeIgniter frontend to call this API
app.use(cors({
    origin: [process.env.CI_APP_URL || 'http://localhost:8080'],
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
    allowedHeaders: ['Content-Type', 'Authorization'],
    credentials: true,
}));

// Parse JSON request bodies (limit size to prevent large payload attacks)
app.use(express.json({ limit: '10kb' }));
app.use(express.urlencoded({ extended: true, limit: '10kb' }));

// General rate limiting on all routes
app.use(generalLimiter);

// ================================================================
// SWAGGER API DOCUMENTATION
// ================================================================

const swaggerOptions = {
    definition: {
        openapi: '3.0.0',
        info: {
            title: 'Alumni Influencers API',
            version: '1.0.0',
            description: 'REST API for the University of Eastminster Alumni Influencers Platform',
        },
        servers: [
            { url: `http://localhost:${PORT}`, description: 'Development server' },
        ],
        components: {
            securitySchemes: {
                bearerAuth: {
                    type: 'http',
                    scheme: 'bearer',
                    bearerFormat: 'JWT',
                },
            },
        },
    },
    apis: ['./routes/*.js'],
};

const swaggerSpec = swaggerJsdoc(swaggerOptions);
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerSpec));

// ================================================================
// API ROUTES
// ================================================================

// Health check endpoint
app.get('/api/health', (req, res) => {
    res.status(200).json({
        status: 'success',
        message: 'Alumni Influencers API is running.',
        timestamp: new Date().toISOString(),
    });
});

// Auth routes
app.use('/api/auth', authRoutes);

// ================================================================
// ERROR HANDLING
// ================================================================

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        status: 'error',
        message: `Route ${req.method} ${req.originalUrl} not found.`,
    });
});

// Global error handler
app.use((err, req, res, next) => {
    console.error('Unhandled error:', err);
    res.status(500).json({
        status: 'error',
        message: 'An unexpected error occurred.',
    });
});

// ================================================================
// START SERVER
// ================================================================

app.listen(PORT, () => {
    console.log(`
    ============================================
    🚀 Alumni Influencers API Server
    ============================================
    Port:       ${PORT}
    Mode:       ${process.env.NODE_ENV || 'development'}
    API Docs:   http://localhost:${PORT}/api-docs
    Health:     http://localhost:${PORT}/api/health
    ============================================
    `);
});

module.exports = app;