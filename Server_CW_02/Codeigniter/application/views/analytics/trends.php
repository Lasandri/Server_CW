<?php
$monthly_certs   = $trends['monthly_certifications'] ?? array();
$monthly_courses = $trends['monthly_courses']        ?? array();
$programmes      = $trends['degree_programmes']      ?? array();
?>

<div class="chart-grid">

    <!-- Monthly Certs (Line) -->
    <div class="card">
        <div class="card-header">
            <h3>📈 Monthly Certification Completions (24 months)</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartMonthlyCerts"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Courses (Line) -->
    <div class="card">
        <div class="card-header">
            <h3>📚 Monthly Course Completions (24 months)</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartMonthlyCourses"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Combined Trend -->
<div class="card">
    <div class="card-header">
        <h3>📊 Certifications vs Courses — Combined Trend</h3>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="chartCombined"></canvas>
        </div>
    </div>
</div>

<!-- Degree Programmes (Radar) -->
<div class="chart-grid">

    <div class="card">
        <div class="card-header">
            <h3>🎓 Alumni by Degree Programme</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartProgrammes"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>📋 Programme Details</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <table class="table">
                <thead>
                    <tr><th>Programme</th><th>Alumni</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($programmes as $p):
                        $p=(object)$p; ?>
                    <tr>
                        <td><?php echo htmlspecialchars(
                            $p->programme ?? ''); ?></td>
                        <td>
                            <strong>
                                <?php echo $p->alumni_count ?? 0; ?>
                            </strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
    return is_object($c)?$c->month:$c['month'];
},(array)$monthly_certs);
$cv=array_map(function($c){
    return is_object($c)?$c->certifications_count:$c['certifications_count'];
},(array)$monthly_certs);

$corl=array_map(function($c){
    return is_object($c)?$c->month:$c['month'];
},(array)$monthly_courses);
$corv=array_map(function($c){
    return is_object($c)?$c->courses_count:$c['courses_count'];
},(array)$monthly_courses);

$pl=array_map(function($p){
    return addslashes(is_object($p)?$p->programme:$p['programme']);
},(array)$programmes);
$pv=array_map(function($p){
    return is_object($p)?$p->alumni_count:$p['alumni_count'];
},(array)$programmes);
?>

// Chart 1: Monthly Certs
new Chart(document.getElementById('chartMonthlyCerts'),{
    type:'line',
    data:{
        labels:<?php echo json_encode($cl); ?>,
        datasets:[{
            label:'Certifications',
            data:<?php echo json_encode($cv); ?>,
            borderColor:'#1565C0',
            backgroundColor:'rgba(21,101,192,0.1)',
            fill:true,tension:0.4,pointRadius:3,
            pointBackgroundColor:'#1565C0',
        }],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        scales:{
            y:{beginAtZero:true},
            x:{ticks:{font:{size:10}}},
        },
    },
});

// Chart 2: Monthly Courses
new Chart(document.getElementById('chartMonthlyCourses'),{
    type:'line',
    data:{
        labels:<?php echo json_encode($corl); ?>,
        datasets:[{
            label:'Courses',
            data:<?php echo json_encode($corv); ?>,
            borderColor:'#4CAF50',
            backgroundColor:'rgba(76,175,80,0.1)',
            fill:true,tension:0.4,pointRadius:3,
            pointBackgroundColor:'#4CAF50',
        }],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        scales:{
            y:{beginAtZero:true},
            x:{ticks:{font:{size:10}}},
        },
    },
});

// Chart 3: Combined
new Chart(document.getElementById('chartCombined'),{
    type:'line',
    data:{
        labels:<?php echo json_encode($cl); ?>,
        datasets:[
            {
                label:'Certifications',
                data:<?php echo json_encode($cv); ?>,
                borderColor:'#1565C0',
                backgroundColor:'rgba(21,101,192,0.05)',
                fill:true,tension:0.4,pointRadius:3,
            },
            {
                label:'Courses',
                data:<?php echo json_encode($corv); ?>,
                borderColor:'#4CAF50',
                backgroundColor:'rgba(76,175,80,0.05)',
                fill:true,tension:0.4,pointRadius:3,
            },
        ],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        scales:{
            y:{beginAtZero:true},
            x:{ticks:{font:{size:10}}},
        },
        plugins:{
            legend:{position:'top'},
        },
    },
});

// Chart 4: Programmes (Bar)
new Chart(document.getElementById('chartProgrammes'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($pl); ?>,
        datasets:[{
            label:'Alumni',
            data:<?php echo json_encode($pv); ?>,
            backgroundColor:makeColors(<?php echo count($pl); ?>),
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