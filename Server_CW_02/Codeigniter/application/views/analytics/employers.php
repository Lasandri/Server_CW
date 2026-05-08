<?php $employers_list = $employers['top_employers'] ?? array(); ?>

<div style="text-align:right;margin-bottom:16px;">
    <a href="<?php echo site_url('analytics/export/employers'); ?>"
       class="btn btn-success">⬇ Export CSV</a>
</div>

<div class="chart-grid">

    <!-- Bar Chart -->
    <div class="card">
        <div class="card-header">
            <h3>🏢 Top Employers by Alumni Count</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartEmployers"></canvas>
            </div>
        </div>
    </div>

    <!-- Current Employees (Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>👥 Current Alumni at Top Employers</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartCurrent"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Table -->
<div class="card">
    <div class="card-header"><h3>📋 Employer Details</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th><th>Company</th>
                    <th>Alumni Count</th><th>Total Roles</th>
                    <th>Currently Employed</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach ($employers_list as $e):
                    $e=(object)$e; ?>
                <tr>
                    <td style="color:#888;"><?php echo $i++; ?></td>
                    <td style="font-weight:600;">
                        <?php echo htmlspecialchars($e->company_name); ?>
                    </td>
                    <td>
                        <strong><?php echo $e->alumni_count; ?></strong>
                    </td>
                    <td><?php echo $e->total_roles; ?></td>
                    <td>
                        <span class="badge badge-green">
                            <?php echo $e->current_employees; ?>
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
$el=array_map(function($e){
    return addslashes(is_object($e)?$e->company_name:$e['company_name']);
},(array)$employers_list);
$ev=array_map(function($e){
    return is_object($e)?$e->alumni_count:$e['alumni_count'];
},(array)$employers_list);
$ecv=array_map(function($e){
    return is_object($e)?$e->current_employees:$e['current_employees'];
},(array)$employers_list);
?>

new Chart(document.getElementById('chartEmployers'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($el); ?>,
        datasets:[{
            label:'Total Alumni',
            data:<?php echo json_encode($ev); ?>,
            backgroundColor:makeColors(<?php echo count($el); ?>),
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

new Chart(document.getElementById('chartCurrent'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($el); ?>,
        datasets:[{
            label:'Currently Employed',
            data:<?php echo json_encode($ecv); ?>,
            backgroundColor:'#4CAF50',
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
</script>