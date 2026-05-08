/**
 * CW2 - University Analytics Dashboard API
 * Port: 4000
 * Uses same alumni_platform database as CW1
 */

const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
const swaggerJsdoc = require('swagger-jsdoc');
const swaggerUi = require('swagger-ui-express');
require('dotenv').config();

// Routes
const authRoutes      = require('./routes/authRoutes');
const analyticsRoutes = require('./routes/analyticsRoutes');
const alumniRoutes    = require('./routes/alumniRoutes');
const { generalLimiter } = require('./middleware/rateLimiter');

const app  = express();
const PORT = process.env.PORT || 4000;

// ================================================================
// SECURITY MIDDLEWARE
// ================================================================
app.use(helmet());

app.use(cors({
    origin: [
        'http://localhost:4000',
        'http://localhost:3000',
        'http://localhost/Server_CW/Server_CW_02/Codeigniter/',
    ],
    methods: ['GET','POST','PUT','DELETE','PATCH','OPTIONS'],
    allowedHeaders: ['Content-Type','Authorization'],
    credentials: true,
}));

app.use(express.json({ limit: '10kb' }));
app.use(express.urlencoded({ extended: true, limit: '10kb' }));
app.use(generalLimiter);

// ================================================================
// SWAGGER
// ================================================================
const swaggerOptions = {
    definition: {
        openapi: '3.0.0',
        info: {
            title: 'University Analytics Dashboard API',
            version: '1.0.0',
            description: `
                CW2 - University Alumni Analytics API
                
                ## API Key Scoping
                | Client Platform     | Permissions                        | Cannot Access     |
                |---------------------|------------------------------------|-------------------|
                | Analytics Dashboard | read:alumni, read:analytics        | AR app endpoints  |
                | Mobile AR App       | read:alumni_of_day                 | Analytics endpoints|
            `,
        },
        servers: [
            {
                url: `http://localhost:${PORT}`,
                description: 'Development Server'
            },
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
        tags: [
            { name: 'Auth',      description: 'Authentication endpoints' },
            { name: 'Analytics', description: 'Analytics data endpoints' },
            { name: 'Alumni',    description: 'Alumni profile endpoints'  },
            { name: 'System',    description: 'Health checks'             },
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
// HEALTH CHECK
// ================================================================
app.get('/api/health', (req, res) => {
    res.status(200).json({
        status: 'success',
        message: 'CW2 University Analytics API running',
        version: '1.0.0',
        timestamp: new Date().toISOString(),
    });
});

// ================================================================
// ROUTES
// ================================================================
app.use('/api/auth',      authRoutes);
app.use('/api/analytics', analyticsRoutes);
app.use('/api/alumni',    alumniRoutes);

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
        message: 'Unexpected error occurred',
    });
});

// ================================================================
// START
// ================================================================
app.listen(PORT, () => {
    console.log(`
    ============================================
    📊 CW2 University Analytics Dashboard API
    ============================================
    Port:       ${PORT}
    Mode:       ${process.env.NODE_ENV || 'development'}
    API Docs:   http://localhost:${PORT}/api-docs
    Health:     http://localhost:${PORT}/api/health
    Database:   alumni_platform (shared with CW1)
    ============================================
    `);
});

module.exports = app;