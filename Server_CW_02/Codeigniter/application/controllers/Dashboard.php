<?php
// Codeigniter/application/controllers/Dashboard.php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    private $api_base_url;
    private $api_key = 'dashboard_key_alumni_2025_secure';

    public function __construct() {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->api_base_url = $this->config->item('api_base_url');

        // Protect all dashboard pages - must be logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    /**
     * Make GET request to Node API
     */
    private function api_get($endpoint, $params = array()) {
        $url = $this->api_base_url . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * GET /dashboard
     * Main dashboard page
     */
    public function index() {
        // Get overview stats
        $overview = $this->api_get('/analytics/overview');

        $data['user']     = array(
            'full_name' => $this->session->userdata('full_name'),
            'email'     => $this->session->userdata('email'),
            'role'      => $this->session->userdata('role')
        );

        $data['overview'] = isset($overview['data']) ? $overview['data'] : array(
            'total_alumni'     => 0,
            'total_sectors'    => 0,
            'total_programmes' => 0,
            'total_years'      => 0
        );

        $data['active_page'] = 'dashboard';
        $data['page_title']  = 'Dashboard';

        $this->load->view('dashboard/index', $data);
    }

    /**
     * GET /dashboard/graphs
     * Analytics graphs page
     */
    public function graphs() {
        $data['user'] = array(
            'full_name' => $this->session->userdata('full_name'),
            'email'     => $this->session->userdata('email')
        );

        // Get filter options for dropdowns
        $filters = $this->api_get('/alumni/filters');
        $data['filter_options'] = isset($filters['data']) ? $filters['data'] : array(
            'programmes'       => array(),
            'graduation_years' => array(),
            'industry_sectors' => array()
        );

        $data['active_page'] = 'graphs';
        $data['page_title']  = 'Analytics & Graphs';
        $data['api_key']     = $this->api_key;
        $data['api_url']     = $this->api_base_url;

        $this->load->view('dashboard/graphs', $data);
    }

    /**
     * GET /dashboard/alumni
     * View alumni page
     */
    public function alumni() {
        $data['user'] = array(
            'full_name' => $this->session->userdata('full_name'),
            'email'     => $this->session->userdata('email')
        );

        // Get filter options
        $filters = $this->api_get('/alumni/filters');
        $data['filter_options'] = isset($filters['data']) ? $filters['data'] : array(
            'programmes'       => array(),
            'graduation_years' => array(),
            'industry_sectors' => array()
        );

        $data['active_page'] = 'alumni';
        $data['page_title']  = 'View Alumni';
        $data['api_key']     = $this->api_key;
        $data['api_url']     = $this->api_base_url;

        $this->load->view('dashboard/alumni', $data);
    }
}