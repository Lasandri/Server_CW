// node-api/config/database.js

const mysql = require('mysql2/promise');
require('dotenv').config();

// Create a connection pool for efficient DB management
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    port: process.env.DB_PORT,
    database: process.env.DB_NAME,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    waitForConnections: true,
    connectionLimit: 10,        // max simultaneous connections
    queueLimit: 0
});

// Test the connection on startup
async function testConnection() {
    try {
        const connection = await pool.getConnection();
        console.log('✅ Database connected successfully');
        connection.release();
    } catch (error) {
        console.error('❌ Database connection failed:', error.message);
        process.exit(1); // stop server if DB is down
    }
}

testConnection();

module.exports = pool;