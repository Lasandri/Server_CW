<?php
$top_certs  = $skills['top_certifications']  ?? array();
$top_courses= $skills['top_courses']         ?? array();
$cert_trend = $skills['certification_trend'] ?? array();
$top_orgs   = $skills['top_organizations']   ?? array();
?>

<!-- Export Button -->
<div style="text-align:right;margin-bottom:16px;">
    <a href="<?php echo site_url('analytics/export/certifications'); ?>"
       class="btn btn-success">⬇ Export Certifications CSV</a>
</div>

<!-- Charts Row 1 -->
<div class="chart-grid">

    <!-- Top Certifications (Horizontal Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>🎯 Top Certifications Acquired</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartTopCerts"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Courses (Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>📚 Top Professional Courses</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartTopCourses"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Charts Row 2 -->
<div class="chart-grid">

    <!-- Certification Trend (Line) -->
    <div class="card">
        <div class="card-header">
            <h3>📈 Certification Growth by Year</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartCertTrend"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Organizations (Pie) -->
    <div class="card">
        <div class="card-header">
            <h3>🏛️ Top Issuing Organizations</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartOrgs"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <h3>📋 Certification Details</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Certification</th>
                    <th>Issuing Organization</th>
                    <th>Alumni Count</th>
                    <th>Gap Severity</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($top_certs as $c):
                    $c = (object)$c;
                    $severity = $c->count >= 5 ? 'Critical' :
                               ($c->count >= 3 ? 'Significant' : 'Emerging');
                    $badge    = $c->count >= 5 ? 'badge-red' :
                               ($c->count >= 3 ? 'badge-orange' : 'badge-green');
                ?>
                <tr>
                    <td style="color:#888;"><?php echo $rank++; ?></td>
                    <td style="font-weight:600;">
                        <?php echo htmlspecialchars($c->certification_name); ?>
                    </td>
                    <td><?php echo htmlspecialchars(
                        $c->issuing_organization); ?></td>
                    <td>
                        <strong><?php echo $c->count; ?></strong> alumni
                    </td>
                    <td>
                        <span class="badge <?php echo $badge; ?>">
                            <?php echo $severity; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const COLORS = [
    '#1565C0','#2196F3','#4CAF50','#FF9800','#9C27B0',
    '#F44336','#00BCD4','#FF5722','#607D8B','#795548',
];
function makeColors(n){
    return Array.from({length:n},(_,i)=>COLORS[i%COLORS.length]);
}

<?php
$cl = array_map(function($c){
    return addslashes(is_object($c)?$c->certification_name:$c['certification_name']);
}, (array)$top_certs);
$cv = array_map(function($c){
    return is_object($c)?$c->count:$c['count'];
}, (array)$top_certs);

$crl = array_map(function($c){
    return addslashes(is_object($c)?$c->course_name:$c['course_name']);
}, (array)$top_courses);
$crv = array_map(function($c){
    return is_object($c)?$c->count:$c['count'];
}, (array)$top_courses);

$tl = array_map(function($c){
    return is_object($c)?$c->year:$c['year'];
}, (array)$cert_trend);
$tv = array_map(function($c){
    return is_object($c)?$c->count:$c['count'];
}, (array)$cert_trend);

$ol = array_map(function($o){
    return addslashes(is_object($o)?$o->organization:$o['organization']);
}, (array)$top_orgs);
$ov = array_map(function($o){
    return is_object($o)?$o->count:$o['count'];
}, (array)$top_orgs);
?>

// Chart 1: Top Certs (Horizontal Bar)
new Chart(document.getElementById('chartTopCerts'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($cl); ?>,
        datasets:[{
            label:'Alumni',
            data:<?php echo json_encode($cv); ?>,
            backgroundColor:makeColors(<?php echo count($cl); ?>),
            borderRadius:4,
        }],
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        indexAxis:'y',
        plugins:{ legend:{ display:false } },
        scales:{ x:{ beginAtZero:true } },
    },
});

// Chart 2: Top Courses
new Chart(document.getElementById('chartTopCourses'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($crl); ?>,
        datasets:[{
            label:'Alumni',
            data:<?php echo json_encode($crv); ?>,
            backgroundColor:'#4CAF50',
            borderRadius:4,
        }],
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        indexAxis:'y',
        plugins:{ legend:{ display:false } },
        scales:{ x:{ beginAtZero:true } },
    },
});

// Chart 3: Trend Line
new Chart(document.getElementById('chartCertTrend'),{
    type:'line',
    data:{
        labels:<?php echo json_encode($tl); ?>,
        datasets:[{
            label:'Certifications Completed',
            data:<?php echo json_encode($tv); ?>,
            borderColor:'#1565C0',
            backgroundColor:'rgba(21,101,192,0.1)',
            fill:true, tension:0.4,
            pointRadius:5,
            pointBackgroundColor:'#1565C0',
        }],
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        scales:{ y:{ beginAtZero:true } },
    },
});

// Chart 4: Orgs Pie
new Chart(document.getElementById('chartOrgs'),{
    type:'pie',
    data:{
        labels:<?php echo json_encode($ol); ?>,
        datasets:[{
            data:<?php echo json_encode($ov); ?>,
            backgroundColor:makeColors(<?php echo count($ol); ?>),
        }],
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{ position:'bottom' } },
    },
});
</script>