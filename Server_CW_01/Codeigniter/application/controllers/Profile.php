<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Profile Controller - Manages complete alumni profile.
 *
 * All methods require authentication (checked in constructor).
 * Each section has add/edit/delete functionality.
 * Profile image upload with validation.
 * URL validation for all URL fields.
 */
class Profile extends CI_Controller
{
    /** @var int Current logged-in user ID */
    private $user_id;

    public function __construct()
    {
        parent::__construct();

        // Load required resources
        $this->load->model('Profile_model');
        $this->load->library(array('session', 'form_validation', 'auth_lib'));
        $this->load->helper(array('url', 'form', 'security'));

        // Require login for all profile pages
        $this->auth_lib->require_login();

        // Store user ID for convenience
        $this->user_id = $this->session->userdata('user_id');
    }

    // ================================================================
    // DASHBOARD / OVERVIEW
    // ================================================================

    /**
     * Profile dashboard - shows all sections and completion status.
     * GET /profile
     */
    public function index()
    {
        $data = $this->Profile_model->get_full_profile($this->user_id);
        $data['first_name'] = $this->session->userdata('first_name');

        $this->load->view('profile/dashboard', $data);
    }

    // ================================================================
    // PERSONAL INFO & BIOGRAPHY
    // ================================================================

    /**
     * Show/edit personal information form.
     * GET /profile/personal
     */
    public function personal()
    {
        $data['profile'] = $this->Profile_model->get_profile($this->user_id);
        $this->load->view('profile/personal', $data);
    }

    /**
     * Save personal information.
     * POST /profile/personal_save
     */
    public function personal_save()
    {
        // Validation rules
        $this->form_validation->set_rules('phone', 'Phone', 'max_length[20]');
        $this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'callback__valid_date');
        $this->form_validation->set_rules('biography', 'Biography', 'max_length[2000]');
        $this->form_validation->set_rules('linkedin_url', 'LinkedIn URL', 'max_length[500]|callback__valid_url_optional');
        $this->form_validation->set_rules('city', 'City', 'max_length[100]|alpha_numeric_spaces');
        $this->form_validation->set_rules('country', 'Country', 'max_length[100]|alpha_numeric_spaces');

        if ($this->form_validation->run() === FALSE) {
            $data['profile'] = $this->Profile_model->get_profile($this->user_id);
            $this->load->view('profile/personal', $data);
            return;
        }

        // Sanitize and save
        $profile_data = array(
            'phone'         => $this->security->xss_clean($this->input->post('phone')),
            'date_of_birth' => $this->input->post('date_of_birth') ?: NULL,
            'biography'     => $this->security->xss_clean($this->input->post('biography')),
            'linkedin_url'  => $this->_sanitize_url($this->input->post('linkedin_url')),
            'city'          => $this->security->xss_clean($this->input->post('city')),
            'country'       => $this->security->xss_clean($this->input->post('country')),
        );

        $this->Profile_model->save_profile($this->user_id, $profile_data);

        $this->session->set_flashdata('success', 'Personal information saved successfully.');
        redirect('profile');
    }

    // ================================================================
    // PROFILE IMAGE UPLOAD
    // ================================================================

    /**
     * Show image upload form.
     * GET /profile/image
     */
    public function image()
    {
        $data['profile'] = $this->Profile_model->get_profile($this->user_id);
        $this->load->view('profile/image_upload', $data);
    }

    /**
     * Process image upload.
     * POST /profile/image_upload
     *
     * Security:
     *   - Only jpg, jpeg, png, gif allowed
     *   - Max 2MB file size
     *   - Filename sanitized (renamed to user_id + timestamp)
     *   - Stored outside web-accessible folder ideally
     */
    
   public function image_upload()
{
    // Check if file was selected
    if (empty($_FILES['profile_image']['name'])) {
        $data['error']   = 'Please select an image file.';
        $data['profile'] = $this->Profile_model->get_profile($this->user_id);
        $this->load->view('profile/image_upload', $data);
        return;
    }

    // Manual file extension check (extra security layer)
    $file_name = $_FILES['profile_image']['name'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed   = array('jpg', 'jpeg', 'png', 'gif');

    if (!in_array($file_ext, $allowed)) {
        $data['error']   = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
        $data['profile'] = $this->Profile_model->get_profile($this->user_id);
        $this->load->view('profile/image_upload', $data);
        return;
    }

    // Check file size (2MB = 2097152 bytes)
    if ($_FILES['profile_image']['size'] > 2097152) {
        $data['error']   = 'File size must be less than 2MB.';
        $data['profile'] = $this->Profile_model->get_profile($this->user_id);
        $this->load->view('profile/image_upload', $data);
        return;
    }

    // Configure upload
    $config['upload_path']      = './uploads/profile_images/';
    $config['allowed_types']    = '*';  // We already validated above
    $config['max_size']         = 2048;
    $config['max_width']        = 2000;
    $config['max_height']       = 2000;
    $config['file_name']        = 'profile_' . $this->user_id . '_' . time();
    $config['overwrite']        = FALSE;
    $config['file_ext_tolower'] = TRUE;

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('profile_image')) {
        $data['error']   = $this->upload->display_errors('', '');
        $data['profile'] = $this->Profile_model->get_profile($this->user_id);
        $this->load->view('profile/image_upload', $data);
        return;
    }

    // Upload successful
    $upload_data = $this->upload->data();

    // Extra security: verify it's actually an image
    $image_info = @getimagesize($upload_data['full_path']);
    if ($image_info === FALSE) {
        // Not a real image - delete it
        unlink($upload_data['full_path']);
        $data['error']   = 'The uploaded file is not a valid image.';
        $data['profile'] = $this->Profile_model->get_profile($this->user_id);
        $this->load->view('profile/image_upload', $data);
        return;
    }

    $image_path = 'uploads/profile_images/' . $upload_data['file_name'];

    // Delete old image if exists
    $old_profile = $this->Profile_model->get_profile($this->user_id);
    if ($old_profile && !empty($old_profile->profile_image)) {
        $old_file = './' . $old_profile->profile_image;
        if (file_exists($old_file)) {
            unlink($old_file);
        }
    }

    // Save to database
    $this->Profile_model->update_profile_image($this->user_id, $image_path);

    $this->session->set_flashdata('success', 'Profile image uploaded successfully.');
    redirect('profile');
}

    // ================================================================
    // DEGREES
    // ================================================================

    /**
     * List all degrees.
     * GET /profile/degrees
     */
    public function degrees()
    {
        $data['degrees'] = $this->Profile_model->get_degrees($this->user_id);
        $this->load->view('profile/degrees', $data);
    }

    /**
     * Show add degree form.
     * GET /profile/degree_add
     */
    public function degree_add()
    {
        $data['degree'] = NULL; // Empty form
        $data['action'] = 'add';
        $this->load->view('profile/degree_form', $data);
    }

    /**
     * Show edit degree form.
     * GET /profile/degree_edit/5
     */
    public function degree_edit($id)
    {
        $degree = $this->Profile_model->get_degree($id, $this->user_id);

        if (!$degree) {
            $this->session->set_flashdata('error', 'Degree not found.');
            redirect('profile/degrees');
        }

        $data['degree'] = $degree;
        $data['action'] = 'edit';
        $this->load->view('profile/degree_form', $data);
    }

    /**
     * Save degree (add or update).
     * POST /profile/degree_save
     */
    public function degree_save()
    {
        // Validation
        $this->form_validation->set_rules('degree_title', 'Degree Title', 'required|max_length[255]');
        $this->form_validation->set_rules('field_of_study', 'Field of Study', 'required|max_length[255]');
        $this->form_validation->set_rules('university_name', 'University', 'required|max_length[255]');
        $this->form_validation->set_rules('degree_url', 'Degree URL', 'max_length[500]|callback__valid_url_optional');
        $this->form_validation->set_rules('completion_date', 'Completion Date', 'required');
        $this->form_validation->set_rules('grade', 'Grade', 'max_length[50]');

        $id = $this->input->post('id');

        if ($this->form_validation->run() === FALSE) {
            $data['degree'] = ($id) ? $this->Profile_model->get_degree($id, $this->user_id) : NULL;
            $data['action'] = ($id) ? 'edit' : 'add';
            $this->load->view('profile/degree_form', $data);
            return;
        }

        $degree_data = array(
            'degree_title'    => $this->security->xss_clean($this->input->post('degree_title')),
            'field_of_study'  => $this->security->xss_clean($this->input->post('field_of_study')),
            'university_name' => $this->security->xss_clean($this->input->post('university_name')),
            'degree_url'      => $this->_sanitize_url($this->input->post('degree_url')),
            'completion_date' => $this->input->post('completion_date'),
            'grade'           => $this->security->xss_clean($this->input->post('grade')),
        );

        if ($id) {
            $this->Profile_model->update_degree($id, $this->user_id, $degree_data);
            $this->session->set_flashdata('success', 'Degree updated successfully.');
        } else {
            $this->Profile_model->add_degree($this->user_id, $degree_data);
            $this->session->set_flashdata('success', 'Degree added successfully.');
        }

        redirect('profile/degrees');
    }

    /**
     * Delete a degree.
     * GET /profile/degree_delete/5
     */
    public function degree_delete($id)
    {
        $this->Profile_model->delete_degree($id, $this->user_id);
        $this->session->set_flashdata('success', 'Degree deleted.');
        redirect('profile/degrees');
    }

    // ================================================================
    // CERTIFICATIONS
    // ================================================================

    public function certifications()
    {
        $data['certifications'] = $this->Profile_model->get_certifications($this->user_id);
        $this->load->view('profile/certifications', $data);
    }

    public function certification_add()
    {
        $data['cert']   = NULL;
        $data['action'] = 'add';
        $this->load->view('profile/certification_form', $data);
    }

    public function certification_edit($id)
    {
        $cert = $this->Profile_model->get_certification($id, $this->user_id);
        if (!$cert) {
            $this->session->set_flashdata('error', 'Certification not found.');
            redirect('profile/certifications');
        }
        $data['cert']   = $cert;
        $data['action'] = 'edit';
        $this->load->view('profile/certification_form', $data);
    }

    public function certification_save()
    {
        $this->form_validation->set_rules('certification_name', 'Certification Name', 'required|max_length[255]');
        $this->form_validation->set_rules('issuing_organization', 'Issuing Organization', 'required|max_length[255]');
        $this->form_validation->set_rules('certification_url', 'URL', 'max_length[500]|callback__valid_url_optional');
        $this->form_validation->set_rules('completion_date', 'Completion Date', 'required');
        $this->form_validation->set_rules('credential_id', 'Credential ID', 'max_length[100]');

        $id = $this->input->post('id');

        if ($this->form_validation->run() === FALSE) {
            $data['cert']   = ($id) ? $this->Profile_model->get_certification($id, $this->user_id) : NULL;
            $data['action'] = ($id) ? 'edit' : 'add';
            $this->load->view('profile/certification_form', $data);
            return;
        }

        $cert_data = array(
            'certification_name'   => $this->security->xss_clean($this->input->post('certification_name')),
            'issuing_organization' => $this->security->xss_clean($this->input->post('issuing_organization')),
            'certification_url'    => $this->_sanitize_url($this->input->post('certification_url')),
            'completion_date'      => $this->input->post('completion_date'),
            'expiry_date'          => $this->input->post('expiry_date') ?: NULL,
            'credential_id'        => $this->security->xss_clean($this->input->post('credential_id')),
        );

        if ($id) {
            $this->Profile_model->update_certification($id, $this->user_id, $cert_data);
            $this->session->set_flashdata('success', 'Certification updated.');
        } else {
            $this->Profile_model->add_certification($this->user_id, $cert_data);
            $this->session->set_flashdata('success', 'Certification added.');
        }

        redirect('profile/certifications');
    }

    public function certification_delete($id)
    {
        $this->Profile_model->delete_certification($id, $this->user_id);
        $this->session->set_flashdata('success', 'Certification deleted.');
        redirect('profile/certifications');
    }

    // ================================================================
    // LICENCES
    // ================================================================

    public function licences()
    {
        $data['licences'] = $this->Profile_model->get_licences($this->user_id);
        $this->load->view('profile/licences', $data);
    }

    public function licence_add()
    {
        $data['licence'] = NULL;
        $data['action']  = 'add';
        $this->load->view('profile/licence_form', $data);
    }

    public function licence_edit($id)
    {
        $licence = $this->Profile_model->get_licence($id, $this->user_id);
        if (!$licence) {
            $this->session->set_flashdata('error', 'Licence not found.');
            redirect('profile/licences');
        }
        $data['licence'] = $licence;
        $data['action']  = 'edit';
        $this->load->view('profile/licence_form', $data);
    }

    public function licence_save()
    {
        $this->form_validation->set_rules('licence_name', 'Licence Name', 'required|max_length[255]');
        $this->form_validation->set_rules('issuing_body', 'Issuing Body', 'required|max_length[255]');
        $this->form_validation->set_rules('licence_url', 'URL', 'max_length[500]|callback__valid_url_optional');
        $this->form_validation->set_rules('licence_number', 'Licence Number', 'max_length[100]');
        $this->form_validation->set_rules('issue_date', 'Issue Date', 'required');

        $id = $this->input->post('id');

        if ($this->form_validation->run() === FALSE) {
            $data['licence'] = ($id) ? $this->Profile_model->get_licence($id, $this->user_id) : NULL;
            $data['action']  = ($id) ? 'edit' : 'add';
            $this->load->view('profile/licence_form', $data);
            return;
        }

        $licence_data = array(
            'licence_name'   => $this->security->xss_clean($this->input->post('licence_name')),
            'issuing_body'   => $this->security->xss_clean($this->input->post('issuing_body')),
            'licence_url'    => $this->_sanitize_url($this->input->post('licence_url')),
            'licence_number' => $this->security->xss_clean($this->input->post('licence_number')),
            'issue_date'     => $this->input->post('issue_date'),
            'expiry_date'    => $this->input->post('expiry_date') ?: NULL,
        );

        if ($id) {
            $this->Profile_model->update_licence($id, $this->user_id, $licence_data);
            $this->session->set_flashdata('success', 'Licence updated.');
        } else {
            $this->Profile_model->add_licence($this->user_id, $licence_data);
            $this->session->set_flashdata('success', 'Licence added.');
        }

        redirect('profile/licences');
    }

    public function licence_delete($id)
    {
        $this->Profile_model->delete_licence($id, $this->user_id);
        $this->session->set_flashdata('success', 'Licence deleted.');
        redirect('profile/licences');
    }

    // ================================================================
    // PROFESSIONAL COURSES
    // ================================================================

    public function courses()
    {
        $data['courses'] = $this->Profile_model->get_courses($this->user_id);
        $this->load->view('profile/courses', $data);
    }

    public function course_add()
    {
        $data['course'] = NULL;
        $data['action'] = 'add';
        $this->load->view('profile/course_form', $data);
    }

    public function course_edit($id)
    {
        $course = $this->Profile_model->get_course($id, $this->user_id);
        if (!$course) {
            $this->session->set_flashdata('error', 'Course not found.');
            redirect('profile/courses');
        }
        $data['course'] = $course;
        $data['action'] = 'edit';
        $this->load->view('profile/course_form', $data);
    }

    public function course_save()
    {
        $this->form_validation->set_rules('course_name', 'Course Name', 'required|max_length[255]');
        $this->form_validation->set_rules('provider', 'Provider', 'required|max_length[255]');
        $this->form_validation->set_rules('course_url', 'URL', 'max_length[500]|callback__valid_url_optional');
        $this->form_validation->set_rules('completion_date', 'Completion Date', 'required');
        $this->form_validation->set_rules('duration', 'Duration', 'max_length[100]');

        $id = $this->input->post('id');

        if ($this->form_validation->run() === FALSE) {
            $data['course'] = ($id) ? $this->Profile_model->get_course($id, $this->user_id) : NULL;
            $data['action'] = ($id) ? 'edit' : 'add';
            $this->load->view('profile/course_form', $data);
            return;
        }

        $course_data = array(
            'course_name'     => $this->security->xss_clean($this->input->post('course_name')),
            'provider'        => $this->security->xss_clean($this->input->post('provider')),
            'course_url'      => $this->_sanitize_url($this->input->post('course_url')),
            'completion_date' => $this->input->post('completion_date'),
            'duration'        => $this->security->xss_clean($this->input->post('duration')),
        );

        if ($id) {
            $this->Profile_model->update_course($id, $this->user_id, $course_data);
            $this->session->set_flashdata('success', 'Course updated.');
        } else {
            $this->Profile_model->add_course($this->user_id, $course_data);
            $this->session->set_flashdata('success', 'Course added.');
        }

        redirect('profile/courses');
    }

    public function course_delete($id)
    {
        $this->Profile_model->delete_course($id, $this->user_id);
        $this->session->set_flashdata('success', 'Course deleted.');
        redirect('profile/courses');
    }

    // ================================================================
    // EMPLOYMENT HISTORY
    // ================================================================

    public function employment()
    {
        $data['employment'] = $this->Profile_model->get_employment($this->user_id);
        $this->load->view('profile/employment', $data);
    }

    public function employment_add()
    {
        $data['job']    = NULL;
        $data['action'] = 'add';
        $this->load->view('profile/employment_form', $data);
    }

    public function employment_edit($id)
    {
        $job = $this->Profile_model->get_employment_record($id, $this->user_id);
        if (!$job) {
            $this->session->set_flashdata('error', 'Employment record not found.');
            redirect('profile/employment');
        }
        $data['job']    = $job;
        $data['action'] = 'edit';
        $this->load->view('profile/employment_form', $data);
    }

    public function employment_save()
    {
        $this->form_validation->set_rules('company_name', 'Company Name', 'required|max_length[255]');
        $this->form_validation->set_rules('job_title', 'Job Title', 'required|max_length[255]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[2000]');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required');

        $id = $this->input->post('id');

        if ($this->form_validation->run() === FALSE) {
            $data['job']    = ($id) ? $this->Profile_model->get_employment_record($id, $this->user_id) : NULL;
            $data['action'] = ($id) ? 'edit' : 'add';
            $this->load->view('profile/employment_form', $data);
            return;
        }

        $is_current = $this->input->post('is_current') ? 1 : 0;

        $emp_data = array(
            'company_name' => $this->security->xss_clean($this->input->post('company_name')),
            'job_title'    => $this->security->xss_clean($this->input->post('job_title')),
            'description'  => $this->security->xss_clean($this->input->post('description')),
            'start_date'   => $this->input->post('start_date'),
            'end_date'     => $is_current ? NULL : ($this->input->post('end_date') ?: NULL),
            'is_current'   => $is_current,
        );

        if ($id) {
            $this->Profile_model->update_employment($id, $this->user_id, $emp_data);
            $this->session->set_flashdata('success', 'Employment record updated.');
        } else {
            $this->Profile_model->add_employment($this->user_id, $emp_data);
            $this->session->set_flashdata('success', 'Employment record added.');
        }

        redirect('profile/employment');
    }

    public function employment_delete($id)
    {
        $this->Profile_model->delete_employment($id, $this->user_id);
        $this->session->set_flashdata('success', 'Employment record deleted.');
        redirect('profile/employment');
    }

    // ================================================================
    // VALIDATION CALLBACKS
    // ================================================================

    /**
     * Custom validation: optional URL must be valid if provided.
     */
    public function _valid_url_optional($url)
    {
        if (empty($url)) {
            return TRUE; // Optional field
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->form_validation->set_message('_valid_url_optional', 'The {field} must be a valid URL (e.g., https://example.com).');
            return FALSE;
        }

        // Only allow http and https
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array(strtolower($scheme), array('http', 'https'))) {
            $this->form_validation->set_message('_valid_url_optional', 'The {field} must start with http:// or https://.');
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Custom validation: optional date must be valid if provided.
     */
    public function _valid_date($date)
    {
        if (empty($date)) {
            return TRUE;
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d && $d->format('Y-m-d') === $date) {
            return TRUE;
        }

        $this->form_validation->set_message('_valid_date', 'The {field} must be a valid date.');
        return FALSE;
    }

    /**
     * Sanitize URL - trim whitespace and validate.
     */
    private function _sanitize_url($url)
    {
        $url = trim($url);
        if (empty($url)) {
            return NULL;
        }
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : NULL;
    }
}