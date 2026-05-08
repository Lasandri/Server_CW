<!-- ============================================================
     MAIN DASHBOARD - Overview with 8 Charts
============================================================ -->

<!-- Stats Row -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e3f2fd;">👥</div>
        <div class="stat-info">
            <div class="number">
                <?php echo number_format($overview['total_alumni']); ?>
            </div>
            <div class="label">Total Alumni</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8f5e9;">📜</div>
        <div class="stat-info">
            <div class="number">
                <?php echo number_format(
                    $overview['total_certifications']
                ); ?>
            </div>
            <div class="label">Certifications</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fff3e0;">💼</div>
        <div class="stat-info">
            <div class="number">
                <?php echo number_format($overview['total_jobs']); ?>
            </div>
            <div class="label">Employment Records</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f3e5f5;">🎓</div>
        <div class="stat-info">
            <div class="number">
                <?php echo number_format($overview['total_degrees']); ?>
            </div>
            <div class="label">Degrees</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fce4ec;">📚</div>
        <div class="stat-info">
            <div class="number">
                <?php echo number_format($overview['total_courses']); ?>
            </div>
            <div class="label">Pro Courses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#e0f7fa;">🪪</div>
        <div class="stat-info">
            <div class="number">
                <?php echo number_format($overview['total_licences']); ?>
            </div>
            <div class="label">Licences</div>
        </div>
    </div>
</div>

<!-- ── Row 1: Employment + Job Titles ── -->
<div class="chart-grid">

    <!-- Chart 1: Employment by Company (Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>💼 Employment by Company</h3>
            <a href="<?php echo site_url('analytics/export/employers'); ?>"
               class="btn btn-success btn-sm">⬇ CSV</a>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartEmployment"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Current Job Titles (Doughnut) -->
    <div class="card">
        <div class="card-header">
            <h3>🏷️ Current Job Titles</h3>
            <a href="<?php echo site_url('analytics/export/job-titles'); ?>"
               class="btn btn-success btn-sm">⬇ CSV</a>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartJobTitles"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Row 2: Skills Gap + Geographic ── -->
<div class="chart-grid">

    <!-- Chart 3: Top Certifications (Horizontal Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>🎯 Top Certifications (Skills Gap)</h3>
            <a href="<?php echo site_url('analytics/export/certifications'); ?>"
               class="btn btn-success btn-sm">⬇ CSV</a>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartCertifications"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 4: Geographic Distribution (Pie) -->
    <div class="card">
        <div class="card-header">
            <h3>🌍 Geographic Distribution</h3>
            <a href="<?php echo site_url('analytics/export/geographic'); ?>"
               class="btn btn-success btn-sm">⬇ CSV</a>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartGeographic"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Row 3: Jobs by Year + Top Courses ── -->
<div class="chart-grid">

    <!-- Chart 5: Jobs by Year (Line) -->
    <div class="card">
        <div class="card-header">
            <h3>📈 Employment Growth by Year</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartJobsByYear"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 6: Top Courses (Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>📚 Top Professional Courses</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartCourses"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Row 4: Top Employers + UK vs International ── -->
<div class="chart-grid">

    <!-- Chart 7: Top Employers (Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>🏢 Top Employers</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartTopEmployers"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 8: UK vs International (Doughnut) -->
    <div class="card">
        <div class="card-header">
            <h3>🗺️ UK vs International Alumni</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartUkIntl"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="card">
    <div class="card-header">
        <h3>🔗 Quick Access</h3>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="<?php echo site_url('alumni'); ?>"
               class="btn btn-primary">👥 View Alumni</a>
            <a href="<?php echo site_url('analytics/skills-gap'); ?>"
               class="btn btn-primary">🎯 Skills Gap</a>
            <a href="<?php echo site_url('analytics/employment'); ?>"
               class="btn btn-primary">💼 Employment</a>
            <a href="<?php echo site_url('analytics/geographic'); ?>"
               class="btn btn-primary">🌍 Geographic</a>
            <a href="<?php echo site_url('analytics/trends'); ?>"
               class="btn btn-primary">📅 Trends</a>
            <a href="<?php echo site_url('alumni/export'); ?>"
               class="btn btn-success">⬇ Export All Alumni</a>
        </div>
    </div>
</div>

<!-- ================================================================
     CHART.JS SCRIPTS
================================================================ -->
<script>
// ── Colour Palette ──────────────────────────────────────────────
const COLORS = [
    '#1565C0','#2196F3','#4CAF50','#FF9800','#9C27B0',
    '#F44336','#00BCD4','#FF5722','#607D8B','#795548',
    '#3F51B5','#009688','#CDDC39','#FFC107','#E91E63',
];

function makeColors(n) {
    return Array.from({length:n},(_,i)=>COLORS[i%COLORS.length]);
}

// Helper - PHP array → JS
<?php
// Employment by company
$emp_labels = array_map(
    function($e){ return addslashes(is_object($e)?$e->company_name:$e['company_name']); },
    (array)($employment['by_company'] ?? [])
);
$emp_values = array_map(
    function($e){ return is_object($e)?$e->employee_count:$e['employee_count']; },
    (array)($employment['by_company'] ?? [])
);

// Job titles
$jt_labels = array_map(
    function($j){ return addslashes(is_object($j)?$j->job_title:$j['job_title']); },
    (array)($job_titles['current_only'] ?? [])
);
$jt_values = array_map(
    function($j){ return is_object($j)?$j->count:$j['count']; },
    (array)($job_titles['current_only'] ?? [])
);

// Certifications
$cert_labels = array_map(
    function($c){ return addslashes(is_object($c)?$c->certification_name:$c['certification_name']); },
    (array)($skills['top_certifications'] ?? [])
);
$cert_values = array_map(
    function($c){ return is_object($c)?$c->count:$c['count']; },
    (array)($skills['top_certifications'] ?? [])
);

// Geographic
$geo_labels = array_map(
    function($g){ return addslashes(is_object($g)?$g->country:$g['country']); },
    (array)($geographic['by_country'] ?? [])
);
$geo_values = array_map(
    function($g){ return is_object($g)?$g->count:$g['count']; },
    (array)($geographic['by_country'] ?? [])
);

// Jobs by year
$year_labels = array_map(
    function($y){ return is_object($y)?$y->year:$y['year']; },
    (array)($employment['jobs_by_year'] ?? [])
);
$year_values = array_map(
    function($y){ return is_object($y)?$y->count:$y['count']; },
    (array)($employment['jobs_by_year'] ?? [])
);

// Top courses
$course_labels = array_map(
    function($c){ return addslashes(is_object($c)?$c->course_name:$c['course_name']); },
    (array)($skills['top_courses'] ?? [])
);
$course_values = array_map(
    function($c){ return is_object($c)?$c->count:$c['count']; },
    (array)($skills['top_courses'] ?? [])
);

// Top employers
$temployer_labels = array_map(
    function($e){ return addslashes(is_object($e)?$e->company_name:$e['company_name']); },
    (array)($employers['top_employers'] ?? [])
);
$temployer_values = array_map(
    function($e){ return is_object($e)?$e->alumni_count:$e['alumni_count']; },
    (array)($employers['top_employers'] ?? [])
);

// UK vs International
$ukintl_labels = array_map(
    function($u){ return is_object($u)?$u->region:$u['region']; },
    (array)($geographic['uk_vs_international'] ?? [])
);
$ukintl_values = array_map(
    function($u){ return is_object($u)?$u->count:$u['count']; },
    (array)($geographic['uk_vs_international'] ?? [])
);
?>

const empLabels    = <?php echo json_encode($emp_labels); ?>;
const empValues    = <?php echo json_encode($emp_values); ?>;
const jtLabels     = <?php echo json_encode($jt_labels); ?>;
const jtValues     = <?php echo json_encode($jt_values); ?>;
const certLabels   = <?php echo json_encode($cert_labels); ?>;
const certValues   = <?php echo json_encode($cert_values); ?>;
const geoLabels    = <?php echo json_encode($geo_labels); ?>;
const geoValues    = <?php echo json_encode($geo_values); ?>;
const yearLabels   = <?php echo json_encode($year_labels); ?>;
const yearValues   = <?php echo json_encode($year_values); ?>;
const courseLabels = <?php echo json_encode($course_labels); ?>;
const courseValues = <?php echo json_encode($course_values); ?>;
const tEmpLabels   = <?php echo json_encode($temployer_labels); ?>;
const tEmpValues   = <?php echo json_encode($temployer_values); ?>;
const ukLabels     = <?php echo json_encode($ukintl_labels); ?>;
const ukValues     = <?php echo json_encode($ukintl_values); ?>;

const defaultOpts = {
    responsive:true,
    maintainAspectRatio:false,
    plugins:{
        legend:{ position:'bottom', labels:{ font:{ size:11 } } },
        tooltip:{ mode:'index', intersect:false },
    },
};

// ── Chart 1: Employment by Company (Bar) ───────────────────────
new Chart(document.getElementById('chartEmployment'), {
    type:'bar',
    data:{
        labels: empLabels.slice(0,10),
        datasets:[{
            label:'Alumni',
            data: empValues.slice(0,10),
            backgroundColor: makeColors(10),
            borderRadius:6,
        }],
    },
    options:{
        ...defaultOpts,
        plugins:{
            ...defaultOpts.plugins,
            legend:{ display:false },
        },
        scales:{
            y:{ beginAtZero:true, ticks:{ stepSize:1 } },
            x:{ ticks:{ font:{ size:10 } } },
        },
    },
});

// ── Chart 2: Current Job Titles (Doughnut) ─────────────────────
new Chart(document.getElementById('chartJobTitles'), {
    type:'doughnut',
    data:{
        labels: jtLabels.slice(0,8),
        datasets:[{
            data: jtValues.slice(0,8),
            backgroundColor: makeColors(8),
            borderWidth:2,
        }],
    },
    options:{
        ...defaultOpts,
        cutout:'60%',
    },
});

// ── Chart 3: Top Certifications (Horizontal Bar) ───────────────
new Chart(document.getElementById('chartCertifications'), {
    type:'bar',
    data:{
        labels: certLabels.slice(0,8),
        datasets:[{
            label:'Count',
            data: certValues.slice(0,8),
            backgroundColor:'#1565C0',
            borderRadius:4,
        }],
    },
    options:{
        ...defaultOpts,
        indexAxis:'y',
        plugins:{
            ...defaultOpts.plugins,
            legend:{ display:false },
        },
        scales:{
            x:{ beginAtZero:true },
        },
    },
});

// ── Chart 4: Geographic (Pie) ──────────────────────────────────
new Chart(document.getElementById('chartGeographic'), {
    type:'pie',
    data:{
        labels: geoLabels.slice(0,8),
        datasets:[{
            data: geoValues.slice(0,8),
            backgroundColor: makeColors(8),
        }],
    },
    options:{ ...defaultOpts },
});

// ── Chart 5: Jobs by Year (Line) ───────────────────────────────
new Chart(document.getElementById('chartJobsByYear'), {
    type:'line',
    data:{
        labels: yearLabels,
        datasets:[{
            label:'New Jobs',
            data: yearValues,
            borderColor:'#1565C0',
            backgroundColor:'rgba(21,101,192,0.1)',
            fill:true,
            tension:0.4,
            pointRadius:5,
            pointBackgroundColor:'#1565C0',
        }],
    },
    options:{
        ...defaultOpts,
        scales:{
            y:{ beginAtZero:true, ticks:{ stepSize:1 } },
        },
    },
});

// ── Chart 6: Top Professional Courses (Bar) ────────────────────
new Chart(document.getElementById('chartCourses'), {
    type:'bar',
    data:{
        labels: courseLabels.slice(0,8),
        datasets:[{
            label:'Alumni Completed',
            data: courseValues.slice(0,8),
            backgroundColor:'#4CAF50',
            borderRadius:6,
        }],
    },
    options:{
        ...defaultOpts,
        plugins:{
            ...defaultOpts.plugins,
            legend:{ display:false },
        },
        scales:{
            y:{ beginAtZero:true },
            x:{ ticks:{ font:{ size:10 } } },
        },
    },
});

// ── Chart 7: Top Employers (Bar) ───────────────────────────────
new Chart(document.getElementById('chartTopEmployers'), {
    type:'bar',
    data:{
        labels: tEmpLabels.slice(0,10),
        datasets:[{
            label:'Alumni Count',
            data: tEmpValues.slice(0,10),
            backgroundColor:'#9C27B0',
            borderRadius:6,
        }],
    },
    options:{
        ...defaultOpts,
        plugins:{
            ...defaultOpts.plugins,
            legend:{ display:false },
        },
        scales:{
            y:{ beginAtZero:true },
        },
    },
});

// ── Chart 8: UK vs International (Doughnut) ────────────────────
new Chart(document.getElementById('chartUkIntl'), {
    type:'doughnut',
    data:{
        labels: ukLabels,
        datasets:[{
            data: ukValues,
            backgroundColor:['#1565C0','#4CAF50','#FF9800'],
            borderWidth:2,
        }],
    },
    options:{
        ...defaultOpts,
        cutout:'55%',
    },
});
</script>