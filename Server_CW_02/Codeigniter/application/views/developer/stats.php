<?php $page_title = 'API Key Statistics'; ?>
<?php $this->load->view('profile/css_header'); ?>

<style>
    .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:16px; margin-bottom:20px; }
    .stat-item { background:#fff; padding:20px; border-radius:12px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
    .stat-item .number { font-size:28px; font-weight:700; }
    .stat-item .label { font-size:12px; color:#888; margin-top:4px; }
    .log-entry { padding:8px 12px; border-bottom:1px solid #f0f0f0; font-size:13px; display:flex; justify-content:space-between; }
    .method-badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700; color:#fff; }
    .method-GET { background:#4CAF50; }
    .method-POST { background:#2196F3; }
    .method-PUT { background:#FF9800; }
    .method-DELETE { background:#f44336; }
    .status-2xx { color:#4CAF50; }
    .status-4xx { color:#FF9800; }
    .status-5xx { color:#f44336; }
</style>

<div class="container">

    <div class="section-nav">
        <a href="<?php echo site_url('developer'); ?>">← API Keys</a>
        <a href="<?php echo site_url('developer/stats/' . $key->id); ?>" class="active">
            📊 <?php echo htmlspecialchars($key->client_name); ?>
        </a>
    </div>

    <!-- Key Info -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <h3><?php echo htmlspecialchars($key->client_name); ?></h3>
            <p style="color:#888;">API Key: <code><?php echo htmlspecialchars($key->api_key); ?></code></p>
            <p style="color:#888;">Created: <?php echo date('d M Y H:i', strtotime($key->created_at)); ?></p>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="stat-grid">
        <div class="stat-item">
            <div class="number"><?php echo number_format($stats['total_requests']); ?></div>
            <div class="label">Total Requests</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color:#4CAF50;"><?php echo number_format($stats['today']); ?></div>
            <div class="label">Today</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color:#2196F3;"><?php echo number_format($stats['this_week']); ?></div>
            <div class="label">This Week</div>
        </div>
        <div class="stat-item">
            <div class="number"><?php echo $stats['avg_response_time']; ?>ms</div>
            <div class="label">Avg Response</div>
        </div>
    </div>

    <!-- Endpoints Accessed -->
    <div class="card">
        <div class="card-header"><h2>🔗 Endpoints Accessed</h2></div>
        <div class="card-body">
            <?php if (empty($stats['by_endpoint'])): ?>
                <p style="color:#888;text-align:center;padding:20px;">No requests logged yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Method</th><th>Endpoint</th><th>Requests</th></tr></thead>
                    <tbody>
                        <?php foreach ($stats['by_endpoint'] as $ep): ?>
                        <tr>
                            <td><span class="method-badge method-<?php echo $ep->method; ?>"><?php echo $ep->method; ?></span></td>
                            <td style="font-family:monospace;font-size:13px;"><?php echo htmlspecialchars($ep->endpoint); ?></td>
                            <td><strong><?php echo number_format($ep->count); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Response Codes -->
    <div class="card">
        <div class="card-header"><h2>📊 Response Codes</h2></div>
        <div class="card-body">
            <?php if (!empty($stats['by_status'])): ?>
                <div style="display:flex;gap:20px;flex-wrap:wrap;">
                    <?php foreach ($stats['by_status'] as $s): ?>
                        <?php
                        $class = 'status-2xx';
                        if ($s->response_code >= 400) $class = 'status-4xx';
                        if ($s->response_code >= 500) $class = 'status-5xx';
                        ?>
                        <div style="text-align:center;">
                            <div style="font-size:24px;font-weight:700;" class="<?php echo $class; ?>">
                                <?php echo $s->response_code; ?>
                            </div>
                            <div style="font-size:12px;color:#888;"><?php echo number_format($s->count); ?> requests</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:#888;">No data yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Auth Events (Login/Logout timestamps) -->
    <div class="card">
        <div class="card-header"><h2>🔐 Authentication Events</h2></div>
        <div class="card-body">
            <?php if (empty($stats['auth_events'])): ?>
                <p style="color:#888;text-align:center;padding:20px;">No authentication events yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Event</th><th>IP Address</th><th>Details</th><th>Time</th></tr></thead>
                    <tbody>
                        <?php foreach ($stats['auth_events'] as $e): ?>
                        <tr>
                            <td>
                                <?php
                                $badges = array(
                                    'login'         => '✅ Login',
                                    'token_refresh' => '🔄 Refresh',
                                    'token_revoke'  => '🔒 Revoke',
                                    'login_failed'  => '❌ Failed',
                                );
                                echo isset($badges[$e->event_type]) ? $badges[$e->event_type] : $e->event_type;
                                ?>
                            </td>
                            <td style="font-family:monospace;font-size:13px;"><?php echo htmlspecialchars($e->ip_address); ?></td>
                            <td style="font-size:13px;"><?php echo htmlspecialchars($e->details); ?></td>
                            <td style="font-size:13px;color:#888;"><?php echo date('d M H:i:s', strtotime($e->created_at)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Requests Log -->
    <div class="card">
        <div class="card-header"><h2>📜 Recent API Requests</h2></div>
        <div class="card-body" style="max-height:400px;overflow-y:auto;">
            <?php if (empty($stats['recent'])): ?>
                <p style="color:#888;text-align:center;padding:20px;">No requests logged yet.</p>
            <?php else: ?>
                <?php foreach ($stats['recent'] as $r): ?>
                    <div class="log-entry">
                        <div>
                            <span class="method-badge method-<?php echo $r->method; ?>"><?php echo $r->method; ?></span>
                            <span style="font-family:monospace;margin-left:8px;"><?php echo htmlspecialchars($r->endpoint); ?></span>
                        </div>
                        <div>
                            <?php
                            $class = 'status-2xx';
                            if ($r->response_code >= 400) $class = 'status-4xx';
                            if ($r->response_code >= 500) $class = 'status-5xx';
                            ?>
                            <span class="<?php echo $class; ?>" style="font-weight:700;"><?php echo $r->response_code; ?></span>
                            <span style="color:#888;margin-left:8px;"><?php echo date('d M H:i:s', strtotime($r->requested_at)); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php $this->load->view('profile/footer'); ?>