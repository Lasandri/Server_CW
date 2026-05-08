<?php
// Codeigniter/application/models/Auth_model.php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth_model
 * Handles communication with the Node.js Auth API
 * Uses cURL to make HTTP requests to the API
 */
class Auth_model extends CI_Model {

    private $api_base_url;

    public function __construct() {
    
    // Load API base URL from config
    $this->api_base_url = $this->config->item('api_base_url');
}

    /**
     * Make a POST request to the Node.js API
     * @param string $endpoint - API endpoint path
     * @param array $data - POST data to send
     * @param string $token - Optional JWT token for authenticated requests
     * @return array - decoded JSON response
     */
    private function api_post($endpoint, $data, $token = null) {
        $url = $this->api_base_url . $endpoint;
        
        $headers = ['Content-Type: application/json'];
        
        // Add Authorization header if token provided
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false  // set true in production
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'cURL Error: ' . $error);
            return ['success' => false, 'message' => 'API connection failed.', 'status' => 0];
        }

        $decoded = json_decode($response, true);
        $decoded['http_status'] = $httpCode;
        return $decoded;
    }

    /**
     * Make a GET request to the Node.js API
     */
    private function api_get($endpoint, $token = null) {
        $url = $this->api_base_url . $endpoint;
        
        $headers = ['Content-Type: application/json'];
        
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        $decoded['http_status'] = $httpCode;
        return $decoded;
    }

    /**
     * Register a new user via the API
     */
    public function register($data) {
        return $this->api_post('/auth/register', $data);
    }

    /**
     * Verify email via the API using token
     */
    public function verify_email($token) {
        $url = $this->api_base_url . '/auth/verify-email/' . $token;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        $decoded['http_status'] = $httpCode;
        return $decoded;
    }

    /**
     * Login user via the API
     */
    public function login($email, $password) {
        return $this->api_post('/auth/login', [
            'email'    => $email,
            'password' => $password
        ]);
    }

    /**
     * Logout user via the API (logs the action)
     */
    public function logout($token) {
        return $this->api_post('/auth/logout', [], $token);
    }

    /**
     * Request password reset email
     */
    public function forgot_password($email) {
        return $this->api_post('/auth/forgot-password', ['email' => $email]);
    }

    /**
     * Submit new password with reset token
     */
    public function reset_password($token, $password, $confirm_password) {
        $url = $this->api_base_url . '/auth/reset-password/' . $token;
        
        $data = [
            'password'         => $password,
            'confirm_password' => $confirm_password
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        $decoded['http_status'] = $httpCode;
        return $decoded;
    }

    /**
     * Resend verification email
     */
    public function resend_verification($email) {
        return $this->api_post('/auth/resend-verification', ['email' => $email]);
    }
}