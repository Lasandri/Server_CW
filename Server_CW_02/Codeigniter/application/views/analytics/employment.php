<?php
$by_company   = $employment['by_company']      ?? array();
$curr_vs_past = $employment['current_vs_past'] ?? array();
$jobs_by_year = $employment['jobs_by_year']    ?? array();
?>

<div style="text-align:right;margin-bottom:16px;">
    <a href="<?php echo site_url('analytics/export/employers'); ?>"
       class="btn btn-success">⬇ Export CSV</a>
</div>

<div class="chart-grid">

    <!-- Employment by Company (Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>💼 Top Companies Employing Alumni</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartByCompany"></canvas>
            </div>
        </div>
    </div>

    <!-- Current vs Past (Doughnut) -->
    <div class="card">
        <div class="card-header">
            <h3>📊 Current vs Past Roles</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartCurrPast"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Jobs by Year (Line) -->
<div class="card">
    <div class="card-header">
        <h3>📈 Employment Growth by Year</h3>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="chartByYear"></canvas>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header"><h3>📋 Company Details</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th><th>Company</th>
                    <th>Total Alumni</th><th>Currently Working</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach ($by_company as $c):
                    $c=(object)$c; ?>
                <tr>
                    <td style="color:#888;"><?php echo $i++; ?></td>
                    <td style="font-weight:600;">
                        <?php echo htmlspecialchars($c->company_name); ?>
                    </td>
                    <td><?php echo $c->employee_count; ?></td>
                    <td>
                        <span class="badge badge-green">
                            <?php echo $c->current_count; ?> current
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const COLORS=[
    '#1565C0','#2196F3','#4CAF50','#FF9800','#9C27B0',
    '#F44336','#00BCD4','#FF5722','#607D8B','#795548',
];
function makeColors(n){
    return Array.from({length:n},(_,i)=>COLORS[i%COLORS.length]);
}

<?php
$cl=array_map(function($c){
    return addslashes(is_object($c)?$c->company_name:$c['company_name']);
},(array)$by_company);
$cv=array_map(function($c){
    return is_object($c)?$c->employee_count:$c['employee_count'];
},(array)$by_company);

$cp = (object)($curr_vs_past ?? new stdClass);
$curr = isset($cp->current_jobs) ? $cp->current_jobs : 0;
$past = isset($cp->past_jobs)    ? $cp->past_jobs    : 0;

$yl=array_map(function($y){
    return is_object($y)?$y->year:$y['year'];
},(array)$jobs_by_year);
$yv=array_map(function($y){
    return is_object($y)?$y->count:$y['count'];
},(array)$jobs_by_year);
?>

new Chart(document.getElementById('chartByCompany'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode(array_slice($cl,0,15)); ?>,
        datasets:[{
            label:'Alumni',
            data:<?php echo json_encode(array_slice($cv,0,15)); ?>,
            backgroundColor:makeColors(15),
            borderRadius:4,
        }],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        indexAxis:'y',
        plugins:{legend:{display:false}},
        scales:{x:{beginAtZero:true}},
    },
});

new Chart(document.getElementById('chartCurrPast'),{
    type:'doughnut',
    data:{
        labels:['Currently Working','Past Roles'],
        datasets:[{
            data:[<?php echo $curr; ?>,<?php echo $past; ?>],
            backgroundColor:['#4CAF50','#e0e0e0'],
            borderWidth:2,
        }],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        cutout:'60%',
        plugins:{legend:{position:'bottom'}},
    },
});

new Chart(document.getElementById('chartByYear'),{
    type:'line',
    data:{
        labels:<?php echo json_encode($yl); ?>,
        datasets:[{
            label:'New Jobs Started',
            data:<?php echo json_encode($yv); ?>,
            borderColor:'#1565C0',
            backgroundColor:'rgba(21,101,192,0.1)',
            fill:true,tension:0.4,
            pointRadius:5,
            pointBackgroundColor:'#1565C0',
        }],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        scales:{y:{beginAtZero:true}},
    },
});
</script>