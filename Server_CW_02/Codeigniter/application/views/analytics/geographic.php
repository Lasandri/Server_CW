<?php
$by_country  = $geographic['by_country']          ?? array();
$by_city     = $geographic['by_city']             ?? array();
$uk_vs_intl  = $geographic['uk_vs_international'] ?? array();
?>

<div style="text-align:right;margin-bottom:16px;">
    <a href="<?php echo site_url('analytics/export/geographic'); ?>"
       class="btn btn-success">⬇ Export CSV</a>
</div>

<div class="chart-grid">

    <!-- By Country (Bar) -->
    <div class="card">
        <div class="card-header">
            <h3>🌍 Alumni by Country</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartCountry"></canvas>
            </div>
        </div>
    </div>

    <!-- UK vs International (Pie) -->
    <div class="card">
        <div class="card-header">
            <h3>🗺️ UK vs International</h3>
        </div>
        <div class="card-body">
            <div class="chart-container-tall">
                <canvas id="chartUkIntl"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- By City (Bar) -->
<div class="card">
    <div class="card-header">
        <h3>🏙️ Top Cities</h3>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="chartCity"></canvas>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header"><h3>📋 Country Details</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr><th>#</th><th>Country</th><th>Alumni</th></tr>
            </thead>
            <tbody>
                <?php $i=1; foreach ($by_country as $c):
                    $c=(object)$c; ?>
                <tr>
                    <td style="color:#888;"><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($c->country); ?></td>
                    <td><strong><?php echo $c->count; ?></strong></td>
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
$cntl=array_map(function($c){
    return addslashes(is_object($c)?$c->country:$c['country']);
},(array)$by_country);
$cntv=array_map(function($c){
    return is_object($c)?$c->count:$c['count'];
},(array)$by_country);

$cityl=array_map(function($c){
    $city = is_object($c)?$c->city:$c['city'];
    $country = is_object($c)?($c->country??''):($c['country']??'');
    return addslashes($city.($country?', '.$country:''));
},(array)$by_city);
$cityv=array_map(function($c){
    return is_object($c)?$c->count:$c['count'];
},(array)$by_city);

$ukl=array_map(function($u){
    return is_object($u)?$u->region:$u['region'];
},(array)$uk_vs_intl);
$ukv=array_map(function($u){
    return is_object($u)?$u->count:$u['count'];
},(array)$uk_vs_intl);
?>

new Chart(document.getElementById('chartCountry'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode(array_slice($cntl,0,12)); ?>,
        datasets:[{
            label:'Alumni',
            data:<?php echo json_encode(array_slice($cntv,0,12)); ?>,
            backgroundColor:makeColors(12),
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

new Chart(document.getElementById('chartUkIntl'),{
    type:'pie',
    data:{
        labels:<?php echo json_encode($ukl); ?>,
        datasets:[{
            data:<?php echo json_encode($ukv); ?>,
            backgroundColor:['#1565C0','#4CAF50','#FF9800'],
        }],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        plugins:{legend:{position:'bottom'}},
    },
});

new Chart(document.getElementById('chartCity'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode(array_slice($cityl,0,10)); ?>,
        datasets:[{
            label:'Alumni',
            data:<?php echo json_encode(array_slice($cityv,0,10)); ?>,
            backgroundColor:'#2196F3',
            borderRadius:4,
        }],
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true}},
    },
});
</script>