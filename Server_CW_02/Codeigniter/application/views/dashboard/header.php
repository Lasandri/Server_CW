<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?> - University Analytics</title>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
               background:#f0f2f5; display:flex; min-height:100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width:260px; background:linear-gradient(180deg,#1565C0,#0D47A1);
            color:#fff; min-height:100vh; padding:0; position:fixed;
            left:0; top:0; z-index:100;
        }
        .sidebar-brand {
            padding:24px 20px; border-bottom:1px solid rgba(255,255,255,0.1);
        }
        .sidebar-brand h1 { font-size:18px; font-weight:700; }
        .sidebar-brand p  { font-size:12px; opacity:0.7; margin-top:4px; }

        .sidebar-nav { padding:16px 0; }
        .nav-section {
            padding:8px 20px 4px;
            font-size:11px; font-weight:600;
            text-transform:uppercase; opacity:0.5; letter-spacing:1px;
        }
        .nav-item a {
            display:flex; align-items:center; gap:12px;
            padding:12px 20px; color:rgba(255,255,255,0.8);
            text-decoration:none; font-size:14px;
            transition:all 0.2s;
        }
        .nav-item a:hover,
        .nav-item a.active {
            background:rgba(255,255,255,0.15);
            color:#fff;
        }
        .nav-item a.active {
            border-left:4px solid #fff;
        }
        .nav-icon { font-size:18px; width:24px; text-align:center; }

        .sidebar-footer {
            position:absolute; bottom:0; left:0; right:0;
            padding:16px 20px; border-top:1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer p  { font-size:12px; opacity:0.7; }
        .sidebar-footer a  { color:rgba(255,255,255,0.8); text-decoration:none;
                             font-size:13px; }
        .sidebar-footer a:hover { color:#fff; }

        /* ── Main Content ── */
        .main-content {
            margin-left:260px; flex:1; padding:0;
        }

        /* ── Top Bar ── */
        .topbar {
            background:#fff; padding:16px 30px;
            display:flex; justify-content:space-between; align-items:center;
            box-shadow:0 2px 8px rgba(0,0,0,0.06); position:sticky;
            top:0; z-index:50;
        }
        .topbar h2 { font-size:20px; color:#333; font-weight:600; }
        .topbar-right { display:flex; align-items:center; gap:16px; }
        .user-badge {
            background:#e3f2fd; color:#1565C0;
            padding:6px 14px; border-radius:20px; font-size:13px;
            font-weight:600;
        }
        .btn-logout {
            background:#f44336; color:#fff; padding:8px 16px;
            border-radius:8px; text-decoration:none; font-size:13px;
            font-weight:600;
        }
        .btn-logout:hover { background:#d32f2f; }

        /* ── Page Body ── */
        .page-body { padding:24px 30px; }

        /* ── Cards ── */
        .card {
            background:#fff; border-radius:12px;
            box-shadow:0 2px 10px rgba(0,0,0,0.07);
            margin-bottom:24px; overflow:hidden;
        }
        .card-header {
            padding:16px 24px; border-bottom:1px solid #f0f0f0;
            display:flex; justify-content:space-between; align-items:center;
        }
        .card-header h3 { font-size:16px; color:#333; font-weight:600; }
        .card-body { padding:24px; }

        /* ── Stat Cards ── */
        .stat-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
            gap:16px; margin-bottom:24px;
        }
        .stat-card {
            background:#fff; border-radius:12px; padding:20px;
            box-shadow:0 2px 8px rgba(0,0,0,0.06);
            display:flex; align-items:center; gap:16px;
        }
        .stat-icon {
            width:52px; height:52px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-size:24px; flex-shrink:0;
        }
        .stat-info .number {
            font-size:28px; font-weight:700; color:#333;
        }
        .stat-info .label {
            font-size:12px; color:#888; margin-top:2px;
        }

        /* ── Chart containers ── */
        .chart-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(400px,1fr));
            gap:24px; margin-bottom:24px;
        }
        .chart-container { position:relative; height:300px; }
        .chart-container-tall { position:relative; height:400px; }

        /* ── Alerts ── */
        .alert {
            padding:12px 16px; border-radius:8px;
            margin-bottom:16px; font-size:14px;
        }
        .alert-success {
            background:#e8f5e9; color:#2e7d32;
            border-left:4px solid #4CAF50;
        }
        .alert-error {
            background:#ffebee; color:#c62828;
            border-left:4px solid #f44336;
        }

        /* ── Buttons ── */
        .btn {
            padding:8px 18px; border:none; border-radius:8px;
            font-size:13px; font-weight:600; cursor:pointer;
            text-decoration:none; display:inline-block;
        }
        .btn-primary { background:#1565C0; color:#fff; }
        .btn-primary:hover { background:#0D47A1; }
        .btn-success { background:#4CAF50; color:#fff; }
        .btn-success:hover { background:#388E3C; }
        .btn-secondary { background:#757575; color:#fff; }
        .btn-sm { padding:5px 12px; font-size:12px; }

        /* ── Tables ── */
        .table { width:100%; border-collapse:collapse; }
        .table th {
            background:#f8f9fa; padding:12px;
            text-align:left; font-size:12px;
            color:#666; border-bottom:2px solid #eee;
        }
        .table td {
            padding:12px; border-bottom:1px solid #f0f0f0;
            font-size:13px;
        }
        .table tr:hover { background:#f8f9fa; }

        /* ── Forms ── */
        .form-row { display:flex; gap:12px; flex-wrap:wrap; }
        .form-group { margin-bottom:12px; }
        .form-group label {
            display:block; margin-bottom:4px;
            font-size:13px; font-weight:600; color:#555;
        }
        .form-group select,
        .form-group input {
            padding:8px 12px; border:2px solid #e0e0e0;
            border-radius:8px; font-size:13px; min-width:150px;
        }
        .form-group select:focus,
        .form-group input:focus {
            outline:none; border-color:#1565C0;
        }

        /* ── Badges ── */
        .badge {
            display:inline-block; padding:3px 10px;
            border-radius:12px; font-size:11px; font-weight:600;
        }
        .badge-blue   { background:#e3f2fd; color:#1565C0; }
        .badge-green  { background:#e8f5e9; color:#2e7d32; }
        .badge-orange { background:#fff3e0; color:#e65100; }
        .badge-red    { background:#ffebee; color:#c62828; }

        /* ── Profile card ── */
        .profile-img {
            width:100px; height:100px; border-radius:50%;
            object-fit:cover; border:4px solid #e3f2fd;
        }
        .profile-placeholder {
            width:100px; height:100px; border-radius:50%;
            background:#e3f2fd; display:flex;
            align-items:center; justify-content:center; font-size:40px;
        }

        /* ── Section divider ── */
        .section-title {
            font-size:18px; font-weight:600; color:#333;
            margin-bottom:16px; padding-bottom:8px;
            border-bottom:2px solid #e3f2fd;
        }

        /* ── Responsive ── */
        @media (max-width:768px) {
            .sidebar { transform:translateX(-260px); }
            .main-content { margin-left:0; }
            .chart-grid { grid-template-columns:1fr; }
        }

        /* ── Loading spinner ── */
        .spinner {
            display:inline-block; width:20px; height:20px;
            border:3px solid #e3f2fd;
            border-top:3px solid #1565C0;
            border-radius:50%; animation:spin 1s linear infinite;
        }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* ── Color helpers ── */
        .text-blue   { color:#1565C0; }
        .text-green  { color:#2e7d32; }
        .text-orange { color:#e65100; }
        .text-red    { color:#c62828; }
        .text-muted  { color:#888; }
        .fw-bold     { font-weight:700; }
        .text-center { text-align:center; }
        .mt-16       { margin-top:16px; }
        .mb-16       { margin-bottom:16px; }
    </style>
</head>
<body>

<!-- ================================================================
     SIDEBAR
================================================================ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h1>📊 UniAnalytics</h1>
        <p>University of Eastminster</p>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>

        <div class="nav-item">
            <a href="<?php echo site_url('dashboard'); ?>"
               class="<?php echo (isset($active_page) && $active_page === 'dashboard') ? 'active' : ''; ?>">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
        </div>

        <div class="nav-item">
            <a href="<?php echo site_url('alumni'); ?>"
               class="<?php echo (isset($active_page) && $active_page === 'alumni') ? 'active' : ''; ?>">
                <span class="nav-icon">👥</span> Alumni Directory
            </a>
        </div>

        <div class="nav-section">Analytics</div>

        <div class="nav-item">
            <a href="<?php echo site_url('analytics'); ?>"
               class="<?php echo (isset($active_page) && $active_page === 'analytics') ? 'active' : ''; ?>">
                <span class="nav-icon">📈</span> Overview
            </a>
        </div>

        <div class="nav-item">
            <a href="<?php echo site_url('analytics/skills-gap'); ?>">
                <span class="nav-icon">🎯</span> Skills Gap
            </a>
        </div>

        <div class="nav-item">
            <a href="<?php echo site_url('analytics/employment'); ?>">
                <span class="nav-icon">💼</span> Employment
            </a>
        </div>

        <div class="nav-item">
            <a href="<?php echo site_url('analytics/job-titles'); ?>">
                <span class="nav-icon">🏷️</span> Job Titles
            </a>
        </div>

        <div class="nav-item">
            <a href="<?php echo site_url('analytics/employers'); ?>">
                <span class="nav-icon">🏢</span> Top Employers
            </a>
        </div>

        <div class="nav-item">
            <a href="<?php echo site_url('analytics/geographic'); ?>">
                <span class="nav-icon">🌍</span> Geographic
            </a>
        </div>

        <div class="nav-item">
            <a href="<?php echo site_url('analytics/trends'); ?>">
                <span class="nav-icon">📅</span> Trends
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <p>Logged in as:</p>
        <p style="margin-top:4px;">
            <strong><?php echo htmlspecialchars(
                $this->session->userdata('first_name') . ' ' .
                $this->session->userdata('last_name')
            ); ?></strong>
        </p>
        <a href="<?php echo site_url('logout'); ?>"
           style="display:block;margin-top:8px;">🚪 Logout</a>
    </div>
</div>

<!-- ================================================================
     MAIN CONTENT
================================================================ -->
<div class="main-content">

    <!-- Top Bar -->
    <div class="topbar">
        <h2><?php echo isset($page_title)
            ? htmlspecialchars($page_title) : 'Dashboard'; ?></h2>
        <div class="topbar-right">
            <span class="user-badge">
                👤 <?php echo htmlspecialchars(
                    $this->session->userdata('first_name')
                ); ?>
            </span>
            <a href="<?php echo site_url('logout'); ?>"
               class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="page-body">

        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars(
                    $this->session->flashdata('success')
                ); ?>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars(
                    $this->session->flashdata('error')
                ); ?>
            </div>
        <?php endif; ?>