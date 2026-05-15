// node-api/config/database.js

const mysql = require('mysql2/promise');
require('dotenv').config();

// Create a connection pool for efficient DB management
const pool = mysql.createPool({
    host:             process.env.DB_HOST     || 'localhost',
    port:             process.env.DB_PORT     || 3306,
    database:         'alumni_platform',       // CW1 database
    user:             process.env.DB_USER     || 'root',
    password:         process.env.DB_PASSWORD || '',
    waitForConnections: true,
    connectionLimit:  10,
    queueLimit:       0
});

// Test the connection on startup
async function testConnection() {
    try {
        const connection = await pool.getConnection();
        console.log('✅ Connected to CW1 alumni_platform database');
        connection.release();
    } catch (error) {
        console.error('❌ Database connection failed:', error.message);
        process.exit(1);
    }
}

testConnection();

module.exports = pool;