<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Api_client - Calls the CW2 Node.js API (port 4000)
 *
 * Handles:
 *   - Authentication with API key + secret
 *   - Bearer token management (stored in session)
 *   - All analytics API calls
 *   - Alumni data calls
 */
class Api_client
{
    private $CI;

    /** @var string CW2 Node API base URL */
    private $api_base = 'http://localhost:4000/api';

    /** @var string API Key from .env or config */
    private $api_key;

    /** @var string API Secret */
    private $api_secret;

    /** @var string|null Current bearer token */
    private $bearer_token = NULL;

    public function __construct()
    {
        $this->CI =& get_instance();

        // Load API credentials from config
        // These are set in application/config/api_credentials.php
        $this->api_key    = defined('CW2_API_KEY')
            ? CW2_API_KEY
            : 'your_api_key_here';
        $this->api_secret = defined('CW2_API_SECRET')
            ? CW2_API_SECRET
            : 'your_api_secret_here';

        // Get stored token from session
        $this->bearer_token = $this->CI->session->userdata('api_bearer_token');
    }

    // ================================================================
    // AUTHENTICATION
    // ================================================================

    /**
     * Authenticate with CW2 API to get bearer token
     * Token stored in session for reuse
     *
     * @return bool
     */
    public function authenticate()
    {
        $response = $this->post('/client/authenticate', array(
            'api_key'    => $this->api_key,
            'api_secret' => $this->api_secret,
        ), FALSE); // Don't use bearer for this call

        if ($response && $response['status'] === 'success') {
            $this->bearer_token = $response['data']['bearer_token'];

            // Store in session
            $this->CI->session->set_userdata(
                'api_bearer_token',
                $this->bearer_token
            );

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Ensure we have a valid bearer token
     * Re-authenticate if needed
     */
    private function ensure_authenticated()
    {
        if (empty($this->bearer_token)) {
            $this->authenticate();
        }
    }

    // ================================================================
    // ANALYTICS ENDPOINTS
    // ================================================================

    /**
     * Get dashboard overview stats
     */
    public function get_overview()
    {
        $this->ensure_authenticated();
        return $this->get('/analytics/overview');
    }

    /**
     * Get employment analytics
     * @param array $filters
     */
    public function get_employment($filters = array())
    {
        $this->ensure_authenticated();
        $query = !empty($filters) ? '?' . http_build_query($filters) : '';
        return $this->get('/analytics/employment' . $query);
    }

    /**
     * Get skills gap analysis
     */
    public function get_skills_gap($filters = array())
    {
        $this->ensure_authenticated();
        $query = !empty($filters) ? '?' . http_build_query($filters) : '';
        return $this->get('/analytics/skills-gap' . $query);
    }

    /**
     * Get job titles
     */
    public function get_job_titles($limit = 15)
    {
        $this->ensure_authenticated();
        return $this->get('/analytics/job-titles?limit=' . $limit);
    }

    /**
     * Get top employers
     */
    public function get_employers($limit = 10)
    {
        $this->ensure_authenticated();
        return $this->get('/analytics/employers?limit=' . $limit);
    }

    /**
     * Get geographic distribution
     */
    public function get_geographic()
    {
        $this->ensure_authenticated();
        return $this->get('/analytics/geographic');
    }

    /**
     * Get certification trends
     */
    public function get_certification_trends()
    {
        $this->ensure_authenticated();
        return $this->get('/analytics/certification-trends');
    }

    // ================================================================
    // ALUMNI ENDPOINTS
    // ================================================================

    /**
     * Get all alumni with filters
     *
     * @param array $filters programme, graduation_year, industry, country
     * @param int   $page
     * @param int   $limit
     */
    public function get_alumni($filters = array(), $page = 1, $limit = 20)
    {
        $this->ensure_authenticated();

        $params = array_merge($filters, array(
            'page'  => $page,
            'limit' => $limit,
        ));

        return $this->get('/alumni?' . http_build_query($params));
    }

    /**
     * Get single alumni by ID
     *
     * @param int $id
     */
    public function get_alumni_by_id($id)
    {
        $this->ensure_authenticated();
        return $this->get('/alumni/' . intval($id));
    }

    // ================================================================
    // HTTP HELPERS
    // ================================================================

    /**
     * Make GET request to API
     *
     * @param string $endpoint
     * @return array|null
     */
    private function get($endpoint)
    {
        $url = $this->api_base . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $this->bearer_token,
                'Content-Type: application/json',
                'Accept: application/json',
            ),
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 401) {
            // Token expired - re-authenticate and retry
            $this->bearer_token = NULL;
            $this->CI->session->unset_userdata('api_bearer_token');

            if ($this->authenticate()) {
                return $this->get($endpoint);
            }
            return NULL;
        }

        return $response ? json_decode($response, TRUE) : NULL;
    }

    /**
     * Make POST request to API
     *
     * @param string $endpoint
     * @param array  $data
     * @param bool   $use_bearer
     * @return array|null
     */
    private function post($endpoint, $data = array(), $use_bearer = TRUE)
    {
        $url = $this->api_base . $endpoint;

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );

        if ($use_bearer && $this->bearer_token) {
            $headers[] = 'Authorization: Bearer ' . $this->bearer_token;
        }

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ? json_decode($response, TRUE) : NULL;
    }
}