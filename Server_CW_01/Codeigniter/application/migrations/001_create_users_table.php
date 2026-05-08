<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create users and user_sessions tables
 * 
 * Run with: php index.php migrate
 * Or create a controller to call $this->migration->latest()
 */
class Migration_Create_users_table extends CI_Migration
{
    public function up()
    {
        // ---- Users Table ----
        $this->dbforge->add_field(array(
            'id' => array(
                'type'           => 'INT',
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ),
            'first_name' => array(
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ),
            'last_name' => array(
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ),
            'email' => array(
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ),
            'password_hash' => array(
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ),
            'is_email_verified' => array(
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ),
            'email_verification_token' => array(
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => TRUE,
            ),
            'email_verification_expires' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
            'password_reset_token' => array(
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => TRUE,
            ),
            'password_reset_expires' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
            'is_active' => array(
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ),
            'last_login_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
            'login_attempts' => array(
                'type'     => 'INT',
                'unsigned' => TRUE,
                'default'  => 0,
            ),
            'locked_until' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
        ));

        $this->dbforge->add_key('id', TRUE);             // Primary key
        $this->dbforge->add_key('email');                 // Index
        $this->dbforge->add_key('email_verification_token');
        $this->dbforge->add_key('password_reset_token');
        $this->dbforge->create_table('users');

        // Add unique constraint on email
        $this->db->query('ALTER TABLE users ADD UNIQUE INDEX idx_email_unique (email)');

        // ---- User Sessions Table ----
        $this->dbforge->add_field(array(
            'id' => array(
                'type'           => 'INT',
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ),
            'user_id' => array(
                'type'     => 'INT',
                'unsigned' => TRUE,
            ),
            'session_id' => array(
                'type'       => 'VARCHAR',
                'constraint' => 128,
            ),
            'ip_address' => array(
                'type'       => 'VARCHAR',
                'constraint' => 45,
            ),
            'user_agent' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'login_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
            'logout_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
            'is_active' => array(
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ),
        ));

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('user_sessions');

        // Add foreign key
        $this->db->query(
            'ALTER TABLE user_sessions 
             ADD CONSTRAINT fk_sessions_user_id 
             FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE'
        );
    }

    public function down()
    {
        $this->dbforge->drop_table('user_sessions', TRUE);
        $this->dbforge->drop_table('users', TRUE);
    }
}