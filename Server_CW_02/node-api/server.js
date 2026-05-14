// node-api/server.js
// REPLACE ENTIRE FILE

const express = require('express');
const helmet  = require('helmet');
const cors    = require('cors');
require('dotenv').config();

const app = express();

// ── Security Middleware ────────────────────────────────────────────────────────
app.use(helmet());

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

app.use(express.json({ limit: '10kb' }));
app.use(express.urlencoded({ extended: true }));
app.set('trust proxy', 1);

// ── Import Routes ─────────────────────────────────────────────────────────────
const authRoutes      = require('./routes/authRoutes');
const alumniRoutes    = require('./routes/alumniRoutes');
const analyticsRoutes = require('./routes/analyticsRoutes');
const biddingRoutes   = require('./routes/biddingRoutes');
const securityRoutes  = require('./routes/securityRoutes');

// ── Mount Routes ──────────────────────────────────────────────────────────────
app.use('/api/auth',      authRoutes);
app.use('/api/alumni',    alumniRoutes);
app.use('/api/analytics', analyticsRoutes);
app.use('/api/bidding',   biddingRoutes);
app.use('/api/security',  securityRoutes);

// ── Health Check ──────────────────────────────────────────────────────────────
app.get('/api/health', (req, res) => {
    res.json({
        success:  true,
        message:  'Alumni Dashboard API is running ✅',
        time:     new Date().toISOString(),
        endpoints: {
            auth:      '/api/auth',
            alumni:    '/api/alumni',
            analytics: '/api/analytics',
            bidding:   '/api/bidding',
            security:  '/api/security'
        }
    });
});

// ── 404 Handler ───────────────────────────────────────────────────────────────
app.use((req, res) => {
    res.status(404).json({
        success: false,
        message: `Cannot ${req.method} ${req.path}`
    });
});

// ── Global Error Handler ──────────────────────────────────────────────────────
app.use((err, req, res, next) => {
    console.error('Server Error:', err.message);
    res.status(500).json({
        success: false,
        message: 'Internal server error'
    });
});

// ── Midnight Cron Job ─────────────────────────────────────────────────────────
const { runMidnightSelection } = require('./jobs/midnightWinnerSelection');

function scheduleMidnightJob() {
    const now      = new Date();
    const midnight = new Date();
    midnight.setHours(24, 0, 0, 0);
    const msUntilMidnight = midnight - now;

    console.log(`\n⏰ Winner selection in: ${Math.round(msUntilMidnight / 60000)} minutes`);

    setTimeout(async () => {
        await runMidnightSelection();
        setInterval(async () => {
            await runMidnightSelection();
        }, 24 * 60 * 60 * 1000);
    }, msUntilMidnight);
}

scheduleMidnightJob();

// ── Start Server ──────────────────────────────────────────────────────────────
const PORT = process.env.PORT || 3001;

app.listen(PORT, () => {
    console.log('\n╔══════════════════════════════════════════╗');
    console.log('║   Alumni Dashboard API - Node.js         ║');
    console.log('╠══════════════════════════════════════════╣');
    console.log(`║  Running : http://localhost:${PORT}          ║`);
    console.log(`║  Health  : http://localhost:${PORT}/api/health ║`);
    console.log('╚══════════════════════════════════════════╝\n');
});