<?php
// Codeigniter/application/helpers/auth_helper.php
// Include this in any controller that needs to be protected

/**
 * Check if user is logged in
 * Redirects to login page if not authenticated
 */
function require_login() {
    $CI =& get_instance();

    if (!$CI->session->userdata('logged_in')) {
        // Store the intended URL to redirect after login
        $CI->session->set_flashdata('error', 'Please log in to access this page.');
        $CI->session->set_userdata('redirect_url', current_url());
        redirect('auth/login');
    }
}

/**
 * Get the JWT token from session (for API calls)
 * @return string|null
 */
function get_jwt_token() {
    $CI =& get_instance();
    return $CI->session->userdata('jwt_token');
}

/**
 * Get current logged in user data
 * @return array|null
 */
function get_logged_in_user() {
    $CI =& get_instance();
    
    if (!$CI->session->userdata('logged_in')) {
        return null;
    }

    return [
        'id'        => $CI->session->userdata('user_id'),
        'full_name' => $CI->session->userdata('full_name'),
        'email'     => $CI->session->userdata('email'),
        'role'      => $CI->session->userdata('role')
    ];
}