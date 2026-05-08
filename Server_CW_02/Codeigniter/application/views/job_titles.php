<?php
$all_titles  = $job_titles['all_time']     ?? array();
$curr_titles = $job_titles['current_only'] ?? array();
?>

<div style="text-align:right;margin-bottom:16px;">
    <a href="<?php echo site_url('analytics/export/job-titles'); ?>"
       class="btn btn-success">⬇ Export CSV</a>
</div>

<div class="chart-grid">

    <!-- All Time (Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>🏷️ Most Common Job Titles (All Time)</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartAllTitles"></canvas>
            </div>
        </div>
    </div>

    <!-- Current Only (Doughnut) -->
    <div class="card">
        <div class="card-header">
            <h3>🏷️ Current Job Titles</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartCurrTitles"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Table -->
<div class="card">
    <div class="card-header"><h3>📋 Job Title Details</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th><th>Job Title</th>
                    <th>Total</th><th>Currently in Role</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach ($all_titles as $t):
                    $t=(object)$t; ?>
                <tr>
                    <td style="color:#888;"><?php echo $i++; ?></td>
                    <td style="font-weight:600;">
                        <?php echo htmlspecialchars($t->job_title); ?>
                    </td>
                    <td><?php echo $t->count; ?></td>
                    <td>
                        <span class="badge badge-green">
                            <?php echo $t->current_count; ?>
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
    '#3F51B5','#009688','#CDDC39','#FFC107','#E91E63',
];
function makeColors(n){
    return Array.from({length:n},(_,i)=>COLORS[i%COLORS.length]);
}

<?php
$al=array_map(function($t){
    return addslashes(is_object($t)?$t->job_title:$t['job_title']);
},(array)$all_titles);
$av=array_map(function($t){
    return is_object($t)?$t->count:$t['count'];
},(array)$all_titles);

$cl=array_map(function($t){
    return addslashes(is_object($t)?$t->job_title:$t['job_title']);
},(array)$curr_titles);
$cv=array_map(function($t){
    return is_object($t)?$t->count:$t['count'];
},(array)$curr_titles);
?>

new Chart(document.getElementById('chartAllTitles'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($al); ?>,
        datasets:[{
            label:'Alumni',
            data:<?php echo json_encode($av); ?>,
            backgroundColor:makeColors(<?php echo count($al); ?>),
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

new Chart(document.getElementById('chartCurrTitles'),{
    type:'doughnut',
    data:{
        labels:<?php echo json_encode($cl); ?>,
        datasets:[{
            data:<?php echo json_encode($cv); ?>,
            backgroundColor:makeColors(<?php echo count($cl); ?>),
        }],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        cutout:'50%',
        plugins:{legend:{position:'bottom',labels:{font:{size:10}}}},
    },
});
</script>