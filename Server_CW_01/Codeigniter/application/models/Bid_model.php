<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bid_model - Handles all blind bidding system operations.
 *
 * Key business rules:
 *   - Blind bidding: users never see other bids' amounts
 *   - Users only see if they are "winning" or "losing"
 *   - Bids can only be increased, never decreased
 *   - Each user: max ONE bid per day
 *   - Monthly limit: max 3 wins per calendar month
 *   - Event participation grants 1 extra win (4th) per month
 *   - Winner selected at 6 PM for next day display
 *   - Bids are for TOMORROW's slot
 */
class Bid_model extends CI_Model
{
    /** @var int Maximum wins per month (base limit) */
    private $monthly_limit = 3;

    /** @var int Extra wins allowed with event participation */
    private $event_bonus = 1;

    

    // ================================================================
    // PLACING & UPDATING BIDS
    // ================================================================

    /**
     * Get tomorrow's date (the date being bid on).
     *
     * @return string Y-m-d format
     */
    public function get_tomorrow()
    {
        return date('Y-m-d', strtotime('tomorrow'));
    }

    /**
     * Get today's date.
     *
     * @return string Y-m-d format
     */
    public function get_today()
    {
        return date('Y-m-d');
    }

    /**
     * Check if bidding is currently open.
     * Bidding closes at 6 PM (18:00) each day.
     *
     * @return bool TRUE if bidding is open
     */

    //================================================================================================
    public function is_bidding_open()
    {
        // $current_hour = (int) date('H');
        // // Bidding is open from 00:00 to 17:59 (closes at 18:00/6PM)
        // return $current_hour < 18;
        return TRUE;
    }
//================================================================================================
    /**
 * Check if bidding is currently open.
 * 
 * For TESTING: Always open
 * For PRODUCTION: Change back to $current_hour < 18
 */
    

    /**
     * Get a user's current bid for tomorrow's slot.
     *
     * @param int $user_id
     * @return object|null
     */
    public function get_user_bid_for_tomorrow($user_id)
    {
        $tomorrow = $this->get_tomorrow();

        $query = $this->db->get_where('bids', array(
            'user_id'  => $user_id,
            'bid_date' => $tomorrow,
            'status !=' => 'cancelled',
        ));

        return $query->row();
    }

    /**
     * Get a user's bid by ID (with ownership check).
     *
     * @param int $bid_id
     * @param int $user_id
     * @return object|null
     */
    public function get_bid($bid_id, $user_id)
    {
        $query = $this->db->get_where('bids', array(
            'id'      => $bid_id,
            'user_id' => $user_id,
        ));

        return $query->row();
    }

    /**
     * Place a new bid for tomorrow's slot.
     *
     * Validates:
     *   - Bidding window is open (before 6 PM)
     *   - User hasn't already bid for this date
     *   - User hasn't exceeded monthly win limit
     *   - Amount is positive
     *
     * @param int   $user_id
     * @param float $amount Bid amount in GBP
     * @return array ['success' => bool, 'message' => string, 'bid_id' => int|null]
     */
    /**
 * Place a new bid for tomorrow's slot.
 * If user had a cancelled bid for same date, reactivate it with new amount.
 */
public function place_bid($user_id, $amount)
{
    $tomorrow = $this->get_tomorrow();

    // 1. Check bidding window
    if (!$this->is_bidding_open()) {
        return array(
            'success' => FALSE,
            'message' => 'Bidding has closed for today (closes at 6 PM). Try again tomorrow.',
        );
    }

    // 2. Check if user already has an ACTIVE bid for tomorrow
    $existing_bid = $this->get_user_bid_for_tomorrow($user_id);
    if ($existing_bid) {
        return array(
            'success' => FALSE,
            'message' => 'You already have a bid for tomorrow. You can update (increase) your existing bid.',
        );
    }

    // 3. Check monthly win limit
    $limit_check = $this->check_monthly_limit($user_id);
    if (!$limit_check['can_bid']) {
        return array(
            'success' => FALSE,
            'message' => $limit_check['message'],
        );
    }

    // 4. Validate amount
    $amount = floatval($amount);
    if ($amount <= 0) {
        return array(
            'success' => FALSE,
            'message' => 'Bid amount must be greater than £0.',
        );
    }

    // 5. Determine initial status (winning or losing)
    $highest_bid = $this->get_highest_bid_for_date($tomorrow);
    $status = (!$highest_bid || $amount > $highest_bid->amount) ? 'winning' : 'losing';

    // 6. Check if there's a CANCELLED bid for this date (reactivate it)
    $cancelled_bid = $this->db->get_where('bids', array(
        'user_id'  => $user_id,
        'bid_date' => $tomorrow,
        'status'   => 'cancelled',
    ))->row();

    if ($cancelled_bid) {
        // Reactivate the cancelled bid with new amount
        $this->db->where('id', $cancelled_bid->id);
        $this->db->update('bids', array(
            'amount'     => $amount,
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        $bid_id = $cancelled_bid->id;
    } else {
        // Insert new bid
        $bid_data = array(
            'user_id'    => $user_id,
            'bid_date'   => $tomorrow,
            'amount'     => $amount,
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );

        $this->db->insert('bids', $bid_data);
        $bid_id = $this->db->insert_id();
    }

    // 7. If this is now the highest bid, update other bids to "losing"
    if ($status === 'winning') {
        $this->update_other_bids_status($tomorrow, $bid_id);
    }

    // 8. Create notification
    $this->create_notification(
        $user_id,
        $bid_id,
        "Your bid of £" . number_format($amount, 2) . " for " . date('d M Y', strtotime($tomorrow)) . " has been placed. Status: " . strtoupper($status),
        'bid_placed'
    );

    return array(
        'success' => TRUE,
        'message' => 'Bid placed successfully! You are currently ' . strtoupper($status) . '.',
        'bid_id'  => $bid_id,
        'status'  => $status,
    );
}

    /**
     * Update (increase) an existing bid.
     * Bids can ONLY be increased, never decreased.
     *
     * @param int   $user_id
     * @param int   $bid_id
     * @param float $new_amount New bid amount
     * @return array ['success' => bool, 'message' => string]
     */
    public function update_bid($user_id, $bid_id, $new_amount)
    {
        // 1. Check bidding window
        if (!$this->is_bidding_open()) {
            return array(
                'success' => FALSE,
                'message' => 'Bidding has closed for today (closes at 6 PM).',
            );
        }

        // 2. Get the existing bid
        $bid = $this->get_bid($bid_id, $user_id);

        if (!$bid) {
            return array(
                'success' => FALSE,
                'message' => 'Bid not found.',
            );
        }

        // 3. Check bid is still active (not cancelled, won, or lost)
        if (!in_array($bid->status, array('pending', 'winning', 'losing'))) {
            return array(
                'success' => FALSE,
                'message' => 'This bid can no longer be updated.',
            );
        }

        // 4. Ensure new amount is HIGHER than current bid
        $new_amount = floatval($new_amount);
        if ($new_amount <= $bid->amount) {
            return array(
                'success' => FALSE,
                'message' => 'New bid must be higher than your current bid of £' . number_format($bid->amount, 2) . '. Bids can only be increased.',
            );
        }

        // 5. Determine new status
        $highest_bid = $this->get_highest_bid_for_date($bid->bid_date);
        $is_highest = (!$highest_bid || $new_amount > $highest_bid->amount || $highest_bid->id == $bid_id);
        $new_status = $is_highest ? 'winning' : 'losing';

        // 6. Update the bid
        $this->db->where('id', $bid_id);
        $this->db->where('user_id', $user_id);
        $this->db->update('bids', array(
            'amount'     => $new_amount,
            'status'     => $new_status,
            'updated_at' => date('Y-m-d H:i:s'),
        ));

        // 7. Update other bids' statuses
        if ($new_status === 'winning') {
            $this->update_other_bids_status($bid->bid_date, $bid_id);
        }

        // 8. Notification
        $this->create_notification(
            $user_id,
            $bid_id,
            "Your bid has been increased to £" . number_format($new_amount, 2) . ". Status: " . strtoupper($new_status),
            'bid_updated'
        );

        return array(
            'success' => TRUE,
            'message' => 'Bid updated to £' . number_format($new_amount, 2) . '! You are currently ' . strtoupper($new_status) . '.',
            'status'  => $new_status,
        );
    }

    /**
     * Cancel a bid.
     *
     * @param int $user_id
     * @param int $bid_id
     * @return array
     */
    public function cancel_bid($user_id, $bid_id)
    {
        $bid = $this->get_bid($bid_id, $user_id);

        if (!$bid) {
            return array('success' => FALSE, 'message' => 'Bid not found.');
        }

        if (!in_array($bid->status, array('pending', 'winning', 'losing'))) {
            return array('success' => FALSE, 'message' => 'This bid can no longer be cancelled.');
        }

        if (!$this->is_bidding_open()) {
            return array('success' => FALSE, 'message' => 'Bidding has closed. Cannot cancel.');
        }

        // Cancel the bid
        $this->db->where('id', $bid_id);
        $this->db->where('user_id', $user_id);
        $this->db->update('bids', array(
            'status'     => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ));

        // Recalculate statuses for remaining bids
        $this->recalculate_bid_statuses($bid->bid_date);

        $this->create_notification(
            $user_id, $bid_id,
            "Your bid of £" . number_format($bid->amount, 2) . " has been cancelled.",
            'status_change'
        );

        return array('success' => TRUE, 'message' => 'Bid cancelled successfully.');
    }

    // ================================================================
    // BID STATUS & QUERIES
    // ================================================================

    /**
     * Get the highest active (non-cancelled) bid for a date.
     * This is INTERNAL ONLY — never exposed to users (blind bidding).
     *
     * @param string $date Y-m-d
     * @return object|null
     */
    private function get_highest_bid_for_date($date)
    {
        $query = $this->db
            ->where('bid_date', $date)
            ->where('status !=', 'cancelled')
            ->order_by('amount', 'DESC')
            ->limit(1)
            ->get('bids');

        return $query->row();
    }

    /**
     * Update all other bids for a date to "losing" status.
     * Called when a new highest bid is placed.
     *
     * @param string $date Y-m-d
     * @param int    $winning_bid_id The ID of the winning bid
     */
    private function update_other_bids_status($date, $winning_bid_id)
    {
        // Set all other active bids to "losing"
        $this->db
            ->where('bid_date', $date)
            ->where('id !=', $winning_bid_id)
            ->where_in('status', array('pending', 'winning', 'losing'))
            ->update('bids', array(
                'status'     => 'losing',
                'updated_at' => date('Y-m-d H:i:s'),
            ));

        // Set the winning bid
        $this->db
            ->where('id', $winning_bid_id)
            ->update('bids', array(
                'status'     => 'winning',
                'updated_at' => date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Recalculate all bid statuses for a date.
     * Called after a bid is cancelled.
     *
     * @param string $date
     */
    private function recalculate_bid_statuses($date)
    {
        // Get all active bids sorted by amount DESC
        $query = $this->db
            ->where('bid_date', $date)
            ->where('status !=', 'cancelled')
            ->order_by('amount', 'DESC')
            ->get('bids');

        $bids = $query->result();

        if (empty($bids)) {
            return;
        }

        // First bid is winning, rest are losing
        foreach ($bids as $index => $bid) {
            $new_status = ($index === 0) ? 'winning' : 'losing';

            $this->db->where('id', $bid->id)->update('bids', array(
                'status'     => $new_status,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        }
    }

    /**
     * Public wrapper for recalculate (used by controller for testing).
     */
    public function recalculate_bid_statuses_public($date)
    {
        $this->recalculate_bid_statuses($date);
    }

    /**
     * Get user's bid status for tomorrow (winning/losing).
     * This is the ONLY feedback they receive (blind bidding).
     *
     * @param int $user_id
     * @return array
     */
    public function get_bid_status($user_id)
    {
        $bid = $this->get_user_bid_for_tomorrow($user_id);

        if (!$bid) {
            return array(
                'has_bid' => FALSE,
                'message' => 'You have not placed a bid for tomorrow.',
            );
        }

        return array(
            'has_bid'    => TRUE,
            'bid_id'     => $bid->id,
            'amount'     => $bid->amount,
            'status'     => $bid->status,
            'bid_date'   => $bid->bid_date,
            'message'    => 'Your bid of £' . number_format($bid->amount, 2) . ' is currently ' . strtoupper($bid->status) . '.',
            'created_at' => $bid->created_at,
        );
    }

    // ================================================================
    // MONTHLY LIMIT ENFORCEMENT
    // ================================================================

    /**
     * Get number of times user has won Alumni of the Day this month.
     *
     * @param int    $user_id
     * @param int    $month   Month number (1-12), defaults to current
     * @param int    $year    Year, defaults to current
     * @return int
     */
    public function get_monthly_wins($user_id, $month = NULL, $year = NULL)
    {
        $month = $month ?: (int) date('m');
        $year  = $year  ?: (int) date('Y');

        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date   = date('Y-m-t', strtotime($start_date)); // Last day of month

        $query = $this->db
            ->where('user_id', $user_id)
            ->where('display_date >=', $start_date)
            ->where('display_date <=', $end_date)
            ->get('daily_winners');

        return $query->num_rows();
    }

    /**
     * Check if user has attended a verified event this month.
     * Grants +1 extra allowed win.
     *
     * @param int $user_id
     * @return bool
     */
    public function has_event_bonus($user_id)
    {
        $month = (int) date('m');
        $year  = (int) date('Y');

        $query = $this->db->get_where('event_participation', array(
            'user_id'     => $user_id,
            'event_month' => $month,
            'event_year'  => $year,
            'is_verified' => 1,
        ));

        return $query->num_rows() > 0;
    }

    /**
     * Get the maximum allowed wins this month for a user.
     *
     * @param int $user_id
     * @return int 3 normally, 4 with event participation
     */
    public function get_max_monthly_wins($user_id)
    {
        $limit = $this->monthly_limit; // 3

        if ($this->has_event_bonus($user_id)) {
            $limit += $this->event_bonus; // +1 = 4
        }

        return $limit;
    }

    /**
     * Check if user can still bid (hasn't exceeded monthly limit).
     *
     * @param int $user_id
     * @return array ['can_bid' => bool, 'message' => string, 'wins' => int, 'limit' => int]
     */
    public function check_monthly_limit($user_id)
    {
        $wins  = $this->get_monthly_wins($user_id);
        $limit = $this->get_max_monthly_wins($user_id);
        $remaining = $limit - $wins;

        if ($wins >= $limit) {
            $msg = "You have reached your monthly limit of {$limit} features. ";
            if (!$this->has_event_bonus($user_id)) {
                $msg .= "Attend a university alumni event to earn 1 extra slot.";
            }

            return array(
                'can_bid'   => FALSE,
                'message'   => $msg,
                'wins'      => $wins,
                'limit'     => $limit,
                'remaining' => 0,
            );
        }

        return array(
            'can_bid'   => TRUE,
            'message'   => "You have used {$wins} of {$limit} monthly slots. {$remaining} remaining.",
            'wins'      => $wins,
            'limit'     => $limit,
            'remaining' => $remaining,
        );
    }

    /**
     * Get monthly limit status for display.
     *
     * @param int $user_id
     * @return array
     */
    public function get_monthly_status($user_id)
    {
        $check = $this->check_monthly_limit($user_id);
        $check['has_event_bonus'] = $this->has_event_bonus($user_id);
        $check['month_name'] = date('F Y');

        return $check;
    }

    // ================================================================
    // AUTOMATED WINNER SELECTION (6 PM / Cron)
    // ================================================================

    /**
     * Select the winner for tomorrow's display.
     * Called by cron job at 6 PM daily.
     *
     * Process:
     *   1. Get all active bids for tomorrow
     *   2. Find the highest bid
     *   3. Check winner hasn't exceeded monthly limit
     *   4. Mark winner's bid as "won", others as "lost"
     *   5. Create daily_winners record
     *   6. Send notifications
     *
     * @return array Result of winner selection
     */
    /**
 * Select the winner for tomorrow's display.
 * Called manually or by cron job at 6 PM daily.
 */
public function select_winner()
{
    $tomorrow = $this->get_tomorrow();

    // Check if winner already selected for this date
    $existing_winner = $this->db->get_where('daily_winners', array(
        'display_date' => $tomorrow,
    ))->row();

    if ($existing_winner) {
        return array(
            'success' => FALSE,
            'message' => "Winner already selected for {$tomorrow}. Use 'Reset Winner' to re-select.",
        );
    }

    // Get ALL non-cancelled bids for tomorrow, ordered by amount DESC
    $query = $this->db
        ->where('bid_date', $tomorrow)
        ->where('status !=', 'cancelled')
        ->order_by('amount', 'DESC')
        ->order_by('created_at', 'ASC')
        ->get('bids');

    $bids = $query->result();

    if (empty($bids)) {
        // Debug: Check if there are ANY bids (including cancelled)
        $all_bids = $this->db->where('bid_date', $tomorrow)->get('bids')->result();
        $total = count($all_bids);

        return array(
            'success' => FALSE,
            'message' => "No active bids for {$tomorrow}. ({$total} total bids found, all may be cancelled). Place a bid first!",
        );
    }

    // Find eligible winner (check monthly limits)
    $winner_bid = NULL;
    foreach ($bids as $bid) {
        $limit_check = $this->check_monthly_limit($bid->user_id);
        if ($limit_check['can_bid']) {
            $winner_bid = $bid;
            break;
        }
    }

    if (!$winner_bid) {
        return array(
            'success' => FALSE,
            'message' => "All {$count} bidders have reached their monthly limit.",
        );
    }

    // Mark winning bid as "won"
    $this->db->where('id', $winner_bid->id)->update('bids', array(
        'status'     => 'won',
        'updated_at' => date('Y-m-d H:i:s'),
    ));

    // Mark all other bids as "lost"
    $this->db
        ->where('bid_date', $tomorrow)
        ->where('id !=', $winner_bid->id)
        ->where('status !=', 'cancelled')
        ->update('bids', array(
            'status'     => 'lost',
            'updated_at' => date('Y-m-d H:i:s'),
        ));

    // Deactivate previous day's winner
    $this->db
        ->where('is_active', 1)
        ->update('daily_winners', array('is_active' => 0));

    // Create winner record
    $this->db->insert('daily_winners', array(
        'user_id'        => $winner_bid->user_id,
        'bid_id'         => $winner_bid->id,
        'display_date'   => $tomorrow,
        'winning_amount' => $winner_bid->amount,
        'is_active'      => 1,
        'created_at'     => date('Y-m-d H:i:s'),
    ));

    // Notify winner
    $this->create_notification(
        $winner_bid->user_id,
        $winner_bid->id,
        "🎉 Congratulations! You are Alumni of the Day for " . date('d M Y', strtotime($tomorrow)) . "! Winning bid: £" . number_format($winner_bid->amount, 2),
        'winner'
    );

    // Notify losers
    foreach ($bids as $bid) {
        if ($bid->id !== $winner_bid->id && $bid->status !== 'cancelled') {
            $this->create_notification(
                $bid->user_id,
                $bid->id,
                "Your bid for " . date('d M Y', strtotime($tomorrow)) . " was not successful. Better luck next time!",
                'loser'
            );
        }
    }

    // Get winner name for message
    $winner_user = $this->db->get_where('users', array('id' => $winner_bid->user_id))->row();
    $winner_name = $winner_user ? $winner_user->first_name . ' ' . $winner_user->last_name : 'Unknown';

    return array(
        'success'        => TRUE,
        'message'        => "🎉 Winner selected for {$tomorrow}: {$winner_name} with £" . number_format($winner_bid->amount, 2) . "!",
        'winner_user_id' => $winner_bid->user_id,
        'winning_amount' => $winner_bid->amount,
    );
}

    // ================================================================
    // TODAY'S ALUMNI OF THE DAY
    // ================================================================

    /**
     * Get today's Alumni of the Day.
     * Returns full profile data for display.
     *
     * @return array|null
     */
    //==========================================================================================================================================================
    // public function get_alumni_of_the_day()
    // {
    //     $today = $this->get_today();

    //     // Get today's winner
    //     $winner = $this->db->get_where('daily_winners', array(
    //         'display_date' => $today,
    //         'is_active'    => 1,
    //     ))->row();

    //     if (!$winner) {
    //         return NULL;
    //     }

    //     // Get user info
    //     $this->db->select('u.id, u.first_name, u.last_name, u.email,
    //                        p.biography, p.linkedin_url, p.profile_image, p.city, p.country');
    //     $this->db->from('users u');
    //     $this->db->join('alumni_profiles p', 'p.user_id = u.id', 'left');
    //     $this->db->where('u.id', $winner->user_id);
    //     $user = $this->db->get()->row();

    //     if (!$user) {
    //         return NULL;
    //     }

    //     // Get degrees, certs, etc.
    //     $degrees        = $this->db->get_where('degrees', array('user_id' => $winner->user_id))->result();
    //     $certifications = $this->db->get_where('certifications', array('user_id' => $winner->user_id))->result();
    //     $licences       = $this->db->get_where('licences', array('user_id' => $winner->user_id))->result();
    //     $courses        = $this->db->get_where('professional_courses', array('user_id' => $winner->user_id))->result();
    //     $employment     = $this->db->get_where('employment_history', array('user_id' => $winner->user_id))->result();

    //     return array(
    //         'user'           => $user,
    //         'display_date'   => $winner->display_date,
    //         'degrees'        => $degrees,
    //         'certifications' => $certifications,
    //         'licences'       => $licences,
    //         'courses'        => $courses,
    //         'employment'     => $employment,
    //     );
    // }

    //===========================================================================================================================================================

    public function get_alumni_of_the_day()
{
    // For TESTING: Show the most recent winner regardless of date
    $winner = $this->db
        ->where('is_active', 1)
        ->order_by('display_date', 'DESC')
        ->limit(1)
        ->get('daily_winners')
        ->row();

    // For PRODUCTION: Use this instead
    // $today = $this->get_today();
    // $winner = $this->db->get_where('daily_winners', array(
    //     'display_date' => $today,
    //     'is_active'    => 1,
    // ))->row();

    if (!$winner) {
        return NULL;
    }

    // Get user info
    $this->db->select('u.id, u.first_name, u.last_name, u.email,
                       p.biography, p.linkedin_url, p.profile_image, p.city, p.country');
    $this->db->from('users u');
    $this->db->join('alumni_profiles p', 'p.user_id = u.id', 'left');
    $this->db->where('u.id', $winner->user_id);
    $user = $this->db->get()->row();

    if (!$user) {
        return NULL;
    }

    $degrees        = $this->db->get_where('degrees', array('user_id' => $winner->user_id))->result();
    $certifications = $this->db->get_where('certifications', array('user_id' => $winner->user_id))->result();
    $licences       = $this->db->get_where('licences', array('user_id' => $winner->user_id))->result();
    $courses        = $this->db->get_where('professional_courses', array('user_id' => $winner->user_id))->result();
    $employment     = $this->db->get_where('employment_history', array('user_id' => $winner->user_id))->result();

    return array(
        'user'           => $user,
        'display_date'   => $winner->display_date,
        'degrees'        => $degrees,
        'certifications' => $certifications,
        'licences'       => $licences,
        'courses'        => $courses,
        'employment'     => $employment,
    );
}

    // ================================================================
    // BIDDING HISTORY
    // ================================================================

    /**
     * Get user's bidding history.
     *
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function get_bid_history($user_id, $limit = 20)
    {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('bids');

        return $query->result();
    }

    /**
     * Get all bids for a specific date (admin view or for counting).
     *
     * @param string $date Y-m-d
     * @return int Number of active bids
     */
    public function get_bid_count_for_date($date)
    {
        $query = $this->db
            ->where('bid_date', $date)
            ->where('status !=', 'cancelled')
            ->get('bids');

        return $query->num_rows();
    }

    // ================================================================
    // NOTIFICATIONS
    // ================================================================

    /**
     * Create a notification for a user.
     */
    public function create_notification($user_id, $bid_id, $message, $type)
    {
        $this->db->insert('bid_notifications', array(
            'user_id'    => $user_id,
            'bid_id'     => $bid_id,
            'message'    => $message,
            'type'       => $type,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Get unread notifications for a user.
     */
    public function get_notifications($user_id, $limit = 10)
    {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('bid_notifications');

        return $query->result();
    }

    /**
     * Get unread notification count.
     */
    public function get_unread_count($user_id)
    {
        $query = $this->db->get_where('bid_notifications', array(
            'user_id' => $user_id,
            'is_read' => 0,
        ));

        return $query->num_rows();
    }

    /**
     * Mark all notifications as read.
     */
    public function mark_notifications_read($user_id)
    {
        $this->db
            ->where('user_id', $user_id)
            ->where('is_read', 0)
            ->update('bid_notifications', array('is_read' => 1));
    }

    // ================================================================
    // PAST WINNERS
    // ================================================================

    /**
     * Get list of past winners.
     */
    public function get_past_winners($limit = 30)
    {
        $this->db->select('dw.*, u.first_name, u.last_name, p.profile_image');
        $this->db->from('daily_winners dw');
        $this->db->join('users u', 'u.id = dw.user_id');
        $this->db->join('alumni_profiles p', 'p.user_id = dw.user_id', 'left');
        $this->db->order_by('dw.display_date', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }
}