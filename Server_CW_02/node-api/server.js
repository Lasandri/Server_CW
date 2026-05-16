// node-api/server.js
// REPLACE ENTIRE FILE

const express = require('express');
const helmet  = require('helmet');
const cors    = require('cors');
require('dotenv').config();

const app = express();

// ── Security Middleware ────────────────────────────────────────────────────────
// app.use(helmet({
//     crossOriginResourcePolicy: { policy: "cross-origin" },
//     crossOriginOpenerPolicy: false,
//     contentSecurityPolicy: false
// }));

app.use(helmet({
    crossOriginResourcePolicy: false,
    crossOriginOpenerPolicy: false,
    originAgentCluster: false,
    contentSecurityPolicy: false,
    crossOriginEmbedderPolicy: false
}));

app.use(cors({
    origin: function(origin, callback) {
        // Allow requests with no origin (PHP cURL, Postman, etc.)
        if (!origin) return callback(null, true);
        
        const allowedOrigins = [
            'http://localhost',
            'http://localhost:3001',
            'http://127.0.0.1',
            'http://localhost/Server_CW/Server_CW_02/Codeigniter',
            'http://localhost/Server_CW/Server_CW_02/Codeigniter/index.php'
        ];
        
        if (allowedOrigins.indexOf(origin) !== -1) {
            callback(null, true);
        } else {
            callback(null, true); // Allow all for development
        }
    },
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'Accept'],
    credentials: true
}));

// Handle preflight requests
app.options('*', cors());

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

// TEMPORARY TEST ROUTE - remove after testing
app.get('/api/test-data', async (req, res) => {
    const pool = require('./config/database');
    try {
        const [users] = await pool.execute(
            'SELECT COUNT(*) as count FROM users WHERE is_active = 1'
        );
        const [alumni] = await pool.execute(
            'SELECT COUNT(*) as count FROM alumni_profiles'
        );
        const [employment] = await pool.execute(
            'SELECT COUNT(*) as count FROM employment_history'
        );
        const [degrees] = await pool.execute(
            'SELECT COUNT(*) as count FROM degrees'
        );
        const [certs] = await pool.execute(
            'SELECT COUNT(*) as count FROM certifications'
        );
        
        res.json({
            success: true,
            counts: {
                users: users[0].count,
                alumni_profiles: alumni[0].count,
                employment_history: employment[0].count,
                degrees: degrees[0].count,
                certifications: certs[0].count
            }
        });
    } catch(e) {
        res.json({ success: false, error: e.message });
    }
});

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