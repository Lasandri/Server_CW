<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User_model - Handles all database operations for users table.
 * 
 * CI3 models extend CI_Model and use $this->db for queries.
 * No built-in callbacks, allowedFields, or auto-timestamps like CI4.
 * All security logic is handled manually.
 * 
 * Methods:
 *   create_user()             - Insert new user with hashed password
 *   find_by_email()           - Look up user by email
 *   find_by_id()              - Look up user by ID
 *   is_university_email()     - Validate email domain
 *   generate_secure_token()   - Create crypto-random token
 *   create_verification_token() - Save email verification token
 *   verify_email_token()      - Validate and find user by token
 *   create_reset_token()      - Save password reset token
 *   verify_reset_token()      - Validate reset token
 *   increment_login_attempts()- Brute-force counter
 *   reset_login_attempts()    - Clear counter on success
 *   is_account_locked()       - Check lockout status
 */
class User_model extends CI_Model
{
    /** @var string Database table name */
    private $table = 'users';

    /** @var string Sessions table name */
    private $sessions_table = 'user_sessions';

    /** @var array Allowed university email domains */
    private $allowed_domains = array(
        'eastminster.ac.uk',
        'westminster.ac.uk',
        'my.westminster.ac.uk',
        'iit.ac.lk',
    );

    /** @var int Bcrypt cost factor */
    private $bcrypt_cost = 12;

    /** @var int Max failed login attempts before lockout */
    private $max_login_attempts = 5;

    /** @var int Lockout duration in minutes */
    private $lockout_minutes = 15;

    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->load->database();
    // }

    // ================================================================
    // CREATE / READ
    // ================================================================

    /**
     * Create a new user with bcrypt-hashed password.
     *
     * @param array $data User data (first_name, last_name, email, password)
     * @return int|bool Inserted user ID or FALSE on failure
     */
    public function create_user($data)
    {
        // Hash password with bcrypt (cost 12)
        $insert_data = array(
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, array('cost' => $this->bcrypt_cost)),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        );

        $result = $this->db->insert($this->table, $insert_data);

        if ($result) {
            return $this->db->insert_id();
        }

        return FALSE;
    }

    /**
     * Find user by email address.
     *
     * @param string $email
     * @return object|null User object or NULL
     */
    public function find_by_email($email)
    {
        $query = $this->db->get_where($this->table, array(
            'email' => strtolower(trim($email))
        ));

        return $query->row(); // Returns object or NULL
    }

    /**
     * Find user by ID.
     *
     * @param int $id
     * @return object|null
     */
    public function find_by_id($id)
    {
        $query = $this->db->get_where($this->table, array('id' => $id));
        return $query->row();
    }

    /**
     * Update user record.
     *
     * @param int   $id   User ID
     * @param array $data Fields to update
     * @return bool
     */
    public function update_user($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Check if email already exists in database.
     *
     * @param string $email
     * @return bool TRUE if email exists
     */
    public function email_exists($email)
    {
        $query = $this->db->get_where($this->table, array(
            'email' => strtolower(trim($email))
        ));

        return $query->num_rows() > 0;
    }

    // ================================================================
    // DOMAIN VALIDATION
    // ================================================================

    /**
     * Validate that email belongs to an allowed university domain.
     *
     * @param string $email
     * @return bool TRUE if domain is allowed
     */
    public function is_university_email($email)
    {
        $parts = explode('@', strtolower($email));

        if (count($parts) !== 2) {
            return FALSE;
        }

        return in_array($parts[1], $this->allowed_domains);
    }

    // ================================================================
    // TOKEN GENERATION
    // ================================================================

    /**
     * Generate a cryptographically secure random token.
     * Uses random_bytes() which is CSPRNG-based (PHP 7+).
     *
     * @return string 64-character hex token
     */
    public function generate_secure_token()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Hash a token with SHA-256 for secure database storage.
     * Raw token is sent to user; only hash is stored in DB.
     *
     * @param string $token Raw token
     * @return string SHA-256 hash
     */
    private function hash_token($token)
    {
        return hash('sha256', $token);
    }

    // ================================================================
    // EMAIL VERIFICATION
    // ================================================================

    /**
     * Create and store email verification token (24 hour expiry).
     *
     * @param int $user_id
     * @return string Raw token (to send in email link)
     */
    public function create_verification_token($user_id)
    {
        $raw_token = $this->generate_secure_token();
        $expiry    = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->update_user($user_id, array(
            'email_verification_token'   => $this->hash_token($raw_token),
            'email_verification_expires' => $expiry,
        ));

        return $raw_token;
    }

    /**
     * Verify email verification token.
     * Checks hashed token match and expiry.
     *
     * @param string $token Raw token from email link
     * @return object|null User object if valid, NULL if invalid/expired
     */
    public function verify_email_token($token)
    {
        $hashed = $this->hash_token($token);

        $query = $this->db
            ->where('email_verification_token', $hashed)
            ->where('email_verification_expires >', date('Y-m-d H:i:s'))
            ->get($this->table);

        return $query->row();
    }

    /**
     * Mark user email as verified and clear the token (single-use).
     *
     * @param int $user_id
     * @return bool
     */
    public function mark_email_verified($user_id)
    {
        return $this->update_user($user_id, array(
            'is_email_verified'          => 1,
            'email_verification_token'   => NULL,
            'email_verification_expires' => NULL,
        ));
    }

    // ================================================================
    // PASSWORD RESET
    // ================================================================

    /**
     * Create and store password reset token (1 hour expiry).
     *
     * @param int $user_id
     * @return string Raw token
     */
    public function create_reset_token($user_id)
    {
        $raw_token = $this->generate_secure_token();
        $expiry    = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update_user($user_id, array(
            'password_reset_token'   => $this->hash_token($raw_token),
            'password_reset_expires' => $expiry,
        ));

        return $raw_token;
    }

    /**
     * Verify password reset token.
     *
     * @param string $token Raw token from email link
     * @return object|null User object if valid
     */
    public function verify_reset_token($token)
    {
        $hashed = $this->hash_token($token);

        $query = $this->db
            ->where('password_reset_token', $hashed)
            ->where('password_reset_expires >', date('Y-m-d H:i:s'))
            ->get($this->table);

        return $query->row();
    }

    /**
     * Reset password: hash new password and clear token.
     *
     * @param int    $user_id
     * @param string $new_password Raw new password
     * @return bool
     */
    public function reset_password($user_id, $new_password)
    {
        return $this->update_user($user_id, array(
            'password_hash'          => password_hash($new_password, PASSWORD_BCRYPT, array('cost' => $this->bcrypt_cost)),
            'password_reset_token'   => NULL,
            'password_reset_expires' => NULL,
            'login_attempts'         => 0,
            'locked_until'           => NULL,
        ));
    }

    // ================================================================
    // BRUTE-FORCE PROTECTION
    // ================================================================

    /**
     * Check if account is currently locked.
     *
     * @param object $user User object from DB
     * @return bool TRUE if locked
     */
    public function is_account_locked($user)
    {
        if (empty($user->locked_until)) {
            return FALSE;
        }

        return strtotime($user->locked_until) > time();
    }

    /**
     * Increment failed login attempt counter.
     * Locks account after max_login_attempts failures.
     *
     * @param int $user_id
     * @param int $current_attempts Current failed attempt count
     */
    public function increment_login_attempts($user_id, $current_attempts)
    {
        $new_attempts = $current_attempts + 1;
        $update_data  = array('login_attempts' => $new_attempts);

        // Lock account if max attempts reached
        if ($new_attempts >= $this->max_login_attempts) {
            $update_data['locked_until'] = date(
                'Y-m-d H:i:s',
                strtotime('+' . $this->lockout_minutes . ' minutes')
            );
        }

        $this->update_user($user_id, $update_data);
    }

    /**
     * Reset login attempts on successful login.
     *
     * @param int $user_id
     */
    public function reset_login_attempts($user_id)
    {
        $this->update_user($user_id, array(
            'login_attempts' => 0,
            'locked_until'   => NULL,
            'last_login_at'  => date('Y-m-d H:i:s'),
        ));
    }

    // ================================================================
    // SESSION AUDIT LOGGING
    // ================================================================

    /**
     * Log a new session for auditing.
     *
     * @param int    $user_id
     * @param string $session_id
     * @param string $ip_address
     * @param string $user_agent
     */
    public function log_session($user_id, $session_id, $ip_address, $user_agent)
    {
        $this->db->insert($this->sessions_table, array(
            'user_id'    => $user_id,
            'session_id' => $session_id,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'login_at'   => date('Y-m-d H:i:s'),
            'is_active'  => 1,
        ));
    }

    /**
     * Mark session as inactive on logout.
     *
     * @param int    $user_id
     * @param string $session_id
     */
    public function end_session($user_id, $session_id)
    {
        $this->db
            ->where('user_id', $user_id)
            ->where('session_id', $session_id)
            ->where('is_active', 1)
            ->update($this->sessions_table, array(
                'logout_at' => date('Y-m-d H:i:s'),
                'is_active' => 0,
            ));
    }
}