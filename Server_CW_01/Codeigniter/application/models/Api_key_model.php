<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Api_key_model - Manages API keys, bearer tokens, and usage tracking.
 *
 * Flow:
 *   1. Developer registers and creates an API key (api_key + api_secret)
 *   2. Client authenticates with api_key + api_secret → receives bearer_token
 *   3. Client uses bearer_token in Authorization header for all API requests
 *   4. Each request is logged in api_usage_logs
 *   5. Developer can view usage stats and revoke keys
 *
 * Security:
 *   - api_key: 32-char hex (public identifier)
 *   - api_secret: bcrypt hashed (never stored in plain text after creation)
 *   - bearer_token: 64-char hex, expires in 24 hours
 *   - All tokens are cryptographically random (random_bytes)
 */
class Api_key_model extends CI_Model
{
    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->load->database();
    // }

    // ================================================================
    // API KEY MANAGEMENT
    // ================================================================

    /**
     * Generate a new API key pair (key + secret).
     *
     * @param int    $user_id
     * @param string $client_name
     * @param string $client_description
     * @param string $client_type
     * @param string $scopes Comma-separated scopes
     * @return array ['api_key' => string, 'api_secret' => string] (secret shown ONCE)
     */
    public function create_api_key($user_id, $client_name, $client_description = '', $client_type = 'web', $scopes = 'read')
    {
        // Generate cryptographically secure key and secret
        $api_key    = bin2hex(random_bytes(16));   // 32-char hex
        $api_secret = bin2hex(random_bytes(32));    // 64-char hex (shown once)

        // Hash the secret for storage (like a password)
        $secret_hash = password_hash($api_secret, PASSWORD_BCRYPT, array('cost' => 12));

        $data = array(
            'user_id'            => $user_id,
            'api_key'            => $api_key,
            'api_secret'         => $secret_hash,
            'client_name'        => $client_name,
            'client_description' => $client_description,
            'client_type'        => $client_type,
            'scopes'             => $scopes,
            'is_active'          => 1,
            'is_revoked'         => 0,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        );

        $this->db->insert('api_keys', $data);

        return array(
            'id'         => $this->db->insert_id(),
            'api_key'    => $api_key,
            'api_secret' => $api_secret,  // Only shown once!
        );
    }

    /**
     * Get all API keys for a user.
     *
     * @param int $user_id
     * @return array
     */
    public function get_user_keys($user_id)
    {
        $query = $this->db
            ->select('id, api_key, client_name, client_description, client_type, scopes, 
                      is_active, is_revoked, revoked_at, revoked_reason,
                      total_requests, last_used_at, rate_limit, created_at')
            ->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->get('api_keys');

        return $query->result();
    }

    /**
     * Get a single API key by ID (with ownership check).
     *
     * @param int $id
     * @param int $user_id
     * @return object|null
     */
    public function get_key($id, $user_id)
    {
        $query = $this->db
            ->select('id, api_key, client_name, client_description, client_type, scopes, 
                      is_active, is_revoked, revoked_at, revoked_reason,
                      total_requests, last_used_at, rate_limit, rate_limit_window, created_at')
            ->get_where('api_keys', array(
                'id'      => $id,
                'user_id' => $user_id,
            ));

        return $query->row();
    }

    /**
     * Get API key by the key string.
     *
     * @param string $api_key
     * @return object|null
     */
    public function get_by_api_key($api_key)
    {
        return $this->db->get_where('api_keys', array('api_key' => $api_key))->row();
    }

    /**
     * Get API key by bearer token.
     *
     * @param string $bearer_token
     * @return object|null Active, non-expired token
     */
    public function get_by_bearer_token($bearer_token)
    {
        $hashed = hash('sha256', $bearer_token);

        $query = $this->db
            ->where('bearer_token', $hashed)
            ->where('bearer_token_expires >', date('Y-m-d H:i:s'))
            ->where('is_active', 1)
            ->where('is_revoked', 0)
            ->get('api_keys');

        return $query->row();
    }

    // ================================================================
    // BEARER TOKEN AUTHENTICATION
    // ================================================================

    /**
     * Authenticate client with api_key + api_secret.
     * Returns a bearer token valid for 24 hours.
     *
     * @param string $api_key
     * @param string $api_secret
     * @param string $ip_address
     * @param string $user_agent
     * @return array
     */
    public function authenticate($api_key, $api_secret, $ip_address = '', $user_agent = '')
    {
        // Find the API key
        $key_record = $this->get_by_api_key($api_key);

        if (!$key_record) {
            $this->log_auth_event_by_key($api_key, 'login_failed', $ip_address, $user_agent, 'Invalid API key');
            return array(
                'success' => FALSE,
                'message' => 'Invalid API key.',
            );
        }

        // Check if key is active and not revoked
        if (!$key_record->is_active || $key_record->is_revoked) {
            $this->log_auth_event($key_record->id, 'login_failed', $ip_address, $user_agent, 'Key is revoked or inactive');
            return array(
                'success' => FALSE,
                'message' => 'API key has been revoked or deactivated.',
            );
        }

        // Verify the secret
        if (!password_verify($api_secret, $key_record->api_secret)) {
            $this->log_auth_event($key_record->id, 'login_failed', $ip_address, $user_agent, 'Invalid secret');
            return array(
                'success' => FALSE,
                'message' => 'Invalid API secret.',
            );
        }

        // Generate bearer token (24 hour expiry)
        $raw_token   = bin2hex(random_bytes(32));  // 64-char hex
        $hashed_token = hash('sha256', $raw_token);
        $expires_at   = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Save hashed token to DB
        $this->db->where('id', $key_record->id);
        $this->db->update('api_keys', array(
            'bearer_token'         => $hashed_token,
            'bearer_token_expires' => $expires_at,
            'last_used_at'         => date('Y-m-d H:i:s'),
            'updated_at'           => date('Y-m-d H:i:s'),
        ));

        // Log the authentication
        $this->log_auth_event($key_record->id, 'login', $ip_address, $user_agent, 'Token generated');

        return array(
            'success'      => TRUE,
            'message'      => 'Authentication successful.',
            'bearer_token' => $raw_token,  // Raw token returned to client
            'token_type'   => 'Bearer',
            'expires_in'   => 86400,  // 24 hours in seconds
            'expires_at'   => $expires_at,
            'scopes'       => $key_record->scopes,
            'client_name'  => $key_record->client_name,
        );
    }

    /**
     * Validate a bearer token from request header.
     * Also logs the API usage.
     *
     * @param string $bearer_token
     * @param string $endpoint
     * @param string $method
     * @param string $ip_address
     * @param string $user_agent
     * @return array
     */
    public function validate_bearer_token($bearer_token, $endpoint = '', $method = 'GET', $ip_address = '', $user_agent = '')
    {
        $key_record = $this->get_by_bearer_token($bearer_token);

        if (!$key_record) {
            return array(
                'valid'   => FALSE,
                'message' => 'Invalid or expired bearer token.',
            );
        }

        // Check rate limiting
        $rate_check = $this->check_rate_limit($key_record);
        if (!$rate_check['allowed']) {
            $this->log_usage($key_record->id, $endpoint, $method, $ip_address, $user_agent, 429, 0);
            return array(
                'valid'   => FALSE,
                'message' => 'Rate limit exceeded. Try again later.',
                'retry_after' => $rate_check['retry_after'],
            );
        }

        // Increment total requests
        $this->db->where('id', $key_record->id);
        $this->db->set('total_requests', 'total_requests + 1', FALSE);
        $this->db->set('last_used_at', date('Y-m-d H:i:s'));
        $this->db->update('api_keys');

        // Log usage
        $this->log_usage($key_record->id, $endpoint, $method, $ip_address, $user_agent, 200, 0);

        return array(
            'valid'       => TRUE,
            'api_key_id'  => $key_record->id,
            'user_id'     => $key_record->user_id,
            'client_name' => $key_record->client_name,
            'scopes'      => explode(',', $key_record->scopes),
        );
    }

    // ================================================================
    // REVOKE TOKENS
    // ================================================================

    /**
     * Revoke an API key (permanently disable).
     *
     * @param int    $id
     * @param int    $user_id
     * @param string $reason
     * @return bool
     */
    public function revoke_key($id, $user_id, $reason = '')
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('api_keys', array(
            'is_active'      => 0,
            'is_revoked'     => 1,
            'revoked_at'     => date('Y-m-d H:i:s'),
            'revoked_reason' => $reason,
            'bearer_token'   => NULL,
            'bearer_token_expires' => NULL,
            'updated_at'     => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Revoke just the bearer token (client must re-authenticate).
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function revoke_bearer_token($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('api_keys', array(
            'bearer_token'         => NULL,
            'bearer_token_expires' => NULL,
            'updated_at'           => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Reactivate a revoked key.
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function reactivate_key($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('api_keys', array(
            'is_active'      => 1,
            'is_revoked'     => 0,
            'revoked_at'     => NULL,
            'revoked_reason' => NULL,
            'updated_at'     => date('Y-m-d H:i:s'),
        ));
    }

    // ================================================================
    // RATE LIMITING
    // ================================================================

    /**
     * Check if API key has exceeded its rate limit.
     *
     * @param object $key_record
     * @return array
     */
    private function check_rate_limit($key_record)
    {
        $window_start = date('Y-m-d H:i:s', time() - $key_record->rate_limit_window);

        $query = $this->db
            ->where('api_key_id', $key_record->id)
            ->where('requested_at >', $window_start)
            ->get('api_usage_logs');

        $request_count = $query->num_rows();

        if ($request_count >= $key_record->rate_limit) {
            return array(
                'allowed'     => FALSE,
                'retry_after' => $key_record->rate_limit_window,
            );
        }

        return array(
            'allowed'   => TRUE,
            'remaining' => $key_record->rate_limit - $request_count,
        );
    }

    // ================================================================
    // USAGE LOGGING
    // ================================================================

    /**
     * Log an API request.
     */
    public function log_usage($api_key_id, $endpoint, $method, $ip_address, $user_agent, $response_code, $response_time_ms)
    {
        $this->db->insert('api_usage_logs', array(
            'api_key_id'       => $api_key_id,
            'endpoint'         => $endpoint,
            'method'           => $method,
            'ip_address'       => $ip_address,
            'user_agent'       => $user_agent,
            'response_code'    => $response_code,
            'response_time_ms' => $response_time_ms,
            'requested_at'     => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Log an authentication event.
     */
    public function log_auth_event($api_key_id, $event_type, $ip_address, $user_agent, $details = '')
    {
        $this->db->insert('api_key_auth_logs', array(
            'api_key_id' => $api_key_id,
            'event_type' => $event_type,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'details'    => $details,
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Log auth by key string (for failed attempts where ID is unknown).
     */
    private function log_auth_event_by_key($api_key, $event_type, $ip_address, $user_agent, $details)
    {
        $record = $this->get_by_api_key($api_key);
        if ($record) {
            $this->log_auth_event($record->id, $event_type, $ip_address, $user_agent, $details);
        }
    }

    // ================================================================
    // USAGE STATISTICS
    // ================================================================

    /**
     * Get usage statistics for an API key.
     *
     * @param int $api_key_id
     * @return array
     */
    public function get_usage_stats($api_key_id)
    {
        $stats = array();

        // Total requests
        $stats['total_requests'] = $this->db
            ->where('api_key_id', $api_key_id)
            ->count_all_results('api_usage_logs');

        // Requests today
        $stats['today'] = $this->db
            ->where('api_key_id', $api_key_id)
            ->where('requested_at >=', date('Y-m-d 00:00:00'))
            ->count_all_results('api_usage_logs');

        // Requests this week
        $stats['this_week'] = $this->db
            ->where('api_key_id', $api_key_id)
            ->where('requested_at >=', date('Y-m-d 00:00:00', strtotime('-7 days')))
            ->count_all_results('api_usage_logs');

        // Requests by endpoint
        $stats['by_endpoint'] = $this->db
            ->select('endpoint, method, COUNT(*) as count')
            ->where('api_key_id', $api_key_id)
            ->group_by('endpoint, method')
            ->order_by('count', 'DESC')
            ->limit(20)
            ->get('api_usage_logs')
            ->result();

        // Requests by hour (last 24 hours)
        $stats['by_hour'] = $this->db
            ->select('HOUR(requested_at) as hour, COUNT(*) as count')
            ->where('api_key_id', $api_key_id)
            ->where('requested_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->group_by('HOUR(requested_at)')
            ->order_by('hour', 'ASC')
            ->get('api_usage_logs')
            ->result();

        // Response code distribution
        $stats['by_status'] = $this->db
            ->select('response_code, COUNT(*) as count')
            ->where('api_key_id', $api_key_id)
            ->group_by('response_code')
            ->order_by('count', 'DESC')
            ->get('api_usage_logs')
            ->result();

        // Recent requests (last 50)
        $stats['recent'] = $this->db
            ->where('api_key_id', $api_key_id)
            ->order_by('requested_at', 'DESC')
            ->limit(50)
            ->get('api_usage_logs')
            ->result();

        // Auth events
        $stats['auth_events'] = $this->db
            ->where('api_key_id', $api_key_id)
            ->order_by('created_at', 'DESC')
            ->limit(20)
            ->get('api_key_auth_logs')
            ->result();

        // Average response time
        $avg = $this->db
            ->select_avg('response_time_ms', 'avg_time')
            ->where('api_key_id', $api_key_id)
            ->where('response_time_ms >', 0)
            ->get('api_usage_logs')
            ->row();
        $stats['avg_response_time'] = $avg ? round($avg->avg_time, 2) : 0;

        return $stats;
    }

    /**
     * Get all usage stats across all keys for a user.
     */
    public function get_user_stats($user_id)
    {
        $keys = $this->get_user_keys($user_id);

        $total_requests = 0;
        $active_keys = 0;
        $revoked_keys = 0;

        foreach ($keys as $key) {
            $total_requests += $key->total_requests;
            if ($key->is_active && !$key->is_revoked) $active_keys++;
            if ($key->is_revoked) $revoked_keys++;
        }

        return array(
            'total_keys'     => count($keys),
            'active_keys'    => $active_keys,
            'revoked_keys'   => $revoked_keys,
            'total_requests' => $total_requests,
            'keys'           => $keys,
        );
    }

    /**
     * Delete a key entirely.
     */
    public function delete_key($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->delete('api_keys');
    }
}