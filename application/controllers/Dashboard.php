<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller - Protected page example.
 * Uses Auth_lib to enforce authentication.
 */
class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load auth library and require login
        $this->load->library('auth_lib');
        $this->auth_lib->require_login();  // Redirects to /login if not logged in
    }

    public function index()
    {
        $data['first_name'] = $this->session->userdata('first_name');
        $data['email']      = $this->session->userdata('email');

        // Load a dashboard view
        echo '<h1>Welcome, ' . htmlspecialchars($data['first_name']) . '!</h1>';
        echo '<p>Email: ' . htmlspecialchars($data['email']) . '</p>';
        echo '<a href="' . site_url('logout') . '">Logout</a>';
    }
}