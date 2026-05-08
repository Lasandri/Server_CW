<?php
// Codeigniter/application/views/dashboard/security.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security & API Keys - Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary:#0f3460; --secondary:#e94560; --sidebar-w:260px; --bg:#f0f2f5; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:var(--bg); display:flex; min-height:100vh; }

        /* Sidebar */
        .sidebar { width:var(--sidebar-w); background:linear-gradient(180deg,#0f3460,#16213e); display:flex; flex-direction:column; position:fixed; top:0; left:0; height:100vh; z-index:100; }
        .sidebar-brand { display:flex; align-items:center; padding:25px 20px; border-bottom:1px solid rgba(255,255,255,0.1); gap:12px; }
        .brand-icon { width:42px; height:42px; background:#e94560; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .brand-icon i { color:white; font-size:18px; }
        .brand-title { color:white; font-size:18px; font-weight:700; display:block; }
        .brand-subtitle { color:rgba(255,255,255,0.6); font-size:12px; display:block; }
        .sidebar-nav { list-style:none; padding:15px 0; flex:1; }
        .nav-link { display:flex; align-items:center; gap:12px; padding:13px 20px; color:rgba(255,255,255,0.7); text-decoration:none; transition:all 0.2s; border-left:3px solid transparent; font-size:14px; }
        .nav-link:hover { color:white; background:rgba(255,255,255,0.08); border-left-color:rgba(255,255,255,0.3); }
        .nav-link.active { color:white; background:rgba(233,69,96,0.2); border-left-color:#e94560; }
        .nav-link i { width:20px; }
        .sidebar-footer { padding:15px 20px; border-top:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:10px; }
        .user-avatar { width:36px; height:36px; background:#e94560; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; flex-shrink:0; }
        .user-details { flex:1; min-width:0; }
        .user-name { color:white; font-size:13px; font-weight:600; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-role { color:rgba(255,255,255,0.5); font-size:11px; }
        .logout-btn { color:rgba(255,255,255,0.6); text-decoration:none; padding:6px; border-radius:6px; }
        .logout-btn:hover { color:#e94560; }

        /* Main */
        .main-content { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
        .topbar { background:white; padding:0 30px; height:65px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 10px rgba(0,0,0,0.08); position:sticky; top:0; z-index:99; }
        .topbar-title { font-size:20px; font-weight:700; color:var(--primary); }
        .page-body { padding:30px; flex:1; }

        /* Cards */
        .card-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }

        .card-box-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f2f5;
        }

        /* Stat boxes */
        .stat-mini {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            text-align: center;
        }

        .stat-mini .val  { font-size:28px; font-weight:800; color:var(--primary); }
        .stat-mini .lbl  { font-size:12px; color:#6c757d; margin-top:3px; }

        /* API Key table */
        .key-table { width:100%; border-collapse:collapse; font-size:13px; }
        .key-table th { background:#f8f9fa; padding:12px 15px; text-align:left; font-weight:700; color:#555; border-bottom:2px solid #eee; }
        .key-table td { padding:12px 15px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
        .key-table tr:hover td { background:#fafafa; }

        /* Permission badges */
        .perm-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin: 2px;
        }

        .perm-alumni    { background:#cfe2ff; color:#084298; }
        .perm-analytics { background:#d1e7dd; color:#0f5132; }
        .perm-bidding   { background:#fff3cd; color:#664d03; }
        .perm-ar        { background:#f8d7da; color:#842029; }
        .perm-admin     { background:#e2d9f3; color:#432874; }

        /* Client type badges */
        .client-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .client-dashboard { background:#e8f4fd; color:#0f3460; }
        .client-mobile    { background:#fde8f4; color:#920f60; }
        .client-admin     { background:#e8fde8; color:#0f600f; }

        /* Status dot */
        .status-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .dot-active   { background: #198754; }
        .dot-inactive { background: #dc3545; }

        /* Logs table */
        .log-table { width:100%; border-collapse:collapse; font-size:12px; }
        .log-table th { background:#f8f9fa; padding:10px 12px; text-align:left; font-weight:700; color:#555; border-bottom:2px solid #eee; }
        .log-table td { padding:10px 12px; border-bottom:1px solid #f5f5f5; }

        /* Status color */
        .status-200 { color:#198754; font-weight:700; }
        .status-403 { color:#dc3545; font-weight:700; }
        .status-401 { color:#fd7e14; font-weight:700; }

        /* Action buttons */
        .btn-sm-action {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-toggle-on  { background:#d1e7dd; color:#0f5132; }
        .btn-toggle-off { background:#f8d7da; color:#842029; }
        .btn-toggle-on:hover  { background:#badbcc; }
        .btn-toggle-off:hover { background:#f5c2c7; }

        /* Scope diagram */
        .scope-diagram {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .scope-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            background: white;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .scope-client {
            min-width: 160px;
            font-weight: 700;
            font-size: 13px;
        }

        .scope-arrow { color: #aaa; font-size: 18px; }

        .scope-can { color: #198754; font-size: 12px; }
        .scope-cannot { 
            color: #dc3545; 
            font-size: 12px; 
            text-decoration: line-through; 
            opacity: 0.7;
        }

        /* Tabs */
        .nav-tabs .nav-link { color:#6c757d; font-size:14px; font-weight:600; }
        .nav-tabs .nav-link.active { color:var(--primary); border-color:#dee2e6 #dee2e6 white; }

        /* Form */
        .form-label { font-weight:600; font-size:13px; color:var(--primary); }
        .form-control, .form-select { font-size:13px; border-radius:8px; }
        .btn-create { background:linear-gradient(135deg,var(--primary),var(--secondary)); border:none; color:white; border-radius:8px; padding:10px 25px; font-weight:600; }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php $this->load->view('dashboard/sidebar'); ?>

<div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">
            <i class="fas fa-shield-alt me-2 text-danger"></i>Security & API Keys
        </div>
        <div style="font-size:12px;color:#aaa;">
            <i class="fas fa-clock me-1"></i>Last updated: <?php echo date('H:i:s'); ?>
        </div>
    </div>

    <div class="page-body">

        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-times-circle me-2"></i>
                <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Row -->
        <?php
        $totals = isset($stats['totals']) ? $stats['totals'] : array();
        ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-mini">
                    <div class="val" style="color:#0f3460;">
                        <?php echo isset($totals['total_requests']) ? number_format($totals['total_requests']) : 0; ?>
                    </div>
                    <div class="lbl">Total API Requests</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <div class="val" style="color:#198754;">
                        <?php echo isset($totals['successful']) ? number_format($totals['successful']) : 0; ?>
                    </div>
                    <div class="lbl">Successful (200)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <div class="val" style="color:#dc3545;">
                        <?php echo isset($totals['forbidden']) ? number_format($totals['forbidden']) : 0; ?>
                    </div>
                    <div class="lbl">Forbidden (403)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <div class="val" style="color:#fd7e14;">
                        <?php echo isset($totals['unauthorized']) ? number_format($totals['unauthorized']) : 0; ?>
                    </div>
                    <div class="lbl">Unauthorized (401)</div>
                </div>
            </div>
        </div>

        <!-- API Key Scope Diagram -->
        <div class="card-box">
            <h6 class="card-box-title">
                <i class="fas fa-project-diagram me-2"></i>API Key Permission Scoping
            </h6>

            <div class="scope-diagram">
                <!-- Analytics Dashboard -->
                <div class="scope-row">
                    <div class="scope-client">
                        <span class="client-badge client-dashboard">
                            <i class="fas fa-chart-bar me-1"></i>Analytics Dashboard
                        </span>
                    </div>
                    <div class="scope-arrow">→</div>
                    <div class="flex-grow-1">
                        <div class="mb-1">
                            <strong style="font-size:12px;color:#555;">CAN ACCESS:</strong>
                            <span class="perm-badge perm-alumni">read:alumni</span>
                            <span class="perm-badge perm-analytics">read:analytics</span>
                            <span class="perm-badge perm-bidding">read:bidding</span>
                        </div>
                        <div>
                            <strong style="font-size:12px;color:#555;">CANNOT ACCESS:</strong>
                            <span class="perm-badge perm-ar" style="text-decoration:line-through;opacity:0.6;">read:alumni_of_day</span>
                            <small style="color:#dc3545;font-size:11px;">(AR app endpoints blocked)</small>
                        </div>
                    </div>
                </div>

                <!-- Mobile AR App -->
                <div class="scope-row">
                    <div class="scope-client">
                        <span class="client-badge client-mobile">
                            <i class="fas fa-mobile-alt me-1"></i>Mobile AR App
                        </span>
                    </div>
                    <div class="scope-arrow">→</div>
                    <div class="flex-grow-1">
                        <div class="mb-1">
                            <strong style="font-size:12px;color:#555;">CAN ACCESS:</strong>
                            <span class="perm-badge perm-ar">read:alumni_of_day</span>
                        </div>
                        <div>
                            <strong style="font-size:12px;color:#555;">CANNOT ACCESS:</strong>
                            <span class="perm-badge perm-analytics" style="text-decoration:line-through;opacity:0.6;">read:analytics</span>
                            <span class="perm-badge perm-alumni" style="text-decoration:line-through;opacity:0.6;">read:alumni</span>
                            <small style="color:#dc3545;font-size:11px;">(Dashboard endpoints blocked)</small>
                        </div>
                    </div>
                </div>

                <!-- Admin -->
                <div class="scope-row">
                    <div class="scope-client">
                        <span class="client-badge client-admin">
                            <i class="fas fa-user-shield me-1"></i>Admin
                        </span>
                    </div>
                    <div class="scope-arrow">→</div>
                    <div class="flex-grow-1">
                        <div>
                            <strong style="font-size:12px;color:#555;">CAN ACCESS:</strong>
                            <span class="perm-badge perm-alumni">read:alumni</span>
                            <span class="perm-badge perm-analytics">read:analytics</span>
                            <span class="perm-badge perm-bidding">read:bidding</span>
                            <span class="perm-badge perm-ar">read:alumni_of_day</span>
                            <span class="perm-badge perm-admin">write:admin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-0" id="secTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tabKeys">
                    <i class="fas fa-key me-1"></i>API Keys
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tabUsage">
                    <i class="fas fa-chart-line me-1"></i>Usage Stats
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tabAccess">
                    <i class="fas fa-list me-1"></i>Access Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tabLogin">
                    <i class="fas fa-sign-in-alt me-1"></i>Login Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tabCreate">
                    <i class="fas fa-plus me-1"></i>Create Key
                </a>
            </li>
        </ul>

        <div class="tab-content"
             style="background:white;border:1px solid #dee2e6;border-top:none;
                    border-radius:0 0 15px 15px;padding:25px;">

            <!-- Tab 1: API Keys -->
            <div class="tab-pane fade show active" id="tabKeys">
                <h6 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                    Active API Keys
                </h6>
                <div style="overflow-x:auto;">
                    <table class="key-table">
                        <thead>
                            <tr>
                                <th>Key Name</th>
                                <th>Client Type</th>
                                <th>API Key (Preview)</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th>Last Used</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($keys)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;color:#aaa;padding:30px;">
                                        No API keys found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($keys as $key): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($key['key_name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            $ct = $key['client_type'];
                                            $cls = array(
                                                'analytics_dashboard' => 'client-dashboard',
                                                'mobile_ar'           => 'client-mobile',
                                                'admin'               => 'client-admin'
                                            );
                                            $labels = array(
                                                'analytics_dashboard' => 'Analytics Dashboard',
                                                'mobile_ar'           => 'Mobile AR App',
                                                'admin'               => 'Admin'
                                            );
                                            ?>
                                            <span class="client-badge <?php echo $cls[$ct] ?? ''; ?>">
                                                <?php echo $labels[$ct] ?? $ct; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code style="font-size:11px;background:#f8f9fa;padding:3px 6px;border-radius:4px;">
                                                <?php echo htmlspecialchars($key['api_key_preview']); ?>
                                            </code>
                                        </td>
                                        <td>
                                            <?php
                                            $perms = is_array($key['permissions'])
                                                ? $key['permissions']
                                                : json_decode($key['permissions'], true);
                                            $permClasses = array(
                                                'read:alumni'        => 'perm-alumni',
                                                'read:analytics'     => 'perm-analytics',
                                                'read:bidding'       => 'perm-bidding',
                                                'read:alumni_of_day' => 'perm-ar',
                                                'write:admin'        => 'perm-admin'
                                            );
                                            foreach ($perms as $perm):
                                            ?>
                                                <span class="perm-badge <?php echo $permClasses[$perm] ?? ''; ?>">
                                                    <?php echo htmlspecialchars($perm); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <span class="status-dot <?php echo $key['is_active'] ? 'dot-active' : 'dot-inactive'; ?>"></span>
                                            <?php echo $key['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($key['usage_count']); ?></strong>
                                            <small style="color:#aaa;"> requests</small>
                                        </td>
                                        <td style="font-size:11px;color:#6c757d;">
                                            <?php echo $key['last_used_at']
                                                ? date('d M Y H:i', strtotime($key['last_used_at']))
                                                : 'Never'; ?>
                                        </td>
                                        <td>
                                            <?php echo form_open('security/toggle_key'); ?>
                                                <input type="hidden" name="key_id"
                                                       value="<?php echo $key['id']; ?>">
                                                <button type="submit"
                                                        class="btn-sm-action <?php echo $key['is_active'] ? 'btn-toggle-off' : 'btn-toggle-on'; ?>">
                                                    <?php echo $key['is_active'] ? 'Disable' : 'Enable'; ?>
                                                </button>
                                            <?php echo form_close(); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Usage Stats -->
            <div class="tab-pane fade" id="tabUsage">
                <div class="row g-4">
                    <!-- Per Key Stats -->
                    <div class="col-md-7">
                        <h6 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                            Usage Per API Key
                        </h6>
                        <?php
                        $keyStats = isset($stats['key_stats']) ? $stats['key_stats'] : array();
                        foreach ($keyStats as $ks):
                        ?>
                            <div style="background:#f8f9fa;border-radius:10px;padding:15px;margin-bottom:12px;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong style="font-size:13px;"><?php echo htmlspecialchars($ks['key_name']); ?></strong>
                                    <span style="font-size:12px;color:#6c757d;">
                                        Success rate: <strong style="color:#198754;"><?php echo $ks['success_rate']; ?></strong>
                                    </span>
                                </div>
                                <div class="d-flex gap-4" style="font-size:12px;">
                                    <span><i class="fas fa-check-circle text-success me-1"></i>
                                        <?php echo $ks['success_count'] ?? 0; ?> OK</span>
                                    <span><i class="fas fa-ban text-danger me-1"></i>
                                        <?php echo $ks['forbidden_count'] ?? 0; ?> Forbidden</span>
                                    <span><i class="fas fa-lock text-warning me-1"></i>
                                        <?php echo $ks['unauthorized_count'] ?? 0; ?> Unauthorized</span>
                                    <span><i class="fas fa-database text-primary me-1"></i>
                                        <?php echo $ks['usage_count']; ?> Total</span>
                                </div>
                                <!-- Usage Bar -->
                                <div style="background:#dee2e6;border-radius:5px;height:6px;margin-top:10px;overflow:hidden;">
                                    <?php
                                    $maxUsage = max(array_column($keyStats, 'usage_count'));
                                    $pct = $maxUsage > 0 ? ($ks['usage_count'] / $maxUsage) * 100 : 0;
                                    ?>
                                    <div style="width:<?php echo $pct; ?>%;height:100%;
                                                background:linear-gradient(90deg,#0f3460,#e94560);
                                                border-radius:5px;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Top Endpoints -->
                    <div class="col-md-5">
                        <h6 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                            Top Accessed Endpoints
                        </h6>
                        <?php
                        $endpoints = isset($stats['top_endpoints']) ? $stats['top_endpoints'] : array();
                        foreach ($endpoints as $ep):
                        ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;
                                        padding:8px 12px;background:#f8f9fa;border-radius:8px;margin-bottom:6px;">
                                <div>
                                    <span style="background:#dee2e6;padding:2px 6px;border-radius:4px;
                                                 font-size:10px;font-weight:700;margin-right:6px;">
                                        <?php echo htmlspecialchars($ep['method']); ?>
                                    </span>
                                    <code style="font-size:11px;"><?php echo htmlspecialchars($ep['endpoint']); ?></code>
                                </div>
                                <strong style="font-size:13px;color:var(--primary);">
                                    <?php echo number_format($ep['hits']); ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Charts -->
                <div class="mt-4">
                    <h6 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                        Hourly Activity (Last 24 Hours)
                    </h6>
                    <canvas id="hourlyChart" height="80"></canvas>
                </div>
            </div>

            <!-- Tab 3: Access Logs -->
            <div class="tab-pane fade" id="tabAccess">
                <h6 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                    Recent API Access Logs
                    <span style="font-size:12px;color:#aaa;font-weight:400;">(Last 20 requests)</span>
                </h6>
                <div style="overflow-x:auto;">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Client</th>
                                <th>Method</th>
                                <th>Endpoint</th>
                                <th>Status</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($access_logs)): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;color:#aaa;padding:30px;">
                                        No access logs yet
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($access_logs as $log): ?>
                                    <tr>
                                        <td style="white-space:nowrap;color:#6c757d;">
                                            <?php echo date('d M H:i:s', strtotime($log['accessed_at'])); ?>
                                        </td>
                                        <td>
                                            <span class="client-badge
                                                <?php
                                                $ct = $log['client_type'] ?? '';
                                                if ($ct === 'analytics_dashboard') echo 'client-dashboard';
                                                elseif ($ct === 'mobile_ar') echo 'client-mobile';
                                                else echo 'client-admin';
                                                ?>">
                                                <?php echo htmlspecialchars($log['key_name'] ?? 'Unknown'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="background:#dee2e6;padding:2px 6px;
                                                         border-radius:4px;font-size:10px;font-weight:700;">
                                                <?php echo htmlspecialchars($log['method']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code style="font-size:11px;">
                                                <?php echo htmlspecialchars($log['endpoint']); ?>
                                            </code>
                                        </td>
                                        <td>
                                            <span class="status-<?php echo $log['response_status']; ?>">
                                                <?php echo $log['response_status']; ?>
                                            </span>
                                        </td>
                                        <td style="font-size:11px;color:#6c757d;">
                                            <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 4: Login Logs -->
            <div class="tab-pane fade" id="tabLogin">
                <h6 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                    User Login History
                </h6>
                <div style="overflow-x:auto;">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Action</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($login_logs)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#aaa;padding:30px;">
                                        No login logs yet
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($login_logs as $log): ?>
                                    <tr>
                                        <td style="white-space:nowrap;color:#6c757d;">
                                            <?php echo date('d M Y H:i', strtotime($log['login_at'])); ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($log['full_name'] ?? 'Unknown'); ?></strong>
                                        </td>
                                        <td style="color:#6c757d;">
                                            <?php echo htmlspecialchars($log['email'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $action = $log['action'] ?? 'login';
                                            $actionColors = array(
                                                'login'          => '#198754',
                                                'logout'         => '#6c757d',
                                                'password_reset' => '#fd7e14'
                                            );
                                            $color = $actionColors[$action] ?? '#555';
                                            ?>
                                            <span style="color:<?php echo $color; ?>;font-weight:600;font-size:12px;">
                                                <i class="fas fa-<?php
                                                echo $action === 'login' ? 'sign-in-alt' :
                                                    ($action === 'logout' ? 'sign-out-alt' : 'key');
                                                ?> me-1"></i>
                                                <?php echo ucfirst(str_replace('_', ' ', $action)); ?>
                                            </span>
                                        </td>
                                        <td style="font-size:11px;color:#6c757d;">
                                            <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 5: Create Key -->
            <div class="tab-pane fade" id="tabCreate">
                <div class="row justify-content-center">
                    <div class="col-md-7">
                        <h6 style="font-weight:700;color:var(--primary);margin-bottom:20px;">
                            Create New Scoped API Key
                        </h6>

                        <?php echo form_open('security/create_key'); ?>

                            <!-- Key Name -->
                            <div class="mb-3">
                                <label class="form-label">Key Name</label>
                                <input type="text" name="key_name"
                                       class="form-control"
                                       placeholder="e.g. Mobile AR App v2"
                                       required>
                            </div>

                            <!-- Client Type -->
                            <div class="mb-3">
                                <label class="form-label">Client Type</label>
                                <select name="client_type" class="form-select"
                                        onchange="updatePermissions(this.value)" required>
                                    <option value="">-- Select Client Type --</option>
                                    <option value="analytics_dashboard">Analytics Dashboard</option>
                                    <option value="mobile_ar">Mobile AR App</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <!-- Permissions -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Permissions
                                    <small style="color:#aaa;">(auto-filled based on client type)</small>
                                </label>
                                <input type="text" name="permissions"
                                       class="form-control"
                                       id="permissionsInput"
                                       placeholder="Select client type above"
                                       readonly>
                                <small class="text-muted">Comma-separated permission scopes</small>
                            </div>

                            <!-- Permission Preview -->
                            <div id="permPreview" class="mb-3"
                                 style="background:#f8f9fa;border-radius:8px;padding:15px;display:none;">
                                <strong style="font-size:13px;color:var(--primary);">Permissions:</strong>
                                <div id="permBadges" class="mt-2"></div>
                                <div class="mt-2" id="restrictionNote"></div>
                            </div>

                            <button type="submit" class="btn-create">
                                <i class="fas fa-plus me-2"></i>Generate API Key
                            </button>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

        </div><!-- end tab-content -->

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Hourly Activity Chart ─────────────────────────────────────────────────
const hourlyData = <?php
    $hourly = isset($stats['hourly_stats']) ? $stats['hourly_stats'] : array();
    // Fill all 24 hours
    $hours = array_fill(0, 24, 0);
    foreach ($hourly as $h) {
        $hours[$h['hour']] = (int)$h['requests'];
    }
    echo json_encode($hours);
?>;

new Chart(document.getElementById('hourlyChart'), {
    type: 'bar',
    data: {
        labels: Array.from({length:24}, (_,i) => i + ':00'),
        datasets: [{
            label: 'API Requests',
            data: hourlyData,
            backgroundColor: 'rgba(15,52,96,0.7)',
            borderColor: '#0f3460',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// ── Auto-fill permissions based on client type ────────────────────────────
const permMap = {
    analytics_dashboard: 'read:alumni,read:analytics,read:bidding',
    mobile_ar:           'read:alumni_of_day',
    admin:               'read:alumni,read:analytics,read:bidding,read:alumni_of_day,write:admin'
};

const restrictionMap = {
    analytics_dashboard: 'Cannot access: read:alumni_of_day (AR App endpoints)',
    mobile_ar:           'Cannot access: read:analytics, read:bidding (Dashboard endpoints)',
    admin:               'No restrictions - full access'
};

const permClasses = {
    'read:alumni':        'perm-alumni',
    'read:analytics':     'perm-analytics',
    'read:bidding':       'perm-bidding',
    'read:alumni_of_day': 'perm-ar',
    'write:admin':        'perm-admin'
};

function updatePermissions(clientType) {
    const input   = document.getElementById('permissionsInput');
    const preview = document.getElementById('permPreview');
    const badges  = document.getElementById('permBadges');
    const note    = document.getElementById('restrictionNote');

    if (!clientType) {
        input.value = '';
        preview.style.display = 'none';
        return;
    }

    const perms = permMap[clientType] || '';
    input.value = perms;
    preview.style.display = 'block';

    badges.innerHTML = perms.split(',').map(p =>
        `<span class="perm-badge ${permClasses[p.trim()] || ''}">${p.trim()}</span>`
    ).join('');

    const restriction = restrictionMap[clientType];
    note.innerHTML = `<small style="color:#dc3545;font-size:12px;">
        <i class="fas fa-ban me-1"></i>${restriction}
    </small>`;
}
</script>
</body>
</html>