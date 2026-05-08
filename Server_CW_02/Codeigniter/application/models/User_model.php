<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User_model - CW2
 * Same structure as CW1 - uses same users table
 */
class User_model extends CI_Model
{
    private $table = 'users';
    private $sessions_table = 'user_sessions';

    private $allowed_domains = array(
        'eastminster.ac.uk',
        'westminster.ac.uk',
        'my.westminster.ac.uk',
        'iit.ac.lk',
    );

    private $bcrypt_cost = 12;
    private $max_login_attempts = 5;
    private $lockout_minutes = 15;

    // ================================================================
    // CREATE / READ
    // ================================================================

    public function create_user($data)
    {
        $insert = array(
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => strtolower(trim($data['email'])),
            'password_hash' => password_hash(
                $data['password'],
                PASSWORD_BCRYPT,
                array('cost' => $this->bcrypt_cost)
            ),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        );

        if ($this->db->insert($this->table, $insert)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function find_by_email($email)
    {
        return $this->db->get_where($this->table, array(
            'email' => strtolower(trim($email))
        ))->row();
    }

    public function find_by_id($id)
    {
        return $this->db->get_where($this->table,
            array('id' => $id))->row();
    }

    public function update_user($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function email_exists($email)
    {
        return $this->db->get_where($this->table, array(
            'email' => strtolower(trim($email))
        ))->num_rows() > 0;
    }

    // ================================================================
    // DOMAIN VALIDATION
    // ================================================================

    public function is_university_email($email)
    {
        $parts = explode('@', strtolower($email));
        if (count($parts) !== 2) return FALSE;
        return in_array($parts[1], $this->allowed_domains);
    }

    // ================================================================
    // TOKEN GENERATION
    // ================================================================

    public function generate_secure_token()
    {
        return bin2hex(random_bytes(32));
    }

    private function hash_token($token)
    {
        return hash('sha256', $token);
    }

    // ================================================================
    // EMAIL VERIFICATION
    // ================================================================

    public function create_verification_token($user_id)
    {
        $raw    = $this->generate_secure_token();
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->update_user($user_id, array(
            'email_verification_token'   => $this->hash_token($raw),
            'email_verification_expires' => $expiry,
        ));

        return $raw;
    }

    public function verify_email_token($token)
    {
        $hashed = $this->hash_token($token);

        return $this->db
            ->where('email_verification_token', $hashed)
            ->where('email_verification_expires >', date('Y-m-d H:i:s'))
            ->get($this->table)
            ->row();
    }

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

    public function create_reset_token($user_id)
    {
        $raw    = $this->generate_secure_token();
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update_user($user_id, array(
            'password_reset_token'   => $this->hash_token($raw),
            'password_reset_expires' => $expiry,
        ));

        return $raw;
    }

    public function verify_reset_token($token)
    {
        $hashed = $this->hash_token($token);

        return $this->db
            ->where('password_reset_token', $hashed)
            ->where('password_reset_expires >', date('Y-m-d H:i:s'))
            ->get($this->table)
            ->row();
    }

    public function reset_password($user_id, $new_password)
    {
        return $this->update_user($user_id, array(
            'password_hash'          => password_hash(
                $new_password,
                PASSWORD_BCRYPT,
                array('cost' => $this->bcrypt_cost)
            ),
            'password_reset_token'   => NULL,
            'password_reset_expires' => NULL,
            'login_attempts'         => 0,
            'locked_until'           => NULL,
        ));
    }

    // ================================================================
    // BRUTE FORCE PROTECTION
    // ================================================================

    public function is_account_locked($user)
    {
        if (empty($user->locked_until)) return FALSE;
        return strtotime($user->locked_until) > time();
    }

    public function increment_login_attempts($user_id, $current)
    {
        $new    = $current + 1;
        $update = array('login_attempts' => $new);

        if ($new >= $this->max_login_attempts) {
            $update['locked_until'] = date(
                'Y-m-d H:i:s',
                strtotime('+' . $this->lockout_minutes . ' minutes')
            );
        }

        $this->update_user($user_id, $update);
    }

    public function reset_login_attempts($user_id)
    {
        $this->update_user($user_id, array(
            'login_attempts' => 0,
            'locked_until'   => NULL,
            'last_login_at'  => date('Y-m-d H:i:s'),
        ));
    }

    // ================================================================
    // SESSION AUDIT
    // ================================================================

    public function log_session($user_id, $session_id, $ip, $agent)
    {
        $this->db->insert($this->sessions_table, array(
            'user_id'    => $user_id,
            'session_id' => $session_id,
            'ip_address' => $ip,
            'user_agent' => $agent,
            'login_at'   => date('Y-m-d H:i:s'),
            'is_active'  => 1,
        ));
    }

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