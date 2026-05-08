// node-api/server.js - Add cron scheduler

const express = require('express');
const helmet  = require('helmet');
const cors    = require('cors');
require('dotenv').config();

const app = express();

// ── Security ───────────────────────────────────────────────────────────────
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

// ── Routes ─────────────────────────────────────────────────────────────────
const authRoutes     = require('./routes/authRoutes');
const alumniRoutes   = require('./routes/alumniRoutes');
const analyticsRoutes = require('./routes/analyticsRoutes');
const biddingRoutes  = require('./routes/biddingRoutes');

app.use('/api/auth',      authRoutes);
app.use('/api/alumni',    alumniRoutes);
app.use('/api/analytics', analyticsRoutes);
app.use('/api/bidding',   biddingRoutes);

// Health check
app.get('/api/health', (req, res) => {
    res.json({
        success: true,
        message: 'Alumni Dashboard API running',
        time: new Date().toISOString()
    });
});

// 404
app.use((req, res) => {
    res.status(404).json({ success: false, message: `Cannot ${req.method} ${req.path}` });
});

// Error handler
app.use((err, req, res, next) => {
    console.error('Error:', err.message);
    res.status(500).json({ success: false, message: 'Internal server error' });
});

// ── Midnight Cron Job ──────────────────────────────────────────────────────
const { runMidnightSelection } = require('./jobs/midnightWinnerSelection');

function scheduleMidnightJob() {
    const now       = new Date();
    // Calculate ms until next midnight
    const midnight  = new Date();
    midnight.setHours(24, 0, 0, 0); // next midnight
    const msUntilMidnight = midnight - now;

    console.log(`\n⏰ Midnight winner selection scheduled in ${Math.round(msUntilMidnight / 60000)} minutes`);

    // Run once at midnight
    setTimeout(async () => {
        await runMidnightSelection();
        // Then schedule again for next midnight (24 hours)
        setInterval(async () => {
            await runMidnightSelection();
        }, 24 * 60 * 60 * 1000); // every 24 hours
    }, msUntilMidnight);
}

// Start cron job
scheduleMidnightJob();

// ── Start Server ───────────────────────────────────────────────────────────
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
    console.log('╔══════════════════════════════════════════╗');
    console.log('║   Alumni Dashboard API - Node.js         ║');
    console.log('╠══════════════════════════════════════════╣');
    console.log(`║  Running : http://localhost:${PORT}          ║`);
    console.log(`║  Health  : http://localhost:${PORT}/api/health ║`);
    console.log('╚══════════════════════════════════════════╝');
});