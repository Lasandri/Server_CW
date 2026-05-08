<?php
// Codeigniter/application/views/dashboard/index.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Alumni Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ── Root Variables ─────────────────────────────── */
        :root {
            --primary:   #0f3460;
            --secondary: #e94560;
            --accent:    #16213e;
            --sidebar-w: 260px;
            --bg:        #f0f2f5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #0f3460 0%, #16213e 100%);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 100;
            transition: width 0.3s;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            gap: 12px;
        }

        .brand-icon {
            width: 42px; height: 42px;
            background: var(--secondary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .brand-icon i { color: white; font-size: 18px; }

        .brand-title {
            color: white;
            font-size: 18px;
            font-weight: 700;
            display: block;
            line-height: 1.2;
        }

        .brand-subtitle {
            color: rgba(255,255,255,0.6);
            font-size: 12px;
            display: block;
        }

        .sidebar-nav {
            list-style: none;
            padding: 15px 0;
            flex: 1;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-size: 14px;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.08);
            border-left-color: rgba(255,255,255,0.3);
        }

        .nav-link.active {
            color: white;
            background: rgba(233, 69, 96, 0.2);
            border-left-color: var(--secondary);
        }

        .nav-link i { width: 20px; font-size: 16px; }

        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px; height: 36px;
            background: var(--secondary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 15px;
            flex-shrink: 0;
        }

        .user-details { flex: 1; min-width: 0; }

        .user-name {
            color: white;
            font-size: 13px;
            font-weight: 600;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            color: rgba(255,255,255,0.5);
            font-size: 11px;
        }

        .logout-btn {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .logout-btn:hover { color: var(--secondary); background: rgba(255,255,255,0.1); }

        /* ── Main Content ─────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ───────────────────────────────────────── */
        .topbar {
            background: white;
            padding: 0 30px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .topbar-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar-date {
            color: #6c757d;
            font-size: 13px;
        }

        /* ── Page Body ────────────────────────────────────── */
        .page-body {
            padding: 30px;
            flex: 1;
        }

        /* ── Stat Cards ───────────────────────────────────── */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px; height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .stat-icon.blue   { background: rgba(15,52,96,0.12);  color: #0f3460; }
        .stat-icon.red    { background: rgba(233,69,96,0.12); color: #e94560; }
        .stat-icon.green  { background: rgba(25,135,84,0.12); color: #198754; }
        .stat-icon.orange { background: rgba(253,126,20,0.12);color: #fd7e14; }

        .stat-info { flex: 1; }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 13px;
            font-weight: 500;
        }

        /* ── Quick Chart Cards ────────────────────────────── */
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            height: 100%;
        }

        .chart-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
        }

        .chart-card-badge {
            background: rgba(15,52,96,0.1);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ── Quick Action Buttons ─────────────────────────── */
        .quick-action {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            text-decoration: none;
            display: block;
            transition: all 0.2s;
            color: var(--primary);
        }

        .quick-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            color: var(--primary);
        }

        .quick-action i {
            font-size: 28px;
            margin-bottom: 10px;
            display: block;
        }

        .quick-action span {
            font-size: 13px;
            font-weight: 600;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php $this->load->view('dashboard/sidebar'); ?>

<!-- Main Content -->
<div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">
            <i class="fas fa-tachometer-alt me-2 text-danger"></i>Dashboard
        </div>
        <div class="topbar-right">
            <span class="topbar-date">
                <i class="fas fa-calendar me-1"></i>
                <?php echo date('l, d F Y'); ?>
            </span>
        </div>
    </div>

    <!-- Page Body -->
    <div class="page-body">

        <!-- Welcome Banner -->
        <div class="alert mb-4"
             style="background:linear-gradient(135deg,#0f3460,#e94560);
                    border:none; border-radius:15px; color:white; padding:20px 25px;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 style="margin:0; font-weight:700;">
                        Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! 👋
                    </h4>
                    <p style="margin:5px 0 0; opacity:0.85; font-size:14px;">
                        Here's your alumni analytics overview
                    </p>
                </div>
                <i class="fas fa-chart-line" style="font-size:40px; opacity:0.3;"></i>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="totalAlumni">
                            <?php echo $overview['total_alumni']; ?>
                        </div>
                        <div class="stat-label">Total Alumni</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">
                            <?php echo $overview['total_sectors']; ?>
                        </div>
                        <div class="stat-label">Industry Sectors</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">
                            <?php echo $overview['total_programmes']; ?>
                        </div>
                        <div class="stat-label">Programmes</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">
                            <?php echo $overview['total_years']; ?>
                        </div>
                        <div class="stat-label">Graduation Years</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h5 class="section-title">Quick Actions</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="<?php echo base_url('index.php/dashboard/graphs'); ?>"
                   class="quick-action">
                    <i class="fas fa-chart-bar text-primary"></i>
                    <span>View Analytics & Graphs</span>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?php echo base_url('index.php/dashboard/alumni'); ?>"
                   class="quick-action">
                    <i class="fas fa-users text-success"></i>
                    <span>Browse Alumni Profiles</span>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?php echo base_url('index.php/dashboard/alumni'); ?>?export=csv"
                   class="quick-action">
                    <i class="fas fa-file-export text-warning"></i>
                    <span>Export Alumni Data</span>
                </a>
            </div>
        </div>

        <!-- Mini Charts Row -->
        <h5 class="section-title">Quick Insights</h5>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <span class="chart-card-title">
                            <i class="fas fa-industry me-2 text-primary"></i>
                            Employment by Sector
                        </span>
                        <span class="chart-card-badge">Live</span>
                    </div>
                    <canvas id="sectorChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <span class="chart-card-title">
                            <i class="fas fa-tools me-2 text-danger"></i>
                            Top Skills (Curriculum Gap)
                        </span>
                        <span class="chart-card-badge">Live</span>
                    </div>
                    <canvas id="skillsChart" height="200"></canvas>
                </div>
            </div>
        </div>

    </div><!-- end page-body -->
</div><!-- end main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const API_URL = '<?php echo $this->config->item("api_base_url"); ?>';
const API_KEY = 'dashboard_key_alumni_2025_secure';

const headers = {
    'Authorization': 'Bearer ' + API_KEY,
    'Content-Type': 'application/json'
};

// ── Load Sector Chart ─────────────────────────────────────────────────────
async function loadSectorChart() {
    try {
        const res = await fetch(API_URL + '/analytics/employment-by-sector', { headers });
        const json = await res.json();

        if (!json.success) return;

        const labels = json.data.map(d => d.industry_sector);
        const values = json.data.map(d => d.count);
        const colors = ['#0f3460','#e94560','#198754','#fd7e14',
                        '#0dcaf0','#6f42c1','#20c997','#ffc107'];

        new Chart(document.getElementById('sectorChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right', labels: { font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} alumni`
                        }
                    }
                }
            }
        });
    } catch (e) {
        console.error('Sector chart error:', e);
    }
}

// ── Load Skills Chart ─────────────────────────────────────────────────────
async function loadSkillsChart() {
    try {
        const res = await fetch(API_URL + '/analytics/skills-gap', { headers });
        const json = await res.json();

        if (!json.success) return;

        const labels = json.data.slice(0, 8).map(d => d.skill);
        const values = json.data.slice(0, 8).map(d => d.count);

        new Chart(document.getElementById('skillsChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Alumni with this skill',
                    data: values,
                    backgroundColor: 'rgba(233,69,96,0.8)',
                    borderColor: '#e94560',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.raw} alumni`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { ticks: { font: { size: 11 } } }
                }
            }
        });
    } catch (e) {
        console.error('Skills chart error:', e);
    }
}

// Load both charts
loadSectorChart();
loadSkillsChart();
</script>
</body>
</html>