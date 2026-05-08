<?php
// Codeigniter/application/controllers/Security.php

defined('BASEPATH') OR exit('No direct script access allowed');

class Security extends CI_Controller {

    private $api_base_url;
    // Admin key - has write:admin permission
    private $admin_key = 'admin_master_key_2025_ultra_secure';

    public function __construct() {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->api_base_url = $this->config->item('api_base_url');

        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    private function api_get($endpoint) {
        $url = $this->api_base_url . $endpoint;
        $ch  = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $this->admin_key,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function api_post($endpoint, $body) {
        $url = $this->api_base_url . $endpoint;
        $ch  = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $this->admin_key,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function api_put($endpoint) {
        $url = $this->api_base_url . $endpoint;
        $ch  = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $this->admin_key,
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
     * GET /security
     * Main security dashboard
     */
    public function index() {
        $stats_res  = $this->api_get('/security/stats');
        $keys_res   = $this->api_get('/security/keys');
        $logs_res   = $this->api_get('/security/access-logs?limit=20');
        $login_res  = $this->api_get('/security/login-logs?limit=20');

        $data['user'] = array(
            'full_name' => $this->session->userdata('full_name'),
            'email'     => $this->session->userdata('email')
        );

        $data['stats']       = isset($stats_res['data'])  ? $stats_res['data']  : array();
        $data['keys']        = isset($keys_res['data'])   ? $keys_res['data']   : array();
        $data['access_logs'] = isset($logs_res['data'])   ? $logs_res['data']   : array();
        $data['login_logs']  = isset($login_res['data'])  ? $login_res['data']  : array();
        $data['active_page'] = 'security';
        $data['page_title']  = 'Security & API Keys';
        $data['admin_key']   = $this->admin_key;
        $data['api_url']     = $this->api_base_url;

        $this->load->view('dashboard/security', $data);
    }

    /**
     * POST /security/toggle_key
     * Enable/disable an API key
     */
    public function toggle_key() {
        $key_id = $this->input->post('key_id', TRUE);
        $result = $this->api_put('/security/keys/' . $key_id . '/toggle');

        $this->session->set_flashdata(
            isset($result['success']) && $result['success'] ? 'success' : 'error',
            isset($result['message']) ? $result['message'] : 'Action failed'
        );
        redirect('security');
    }

    /**
     * POST /security/create_key
     * Create new API key
     */
    public function create_key() {
        $key_name    = $this->input->post('key_name', TRUE);
        $client_type = $this->input->post('client_type', TRUE);
        $perms_raw   = $this->input->post('permissions', TRUE);
        $permissions = explode(',', $perms_raw);
        $permissions = array_map('trim', $permissions);

        $result = $this->api_post('/security/keys', array(
            'key_name'    => $key_name,
            'client_type' => $client_type,
            'permissions' => $permissions
        ));

        if (isset($result['success']) && $result['success']) {
            $this->session->set_flashdata('success',
                'Key created: ' . $result['data']['api_key']);
        } else {
            $this->session->set_flashdata('error',
                isset($result['message']) ? $result['message'] : 'Failed');
        }
        redirect('security');
    }
}