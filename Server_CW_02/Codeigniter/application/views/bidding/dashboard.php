<?php $page_title = 'Bidding Dashboard'; ?>
<?php $this->load->view('profile/css_header'); ?>

<style>
    .bid-card { background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; border-radius:12px; padding:30px; margin-bottom:20px; }
    .bid-card h2 { margin-bottom:12px; }
    .status-badge { display:inline-block; padding:6px 16px; border-radius:20px; font-weight:700; font-size:14px; text-transform:uppercase; }
    .status-winning { background:#4CAF50; color:#fff; }
    .status-losing { background:#f44336; color:#fff; }
    .status-pending { background:#FF9800; color:#fff; }
    .status-won { background:#4CAF50; color:#fff; }
    .status-lost { background:#9e9e9e; color:#fff; }
    .status-cancelled { background:#757575; color:#fff; }
    .bid-input { display:flex; gap:12px; align-items:end; }
    .bid-input input { padding:12px; border:2px solid rgba(0, 0, 0, 0.3); border-radius:8px; font-size:18px; background:rgba(255,255,255,0.15); color:#fff; width:200px; }
    .bid-input input::placeholder { color:rgba(0, 0, 0, 0.6); }
    .bid-input .btn { padding:12px 24px; background:#fff; color:#667eea; font-weight:700; }
    .bid-info { display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:16px; margin-top:20px; }
    .bid-info-item { background:rgba(255,255,255,0.15); padding:16px; border-radius:8px; text-align:center; }
    .bid-info-item .number { font-size:28px; font-weight:700; }
    .bid-info-item .label { font-size:12px; opacity:0.8; margin-top:4px; }
    .monthly-bar { background:rgba(255,255,255,0.2); border-radius:10px; height:12px; margin-top:8px; overflow:hidden; }
    .monthly-fill { background:#fff; height:100%; border-radius:10px; }
    .closed-banner { background:#ff5252; color:#fff; padding:12px; border-radius:8px; text-align:center; margin-bottom:16px; font-weight:600; }
    .notif-badge { background:#f44336; color:#fff; border-radius:50%; padding:2px 8px; font-size:11px; font-weight:700; }
    .alumni-preview { display:flex; align-items:center; gap:16px; padding:16px; background:#f8f9fa; border-radius:8px; margin-top:16px; }
    .alumni-preview img { width:60px; height:60px; border-radius:50%; object-fit:cover; }
</style>

<div class="container">

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($this->session->flashdata('error')); ?></div>
    <?php endif; ?>

    <!-- Navigation -->
    <div class="section-nav" style="margin-bottom:20px;">
        <a href="<?php echo site_url('profile'); ?>">← Profile</a>
        <a href="<?php echo site_url('bidding'); ?>" class="active">Bidding</a>
        <a href="<?php echo site_url('bidding/history'); ?>">History</a>
        <a href="<?php echo site_url('bidding/notifications'); ?>">
            Notifications
            <?php if ($unread_count > 0): ?>
                <span class="notif-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo site_url('bidding/alumni_of_the_day'); ?>">Alumni of the Day</a>
    </div>

    <!-- Bidding Closed Banner -->
    <?php if (!$is_bidding_open): ?>
        <div class="closed-banner">
            🔒 Bidding is CLOSED for today (closes at 6 PM). Winner will be selected shortly. Try again tomorrow!
        </div>
    <?php endif; ?>

    <!-- Main Bidding Card -->
    <div class="bid-card">
        <h2>🏆 Tomorrow's Alumni of the Day Slot</h2>
        <p style="opacity:0.8;">Bidding for: <strong><?php echo date('l, d F Y', strtotime($tomorrow)); ?></strong></p>

        <!-- Bid Info -->
        <div class="bid-info">
            <div class="bid-info-item">
                <div class="number"><?php echo $bid_count; ?></div>
                <div class="label">Active Bids</div>
            </div>
            <div class="bid-info-item">
                <div class="number"><?php echo $monthly_status['wins']; ?>/<?php echo $monthly_status['limit']; ?></div>
                <div class="label">Monthly Wins Used</div>
                <div class="monthly-bar">
                    <?php $pct = ($monthly_status['limit'] > 0) ? ($monthly_status['wins'] / $monthly_status['limit']) * 100 : 0; ?>
                    <div class="monthly-fill" style="width:<?php echo $pct; ?>%"></div>
                </div>
            </div>
            <div class="bid-info-item">
                <div class="number"><?php echo $monthly_status['remaining']; ?></div>
                <div class="label">Slots Remaining</div>
            </div>
            <div class="bid-info-item">
                <div class="number"><?php echo $monthly_status['has_event_bonus'] ? '✅' : '❌'; ?></div>
                <div class="label">Event Bonus</div>
            </div>
        </div>
    </div>

    <!-- Current Bid Status / Place Bid -->
    <div class="card">
        <div class="card-header">
            <h2>
                <?php if ($bid_status['has_bid']): ?>
                    Your Current Bid
                <?php else: ?>
                    Place Your Bid
                <?php endif; ?>
            </h2>
        </div>
        <div class="card-body">

            <?php if ($bid_status['has_bid']): ?>
                <!-- Show current bid status (BLIND - no other bid amounts shown) -->
                <div style="text-align:center; padding:20px;">
                    <p style="font-size:18px; margin-bottom:12px;">Your bid amount:</p>
                    <p style="font-size:42px; font-weight:700; color:#333;">
                        £<?php echo number_format($bid_status['amount'], 2); ?>
                    </p>
                    <p style="margin:16px 0;">
                        <span class="status-badge status-<?php echo $bid_status['status']; ?>">
                            <?php echo strtoupper($bid_status['status']); ?>
                        </span>
                    </p>

                    <?php if ($bid_status['status'] === 'winning'): ?>
                        <p style="color:#4CAF50; font-weight:600;">🎯 You are currently the highest bidder!</p>
                    <?php elseif ($bid_status['status'] === 'losing'): ?>
                        <p style="color:#f44336; font-weight:600;">⚠️ Someone has outbid you. Increase your bid to compete!</p>
                    <?php endif; ?>

                    <!-- Update Bid Form (Increase Only) -->
                    <?php if ($is_bidding_open && in_array($bid_status['status'], array('winning', 'losing', 'pending'))): ?>
                        <div style="margin-top:24px; padding-top:24px; border-top:1px solid #eee;">
                            <h3 style="margin-bottom:12px;">Increase Your Bid</h3>
                            <p style="color:#888; font-size:13px; margin-bottom:12px;">
                                Minimum new amount: £<?php echo number_format($bid_status['amount'] + 0.01, 2); ?> (bids can only be increased)
                            </p>

                            <?php echo form_open('bidding/update'); ?>
                                <input type="hidden" name="bid_id" value="<?php echo $bid_status['bid_id']; ?>">
                                <div class="bid-input" style="justify-content:center;">
                                    <div class="form-group" style="margin:0;">
                                        <input type="number" name="new_amount" step="0.01"
                                               min="<?php echo $bid_status['amount'] + 0.01; ?>"
                                               placeholder="£ New amount" required
                                               style="color:#333; background:#fff; border-color:#e0e0e0;">
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="color:#fff; background:#2196F3;">
                                        ⬆️ Increase Bid
                                    </button>
                                </div>
                            <?php echo form_close(); ?>

                            <!-- Cancel Bid -->
                            <div style="margin-top:16px;">
                                <?php echo form_open('bidding/cancel', array('style' => 'display:inline;')); ?>
                                    <input type="hidden" name="bid_id" value="<?php echo $bid_status['bid_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to cancel your bid?');">
                                        Cancel Bid
                                    </button>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Place New Bid Form -->
                <?php if ($is_bidding_open && $monthly_status['can_bid']): ?>
                    <div style="text-align:center; padding:20px;">
                        <p style="font-size:16px; color:#666; margin-bottom:20px;">
                            Enter your bid amount for tomorrow's Alumni of the Day slot.<br>
                            <strong>This is blind bidding</strong> — you cannot see other bids.
                        </p>

                        <?php echo form_open('bidding/place'); ?>
                            <div class="bid-input" style="justify-content:center;">
                                <div class="form-group" style="margin:0;">
                                    <label style="text-align:left;">Bid Amount (£)</label>
                                    <input type="number" name="amount" step="0.01" min="0.01"placeholder="£ Enter amount" required 
                                    style="font-size:20px; width:250px; color:#333; background:#fff; border:2px solid #e0e0e0;">
                                </div>
                                <button type="submit" class="btn btn-success" style="padding:14px 30px; font-size:16px;">
                                    🏆 Place Bid
                                </button>
                            </div>
                        <?php echo form_close(); ?>

                        <p style="color:#888; font-size:13px; margin-top:16px;">
                            💡 Tip: You won't see other bid amounts, but you'll know if you're winning or losing.
                        </p>
                    </div>

                <?php elseif (!$monthly_status['can_bid']): ?>
                    <div style="text-align:center; padding:30px;">
                        <p style="font-size:48px;">🏆</p>
                        <h3 style="color:#f44336;">Monthly Limit Reached</h3>
                        <p style="color:#666; margin-top:12px;">
                            <?php echo htmlspecialchars($monthly_status['message']); ?>
                        </p>
                    </div>

                <?php else: ?>
                    <div style="text-align:center; padding:30px;">
                        <p style="font-size:48px;">🔒</p>
                        <h3>Bidding Closed</h3>
                        <p style="color:#666; margin-top:12px;">
                            Bidding closes at 6 PM daily. Come back tomorrow!
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Today's Alumni of the Day Preview -->
    <div class="card">
        <div class="card-header">
            <h2>⭐ Today's Alumni of the Day</h2>
            <a href="<?php echo site_url('bidding/alumni_of_the_day'); ?>" class="btn btn-primary btn-sm">View Full Profile</a>
        </div>
        <div class="card-body">
            <?php if ($alumni_of_day): ?>
                <div class="alumni-preview">
                    <?php if (!empty($alumni_of_day['user']->profile_image)): ?>
                        <img src="<?php echo base_url($alumni_of_day['user']->profile_image); ?>" alt="Alumni">
                    <?php else: ?>
                        <div style="width:60px;height:60px;border-radius:50%;background:#e0e0e0;display:flex;align-items:center;justify-content:center;font-size:24px;">👤</div>
                    <?php endif; ?>
                    <div>
                        <h3><?php echo htmlspecialchars($alumni_of_day['user']->first_name . ' ' . $alumni_of_day['user']->last_name); ?></h3>
                        <p style="color:#666;">
                            <?php if (!empty($alumni_of_day['user']->city)): ?>
                                📍 <?php echo htmlspecialchars($alumni_of_day['user']->city); ?>
                            <?php endif; ?>
                            <?php if (!empty($alumni_of_day['user']->linkedin_url)): ?>
                                • <a href="<?php echo htmlspecialchars($alumni_of_day['user']->linkedin_url); ?>" target="_blank">LinkedIn ↗</a>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <p style="color:#888; text-align:center; padding:20px;">No Alumni of the Day selected yet for today.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- For Testing: Manual Winner Selection -->
<div class="card" style="border:2px dashed #FF9800;">
    <div class="card-header" style="background:#fff3e0;">
        <h2>🧪 Testing Controls</h2>
    </div>
    <div class="card-body" style="text-align:center;">
        <p style="color:#666; margin-bottom:12px;">Select winner for tomorrow.</p>
        
        <a href="<?php echo site_url('bidding/select_winner'); ?>" class="btn btn-primary"
           onclick="return confirm('Select winner for tomorrow?');">
            🏆 Select Winner Now
        </a>
        
        &nbsp;&nbsp;
        
        <!-- <a href="<?php echo site_url('bidding/reset_winner'); ?>" class="btn btn-danger"
           onclick="return confirm('Reset winner? This will undo the selection.');">
            🔄 Reset Winner
        </a> -->
    </div>
</div>

</div>

<?php $this->load->view('profile/footer'); ?>