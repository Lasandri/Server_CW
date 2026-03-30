<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Profile'; ?> - Alumni Influencers</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Tahoma,sans-serif; background:#f0f2f5; }
        
        /* Navigation */
        .navbar { background:#2196F3; color:#fff; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
        .navbar h1 { font-size:20px; }
        .navbar a { color:#fff; text-decoration:none; margin-left:20px; font-size:14px; }
        .navbar a:hover { text-decoration:underline; }
        
        /* Layout */
        .container { max-width:900px; margin:20px auto; padding:0 20px; }
        
        /* Cards */
        .card { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08); margin-bottom:20px; overflow:hidden; }
        .card-header { background:#f8f9fa; padding:16px 24px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
        .card-header h2 { font-size:18px; color:#333; }
        .card-body { padding:24px; }
        
        /* Forms */
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; margin-bottom:6px; font-weight:600; color:#444; font-size:14px; }
        .form-group input,
        .form-group textarea,
        .form-group select { width:100%; padding:10px 12px; border:2px solid #e0e0e0; border-radius:8px; font-size:14px; font-family:inherit; }
        .form-group input:focus,
        .form-group textarea:focus { outline:none; border-color:#2196F3; }
        .form-group textarea { resize:vertical; min-height:100px; }
        .form-row { display:flex; gap:16px; }
        .form-row .form-group { flex:1; }
        .form-hint { font-size:12px; color:#888; margin-top:4px; }
        
        /* Buttons */
        .btn { padding:10px 20px; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn-primary { background:#2196F3; color:#fff; }
        .btn-primary:hover { background:#1976D2; }
        .btn-success { background:#4CAF50; color:#fff; }
        .btn-success:hover { background:#43A047; }
        .btn-danger { background:#f44336; color:#fff; font-size:12px; padding:6px 12px; }
        .btn-danger:hover { background:#d32f2f; }
        .btn-secondary { background:#757575; color:#fff; }
        .btn-secondary:hover { background:#616161; }
        .btn-sm { padding:6px 14px; font-size:13px; }
        
        /* Messages */
        .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; }
        .alert-success { background:#e8f5e9; color:#2e7d32; border-left:4px solid #4CAF50; }
        .alert-error { background:#ffebee; color:#c62828; border-left:4px solid #f44336; }
        
        /* Tables */
        .table { width:100%; border-collapse:collapse; }
        .table th { background:#f8f9fa; padding:12px; text-align:left; font-size:13px; color:#666; border-bottom:2px solid #eee; }
        .table td { padding:12px; border-bottom:1px solid #f0f0f0; font-size:14px; }
        .table tr:hover { background:#f8f9fa; }
        .table .actions { white-space:nowrap; }
        .table .actions a { margin-right:8px; }
        
        /* Completion bar */
        .progress-bar { background:#e0e0e0; border-radius:10px; height:20px; overflow:hidden; margin:8px 0; }
        .progress-fill { height:100%; border-radius:10px; transition:width 0.3s; }
        .progress-fill.low { background:#f44336; }
        .progress-fill.mid { background:#FF9800; }
        .progress-fill.high { background:#4CAF50; }
        
        /* Profile image */
        .profile-img { width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid #e0e0e0; }
        .profile-img-placeholder { width:120px; height:120px; border-radius:50%; background:#e0e0e0; display:flex; align-items:center; justify-content:center; font-size:48px; }
        
        /* Section nav */
        .section-nav { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px; }
        .section-nav a { padding:8px 16px; background:#fff; border:2px solid #e0e0e0; border-radius:8px; text-decoration:none; color:#555; font-size:13px; font-weight:600; }
        .section-nav a:hover { border-color:#2196F3; color:#2196F3; }
        .section-nav a.active { background:#2196F3; color:#fff; border-color:#2196F3; }
        
        /* URL display */
        .url-link { color:#2196F3; text-decoration:none; font-size:13px; word-break:break-all; }
        .url-link:hover { text-decoration:underline; }

        /* Checkbox */
        .checkbox-group { display:flex; align-items:center; gap:8px; }
        .checkbox-group input[type="checkbox"] { width:auto; }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<div class="navbar">
    <h1>🎓 Alumni Influencers</h1>
    <div>
        <a href="<?php echo site_url('profile'); ?>">Dashboard</a>
        <a href="<?php echo site_url('logout'); ?>">Logout</a>
    </div>
</div>