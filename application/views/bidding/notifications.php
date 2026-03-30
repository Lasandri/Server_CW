<?php $page_title = 'Notifications'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">

    <div class="section-nav">
        <a href="<?php echo site_url('bidding'); ?>">← Bidding</a>
        <a href="<?php echo site_url('bidding/notifications'); ?>" class="active">Notifications</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>🔔 Notifications</h2>
        </div>
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <p style="color:#888; text-align:center; padding:30px;">No notifications yet.</p>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div style="padding:12px 16px; border-bottom:1px solid #f0f0f0; 
                                <?php echo !$n->is_read ? 'background:#f0f7ff;' : ''; ?>">
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <div>
                                <?php
                                $icons = array(
                                    'bid_placed'    => '📝',
                                    'bid_updated'   => '⬆️',
                                    'status_change' => '🔄',
                                    'winner'        => '🎉',
                                    'loser'         => '😔',
                                );
                                echo isset($icons[$n->type]) ? $icons[$n->type] : '📢';
                                ?>
                                <span style="margin-left:8px;"><?php echo htmlspecialchars($n->message); ?></span>
                            </div>
                            <span style="color:#888; font-size:12px; white-space:nowrap; margin-left:16px;">
                                <?php echo date('d M H:i', strtotime($n->created_at)); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php $this->load->view('profile/footer'); ?>