<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Developer Controller - API Key management dashboard.
 *
 * Allows authenticated users to:
 *   - Generate API keys
 *   - View their API keys list
 *   - View usage statistics for each key
 *   - Revoke / reactivate keys
 *   - Revoke bearer tokens
 */
class Developer extends CI_Controller
{
    private $user_id;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Api_key_model');
        $this->load->library(array('session', 'form_validation', 'auth_lib'));
        $this->load->helper(array('url', 'form', 'security'));

        $this->auth_lib->require_login();
        $this->user_id = $this->session->userdata('user_id');
    }

    // ================================================================
    // DASHBOARD — LIST ALL KEYS
    // ================================================================

    /**
     * Developer dashboard — shows all API keys and overview stats.
     * GET /developer
     */
    public function index()
    {
        $data['stats'] = $this->Api_key_model->get_user_stats($this->user_id);
        $data['first_name'] = $this->session->userdata('first_name');

        $this->load->view('developer/dashboard', $data);
    }

    // ================================================================
    // GENERATE NEW API KEY
    // ================================================================

    /**
     * Show create API key form.
     * GET /developer/create
     */
    public function create()
    {
        $this->load->view('developer/create_key');
    }

    /**
     * Process API key creation.
     * POST /developer/create_save
     */
    public function create_save()
    {
        $this->form_validation->set_rules('client_name', 'Client Name', 'required|max_length[255]');
        $this->form_validation->set_rules('client_description', 'Description', 'max_length[1000]');
        $this->form_validation->set_rules('client_type', 'Client Type', 'required|in_list[web,mobile,desktop,iot,ar,other]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('developer/create_key');
            return;
        }

        $scopes = $this->input->post('scopes');
        if (is_array($scopes)) {
            $scopes = implode(',', $scopes);
        } else {
            $scopes = 'read';
        }

        $result = $this->Api_key_model->create_api_key(
            $this->user_id,
            $this->security->xss_clean($this->input->post('client_name')),
            $this->security->xss_clean($this->input->post('client_description')),
            $this->input->post('client_type'),
            $scopes
        );

        // Show the secret ONCE
        $data['new_key'] = $result;
        $this->load->view('developer/key_created', $data);
    }

    // ================================================================
    // VIEW KEY STATISTICS
    // ================================================================

    /**
     * View detailed statistics for a specific API key.
     * GET /developer/stats/5
     */
    public function stats($id)
    {
        $key = $this->Api_key_model->get_key($id, $this->user_id);

        if (!$key) {
            $this->session->set_flashdata('error', 'API key not found.');
            redirect('developer');
        }

        $data['key']   = $key;
        $data['stats'] = $this->Api_key_model->get_usage_stats($id);

        $this->load->view('developer/stats', $data);
    }

    // ================================================================
    // REVOKE / REACTIVATE
    // ================================================================

    /**
     * Revoke an API key entirely.
     * POST /developer/revoke
     */
    public function revoke()
    {
        $id     = intval($this->input->post('key_id'));
        $reason = $this->security->xss_clean($this->input->post('reason'));

        $this->Api_key_model->revoke_key($id, $this->user_id, $reason);

        $this->session->set_flashdata('success', 'API key has been revoked.');
        redirect('developer');
    }

    /**
     * Revoke only the bearer token (force re-authentication).
     * POST /developer/revoke_token
     */
    public function revoke_token()
    {
        $id = intval($this->input->post('key_id'));

        $this->Api_key_model->revoke_bearer_token($id, $this->user_id);

        $this->session->set_flashdata('success', 'Bearer token has been revoked. Client must re-authenticate.');
        redirect('developer');
    }

    /**
     * Reactivate a revoked key.
     * GET /developer/reactivate/5
     */
    public function reactivate($id)
    {
        $this->Api_key_model->reactivate_key($id, $this->user_id);

        $this->session->set_flashdata('success', 'API key has been reactivated.');
        redirect('developer');
    }

    /**
     * Delete a key permanently.
     * GET /developer/delete/5
     */
    public function delete_key($id)
    {
        $this->Api_key_model->delete_key($id, $this->user_id);

        $this->session->set_flashdata('success', 'API key deleted permanently.');
        redirect('developer');
    }
}