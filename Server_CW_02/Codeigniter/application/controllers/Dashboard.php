<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller - CW2 Main Dashboard
 */
class Dashboard extends CI_Controller
{
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Analytics_model');
        $this->load->library(array('auth_lib', 'api_client'));
        $this->auth_lib->require_login();
        $this->user_id = $this->session->userdata('user_id');
    }

    /**
     * Main dashboard - overview stats + charts
     * GET /dashboard
     */
    public function index()
    {
        // Try API first, fallback to direct DB
        $api_data = $this->api_client->get_overview();

        if ($api_data && $api_data['status'] === 'success') {
            $data['overview'] = $api_data['data'];
        } else {
            $data['overview'] = $this->Analytics_model->get_overview();
        }

        // Employment chart data
        $emp_data = $this->api_client->get_employment();
        if ($emp_data && $emp_data['status'] === 'success') {
            $data['employment'] = $emp_data['data'];
        } else {
            $data['employment'] = array(
                'by_company'     => $this->Analytics_model
                    ->get_employment_by_company(),
                'jobs_by_year'   => $this->Analytics_model
                    ->get_jobs_by_year(),
            );
        }

        // Skills gap data
        $skills_data = $this->api_client->get_skills_gap();
        if ($skills_data && $skills_data['status'] === 'success') {
            $data['skills'] = $skills_data['data'];
        } else {
            $data['skills'] = array(
                'top_certifications' => $this->Analytics_model
                    ->get_top_certifications(10),
                'top_courses'        => $this->Analytics_model
                    ->get_top_courses(10),
            );
        }

        // Geographic data
        $geo_data = $this->api_client->get_geographic();
        if ($geo_data && $geo_data['status'] === 'success') {
            $data['geographic'] = $geo_data['data'];
        } else {
            $data['geographic'] = array(
                'by_country' => $this->Analytics_model->get_by_country(),
                'by_city'    => $this->Analytics_model->get_by_city(),
            );
        }

        // Job titles
        $titles_data = $this->api_client->get_job_titles(10);
        if ($titles_data && $titles_data['status'] === 'success') {
            $data['job_titles'] = $titles_data['data'];
        } else {
            $data['job_titles'] = array(
                'all_time'     => $this->Analytics_model
                    ->get_top_job_titles(10),
                'current_only' => $this->Analytics_model
                    ->get_top_job_titles(10, TRUE),
            );
        }

        // Top employers
        $emp_top = $this->api_client->get_employers(10);
        if ($emp_top && $emp_top['status'] === 'success') {
            $data['employers'] = $emp_top['data'];
        } else {
            $data['employers'] = array(
                'top_employers' => $this->Analytics_model
                    ->get_top_employers(10),
            );
        }

        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Dashboard';
        $data['active_page'] = 'dashboard';

        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('dashboard/footer');
    }
}