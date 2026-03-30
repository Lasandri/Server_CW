<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth_lib - Authentication check library.
 *
 * CI3 doesn't have route filters like CI4.
 * Instead, call $this->auth_lib->require_login() in controller constructors
 * for any protected pages.
 *
 * Also handles session timeout (30 min inactivity).
 *
 * Usage in any protected controller:
 *   $this->load->library('auth_lib');
 *   $this->auth_lib->require_login();
 */
class Auth_lib
{
    /** @var CI_Controller CodeIgniter instance */
    private $CI;

    /** @var int Session timeout in seconds (30 minutes) */
    private $session_timeout = 1800;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('session');
    }

    /**
     * Require the user to be logged in.
     * Redirects to login page if not authenticated or session expired.
     * Call this in the constructor of any protected controller.
     */
    public function require_login()
    {
        // Check if user is logged in
        if (!$this->CI->session->userdata('logged_in')) {
            $this->CI->session->set_flashdata('error', 'Please log in to access this page.');
            redirect('login');
        }

        // Check session timeout (30 min inactivity)
        $last_activity = $this->CI->session->userdata('last_activity');

        if ($last_activity && (time() - $last_activity > $this->session_timeout)) {
            $this->CI->session->sess_destroy();
            // Need to start a new session to set flashdata
            $this->CI->load->library('session');
            $this->CI->session->set_flashdata('error', 'Your session has expired. Please log in again.');
            redirect('login');
        }

        // Update last activity timestamp
        $this->CI->session->set_userdata('last_activity', time());
    }

    /**
     * Check if user is currently logged in (without redirect).
     *
     * @return bool
     */
    public function is_logged_in()
    {
        return (bool) $this->CI->session->userdata('logged_in');
    }

    /**
     * Get current logged-in user ID.
     *
     * @return int|null
     */
    public function get_user_id()
    {
        return $this->CI->session->userdata('user_id');
    }
}