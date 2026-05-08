<?php
// Codeigniter/application/controllers/Auth.php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Auth_model');
        $this->load->library('session');
        $this->load->helper(array('url', 'form'));
    }

    // ── redirect logged-in users ──────────────────────────────────────────
    private function redirect_if_logged_in() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
        }
    }

    // ── INDEX → redirect to login ─────────────────────────────────────────
    public function index() {
        $this->login();
    }

    // ── REGISTER ──────────────────────────────────────────────────────────
    public function register() {
        $this->redirect_if_logged_in();
        $this->load->view('auth/register');
    }

    public function register_submit() {
        $this->redirect_if_logged_in();

        $this->load->library('form_validation');
        $this->form_validation->set_rules('full_name', 'Full Name', 'required|min_length[2]|max_length[100]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $data['errors'] = validation_errors('<div class="text-danger small">', '</div>');
            $this->load->view('auth/register', $data);
            return;
        }

        $result = $this->Auth_model->register([
            'full_name'        => $this->input->post('full_name', TRUE),
            'email'            => $this->input->post('email', TRUE),
            'password'         => $this->input->post('password'),
            'confirm_password' => $this->input->post('confirm_password')
        ]);

        if (isset($result['success']) && $result['success'] === true) {
            $this->session->set_flashdata('success', $result['message']);
            redirect('auth/login');
        } else {
            $data['error'] = isset($result['message']) ? $result['message'] : 'Registration failed.';
            if (isset($result['errors'])) {
                $data['field_errors'] = $result['errors'];
            }
            $this->load->view('auth/register', $data);
        }
    }

    // ── VERIFY EMAIL ──────────────────────────────────────────────────────
    public function verify_email($token = NULL) {
        if (!$token) {
            $this->session->set_flashdata('error', 'Invalid verification link.');
            redirect('auth/login');
            return;
        }

        $result = $this->Auth_model->verify_email($token);

        if (isset($result['success']) && $result['success'] === true) {
            $this->session->set_flashdata('success', 'Email verified! You can now log in.');
        } else {
            $this->session->set_flashdata('error',
                isset($result['message']) ? $result['message'] : 'Verification failed.');
        }
        redirect('auth/login');
    }

    public function resend_verification() {
        $this->redirect_if_logged_in();
        $this->load->view('auth/resend_verification');
    }

    public function resend_verification_submit() {
        $email = $this->input->post('email', TRUE);

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['error'] = 'Please enter a valid email address.';
            $this->load->view('auth/resend_verification', $data);
            return;
        }

        $result = $this->Auth_model->resend_verification($email);
        $data['message'] = isset($result['message']) ? $result['message'] : 'Request processed.';
        $this->load->view('auth/resend_verification', $data);
    }

    // ── LOGIN ─────────────────────────────────────────────────────────────
    public function login() {
        $this->redirect_if_logged_in();
        $this->load->view('auth/login');
    }

    public function login_submit() {
        $this->redirect_if_logged_in();

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $data['errors'] = validation_errors('<div class="text-danger small">', '</div>');
            $this->load->view('auth/login', $data);
            return;
        }

        $email    = $this->input->post('email', TRUE);
        $password = $this->input->post('password');

        $result = $this->Auth_model->login($email, $password);

        if (isset($result['success']) && $result['success'] === true) {
            // Store user data in CI session
            $this->session->set_userdata([
                'logged_in' => TRUE,
                'user_id'   => $result['data']['user']['id'],
                'full_name' => $result['data']['user']['full_name'],
                'email'     => $result['data']['user']['email'],
                'role'      => $result['data']['user']['role'],
                'jwt_token' => $result['data']['token']
            ]);
            redirect('dashboard');
        } else {
            $data['error'] = isset($result['message']) ? $result['message'] : 'Login failed.';

            if (isset($result['action']) && $result['action'] === 'VERIFY_EMAIL') {
                $data['show_resend'] = TRUE;
                $data['unverified_email'] = $email;
            }
            $this->load->view('auth/login', $data);
        }
    }

    // ── LOGOUT ────────────────────────────────────────────────────────────
    public function logout() {
        $token = $this->session->userdata('jwt_token');
        if ($token) {
            $this->Auth_model->logout($token);
        }
        $this->session->sess_destroy();
        $this->session->set_flashdata('success', 'Logged out successfully.');
        redirect('auth/login');
    }

    // ── FORGOT PASSWORD ───────────────────────────────────────────────────
    public function forgot_password() {
        $this->redirect_if_logged_in();
        $this->load->view('auth/forgot_password');
    }

    public function forgot_password_submit() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

        if ($this->form_validation->run() === FALSE) {
            $data['errors'] = validation_errors('<div class="text-danger small">', '</div>');
            $this->load->view('auth/forgot_password', $data);
            return;
        }

        $email = $this->input->post('email', TRUE);
        $this->Auth_model->forgot_password($email);

        // Always show same message - security best practice
        $data['message'] = 'If an account with that email exists, a reset link has been sent.';
        $this->load->view('auth/forgot_password', $data);
    }

    // ── RESET PASSWORD ────────────────────────────────────────────────────
    public function reset_password($token = NULL) {
        if (!$token) {
            $this->session->set_flashdata('error', 'Invalid reset link.');
            redirect('auth/forgot_password');
            return;
        }
        $data['token'] = $token;
        $this->load->view('auth/reset_password', $data);
    }

    public function reset_password_submit() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password',
            'required|matches[password]');

        $token = $this->input->post('token', TRUE);

        if ($this->form_validation->run() === FALSE) {
            $data['errors'] = validation_errors('<div class="text-danger small">', '</div>');
            $data['token']  = $token;
            $this->load->view('auth/reset_password', $data);
            return;
        }

        $password         = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');

        $result = $this->Auth_model->reset_password($token, $password, $confirm_password);

        if (isset($result['success']) && $result['success'] === true) {
            $this->session->set_flashdata('success', 'Password reset! Please log in.');
            redirect('auth/login');
        } else {
            $data['error'] = isset($result['message'])
                ? $result['message']
                : 'Reset failed. The link may have expired.';
            $data['token'] = $token;
            $this->load->view('auth/reset_password', $data);
        }
    }
}