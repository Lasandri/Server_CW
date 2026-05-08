<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bidding Controller - Manages the blind bidding system.
 *
 * All methods require authentication.
 *
 * Routes:
 *   GET  /bidding              - Dashboard (view tomorrow's slot, place bid)
 *   POST /bidding/place        - Place a new bid
 *   POST /bidding/update       - Update (increase) existing bid
 *   POST /bidding/cancel       - Cancel a bid
 *   GET  /bidding/history      - View bidding history
 *   GET  /bidding/notifications - View notifications
 *   GET  /bidding/alumni-of-the-day - View today's featured alumni
 *   GET  /bidding/select-winner - Manual winner selection (cron alternative)
 */
class Bidding extends CI_Controller
{
    private $user_id;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Bid_model');
        $this->load->model('Profile_model');
        $this->load->library(array('session', 'form_validation', 'auth_lib'));
        $this->load->helper(array('url', 'form', 'security'));

        // Require login
        $this->auth_lib->require_login();
        $this->user_id = $this->session->userdata('user_id');
    }

    // ================================================================
    // BIDDING DASHBOARD
    // ================================================================

    /**
     * Main bidding page - view slot info, place/update bid.
     * GET /bidding
     */
    public function index()
    {
        // Get user's current bid for tomorrow
        $data['bid_status']     = $this->Bid_model->get_bid_status($this->user_id);
        $data['monthly_status'] = $this->Bid_model->get_monthly_status($this->user_id);
        $data['is_bidding_open']= $this->Bid_model->is_bidding_open();
        $data['tomorrow']       = $this->Bid_model->get_tomorrow();
        $data['bid_count']      = $this->Bid_model->get_bid_count_for_date($data['tomorrow']);

        // Get notifications
        $data['notifications']  = $this->Bid_model->get_notifications($this->user_id, 5);
        $data['unread_count']   = $this->Bid_model->get_unread_count($this->user_id);

        // Get today's alumni of the day
        $data['alumni_of_day']  = $this->Bid_model->get_alumni_of_the_day();

        $data['first_name']     = $this->session->userdata('first_name');

        $this->load->view('bidding/dashboard', $data);
    }

    // ================================================================
    // PLACE BID
    // ================================================================

    /**
     * Place a new bid.
     * POST /bidding/place
     */
    public function place()
    {
        $this->form_validation->set_rules('amount', 'Bid Amount', 'required|numeric|greater_than[0]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', 'Please enter a valid bid amount greater than £0.');
            redirect('bidding');
            return;
        }

        $amount = floatval($this->input->post('amount'));

        // Place the bid
        $result = $this->Bid_model->place_bid($this->user_id, $amount);

        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('bidding');
    }

    // ================================================================
    // UPDATE BID (INCREASE ONLY)
    // ================================================================

    /**
     * Update an existing bid (increase only).
     * POST /bidding/update
     */
    public function update()
    {
        $this->form_validation->set_rules('bid_id', 'Bid ID', 'required|integer');
        $this->form_validation->set_rules('new_amount', 'New Amount', 'required|numeric|greater_than[0]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', 'Please enter a valid bid amount.');
            redirect('bidding');
            return;
        }

        $bid_id     = intval($this->input->post('bid_id'));
        $new_amount = floatval($this->input->post('new_amount'));

        $result = $this->Bid_model->update_bid($this->user_id, $bid_id, $new_amount);

        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('bidding');
    }

    // ================================================================
    // CANCEL BID
    // ================================================================

    /**
     * Cancel a bid.
     * POST /bidding/cancel
     */
    public function cancel()
    {
        $bid_id = intval($this->input->post('bid_id'));

        $result = $this->Bid_model->cancel_bid($this->user_id, $bid_id);

        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('bidding');
    }

    // ================================================================
    // BIDDING HISTORY
    // ================================================================

    /**
     * View past bids.
     * GET /bidding/history
     */
    public function history()
    {
        $data['bids']           = $this->Bid_model->get_bid_history($this->user_id);
        $data['monthly_status'] = $this->Bid_model->get_monthly_status($this->user_id);
        $data['past_winners']   = $this->Bid_model->get_past_winners(10);

        $this->load->view('bidding/history', $data);
    }

    // ================================================================
    // NOTIFICATIONS
    // ================================================================

    /**
     * View and mark notifications as read.
     * GET /bidding/notifications
     */
    public function notifications()
    {
        $data['notifications'] = $this->Bid_model->get_notifications($this->user_id, 50);

        // Mark all as read
        $this->Bid_model->mark_notifications_read($this->user_id);

        $this->load->view('bidding/notifications', $data);
    }

    // ================================================================
    // ALUMNI OF THE DAY
    // ================================================================

    /**
     * View today's featured alumni.
     * GET /bidding/alumni-of-the-day
     */
    public function alumni_of_the_day()
    {
        $data['alumni'] = $this->Bid_model->get_alumni_of_the_day();
        $this->load->view('bidding/alumni_of_day', $data);
    }

    // ================================================================
    // WINNER SELECTION (Manual Trigger / Cron)
    // ================================================================

    /**
     * Manually trigger winner selection.
     * GET /bidding/select-winner
     *
     * In production, this would be called by a cron job at 6 PM.
     * For development/testing, you can visit this URL manually.
     */
    public function select_winner()
    {
        $result = $this->Bid_model->select_winner();

        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('bidding');
    }

    /**
     * Reset winner for testing — removes tomorrow's winner so you can re-select.
     * DELETE THIS BEFORE SUBMISSION.
     * GET /bidding/reset-winner
     */
    public function reset_winner()
    {
        $tomorrow = $this->Bid_model->get_tomorrow();

        // Delete winner record for tomorrow
        $this->db->where('display_date', $tomorrow)->delete('daily_winners');

        // Reset all bids for tomorrow back to pending
        $this->db
            ->where('bid_date', $tomorrow)
            ->where_in('status', array('won', 'lost'))
            ->update('bids', array(
                'status'     => 'pending',
                'updated_at' => date('Y-m-d H:i:s'),
            ));

        // Recalculate who's winning/losing
        $this->Bid_model->recalculate_bid_statuses_public($tomorrow);

        $this->session->set_flashdata('success', 'Winner reset for ' . $tomorrow . '. You can now select again.');
        redirect('bidding');
    }

    
}