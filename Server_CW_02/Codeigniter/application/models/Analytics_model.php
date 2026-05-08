<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Analytics_model - CW2
 * Direct DB queries for analytics data
 * Used as fallback if Node API is unavailable
 */
class Analytics_model extends CI_Model
{
    // ================================================================
    // OVERVIEW
    // ================================================================

    public function get_overview()
    {
        return array(
            'total_alumni' => $this->db
                ->where('is_active', 1)
                ->count_all_results('users'),

            'complete_profiles' => $this->db
                ->where('is_profile_complete', 1)
                ->count_all_results('alumni_profiles'),

            'total_certifications' => $this->db
                ->count_all('certifications'),

            'total_degrees' => $this->db
                ->count_all('degrees'),

            'total_jobs' => $this->db
                ->count_all('employment_history'),

            'total_courses' => $this->db
                ->count_all('professional_courses'),

            'total_licences' => $this->db
                ->count_all('licences'),

            'new_this_month' => $this->db
                ->where('MONTH(created_at) = MONTH(NOW())', NULL, FALSE)
                ->where('YEAR(created_at) = YEAR(NOW())', NULL, FALSE)
                ->count_all_results('users'),
        );
    }

    // ================================================================
    // EMPLOYMENT
    // ================================================================

    public function get_employment_by_company($limit = 20)
    {
        return $this->db
            ->select('company_name, COUNT(*) as employee_count,
                      SUM(CASE WHEN is_current=1 THEN 1 ELSE 0 END)
                      as current_count')
            ->group_by('company_name')
            ->order_by('employee_count', 'DESC')
            ->limit($limit)
            ->get('employment_history')
            ->result();
    }

    public function get_jobs_by_year()
    {
        return $this->db
            ->select('YEAR(start_date) as year, COUNT(*) as count')
            ->where('start_date IS NOT NULL', NULL, FALSE)
            ->where('YEAR(start_date) >=', 2015)
            ->group_by('YEAR(start_date)')
            ->order_by('year', 'ASC')
            ->get('employment_history')
            ->result();
    }

    // ================================================================
    // SKILLS GAP
    // ================================================================

    public function get_top_certifications($limit = 15)
    {
        return $this->db
            ->select('certification_name, issuing_organization,
                      COUNT(*) as count')
            ->group_by('certification_name, issuing_organization')
            ->order_by('count', 'DESC')
            ->limit($limit)
            ->get('certifications')
            ->result();
    }

    public function get_top_courses($limit = 15)
    {
        return $this->db
            ->select('course_name, provider, COUNT(*) as count')
            ->group_by('course_name, provider')
            ->order_by('count', 'DESC')
            ->limit($limit)
            ->get('professional_courses')
            ->result();
    }

    public function get_certifications_by_year()
    {
        return $this->db
            ->select('YEAR(completion_date) as year, COUNT(*) as count')
            ->where('completion_date IS NOT NULL', NULL, FALSE)
            ->where('YEAR(completion_date) >=', 2018)
            ->group_by('YEAR(completion_date)')
            ->order_by('year', 'ASC')
            ->get('certifications')
            ->result();
    }

    // ================================================================
    // JOB TITLES
    // ================================================================

    public function get_top_job_titles($limit = 15, $current_only = FALSE)
    {
        $this->db->select('job_title, COUNT(*) as count,
                   SUM(CASE WHEN is_current=1 THEN 1 ELSE 0 END)
                   as current_count');

        if ($current_only) {
            $this->db->where('is_current', 1);
        }

        return $this->db
            ->group_by('job_title')
            ->order_by('count', 'DESC')
            ->limit($limit)
            ->get('employment_history')
            ->result();
    }

    // ================================================================
    // EMPLOYERS
    // ================================================================

    public function get_top_employers($limit = 10)
    {
        return $this->db
            ->select('company_name,
                      COUNT(DISTINCT user_id) as alumni_count,
                      COUNT(*) as total_roles,
                      SUM(CASE WHEN is_current=1 THEN 1 ELSE 0 END)
                      as current_employees')
            ->group_by('company_name')
            ->order_by('alumni_count', 'DESC')
            ->limit($limit)
            ->get('employment_history')
            ->result();
    }

    // ================================================================
    // GEOGRAPHIC
    // ================================================================

    public function get_by_country($limit = 20)
    {
        return $this->db
            ->select('country, COUNT(*) as count')
            ->where('country IS NOT NULL', NULL, FALSE)
            ->where('country !=', '')
            ->group_by('country')
            ->order_by('count', 'DESC')
            ->limit($limit)
            ->get('alumni_profiles')
            ->result();
    }

    public function get_by_city($limit = 20)
    {
        return $this->db
            ->select('city, country, COUNT(*) as count')
            ->where('city IS NOT NULL', NULL, FALSE)
            ->where('city !=', '')
            ->group_by('city, country')
            ->order_by('count', 'DESC')
            ->limit($limit)
            ->get('alumni_profiles')
            ->result();
    }

    // ================================================================
    // ALUMNI LIST
    // ================================================================

    public function get_alumni($filters = array(), $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;

        $this->db->select('u.id, u.first_name, u.last_name,
                           u.email, ap.city, ap.country,
                           ap.linkedin_url, ap.profile_image,
                           d.field_of_study as programme,
                           d.university_name,
                           YEAR(d.completion_date) as graduation_year,
                           eh.job_title as current_job_title,
                           eh.company_name as current_company');
        $this->db->from('users u');
        $this->db->join('alumni_profiles ap', 'ap.user_id = u.id', 'left');
        $this->db->join('degrees d', 'd.user_id = u.id', 'left');
        $this->db->join('employment_history eh',
            'eh.user_id = u.id AND eh.is_current = 1', 'left');
        $this->db->where('u.is_active', 1);

        if (!empty($filters['programme'])) {
            $this->db->like('d.field_of_study', $filters['programme']);
        }
        if (!empty($filters['graduation_year'])) {
            $this->db->where(
                'YEAR(d.completion_date)',
                $filters['graduation_year']
            );
        }
        if (!empty($filters['industry'])) {
            $this->db->like('eh.company_name', $filters['industry']);
        }
        if (!empty($filters['country'])) {
            $this->db->like('ap.country', $filters['country']);
        }

        $this->db->group_by('u.id');
        $this->db->order_by('u.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    public function count_alumni($filters = array())
    {
        $this->db->from('users u');
        $this->db->join('alumni_profiles ap', 'ap.user_id = u.id', 'left');
        $this->db->join('degrees d', 'd.user_id = u.id', 'left');
        $this->db->join('employment_history eh',
            'eh.user_id = u.id AND eh.is_current = 1', 'left');
        $this->db->where('u.is_active', 1);

        if (!empty($filters['programme'])) {
            $this->db->like('d.field_of_study', $filters['programme']);
        }
        if (!empty($filters['graduation_year'])) {
            $this->db->where(
                'YEAR(d.completion_date)',
                $filters['graduation_year']
            );
        }

        $this->db->group_by('u.id');
        return $this->db->count_all_results();
    }

    public function get_alumni_by_id($id)
    {
        $this->db->select('u.id, u.first_name, u.last_name,
                           u.email, u.created_at,
                           ap.biography, ap.linkedin_url,
                           ap.profile_image, ap.city, ap.country');
        $this->db->from('users u');
        $this->db->join('alumni_profiles ap', 'ap.user_id = u.id', 'left');
        $this->db->where('u.id', $id);
        $user = $this->db->get()->row();

        if (!$user) return NULL;

        return array(
            'user' => $user,
            'degrees' => $this->db
                ->where('user_id', $id)
                ->order_by('completion_date', 'DESC')
                ->get('degrees')->result(),
            'certifications' => $this->db
                ->where('user_id', $id)
                ->order_by('completion_date', 'DESC')
                ->get('certifications')->result(),
            'licences' => $this->db
                ->where('user_id', $id)
                ->order_by('issue_date', 'DESC')
                ->get('licences')->result(),
            'courses' => $this->db
                ->where('user_id', $id)
                ->order_by('completion_date', 'DESC')
                ->get('professional_courses')->result(),
            'employment' => $this->db
                ->where('user_id', $id)
                ->order_by('start_date', 'DESC')
                ->get('employment_history')->result(),
        );
    }

    // ================================================================
    // FILTER DROPDOWNS
    // ================================================================

    public function get_programmes()
    {
        return $this->db
            ->select('DISTINCT field_of_study as programme')
            ->where('field_of_study IS NOT NULL', NULL, FALSE)
            ->order_by('field_of_study', 'ASC')
            ->get('degrees')
            ->result();
    }

    public function get_countries()
    {
        return $this->db
            ->select('DISTINCT country')
            ->where('country IS NOT NULL', NULL, FALSE)
            ->where('country !=', '')
            ->order_by('country', 'ASC')
            ->get('alumni_profiles')
            ->result();
    }

    public function get_graduation_years()
    {
        return $this->db
            ->select('DISTINCT YEAR(completion_date) as year')
            ->where('completion_date IS NOT NULL', NULL, FALSE)
            ->order_by('year', 'DESC')
            ->get('degrees')
            ->result();
    }
}