<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller - Handles all authentication operations.
 *
 * CI3 version: uses CI_Controller, $this->load, $this->session,
 * $this->form_validation, $this->input instead of CI4 equivalents.
 *
 * Methods:
 *   showRegister()      - GET  Display registration form
 *   register()          - POST Process registration
 *   verifyEmail()       - GET  Verify email via token link
 *   resendVerification()- POST Resend verification email
 *   showLogin()         - GET  Display login form
 *   login()             - POST Process login
 *   logout()            - GET  Destroy session
 *   showForgotPassword()- GET  Display forgot password form
 *   forgotPassword()    - POST Send reset email
 *   showResetPassword() - GET  Display reset form
 *   resetPassword()     - POST Process password reset
 *
 * Security:
 *   - University domain email validation
 *   - Strong password (upper, lower, digit, special, min 8)
 *   - Bcrypt hashing (cost 12)
 *   - SHA-256 hashed tokens in DB, raw in email
 *   - 24h verification expiry, 1h reset expiry
 *   - Brute-force: 5 fails → 15 min lockout
 *   - Session regeneration on login
 *   - CSRF via CI3 config (enabled globally)
 *   - XSS cleaning via $this->security->xss_clean()
 */
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load the user model
        $this->load->model('User_model');

        // Load required libraries (also in autoload, but explicit is fine)
        $this->load->library(array('session', 'form_validation', 'email'));
        $this->load->helper(array('url', 'form', 'security'));
    }

    // ================================================================
    // REGISTRATION
    // ================================================================

    /**
     * Display registration form.
     * GET /register
     */
    public function showRegister()
    {
        // Redirect if already logged in
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $this->load->view('auth/register');
    }

    /**
     * Process registration form submission.
     * POST /register/submit
     */
    public function register()
    {
        // ---- Set validation rules ----
        $this->form_validation->set_rules(
            'first_name', 'First Name',
            'required|min_length[2]|max_length[100]|alpha_numeric_spaces',
            array(
                'required'    => 'First name is required.',
                'min_length'  => 'First name must be at least 2 characters.',
                'alpha_numeric_spaces' => 'First name may only contain letters, numbers and spaces.',
            )
        );

        $this->form_validation->set_rules(
            'last_name', 'Last Name',
            'required|min_length[2]|max_length[100]|alpha_numeric_spaces',
            array(
                'required' => 'Last name is required.',
            )
        );

        $this->form_validation->set_rules(
            'email', 'Email',
            'required|valid_email|max_length[255]|is_unique[users.email]',
            array(
                'valid_email' => 'Please enter a valid email address.',
                'is_unique'   => 'This email is already registered.',
            )
        );

        $this->form_validation->set_rules(
            'password', 'Password',
            'required|min_length[8]|max_length[255]',
            array(
                'min_length' => 'Password must be at least 8 characters.',
            )
        );

        $this->form_validation->set_rules(
            'password_confirm', 'Confirm Password',
            'required|matches[password]',
            array(
                'matches' => 'Passwords do not match.',
            )
        );

        // ---- Run validation ----
        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/register');
            return;
        }

        // ---- Sanitize inputs ----
        $first_name = $this->security->xss_clean(trim($this->input->post('first_name')));
        $last_name  = $this->security->xss_clean(trim($this->input->post('last_name')));
        $email      = strtolower(trim($this->input->post('email')));
        $password   = $this->input->post('password');

        // ---- Validate university email domain ----
        if (!$this->User_model->is_university_email($email)) {
            $data['error'] = 'Registration requires a valid university email (e.g., name@eastminster.ac.uk).';
            $this->load->view('auth/register', $data);
            return;
        }

        // ---- Validate password strength ----
        if (!$this->_is_strong_password($password)) {
            $data['error'] = 'Password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.';
            $this->load->view('auth/register', $data);
            return;
        }

        // ---- Create user ----
        $user_id = $this->User_model->create_user(array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
            'password'   => $password,  // Will be hashed in model
        ));

        if (!$user_id) {
            $data['error'] = 'Registration failed. Please try again.';
            $this->load->view('auth/register', $data);
            return;
        }

        // ---- Generate verification token & send email ----
        $token = $this->User_model->create_verification_token($user_id);
        $this->_send_verification_email($email, $first_name, $token);

        // Show verification sent page
        $data['email'] = $email;
        $this->load->view('auth/verification_sent', $data);
    }

    // ================================================================
    // EMAIL VERIFICATION
    // ================================================================

    /**
     * Verify email via token link.
     * GET /verify-email?token=xxx
     */
    public function verifyEmail()
    {
        $token = $this->input->get('token');

        if (empty($token)) {
            $this->session->set_flashdata('error', 'Invalid verification link.');
            redirect('login');
        }

        // Verify token (model hashes it and checks DB)
        $user = $this->User_model->verify_email_token($token);

        if (!$user) {
            $this->session->set_flashdata('error', 'Verification link is invalid or has expired. Please request a new one.');
            redirect('login');
        }

        // Mark as verified, clear token (single-use)
        $this->User_model->mark_email_verified($user->id);

        $this->session->set_flashdata('success', 'Email verified successfully! You can now log in.');
        redirect('login');
    }

    /**
     * Resend verification email.
     * POST /resend-verification
     */
    public function resendVerification()
    {
        $email = $this->input->post('email');
        $user  = $this->User_model->find_by_email($email);

        if ($user && !$user->is_email_verified) {
            $token = $this->User_model->create_verification_token($user->id);
            $this->_send_verification_email($email, $user->first_name, $token);
        }

        // Always same message to prevent email enumeration
        $this->session->set_flashdata('success', 'If that email is registered and unverified, a new verification link has been sent.');
        redirect('login');
    }

    // ================================================================
    // LOGIN / LOGOUT
    // ================================================================

    /**
     * Display login form.
     * GET /login
     */
    public function showLogin()
    {
        // Redirect if already logged in
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $this->load->view('auth/login');
    }

    /**
     * Process login form.
     * POST /login/submit
     */
    public function login()
    {
        // ---- Validation ----
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/login');
            return;
        }

        $email    = strtolower(trim($this->input->post('email')));
        $password = $this->input->post('password');

        // ---- Find user ----
        $user = $this->User_model->find_by_email($email);

        if (!$user) {
            $data['error'] = 'Invalid email or password.';
            $this->load->view('auth/login', $data);
            return;
        }

        // ---- Check account lockout ----
        if ($this->User_model->is_account_locked($user)) {
            $locked_until = date('H:i', strtotime($user->locked_until));
            $data['error'] = "Account is temporarily locked. Try again after {$locked_until}.";
            $this->load->view('auth/login', $data);
            return;
        }

        // ---- Verify password ----
        if (!password_verify($password, $user->password_hash)) {
            $this->User_model->increment_login_attempts($user->id, $user->login_attempts);
            $data['error'] = 'Invalid email or password.';
            $this->load->view('auth/login', $data);
            return;
        }

        // ---- Check email verification ----
        if (!$user->is_email_verified) {
            $data['error']        = 'Please verify your email address before logging in.';
            $data['show_resend']  = TRUE;
            $data['resend_email'] = $email;
            $this->load->view('auth/login', $data);
            return;
        }

        // ---- Check account active ----
        if (!$user->is_active) {
            $data['error'] = 'Your account has been deactivated. Please contact support.';
            $this->load->view('auth/login', $data);
            return;
        }

        // ---- Successful login ----
        // Reset failed attempts
        $this->User_model->reset_login_attempts($user->id);

        // Regenerate session ID (prevents session fixation attacks)
        $this->session->sess_regenerate(TRUE);

        // Set session data
        $this->session->set_userdata(array(
            'user_id'       => $user->id,
            'email'         => $user->email,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'logged_in'     => TRUE,
            'login_time'    => time(),
            'last_activity' => time(),
        ));

        // Log session for auditing
        $this->User_model->log_session(
            $user->id,
            session_id(),
            $this->input->ip_address(),
            $this->input->user_agent()
        );

        $this->session->set_flashdata('success', 'Welcome back, ' . htmlspecialchars($user->first_name) . '!');
        redirect('dashboard');
    }

    /**
     * Logout - destroy session.
     * GET /logout
     */
    public function logout()
    {
        $user_id    = $this->session->userdata('user_id');
        $session_id = session_id();

        // Mark session as inactive in audit log
        if ($user_id) {
            $this->User_model->end_session($user_id, $session_id);
        }

        // Destroy all session data
        $this->session->sess_destroy();

        // CI3: After sess_destroy, you may need to redirect manually
        redirect('login');
    }

    // ================================================================
    // PASSWORD RESET
    // ================================================================

    /**
     * Display forgot password form.
     * GET /forgot-password
     */
    public function showForgotPassword()
    {
        $this->load->view('auth/forgot_password');
    }

    /**
     * Process forgot password - send reset email.
     * POST /forgot-password/submit
     */
    public function forgotPassword()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/forgot_password');
            return;
        }

        $email = strtolower(trim($this->input->post('email')));
        $user  = $this->User_model->find_by_email($email);

        if ($user) {
            $token = $this->User_model->create_reset_token($user->id);
            $this->_send_password_reset_email($email, $user->first_name, $token);
        }

        // Always same message (prevents email enumeration)
        $this->session->set_flashdata('success', 'If that email is registered, a password reset link has been sent.');
        redirect('forgot-password');
    }

    /**
     * Display reset password form.
     * GET /reset-password?token=xxx
     */
    public function showResetPassword()
    {
        $token = $this->input->get('token');

        if (empty($token)) {
            $this->session->set_flashdata('error', 'Invalid password reset link.');
            redirect('login');
        }

        // Validate token before showing form
        $user = $this->User_model->verify_reset_token($token);

        if (!$user) {
            $this->session->set_flashdata('error', 'Reset link is invalid or has expired. Please request a new one.');
            redirect('forgot-password');
        }

        $data['token'] = $token;
        $this->load->view('auth/reset_password', $data);
    }

    /**
     * Process password reset.
     * POST /reset-password/submit
     */
    public function resetPassword()
    {
        $this->form_validation->set_rules('token', 'Token', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[255]');
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');

        $token = $this->input->post('token');

        if ($this->form_validation->run() === FALSE) {
            $data['token'] = $token;
            $this->load->view('auth/reset_password', $data);
            return;
        }

        $password = $this->input->post('password');

        // Validate password strength
        if (!$this->_is_strong_password($password)) {
            $data['token'] = $token;
            $data['error'] = 'Password must contain uppercase, lowercase, digit, and special character.';
            $this->load->view('auth/reset_password', $data);
            return;
        }

        // Verify token is still valid
        $user = $this->User_model->verify_reset_token($token);

        if (!$user) {
            $this->session->set_flashdata('error', 'Reset link is invalid or has expired.');
            redirect('forgot-password');
        }

        // Reset password (model hashes it and clears token)
        $this->User_model->reset_password($user->id, $password);

        $this->session->set_flashdata('success', 'Password has been reset successfully. You can now log in.');
        redirect('login');
    }

    // ================================================================
    // PRIVATE HELPER METHODS
    // ================================================================

    /**
     * Check if password meets strength requirements.
     *   - At least 1 uppercase letter
     *   - At least 1 lowercase letter
     *   - At least 1 digit
     *   - At least 1 special character
     *   - Minimum 8 characters (enforced by validation rule)
     *
     * @param string $password
     * @return bool
     */
    private function _is_strong_password($password)
    {
        return preg_match('/[A-Z]/', $password)        // Uppercase
            && preg_match('/[a-z]/', $password)         // Lowercase
            && preg_match('/[0-9]/', $password)         // Digit
            && preg_match('/[^A-Za-z0-9]/', $password); // Special char
    }

    /**
     * Send verification email.
     *
     * @param string $to_email
     * @param string $first_name
     * @param string $token Raw token
     */
    private function _send_verification_email($to_email, $first_name, $token)
    {
        $verification_url = site_url('verify-email?token=' . $token);

        // Load email config
        $this->email->initialize($this->config->item('email') ?: array());

        $this->email->from('noreply@eastminster.ac.uk', 'Alumni Influencers Platform');
        $this->email->to($to_email);
        $this->email->subject('Verify Your Email - Alumni Influencers');
        $this->email->message(
            "<h2>Welcome to Alumni Influencers, {$first_name}!</h2>
            <p>Please verify your email by clicking below:</p>
            <p><a href='{$verification_url}' 
                  style='background-color:#4CAF50;color:white;padding:12px 24px;
                         text-decoration:none;border-radius:4px;display:inline-block;'>
                Verify Email
            </a></p>
            <p>Or copy this URL: {$verification_url}</p>
            <p><strong>This link expires in 24 hours.</strong></p>
            <hr>
            <p><small>If you didn't create this account, ignore this email.</small></p>"
        );

        $this->email->send();
    }

    /**
     * Send password reset email.
     *
     * @param string $to_email
     * @param string $first_name
     * @param string $token Raw token
     */
    private function _send_password_reset_email($to_email, $first_name, $token)
    {
        $reset_url = site_url('reset-password?token=' . $token);

        $this->email->initialize($this->config->item('email') ?: array());

        $this->email->from('noreply@eastminster.ac.uk', 'Alumni Influencers Platform');
        $this->email->to($to_email);
        $this->email->subject('Password Reset - Alumni Influencers');
        $this->email->message(
            "<h2>Password Reset Request</h2>
            <p>Hello {$first_name},</p>
            <p>Click below to reset your password:</p>
            <p><a href='{$reset_url}' 
                  style='background-color:#2196F3;color:white;padding:12px 24px;
                         text-decoration:none;border-radius:4px;display:inline-block;'>
                Reset Password
            </a></p>
            <p>Or copy this URL: {$reset_url}</p>
            <p><strong>This link expires in 1 hour.</strong></p>
            <hr>
            <p><small>If you didn't request this, ignore this email.</small></p>"
        );

        $this->email->send();
    }
}