<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Analytics Controller - CW2
 * All chart pages
 */
class Analytics extends CI_Controller
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

    // ================================================================
    // ANALYTICS OVERVIEW
    // ================================================================

    public function index()
    {
        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Analytics Overview';
        $data['active_page'] = 'analytics';

        $this->load->view('dashboard/header', $data);
        $this->load->view('analytics/index', $data);
        $this->load->view('dashboard/footer');
    }

    // ================================================================
    // SKILLS GAP
    // ================================================================

    public function skillsGap()
    {
        $api_data = $this->api_client->get_skills_gap();

        if ($api_data && $api_data['status'] === 'success') {
            $data['skills'] = $api_data['data'];
        } else {
            $data['skills'] = array(
                'top_certifications'  => $this->Analytics_model
                    ->get_top_certifications(),
                'top_courses'         => $this->Analytics_model
                    ->get_top_courses(),
                'certification_trend' => $this->Analytics_model
                    ->get_certifications_by_year(),
            );
        }

        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Skills Gap Analysis';
        $data['active_page'] = 'analytics';

        $this->load->view('dashboard/header', $data);
        $this->load->view('analytics/skills_gap', $data);
        $this->load->view('dashboard/footer');
    }

    // ================================================================
    // EMPLOYMENT
    // ================================================================

    public function employment()
    {
        $api_data = $this->api_client->get_employment();

        if ($api_data && $api_data['status'] === 'success') {
            $data['employment'] = $api_data['data'];
        } else {
            $data['employment'] = array(
                'by_company'   => $this->Analytics_model
                    ->get_employment_by_company(),
                'jobs_by_year' => $this->Analytics_model
                    ->get_jobs_by_year(),
            );
        }

        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Employment Analytics';
        $data['active_page'] = 'analytics';

        $this->load->view('dashboard/header', $data);
        $this->load->view('analytics/employment', $data);
        $this->load->view('dashboard/footer');
    }

    // ================================================================
    // JOB TITLES
    // ================================================================

    public function jobTitles()
    {
        $api_data = $this->api_client->get_job_titles(15);

        if ($api_data && $api_data['status'] === 'success') {
            $data['job_titles'] = $api_data['data'];
        } else {
            $data['job_titles'] = array(
                'all_time'     => $this->Analytics_model
                    ->get_top_job_titles(15),
                'current_only' => $this->Analytics_model
                    ->get_top_job_titles(15, TRUE),
            );
        }

        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Most Common Job Titles';
        $data['active_page'] = 'analytics';

        $this->load->view('dashboard/header', $data);
        $this->load->view('analytics/job_titles', $data);
        $this->load->view('dashboard/footer');
    }

    // ================================================================
    // TOP EMPLOYERS
    // ================================================================

    public function employers()
    {
        $api_data = $this->api_client->get_employers(10);

        if ($api_data && $api_data['status'] === 'success') {
            $data['employers'] = $api_data['data'];
        } else {
            $data['employers'] = array(
                'top_employers' => $this->Analytics_model
                    ->get_top_employers(10),
            );
        }

        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Top Employers';
        $data['active_page'] = 'analytics';

        $this->load->view('dashboard/header', $data);
        $this->load->view('analytics/employers', $data);
        $this->load->view('dashboard/footer');
    }

    // ================================================================
    // GEOGRAPHIC
    // ================================================================

    public function geographic()
    {
        $api_data = $this->api_client->get_geographic();

        if ($api_data && $api_data['status'] === 'success') {
            $data['geographic'] = $api_data['data'];
        } else {
            $data['geographic'] = array(
                'by_country' => $this->Analytics_model->get_by_country(),
                'by_city'    => $this->Analytics_model->get_by_city(),
            );
        }

        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Geographic Distribution';
        $data['active_page'] = 'analytics';

        $this->load->view('dashboard/header', $data);
        $this->load->view('analytics/geographic', $data);
        $this->load->view('dashboard/footer');
    }

    // ================================================================
    // TRENDS
    // ================================================================

    public function trends()
    {
        $api_data = $this->api_client->get_certification_trends();

        if ($api_data && $api_data['status'] === 'success') {
            $data['trends'] = $api_data['data'];
        } else {
            $data['trends'] = array(
                'monthly_certifications' => array(),
                'monthly_courses'        => array(),
                'degree_programmes'      => $this->Analytics_model
                    ->get_programmes(),
            );
        }

        $data['first_name']  = $this->session->userdata('first_name');
        $data['page_title']  = 'Certification Trends';
        $data['active_page'] = 'analytics';

        $this->load->view('dashboard/header', $data);
        $this->load->view('analytics/trends', $data);
        $this->load->view('dashboard/footer');
    }

    // ================================================================
    // EXPORT CHART DATA AS CSV
    // ================================================================

    public function export($type)
    {
        $type = $this->security->xss_clean($type);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' .
            $type . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        switch ($type) {
            case 'employers':
                fputcsv($output, array(
                    'Company', 'Alumni Count',
                    'Total Roles', 'Current Employees'
                ));
                $data = $this->Analytics_model->get_top_employers(50);
                foreach ($data as $row) {
                    fputcsv($output, array(
                        $row->company_name,
                        $row->alumni_count,
                        $row->total_roles,
                        $row->current_employees,
                    ));
                }
                break;

            case 'job-titles':
                fputcsv($output, array(
                    'Job Title', 'Total Count', 'Current Count'
                ));
                $data = $this->Analytics_model->get_top_job_titles(50);
                foreach ($data as $row) {
                    fputcsv($output, array(
                        $row->job_title,
                        $row->count,
                        $row->current_count,
                    ));
                }
                break;

            case 'certifications':
                fputcsv($output, array(
                    'Certification', 'Organization', 'Count'
                ));
                $data = $this->Analytics_model->get_top_certifications(50);
                foreach ($data as $row) {
                    fputcsv($output, array(
                        $row->certification_name,
                        $row->issuing_organization,
                        $row->count,
                    ));
                }
                break;

            case 'geographic':
                fputcsv($output, array('Country', 'Alumni Count'));
                $data = $this->Analytics_model->get_by_country(50);
                foreach ($data as $row) {
                    fputcsv($output, array(
                        $row->country,
                        $row->count,
                    ));
                }
                break;

            default:
                fputcsv($output, array('Error', 'Unknown export type'));
        }

        fclose($output);
        exit;
    }
}