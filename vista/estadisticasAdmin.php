<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Dashboard Gerencial</h3>

  <div class="row mt-3">
    <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Siniestralidad Global</div><h4>7.2%</h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Primas Suscritas</div><h4>$1,250,000</h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Agentes Activos</div><h4>128</h4></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Siniestros Abiertos</div><h4>42</h4></div></div>
  </div>

  <div class="row mt-4">
    <div class="col-lg-6"><div class="card p-3"><h6>Pólizas por Ramo</h6><canvas id="ramoChart"></canvas></div></div>
    <div class="col-lg-6"><div class="card p-3"><h6>Tendencia de Siniestros (últimos 12 meses)</h6><canvas id="siniestrosTrend"></canvas></div></div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
\$(function(){
  new Chart(document.getElementById('ramoChart'), {
    type:'pie',
    data:{labels:['Personas','Automóvil','Patrimoniales'], datasets:[{data:[540,320,140], backgroundColor:['#007bff','#28a745','#ffc107']}]},
    options:{plugins:{legend:{position:'bottom'}}}
  });
  new Chart(document.getElementById('siniestrosTrend'), {
    type:'line',
    data:{labels:['Nov','Dec','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct'], datasets:[{label:'Siniestros', data:[30,28,35,40,38,44,50,45,42,41,39,42], borderColor:'#dc3545', tension:0.3}]},
    options:{plugins:{legend:{display:false}}}
  });
});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
