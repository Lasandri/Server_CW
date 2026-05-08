<?php
// Codeigniter/application/views/dashboard/graphs.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Graphs - Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0f3460;
            --secondary: #e94560;
            --sidebar-w: 260px;
            --bg: #f0f2f5;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:var(--bg); display:flex; min-height:100vh; }

        /* Sidebar - same styles */
        .sidebar { width:var(--sidebar-w); background:linear-gradient(180deg,#0f3460 0%,#16213e 100%); display:flex; flex-direction:column; position:fixed; top:0; left:0; height:100vh; z-index:100; }
        .sidebar-brand { display:flex; align-items:center; padding:25px 20px; border-bottom:1px solid rgba(255,255,255,0.1); gap:12px; }
        .brand-icon { width:42px; height:42px; background:#e94560; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .brand-icon i { color:white; font-size:18px; }
        .brand-title { color:white; font-size:18px; font-weight:700; display:block; line-height:1.2; }
        .brand-subtitle { color:rgba(255,255,255,0.6); font-size:12px; display:block; }
        .sidebar-nav { list-style:none; padding:15px 0; flex:1; }
        .nav-link { display:flex; align-items:center; gap:12px; padding:13px 20px; color:rgba(255,255,255,0.7); text-decoration:none; transition:all 0.2s; border-left:3px solid transparent; font-size:14px; }
        .nav-link:hover { color:white; background:rgba(255,255,255,0.08); border-left-color:rgba(255,255,255,0.3); }
        .nav-link.active { color:white; background:rgba(233,69,96,0.2); border-left-color:#e94560; }
        .nav-link i { width:20px; font-size:16px; }
        .sidebar-footer { padding:15px 20px; border-top:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:10px; }
        .user-avatar { width:36px; height:36px; background:#e94560; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:15px; flex-shrink:0; }
        .user-details { flex:1; min-width:0; }
        .user-name { color:white; font-size:13px; font-weight:600; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-role { color:rgba(255,255,255,0.5); font-size:11px; }
        .logout-btn { color:rgba(255,255,255,0.6); text-decoration:none; padding:6px; border-radius:6px; transition:all 0.2s; }
        .logout-btn:hover { color:#e94560; }

        /* Main */
        .main-content { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
        .topbar { background:white; padding:0 30px; height:65px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 10px rgba(0,0,0,0.08); position:sticky; top:0; z-index:99; }
        .topbar-title { font-size:20px; font-weight:700; color:var(--primary); }
        .page-body { padding:30px; flex:1; }

        /* Filter Bar */
        .filter-bar {
            background:white;
            border-radius:15px;
            padding:20px 25px;
            box-shadow:0 4px 15px rgba(0,0,0,0.06);
            margin-bottom:25px;
        }

        .filter-bar label { font-weight:600; font-size:13px; color:var(--primary); }
        .form-select { font-size:13px; border-radius:8px; border-color:#dee2e6; }
        .form-select:focus { border-color:var(--primary); box-shadow:0 0 0 0.2rem rgba(15,52,96,0.15); }

        .btn-apply {
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            border:none; color:white; border-radius:8px;
            padding:8px 20px; font-size:13px; font-weight:600;
        }

        /* Chart Cards */
        .chart-card {
            background:white;
            border-radius:15px;
            padding:25px;
            box-shadow:0 4px 15px rgba(0,0,0,0.06);
            margin-bottom:25px;
            height:100%;
        }

        .chart-header {
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            margin-bottom:20px;
        }

        .chart-title {
            font-size:16px;
            font-weight:700;
            color:var(--primary);
            margin:0;
        }

        .chart-subtitle {
            font-size:12px;
            color:#6c757d;
            margin:3px 0 0;
        }

        .chart-badge {
            padding:4px 12px;
            border-radius:20px;
            font-size:11px;
            font-weight:600;
        }

        .badge-critical  { background:#ffe5e9; color:#e94560; }
        .badge-analytics { background:#e8f4fd; color:var(--primary); }

        /* Loading spinner */
        .chart-loading {
            display:flex;
            align-items:center;
            justify-content:center;
            height:200px;
            color:#aaa;
        }

        /* Export btn */
        .btn-export {
            background:white;
            border:2px solid var(--primary);
            color:var(--primary);
            border-radius:8px;
            padding:8px 18px;
            font-size:13px;
            font-weight:600;
            text-decoration:none;
            transition:all 0.2s;
        }

        .btn-export:hover {
            background:var(--primary);
            color:white;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php $this->load->view('dashboard/sidebar'); ?>

<div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">
            <i class="fas fa-chart-bar me-2 text-danger"></i>Analytics & Graphs
        </div>
        <div class="d-flex gap-2">
            <button class="btn-export" onclick="exportCSV()">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </button>
            <button class="btn-export" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print
            </button>
        </div>
    </div>

    <div class="page-body">

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Programme</label>
                    <select class="form-select" id="filterProgramme">
                        <option value="all">All Programmes</option>
                        <?php foreach ($filter_options['programmes'] as $prog): ?>
                            <option value="<?php echo htmlspecialchars($prog); ?>">
                                <?php echo htmlspecialchars($prog); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Graduation Year</label>
                    <select class="form-select" id="filterYear">
                        <option value="all">All Years</option>
                        <?php foreach ($filter_options['graduation_years'] as $year): ?>
                            <option value="<?php echo $year; ?>">
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Industry Sector</label>
                    <select class="form-select" id="filterSector">
                        <option value="all">All Sectors</option>
                        <?php foreach ($filter_options['industry_sectors'] as $sector): ?>
                            <option value="<?php echo htmlspecialchars($sector); ?>">
                                <?php echo htmlspecialchars($sector); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn-apply w-100 py-2" onclick="applyFilters()">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Row 1: Sector + Skills Gap -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h6 class="chart-title">
                                <i class="fas fa-industry me-2"></i>Employment by Industry Sector
                            </h6>
                            <p class="chart-subtitle">Distribution of alumni across sectors</p>
                        </div>
                        <span class="chart-badge badge-analytics">Bar Chart</span>
                    </div>
                    <canvas id="sectorBarChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h6 class="chart-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Curriculum Skills Gap
                            </h6>
                            <p class="chart-subtitle">Skills independently acquired post-graduation</p>
                        </div>
                        <span class="chart-badge badge-critical">⚠ Critical</span>
                    </div>
                    <canvas id="skillsGapChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 2: Sector Pie + Employers -->
        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h6 class="chart-title">
                                <i class="fas fa-chart-pie me-2"></i>Sector Distribution
                            </h6>
                            <p class="chart-subtitle">Alumni percentage per sector</p>
                        </div>
                        <span class="chart-badge badge-analytics">Pie Chart</span>
                    </div>
                    <canvas id="sectorPieChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h6 class="chart-title">
                                <i class="fas fa-building me-2"></i>Top Employers
                            </h6>
                            <p class="chart-subtitle">Most common employers among alumni</p>
                        </div>
                        <span class="chart-badge badge-analytics">Horizontal Bar</span>
                    </div>
                    <canvas id="employersChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 3: Job Titles + Graduation Trends -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h6 class="chart-title">
                                <i class="fas fa-user-tie me-2"></i>Most Common Job Titles
                            </h6>
                            <p class="chart-subtitle">Top career paths after graduation</p>
                        </div>
                        <span class="chart-badge badge-analytics">Doughnut</span>
                    </div>
                    <canvas id="jobTitlesChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h6 class="chart-title">
                                <i class="fas fa-graduation-cap me-2"></i>Graduation Year Trends
                            </h6>
                            <p class="chart-subtitle">Alumni count by graduation year</p>
                        </div>
                        <span class="chart-badge badge-analytics">Line Chart</span>
                    </div>
                    <canvas id="gradTrendsChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 4: Certifications + Geographic -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h6 class="chart-title">
                                <i class="fas fa-certificate me-2"></i>Top Certifications
                            </h6>
                            <p class="chart-subtitle">Post-graduation professional certifications</p>
                        </div>
                        <span class="chart-badge badge-critical">Radar</span>
                    </div>
                    <canvas id="certsChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h6 class="chart-title">
                                <i class="fas fa-map-marker-alt me-2"></i>Geographic Distribution
                            </h6>
                            <p class="chart-subtitle">Where alumni are working</p>
                        </div>
                        <span class="chart-badge badge-analytics">Bar Chart</span>
                    </div>
                    <canvas id="geoChart" height="250"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const API_URL = '<?php echo $api_url; ?>';
const API_KEY = '<?php echo $api_key; ?>';

const HEADERS = {
    'Authorization': 'Bearer ' + API_KEY,
    'Content-Type': 'application/json'
};

// Store chart instances so we can destroy before re-render
const charts = {};

// Color palette
const COLORS = [
    '#0f3460','#e94560','#198754','#fd7e14',
    '#0dcaf0','#6f42c1','#20c997','#ffc107',
    '#dc3545','#0d6efd'
];

// ── Get current filter values ─────────────────────────────────────────────
function getFilters() {
    return {
        programme:       document.getElementById('filterProgramme').value,
        graduation_year: document.getElementById('filterYear').value,
        industry_sector: document.getElementById('filterSector').value
    };
}

// ── Build query string from filters ──────────────────────────────────────
function buildQuery(filters) {
    return Object.entries(filters)
        .filter(([k, v]) => v && v !== 'all')
        .map(([k, v]) => `${k}=${encodeURIComponent(v)}`)
        .join('&');
}

// ── Destroy existing chart ────────────────────────────────────────────────
function destroyChart(id) {
    if (charts[id]) {
        charts[id].destroy();
        delete charts[id];
    }
}

// ── Apply all filters ─────────────────────────────────────────────────────
function applyFilters() {
    loadAllCharts();
}

// ── Load all charts ───────────────────────────────────────────────────────
function loadAllCharts() {
    const filters = getFilters();
    const query   = buildQuery(filters);

    loadSectorBar(query);
    loadSkillsGap(query);
    loadSectorPie(query);
    loadEmployers(query);
    loadJobTitles(query);
    loadGradTrends(query);
    loadCertifications(query);
    loadGeographic(query);
}

// ── 1. Employment by Sector (Bar) ─────────────────────────────────────────
async function loadSectorBar(query) {
    destroyChart('sectorBarChart');
    const res  = await fetch(`${API_URL}/analytics/employment-by-sector?${query}`, {headers: HEADERS});
    const json = await res.json();
    if (!json.success) return;

    charts['sectorBarChart'] = new Chart(document.getElementById('sectorBarChart'), {
        type: 'bar',
        data: {
            labels: json.data.map(d => d.industry_sector),
            datasets: [{
                label: 'Number of Alumni',
                data: json.data.map(d => d.count),
                backgroundColor: COLORS,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.raw} alumni` }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// ── 2. Skills Gap (Horizontal Bar) ───────────────────────────────────────
async function loadSkillsGap(query) {
    destroyChart('skillsGapChart');
    const res  = await fetch(`${API_URL}/analytics/skills-gap?${query}`, {headers: HEADERS});
    const json = await res.json();
    if (!json.success) return;

    charts['skillsGapChart'] = new Chart(document.getElementById('skillsGapChart'), {
        type: 'bar',
        data: {
            labels: json.data.map(d => d.skill),
            datasets: [{
                label: 'Alumni with skill',
                data: json.data.map(d => d.count),
                backgroundColor: 'rgba(233,69,96,0.8)',
                borderColor: '#e94560',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.raw} alumni acquired post-graduation` }
                }
            },
            scales: { x: { beginAtZero: true } }
        }
    });
}

// ── 3. Sector Pie ─────────────────────────────────────────────────────────
async function loadSectorPie(query) {
    destroyChart('sectorPieChart');
    const res  = await fetch(`${API_URL}/analytics/employment-by-sector?${query}`, {headers: HEADERS});
    const json = await res.json();
    if (!json.success) return;

    charts['sectorPieChart'] = new Chart(document.getElementById('sectorPieChart'), {
        type: 'pie',
        data: {
            labels: json.data.map(d => d.industry_sector),
            datasets: [{
                data: json.data.map(d => d.count),
                backgroundColor: COLORS,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            const pct   = ((ctx.raw / total) * 100).toFixed(1);
                            return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
}

// ── 4. Top Employers ──────────────────────────────────────────────────────
async function loadEmployers(query) {
    destroyChart('employersChart');
    const res  = await fetch(`${API_URL}/analytics/top-employers?${query}`, {headers: HEADERS});
    const json = await res.json();
    if (!json.success) return;

    charts['employersChart'] = new Chart(document.getElementById('employersChart'), {
        type: 'bar',
        data: {
            labels: json.data.map(d => d.employer),
            datasets: [{
                label: 'Alumni',
                data: json.data.map(d => d.count),
                backgroundColor: 'rgba(15,52,96,0.8)',
                borderColor: '#0f3460',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.raw} alumni` }
                }
            },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

// ── 5. Job Titles (Doughnut) ──────────────────────────────────────────────
async function loadJobTitles(query) {
    destroyChart('jobTitlesChart');
    const res  = await fetch(`${API_URL}/analytics/job-titles?${query}`, {headers: HEADERS});
    const json = await res.json();
    if (!json.success) return;

    charts['jobTitlesChart'] = new Chart(document.getElementById('jobTitlesChart'), {
        type: 'doughnut',
        data: {
            labels: json.data.map(d => d.job_title),
            datasets: [{
                data: json.data.map(d => d.count),
                backgroundColor: COLORS,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 } } },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` }
                }
            }
        }
    });
}

// ── 6. Graduation Trends (Line) ───────────────────────────────────────────
async function loadGradTrends(query) {
    destroyChart('gradTrendsChart');
    const res  = await fetch(`${API_URL}/analytics/graduation-trends?${query}`, {headers: HEADERS});
    const json = await res.json();
    if (!json.success) return;

    charts['gradTrendsChart'] = new Chart(document.getElementById('gradTrendsChart'), {
        type: 'line',
        data: {
            labels: json.data.map(d => d.graduation_year),
            datasets: [{
                label: 'Graduates',
                data: json.data.map(d => d.count),
                borderColor: '#0f3460',
                backgroundColor: 'rgba(15,52,96,0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#e94560',
                pointRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.raw} graduates` }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// ── 7. Certifications (Radar) ─────────────────────────────────────────────
async function loadCertifications(query) {
    destroyChart('certsChart');
    const res  = await fetch(`${API_URL}/analytics/certifications?${query}`, {headers: HEADERS});
    const json = await res.json();
    if (!json.success) return;

    charts['certsChart'] = new Chart(document.getElementById('certsChart'), {
        type: 'radar',
        data: {
            labels: json.data.map(d => d.cert),
            datasets: [{
                label: 'Alumni Certified',
                data: json.data.map(d => d.count),
                backgroundColor: 'rgba(233,69,96,0.2)',
                borderColor: '#e94560',
                borderWidth: 2,
                pointBackgroundColor: '#e94560'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}

// ── 8. Geographic Distribution ────────────────────────────────────────────
async function loadGeographic(query) {
    destroyChart('geoChart');
    const res  = await fetch(`${API_URL}/analytics/geographic?${query}`, {headers: HEADERS});
    const json = await res.json();
    if (!json.success) return;

    const labels = json.data.map(d => d.location_city + ', ' + d.location_country);

    charts['geoChart'] = new Chart(document.getElementById('geoChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Alumni',
                data: json.data.map(d => d.count),
                backgroundColor: COLORS,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.raw} alumni` }
                }
            },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

// ── CSV Export ────────────────────────────────────────────────────────────
async function exportCSV() {
    const filters = getFilters();
    const query   = buildQuery(filters);

    const res  = await fetch(`${API_URL}/analytics/employment-by-sector?${query}`, {headers: HEADERS});
    const json = await res.json();

    if (!json.success) return;

    let csv = 'Industry Sector,Alumni Count\n';
    json.data.forEach(d => {
        csv += `"${d.industry_sector}",${d.count}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'alumni_analytics.csv';
    a.click();
}

// Load all charts on page load
loadAllCharts();
</script>
</body>
</html>