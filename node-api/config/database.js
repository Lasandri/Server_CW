/**
 * Database Configuration Module
 * 
 * Creates a MySQL connection pool for efficient connection management.
 * Uses mysql2/promise for async/await support.
 * Connection pool automatically manages opening/closing connections.
 */

const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT || 3306,
    waitForConnections: true,
    connectionLimit: 10,        // Max simultaneous connections
    queueLimit: 0,              // Unlimited queued requests
    enableKeepAlive: true,
    keepAliveInitialDelay: 0,
});

// Test connection on startup
pool.getConnection()
    .then(conn => {
        console.log('✅ MySQL database connected successfully');
        conn.release();
    })
    .catch(err => {
        console.error('❌ Database connection failed:', err.message);
    });

module.exports = pool;