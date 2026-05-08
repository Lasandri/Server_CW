<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller - CW2 University Dashboard
 */
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library(array('session', 'form_validation', 'email'));
        $this->load->helper(array('url', 'form', 'security'));
    }

    // ================================================================
    // REGISTER
    // ================================================================

    public function showRegister()
    {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $this->load->view('auth/register');
    }

    public function register()
    {
        $this->form_validation->set_rules(
            'first_name', 'First Name',
            'required|min_length[2]|max_length[100]|alpha_numeric_spaces'
        );
        $this->form_validation->set_rules(
            'last_name', 'Last Name',
            'required|min_length[2]|max_length[100]|alpha_numeric_spaces'
        );
        $this->form_validation->set_rules(
            'email', 'Email',
            'required|valid_email|max_length[255]|is_unique[users.email]',
            array('is_unique' => 'This email is already registered.')
        );
        $this->form_validation->set_rules(
            'password', 'Password',
            'required|min_length[8]|max_length[255]'
        );
        $this->form_validation->set_rules(
            'password_confirm', 'Confirm Password',
            'required|matches[password]',
            array('matches' => 'Passwords do not match.')
        );

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/register');
            return;
        }

        $first_name = $this->security->xss_clean(
            trim($this->input->post('first_name'))
        );
        $last_name  = $this->security->xss_clean(
            trim($this->input->post('last_name'))
        );
        $email      = strtolower(trim($this->input->post('email')));
        $password   = $this->input->post('password');

        if (!$this->User_model->is_university_email($email)) {
            $data['error'] = 'Registration requires a valid university email.';
            $this->load->view('auth/register', $data);
            return;
        }

        if (!$this->_is_strong_password($password)) {
            $data['error'] = 'Password must contain uppercase, lowercase, digit, and special character.';
            $this->load->view('auth/register', $data);
            return;
        }

        $user_id = $this->User_model->create_user(array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
            'password'   => $password,
        ));

        if (!$user_id) {
            $data['error'] = 'Registration failed. Please try again.';
            $this->load->view('auth/register', $data);
            return;
        }

        $token = $this->User_model->create_verification_token($user_id);
        $this->_send_verification_email($email, $first_name, $token);

        $data['email'] = $email;
        $this->load->view('auth/verification_sent', $data);
    }

    // ================================================================
    // EMAIL VERIFICATION
    // ================================================================

    public function verifyEmail()
    {
        $token = $this->input->get('token');

        if (empty($token)) {
            $this->session->set_flashdata('error', 'Invalid link.');
            redirect('login');
        }

        $user = $this->User_model->verify_email_token($token);

        if (!$user) {
            $this->session->set_flashdata(
                'error',
                'Link is invalid or expired.'
            );
            redirect('login');
        }

        $this->User_model->mark_email_verified($user->id);
        $this->session->set_flashdata(
            'success',
            'Email verified! You can now log in.'
        );
        redirect('login');
    }

    public function resendVerification()
    {
        $email = $this->input->post('email');
        $user  = $this->User_model->find_by_email($email);

        if ($user && !$user->is_email_verified) {
            $token = $this->User_model->create_verification_token($user->id);
            $this->_send_verification_email(
                $email, $user->first_name, $token
            );
        }

        $this->session->set_flashdata(
            'success',
            'If that email is registered and unverified, a new link has been sent.'
        );
        redirect('login');
    }

    // ================================================================
    // LOGIN / LOGOUT
    // ================================================================

    public function showLogin()
    {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $this->load->view('auth/login');
    }

    public function login()
    {
        $this->form_validation->set_rules(
            'email', 'Email', 'required|valid_email'
        );
        $this->form_validation->set_rules(
            'password', 'Password', 'required'
        );

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/login');
            return;
        }

        $email    = strtolower(trim($this->input->post('email')));
        $password = $this->input->post('password');
        $user     = $this->User_model->find_by_email($email);

        if (!$user) {
            $data['error'] = 'Invalid email or password.';
            $this->load->view('auth/login', $data);
            return;
        }

        if ($this->User_model->is_account_locked($user)) {
            $data['error'] = 'Account locked. Try again after ' .
                date('H:i', strtotime($user->locked_until));
            $this->load->view('auth/login', $data);
            return;
        }

        if (!password_verify($password, $user->password_hash)) {
            $this->User_model->increment_login_attempts(
                $user->id, $user->login_attempts
            );
            $data['error'] = 'Invalid email or password.';
            $this->load->view('auth/login', $data);
            return;
        }

        if (!$user->is_email_verified) {
            $data['error']        = 'Please verify your email first.';
            $data['show_resend']  = TRUE;
            $data['resend_email'] = $email;
            $this->load->view('auth/login', $data);
            return;
        }

        if (!$user->is_active) {
            $data['error'] = 'Account deactivated.';
            $this->load->view('auth/login', $data);
            return;
        }

        // Success
        $this->User_model->reset_login_attempts($user->id);
        $this->session->sess_regenerate(TRUE);

        $this->session->set_userdata(array(
            'user_id'       => $user->id,
            'email'         => $user->email,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'logged_in'     => TRUE,
            'login_time'    => time(),
            'last_activity' => time(),
        ));

        $this->User_model->log_session(
            $user->id,
            session_id(),
            $this->input->ip_address(),
            $this->input->user_agent()
        );

        $this->session->set_flashdata(
            'success',
            'Welcome back, ' . htmlspecialchars($user->first_name) . '!'
        );
        redirect('dashboard');
    }

    public function logout()
    {
        $user_id    = $this->session->userdata('user_id');
        $session_id = session_id();

        if ($user_id) {
            $this->User_model->end_session($user_id, $session_id);
        }

        $this->session->sess_destroy();
        redirect('login');
    }

    // ================================================================
    // PASSWORD RESET
    // ================================================================

    public function showForgotPassword()
    {
        $this->load->view('auth/forgot_password');
    }

    public function forgotPassword()
    {
        $this->form_validation->set_rules(
            'email', 'Email', 'required|valid_email'
        );

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/forgot_password');
            return;
        }

        $email = strtolower(trim($this->input->post('email')));
        $user  = $this->User_model->find_by_email($email);

        if ($user) {
            $token = $this->User_model->create_reset_token($user->id);
            $this->_send_reset_email($email, $user->first_name, $token);
        }

        $this->session->set_flashdata(
            'success',
            'If that email is registered, a reset link has been sent.'
        );
        redirect('forgot-password');
    }

    public function showResetPassword()
    {
        $token = $this->input->get('token');

        if (empty($token)) {
            $this->session->set_flashdata('error', 'Invalid link.');
            redirect('login');
        }

        $user = $this->User_model->verify_reset_token($token);
        if (!$user) {
            $this->session->set_flashdata('error', 'Link expired.');
            redirect('forgot-password');
        }

        $data['token'] = $token;
        $this->load->view('auth/reset_password', $data);
    }

    public function resetPassword()
    {
        $this->form_validation->set_rules(
            'token', 'Token', 'required'
        );
        $this->form_validation->set_rules(
            'password', 'Password',
            'required|min_length[8]|max_length[255]'
        );
        $this->form_validation->set_rules(
            'password_confirm', 'Confirm Password',
            'required|matches[password]'
        );

        $token = $this->input->post('token');

        if ($this->form_validation->run() === FALSE) {
            $data['token'] = $token;
            $this->load->view('auth/reset_password', $data);
            return;
        }

        $password = $this->input->post('password');

        if (!$this->_is_strong_password($password)) {
            $data['token'] = $token;
            $data['error'] = 'Password too weak.';
            $this->load->view('auth/reset_password', $data);
            return;
        }

        $user = $this->User_model->verify_reset_token($token);
        if (!$user) {
            $this->session->set_flashdata('error', 'Link expired.');
            redirect('forgot-password');
        }

        $this->User_model->reset_password($user->id, $password);
        $this->session->set_flashdata(
            'success',
            'Password reset! You can now log in.'
        );
        redirect('login');
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function _is_strong_password($password)
    {
        return preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }

    private function _send_verification_email($to, $name, $token)
    {
        $url = site_url('verify-email?token=' . $token);

        $this->email->from(
            'lasandri.20221602@iit.ac.lk',
            'University Analytics Dashboard'
        );
        $this->email->to($to);
        $this->email->subject('Verify Your Email - University Dashboard');
        $this->email->message("
            <h2>Welcome, {$name}!</h2>
            <p>Click below to verify your email:</p>
            <p><a href='{$url}'
                  style='background:#1565C0;color:#fff;padding:12px 24px;
                         text-decoration:none;border-radius:4px;'>
                Verify Email
            </a></p>
            <p>Link expires in 24 hours.</p>
        ");

        if (!$this->email->send()) {
            log_message('error', 'Verification email failed: ' .
                $this->email->print_debugger());
        }
    }

    private function _send_reset_email($to, $name, $token)
    {
        $url = site_url('reset-password?token=' . $token);

        $this->email->from(
            'lasandri.20221602@iit.ac.lk',
            'University Analytics Dashboard'
        );
        $this->email->to($to);
        $this->email->subject('Password Reset - University Dashboard');
        $this->email->message("
            <h2>Password Reset</h2>
            <p>Hello {$name}, click below to reset:</p>
            <p><a href='{$url}'
                  style='background:#1565C0;color:#fff;padding:12px 24px;
                         text-decoration:none;border-radius:4px;'>
                Reset Password
            </a></p>
            <p>Link expires in 1 hour.</p>
        ");

        if (!$this->email->send()) {
            log_message('error', 'Reset email failed.');
        }
    }
}