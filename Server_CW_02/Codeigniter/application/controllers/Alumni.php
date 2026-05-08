<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alumni Controller - CW2
 * View alumni filtered by programme, graduation date, industry sector
 */
class Alumni extends CI_Controller
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
     * Alumni list with filters
     * GET /alumni
     */
    public function index()
    {
        // Get filter values from GET params
        $filters = array();

        if ($this->input->get('programme')) {
            $filters['programme'] = $this->security->xss_clean(
                $this->input->get('programme')
            );
        }
        if ($this->input->get('graduation_year')) {
            $filters['graduation_year'] = intval(
                $this->input->get('graduation_year')
            );
        }
        if ($this->input->get('industry')) {
            $filters['industry'] = $this->security->xss_clean(
                $this->input->get('industry')
            );
        }
        if ($this->input->get('country')) {
            $filters['country'] = $this->security->xss_clean(
                $this->input->get('country')
            );
        }

        $page  = max(1, intval($this->input->get('page') ?: 1));
        $limit = 20;

        // Try API
        $api_data = $this->api_client->get_alumni($filters, $page, $limit);

        if ($api_data && $api_data['status'] === 'success') {
            $data['alumni']      = $api_data['data']['alumni'];
            $data['pagination']  = $api_data['data']['pagination'];
            $data['filter_opts'] = $api_data['data']['filters'];
        } else {
            // Fallback to direct DB
            $data['alumni']  = $this->Analytics_model
                ->get_alumni($filters, $page, $limit);
            $total           = $this->Analytics_model->count_alumni($filters);
            $data['pagination'] = array(
                'total'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),
            );
            $data['filter_opts'] = array(
                'programmes'       => $this->Analytics_model
                    ->get_programmes(),
                'countries'        => $this->Analytics_model
                    ->get_countries(),
                'graduation_years' => $this->Analytics_model
                    ->get_graduation_years(),
            );
        }

        $data['filters']     = $filters;
        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Alumni Directory';
        $data['active_page'] = 'alumni';

        $this->load->view('dashboard/header', $data);
        $this->load->view('alumni/index', $data);
        $this->load->view('dashboard/footer');
    }

    /**
     * View single alumni profile
     * GET /alumni/view/5
     */
    public function view($id)
    {
        $id = intval($id);

        $api_data = $this->api_client->get_alumni_by_id($id);

        if ($api_data && $api_data['status'] === 'success') {
            $data['alumni_data'] = $api_data['data'];
        } else {
            $data['alumni_data'] = $this->Analytics_model
                ->get_alumni_by_id($id);
        }

        if (!$data['alumni_data']) {
            $this->session->set_flashdata('error', 'Alumni not found.');
            redirect('alumni');
        }

        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Alumni Profile';
        $data['active_page'] = 'alumni';

        $this->load->view('dashboard/header', $data);
        $this->load->view('alumni/profile', $data);
        $this->load->view('dashboard/footer');
    }

    /**
     * Export alumni to CSV
     * GET /alumni/export
     */
    public function export()
    {
        $filters = array();
        if ($this->input->get('programme')) {
            $filters['programme'] = $this->input->get('programme');
        }
        if ($this->input->get('graduation_year')) {
            $filters['graduation_year'] = intval(
                $this->input->get('graduation_year')
            );
        }
        if ($this->input->get('country')) {
            $filters['country'] = $this->input->get('country');
        }

        // Get all alumni (high limit for export)
        $api_data = $this->api_client->get_alumni($filters, 1, 1000);

        if ($api_data && $api_data['status'] === 'success') {
            $alumni = $api_data['data']['alumni'];
        } else {
            $alumni = $this->Analytics_model->get_alumni($filters, 1, 1000);
        }

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="alumni_export_' .
            date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, array(
            'ID', 'First Name', 'Last Name', 'Email',
            'Programme', 'Graduation Year', 'University',
            'Current Job Title', 'Current Company',
            'City', 'Country', 'LinkedIn'
        ));

        foreach ($alumni as $a) {
            $a = (object) $a;
            fputcsv($output, array(
                $a->id,
                $a->first_name,
                $a->last_name,
                $a->email,
                isset($a->programme)        ? $a->programme        : '',
                isset($a->graduation_year)  ? $a->graduation_year  : '',
                isset($a->university_name)  ? $a->university_name  : '',
                isset($a->current_job_title)? $a->current_job_title: '',
                isset($a->current_company)  ? $a->current_company  : '',
                isset($a->city)             ? $a->city             : '',
                isset($a->country)          ? $a->country          : '',
                isset($a->linkedin_url)     ? $a->linkedin_url     : '',
            ));
        }

        fclose($output);
        exit;
    }
}