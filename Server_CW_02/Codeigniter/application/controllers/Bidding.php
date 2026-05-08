<?php
// Codeigniter/application/controllers/Bidding.php

defined('BASEPATH') OR exit('No direct script access allowed');

class Bidding extends CI_Controller {

    private $api_base_url;
    private $api_key = 'dashboard_key_alumni_2025_secure';

    public function __construct() {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->api_base_url = $this->config->item('api_base_url');

        // Protect - must be logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    /**
     * Make GET request to Node API
     */
    private function api_get($endpoint) {
        $url = $this->api_base_url . $endpoint;
        $ch  = curl_init($url);
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
     * GET /bidding
     * Main bidding page
     */
    public function index() {
        // Get features and alumni for dropdown
        $features_res = $this->api_get('/bidding/features');
        $alumni_res   = $this->api_get('/alumni?limit=100');

        $data['user'] = array(
            'full_name' => $this->session->userdata('full_name'),
            'email'     => $this->session->userdata('email')
        );

        $data['features'] = isset($features_res['data']) ? $features_res['data'] : array();
        $data['alumni']   = isset($alumni_res['data'])   ? $alumni_res['data']   : array();
        $data['active_page'] = 'bidding';
        $data['page_title']  = 'Blind Bidding System';
        $data['api_key']     = $this->api_key;
        $data['api_url']     = $this->api_base_url;

        $this->load->view('dashboard/bidding', $data);
    }

    /**
     * GET /bidding/featured
     * Show featured alumni winners
     */
    public function featured() {
        $featured_res = $this->api_get('/bidding/featured');

        $data['user'] = array(
            'full_name' => $this->session->userdata('full_name'),
            'email'     => $this->session->userdata('email')
        );

        $data['featured']    = isset($featured_res['data']) ? $featured_res['data'] : array();
        $data['active_page'] = 'bidding';
        $data['page_title']  = 'Featured Alumni';
        $data['api_key']     = $this->api_key;
        $data['api_url']     = $this->api_base_url;

        $this->load->view('dashboard/featured', $data);
    }
}