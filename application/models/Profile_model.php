<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Profile_model - Handles all alumni profile database operations.
 *
 * Manages:
 *   - Personal info & biography (alumni_profiles table)
 *   - Degrees (degrees table)
 *   - Certifications (certifications table)
 *   - Licences (licences table)
 *   - Professional courses (professional_courses table)
 *   - Employment history (employment_history table)
 *   - Profile image upload
 *   - Profile completion percentage calculation
 *
 * All tables linked to users via user_id foreign key.
 */
class Profile_model extends CI_Model
{
    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->load->database();
    // }

    // ================================================================
    // ALUMNI PROFILE (Personal Info, Bio, LinkedIn, Image)
    // ================================================================

    /**
     * Get alumni profile by user ID.
     *
     * @param int $user_id
     * @return object|null
     */
    public function get_profile($user_id)
    {
        $query = $this->db->get_where('alumni_profiles', array('user_id' => $user_id));
        return $query->row();
    }

    /**
     * Create or update alumni profile.
     * Uses INSERT if no profile exists, UPDATE if it does.
     *
     * @param int   $user_id
     * @param array $data Profile fields
     * @return bool
     */
    public function save_profile($user_id, $data)
    {
        $existing = $this->get_profile($user_id);

        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($existing) {
            // Update existing profile
            $this->db->where('user_id', $user_id);
            return $this->db->update('alumni_profiles', $data);
        } else {
            // Create new profile
            $data['user_id']    = $user_id;
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert('alumni_profiles', $data);
        }
    }

    /**
     * Update profile image path.
     *
     * @param int    $user_id
     * @param string $image_path Path to uploaded image
     * @return bool
     */
    public function update_profile_image($user_id, $image_path)
    {
        return $this->save_profile($user_id, array(
            'profile_image' => $image_path,
        ));
    }

    // ================================================================
    // DEGREES
    // ================================================================

    /**
     * Get all degrees for a user.
     *
     * @param int $user_id
     * @return array
     */
    public function get_degrees($user_id)
    {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('completion_date', 'DESC')
            ->get('degrees');
        return $query->result();
    }

    /**
     * Get a single degree by ID (with ownership check).
     *
     * @param int $id      Degree ID
     * @param int $user_id Owner's user ID
     * @return object|null
     */
    public function get_degree($id, $user_id)
    {
        $query = $this->db->get_where('degrees', array(
            'id'      => $id,
            'user_id' => $user_id,
        ));
        return $query->row();
    }

    /**
     * Add a new degree.
     *
     * @param int   $user_id
     * @param array $data
     * @return int|bool Insert ID or FALSE
     */
    public function add_degree($user_id, $data)
    {
        $data['user_id']    = $user_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('degrees', $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update an existing degree (with ownership check).
     *
     * @param int   $id
     * @param int   $user_id
     * @param array $data
     * @return bool
     */
    public function update_degree($id, $user_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id); // Ownership check
        return $this->db->update('degrees', $data);
    }

    /**
     * Delete a degree (with ownership check).
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function delete_degree($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->delete('degrees');
    }

    // ================================================================
    // CERTIFICATIONS
    // ================================================================

    public function get_certifications($user_id)
    {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('completion_date', 'DESC')
            ->get('certifications');
        return $query->result();
    }

    public function get_certification($id, $user_id)
    {
        $query = $this->db->get_where('certifications', array(
            'id'      => $id,
            'user_id' => $user_id,
        ));
        return $query->row();
    }

    public function add_certification($user_id, $data)
    {
        $data['user_id']    = $user_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('certifications', $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function update_certification($id, $user_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('certifications', $data);
    }

    public function delete_certification($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->delete('certifications');
    }

    // ================================================================
    // LICENCES
    // ================================================================

    public function get_licences($user_id)
    {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('issue_date', 'DESC')
            ->get('licences');
        return $query->result();
    }

    public function get_licence($id, $user_id)
    {
        $query = $this->db->get_where('licences', array(
            'id'      => $id,
            'user_id' => $user_id,
        ));
        return $query->row();
    }

    public function add_licence($user_id, $data)
    {
        $data['user_id']    = $user_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('licences', $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function update_licence($id, $user_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('licences', $data);
    }

    public function delete_licence($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->delete('licences');
    }

    // ================================================================
    // PROFESSIONAL COURSES
    // ================================================================

    public function get_courses($user_id)
    {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('completion_date', 'DESC')
            ->get('professional_courses');
        return $query->result();
    }

    public function get_course($id, $user_id)
    {
        $query = $this->db->get_where('professional_courses', array(
            'id'      => $id,
            'user_id' => $user_id,
        ));
        return $query->row();
    }

    public function add_course($user_id, $data)
    {
        $data['user_id']    = $user_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('professional_courses', $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function update_course($id, $user_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('professional_courses', $data);
    }

    public function delete_course($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->delete('professional_courses');
    }

    // ================================================================
    // EMPLOYMENT HISTORY
    // ================================================================

    public function get_employment($user_id)
    {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('start_date', 'DESC')
            ->get('employment_history');
        return $query->result();
    }

    public function get_employment_record($id, $user_id)
    {
        $query = $this->db->get_where('employment_history', array(
            'id'      => $id,
            'user_id' => $user_id,
        ));
        return $query->row();
    }

    public function add_employment($user_id, $data)
    {
        $data['user_id']    = $user_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('employment_history', $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function update_employment($id, $user_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('employment_history', $data);
    }

    public function delete_employment($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->delete('employment_history');
    }

    // ================================================================
    // PROFILE COMPLETION STATUS
    // ================================================================

    /**
     * Calculate profile completion percentage.
     * Checks each section and returns percentage + details.
     *
     * @param int $user_id
     * @return array ['percentage' => int, 'sections' => array]
     */
    public function get_completion_status($user_id)
    {
        $sections = array();
        $completed = 0;
        $total = 7; // Total number of sections

        // 1. Personal info & bio
        $profile = $this->get_profile($user_id);
        $sections['personal_info'] = ($profile && !empty($profile->biography));
        if ($sections['personal_info']) $completed++;

        // 2. LinkedIn URL
        $sections['linkedin'] = ($profile && !empty($profile->linkedin_url));
        if ($sections['linkedin']) $completed++;

        // 3. Profile image
        $sections['profile_image'] = ($profile && !empty($profile->profile_image));
        if ($sections['profile_image']) $completed++;

        // 4. At least one degree
        $degrees = $this->get_degrees($user_id);
        $sections['degrees'] = (count($degrees) > 0);
        if ($sections['degrees']) $completed++;

        // 5. At least one certification OR licence OR course
        $certs    = $this->get_certifications($user_id);
        $sections['certifications'] = (count($certs) > 0);
        if ($sections['certifications']) $completed++;

        // 6. Licences or courses
        $licences = $this->get_licences($user_id);
        $courses  = $this->get_courses($user_id);
        $sections['licences_courses'] = (count($licences) > 0 || count($courses) > 0);
        if ($sections['licences_courses']) $completed++;

        // 7. Employment history
        $employment = $this->get_employment($user_id);
        $sections['employment'] = (count($employment) > 0);
        if ($sections['employment']) $completed++;

        $percentage = round(($completed / $total) * 100);

        // Update profile completion flag
        $this->save_profile($user_id, array(
            'is_profile_complete' => ($percentage == 100) ? 1 : 0,
        ));

        return array(
            'percentage' => $percentage,
            'completed'  => $completed,
            'total'      => $total,
            'sections'   => $sections,
        );
    }

    /**
     * Get full profile data (all sections combined).
     * Used for displaying complete profile or API responses.
     *
     * @param int $user_id
     * @return array All profile data
     */
    public function get_full_profile($user_id)
    {
        // Get user basic info
        $this->db->select('id, first_name, last_name, email, created_at');
        $user = $this->db->get_where('users', array('id' => $user_id))->row();

        return array(
            'user'           => $user,
            'profile'        => $this->get_profile($user_id),
            'degrees'        => $this->get_degrees($user_id),
            'certifications' => $this->get_certifications($user_id),
            'licences'       => $this->get_licences($user_id),
            'courses'        => $this->get_courses($user_id),
            'employment'     => $this->get_employment($user_id),
            'completion'     => $this->get_completion_status($user_id),
        );
    }
}