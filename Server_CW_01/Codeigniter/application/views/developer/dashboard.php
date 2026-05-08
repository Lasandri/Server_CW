<?php $page_title = 'Developer Dashboard'; ?>
<?php $this->load->view('profile/css_header'); ?>

<style>
    .key-card { border-left: 4px solid #4CAF50; }
    .key-card.revoked { border-left-color: #f44336; opacity: 0.7; }
    .key-value { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 4px 8px; border-radius: 4px; font-size: 13px; word-break: break-all; }
    .scope-badge { display: inline-block; background: #e3f2fd; color: #1565C0; padding: 2px 10px; border-radius: 12px; font-size: 12px; margin: 2px; }
    .type-badge { display: inline-block; background: #f3e5f5; color: #7B1FA2; padding: 2px 10px; border-radius: 12px; font-size: 12px; }
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 20px; }
    .stat-item { background: #fff; padding: 20px; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .stat-item .number { font-size: 32px; font-weight: 700; color: #333; }
    .stat-item .label { font-size: 13px; color: #888; margin-top: 4px; }
</style>

<div class="container">

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($this->session->flashdata('error')); ?></div>
    <?php endif; ?>

    <!-- Navigation -->
    <div class="section-nav">
        <a href="<?php echo site_url('profile'); ?>">Profile</a>
        <a href="<?php echo site_url('bidding'); ?>">Bidding</a>
        <a href="<?php echo site_url('developer'); ?>" class="active">🔑 Developer</a>
    </div>

    <!-- Overview Stats -->
    <div class="stat-grid">
        <div class="stat-item">
            <div class="number"><?php echo $stats['total_keys']; ?></div>
            <div class="label">Total Keys</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color:#4CAF50;"><?php echo $stats['active_keys']; ?></div>
            <div class="label">Active Keys</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color:#f44336;"><?php echo $stats['revoked_keys']; ?></div>
            <div class="label">Revoked Keys</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color:#2196F3;"><?php echo number_format($stats['total_requests']); ?></div>
            <div class="label">Total Requests</div>
        </div>
    </div>

    <!-- Generate New Key Button -->
    <div style="text-align: right; margin-bottom: 16px;">
        <a href="<?php echo site_url('developer/create'); ?>" class="btn btn-success">
            🔑 Generate New API Key
        </a>
    </div>

    <!-- API Keys List -->
    <?php if (empty($stats['keys'])): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 40px;">
                <p style="font-size: 48px;">🔑</p>
                <h3>No API Keys Yet</h3>
                <p style="color: #888; margin-top: 8px;">Generate your first API key to start accessing the Alumni API.</p>
                <a href="<?php echo site_url('developer/create'); ?>" class="btn btn-success" style="margin-top: 16px;">Generate API Key</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($stats['keys'] as $key): ?>
            <div class="card key-card <?php echo $key->is_revoked ? 'revoked' : ''; ?>" style="margin-bottom: 16px;">
                <div class="card-header">
                    <div>
                        <h2 style="font-size: 16px;">
                            <?php echo htmlspecialchars($key->client_name); ?>
                            <span class="type-badge"><?php echo strtoupper($key->client_type); ?></span>
                            <?php if ($key->is_revoked): ?>
                                <span style="background:#f44336;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">REVOKED</span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div>
                        <a href="<?php echo site_url('developer/stats/' . $key->id); ?>" class="btn btn-primary btn-sm">📊 Stats</a>
                    </div>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <strong>API Key:</strong><br>
                            <span class="key-value"><?php echo htmlspecialchars($key->api_key); ?></span>
                        </div>
                        <div>
                            <strong>Created:</strong><br>
                            <?php echo date('d M Y H:i', strtotime($key->created_at)); ?>
                        </div>
                        <div>
                            <strong>Total Requests:</strong><br>
                            <?php echo number_format($key->total_requests); ?>
                        </div>
                        <div>
                            <strong>Last Used:</strong><br>
                            <?php echo $key->last_used_at ? date('d M Y H:i', strtotime($key->last_used_at)) : 'Never'; ?>
                        </div>
                        <div>
                            <strong>Rate Limit:</strong><br>
                            <?php echo $key->rate_limit; ?> requests / hour
                        </div>
                        <div>
                            <strong>Scopes:</strong><br>
                            <?php foreach (explode(',', $key->scopes) as $scope): ?>
                                <span class="scope-badge"><?php echo htmlspecialchars(trim($scope)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($key->is_revoked): ?>
                        <div style="margin-top: 16px; padding: 12px; background: #ffebee; border-radius: 8px;">
                            <strong>Revoked:</strong> <?php echo date('d M Y H:i', strtotime($key->revoked_at)); ?>
                            <?php if ($key->revoked_reason): ?>
                                <br><strong>Reason:</strong> <?php echo htmlspecialchars($key->revoked_reason); ?>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 12px;">
                            <a href="<?php echo site_url('developer/reactivate/' . $key->id); ?>" 
                               class="btn btn-success btn-sm"
                               onclick="return confirm('Reactivate this API key?');">✅ Reactivate</a>
                            <a href="<?php echo site_url('developer/delete/' . $key->id); ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Permanently delete this key? This cannot be undone.');">🗑️ Delete</a>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 16px; display: flex; gap: 8px;">
                            <!-- Revoke Bearer Token -->
                            <?php echo form_open('developer/revoke_token', array('style' => 'display:inline;')); ?>
                                <input type="hidden" name="key_id" value="<?php echo $key->id; ?>">
                                <button type="submit" class="btn btn-secondary btn-sm"
                                        onclick="return confirm('Revoke bearer token? Client must re-authenticate.');">
                                    🔒 Revoke Token
                                </button>
                            <?php echo form_close(); ?>

                            <!-- Revoke Entire Key -->
                            <?php echo form_open('developer/revoke', array('style' => 'display:inline;')); ?>
                                <input type="hidden" name="key_id" value="<?php echo $key->id; ?>">
                                <input type="text" name="reason" placeholder="Reason (optional)" 
                                       style="padding:6px;border:1px solid #ccc;border-radius:4px;font-size:13px;width:200px;">
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Revoke this API key? It will stop working immediately.');">
                                    ❌ Revoke Key
                                </button>
                            <?php echo form_close(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php $this->load->view('profile/footer'); ?>