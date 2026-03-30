/**
 * Alumni Influencers Platform — Node.js API Server
 */

const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
const swaggerJsdoc = require('swagger-jsdoc');
const swaggerUi = require('swagger-ui-express');
require('dotenv').config();

const clientRoutes = require('./routes/clientRoutes');
const { generalLimiter } = require('./middleware/rateLimiter');

const app = express();
const PORT = process.env.PORT || 3000;

// ================================================================
// ⚠️ MIDDLEWARE MUST BE BEFORE ROUTES
// ================================================================

// Security headers
app.use(helmet());

// CORS
app.use(cors({
    origin: [process.env.CI_APP_URL || 'http://localhost:8080', 'http://localhost:3000'],
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization'],
    credentials: true,
}));

// ⚠️ JSON parser MUST come before routes
app.use(express.json({ limit: '10kb' }));
app.use(express.urlencoded({ extended: true, limit: '10kb' }));

// Rate limiting
app.use(generalLimiter);

// ================================================================
// SWAGGER CONFIGURATION
// ================================================================

const swaggerOptions = {
    definition: {
        openapi: '3.0.0',
        info: {
            title: 'Alumni Influencers API',
            version: '1.0.0',
            description: 'API for accessing alumni data',
        },
        servers: [
            { url: `http://localhost:${PORT}`, description: 'Development Server' },
        ],
        components: {
            securitySchemes: {
                bearerAuth: {
                    type: 'http',
                    scheme: 'bearer',
                    bearerFormat: 'JWT',
                },
            },
            schemas: {
                AuthRequest: {
                    type: 'object',
                    required: ['api_key', 'api_secret'],
                    properties: {
                        api_key: { type: 'string', example: 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6' },
                        api_secret: { type: 'string', example: 'x1y2z3a4b5c6d7e8f9g0...' },
                    },
                },
                AuthResponse: {
                    type: 'object',
                    properties: {
                        status: { type: 'string', example: 'success' },
                        data: {
                            type: 'object',
                            properties: {
                                bearer_token: { type: 'string' },
                                expires_in: { type: 'integer', example: 86400 },
                            },
                        },
                    },
                },
            },
        },
        tags: [
            { name: 'Authentication', description: 'API key authentication' },
            { name: 'Alumni', description: 'Access alumni profiles' },
            { name: 'System', description: 'Health checks' },
        ],
    },
    apis: ['./routes/*.js'],
};

const swaggerSpec = swaggerJsdoc(swaggerOptions);
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerSpec));
app.get('/api-docs.json', (req, res) => {
    res.setHeader('Content-Type', 'application/json');
    res.send(swaggerSpec);
});

// ================================================================
// ⚠️ ROUTES — Mount ONLY ONCE
// ================================================================

// Health check (public)
app.get('/api/health', (req, res) => {
    res.status(200).json({
        status: 'success',
        message: 'Alumni Influencers API is running',
        timestamp: new Date().toISOString(),
    });
});

// ⚠️ Mount client routes ONLY ONCE at /api
app.use('/api', clientRoutes);

// ================================================================
// ERROR HANDLING
// ================================================================

app.use((req, res) => {
    res.status(404).json({
        status: 'error',
        message: `Route ${req.method} ${req.originalUrl} not found`,
    });
});

app.use((err, req, res, next) => {
    console.error('Unhandled error:', err);
    res.status(500).json({
        status: 'error',
        message: 'An unexpected error occurred',
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