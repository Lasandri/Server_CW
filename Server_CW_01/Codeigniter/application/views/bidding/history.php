<?php $page_title = 'Bidding History'; ?>
<?php $this->load->view('profile/css_header'); ?>

<style>
    .status-badge { display:inline-block; padding:4px 12px; border-radius:12px; font-weight:600; font-size:12px; text-transform:uppercase; }
    .status-winning { background:#e8f5e9; color:#2e7d32; }
    .status-losing { background:#ffebee; color:#c62828; }
    .status-won { background:#4CAF50; color:#fff; }
    .status-lost { background:#9e9e9e; color:#fff; }
    .status-cancelled { background:#f5f5f5; color:#757575; }
    .status-pending { background:#fff3e0; color:#e65100; }
</style>

<div class="container">

    <div class="section-nav">
        <a href="<?php echo site_url('bidding'); ?>">← Bidding</a>
        <a href="<?php echo site_url('bidding/history'); ?>" class="active">History</a>
        <a href="<?php echo site_url('bidding/notifications'); ?>">Notifications</a>
    </div>

    <!-- Monthly Status -->
    <div class="card">
        <div class="card-header">
            <h2>📊 Monthly Status — <?php echo $monthly_status['month_name']; ?></h2>
        </div>
        <div class="card-body">
            <div style="display:flex; gap:30px; flex-wrap:wrap;">
                <div>
                    <strong>Wins Used:</strong> <?php echo $monthly_status['wins']; ?> / <?php echo $monthly_status['limit']; ?>
                </div>
                <div>
                    <strong>Remaining:</strong> <?php echo $monthly_status['remaining']; ?>
                </div>
                <div>
                    <strong>Event Bonus:</strong>
                    <?php echo $monthly_status['has_event_bonus'] ? '✅ Active (+1 slot)' : '❌ Not earned'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- My Bid History -->
    <div class="card">
        <div class="card-header">
            <h2>📜 Your Bidding History</h2>
        </div>
        <div class="card-body">
            <?php if (empty($bids)): ?>
                <p style="color:#888; text-align:center; padding:20px;">No bids placed yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bid Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Placed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bids as $b): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($b->bid_date)); ?></td>
                            <td><strong>£<?php echo number_format($b->amount, 2); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $b->status; ?>">
                                    <?php echo strtoupper($b->status); ?>
                                </span>
                            </td>
                            <td style="color:#888; font-size:13px;"><?php echo date('d M Y H:i', strtotime($b->created_at)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Past Winners -->
    <div class="card">
        <div class="card-header">
            <h2>🏆 Recent Alumni of the Day Winners</h2>
        </div>
        <div class="card-body">
            <?php if (empty($past_winners)): ?>
                <p style="color:#888; text-align:center; padding:20px;">No winners yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Date</th><th>Alumni</th><th>Winning Bid</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past_winners as $w): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($w->display_date)); ?></td>
                            <td>
                                <?php echo htmlspecialchars($w->first_name . ' ' . $w->last_name); ?>
                            </td>
                            <td>£<?php echo number_format($w->winning_amount, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php $this->load->view('profile/footer'); ?>