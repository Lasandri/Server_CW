<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth_lib - Session authentication check
 * Same as CW1 but for CW2 dashboard
 */
class Auth_lib
{
    private $CI;
    private $session_timeout = 1800; // 30 minutes

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('session');
    }

    /**
     * Require login - redirect if not authenticated
     */
    public function require_login()
    {
        if (!$this->CI->session->userdata('logged_in')) {
            $this->CI->session->set_flashdata(
                'error',
                'Please log in to access this page.'
            );
            redirect('login');
        }

        // Session timeout check
        $last_activity = $this->CI->session->userdata('last_activity');
        if ($last_activity &&
            (time() - $last_activity > $this->session_timeout)) {
            $this->CI->session->sess_destroy();
            redirect('login');
        }

        // Update last activity
        $this->CI->session->set_userdata('last_activity', time());
    }

    public function is_logged_in()
    {
        return (bool) $this->CI->session->userdata('logged_in');
    }

    public function get_user_id()
    {
        return $this->CI->session->userdata('user_id');
    }
}