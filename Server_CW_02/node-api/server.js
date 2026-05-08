// node-api/server.js

const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
require('dotenv').config();

const app = express();

// ── Security Middleware ────────────────────────────────────────────────────────
app.use(helmet());

// Allow CodeIgniter frontend
app.use(cors({
    origin: [
        'http://localhost',
        'http://localhost/Server_CW/Server_CW_02/Codeigniter',
        'http://localhost/Server_CW/Server_CW_02/Codeigniter/index.php',
        'http://127.0.0.1'
    ],
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization'],
    credentials: true
}));

// Body parsers
app.use(express.json({ limit: '10kb' }));
app.use(express.urlencoded({ extended: true }));

app.set('trust proxy', 1);

// ── Routes ────────────────────────────────────────────────────────────────────
const authRoutes     = require('./routes/authRoutes');
const alumniRoutes   = require('./routes/alumniRoutes');
const analyticsRoutes = require('./routes/analyticsRoutes');

app.use('/api/auth',      authRoutes);
app.use('/api/alumni',    alumniRoutes);
app.use('/api/analytics', analyticsRoutes);


// Health check
app.get('/api/health', (req, res) => {
    res.json({
        success: true,
        message: 'Alumni Dashboard API is running',
        time: new Date().toISOString()
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
        message: `Cannot ${req.method} ${req.path}`
    });
});

// Global error handler
app.use((err, req, res, next) => {
    console.error('Server Error:', err.message);
    res.status(500).json({
        success: false,
        message: 'Internal server error.'
    });
});

// ── Start Server ──────────────────────────────────────────────────────────────
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
    console.log('╔══════════════════════════════════════════╗');
    console.log('║   Alumni Dashboard API - Node.js         ║');
    console.log('╠══════════════════════════════════════════╣');
    console.log(`║  Running : http://localhost:${PORT}          ║`);
    console.log(`║  Health  : http://localhost:${PORT}/api/health ║`);
    console.log('╚══════════════════════════════════════════╝');
});