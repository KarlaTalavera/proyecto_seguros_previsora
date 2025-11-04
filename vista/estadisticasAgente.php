<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Mi Rendimiento</h3>
    <small class="text-muted">Última actualización: <?php echo date('Y-m-d'); ?></small>
  </div>

  <!-- KPIs -->
  <div class="row">
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 p-3">
        <div class="small text-muted">Primas Vendidas</div>
        <h3>$45,000</h3>
        <div class="progress mt-2" style="height:6px"><div class="progress-bar bg-success" style="width:60%"></div></div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 p-3">
        <div class="small text-muted">Comisiones Pendientes</div>
        <h3>$4,200</h3>
        <div class="progress mt-2" style="height:6px"><div class="progress-bar bg-warning" style="width:35%"></div></div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 p-3">
        <div class="small text-muted">Tasa de Renovación</div>
        <h3>66%</h3>
        <div class="progress mt-2" style="height:6px"><div class="progress-bar bg-info" style="width:66%"></div></div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 p-3">
        <div class="small text-muted">Cotizaciones</div>
        <h3>12</h3>
        <div class="progress mt-2" style="height:6px"><div class="progress-bar bg-primary" style="width:40%"></div></div>
      </div>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-lg-7">
      <div class="card p-3">
        <h6>Ventas vs Meta (últimos 6 meses)</h6>
        <canvas id="ventasMeta" height="150"></canvas>
      </div>
      <div class="card p-3 mt-3">
        <h6>Polizas por tipo de cliente</h6>
        <canvas id="polizasPorCliente" height="150"></canvas>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card p-3">
        <h6>Últimas Cotizaciones / Pólizas</h6>
        <table id="recentSales" class="table table-sm table-striped w-100">
          <thead><tr><th>ID</th><th>Producto</th><th>Cliente</th><th>Estado</th></tr></thead>
          <tbody>
            <tr><td>Q-1001</td><td>RCV</td><td>María López</td><td><span class="badge badge-success">Aceptada</span></td></tr>
            <tr><td>P-2005</td><td>Combinado Residencial</td><td>Empresa XYZ</td><td><span class="badge badge-warning">Pendiente</span></td></tr>
            <tr><td>Q-1008</td><td>Automóvil</td><td>Carlos Pérez</td><td><span class="badge badge-secondary">Rechazada</span></td></tr>
            <tr><td>P-2010</td><td>RCV</td><td>Ana Ruiz</td><td><span class="badge badge-success">Activa</span></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
\$(function(){
  if (\$.fn.DataTable) {\$('#recentSales').DataTable({ pageLength:5, lengthChange:false });}
  const ctx = document.getElementById('ventasMeta').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['May','Jun','Jul','Aug','Sep','Oct'],
      datasets: [{label:'Ventas', data:[8,12,15,10,20,18], borderColor:'#1f3b73', backgroundColor:'rgba(31,59,115,0.08)', tension:0.3},
                 {label:'Meta', data:[12,12,12,12,12,12], borderColor:'#6c757d', borderDash:[5,5], fill:false}]
    },
    options:{plugins:{legend:{position:'bottom'}}}
  });
  new Chart(document.getElementById('polizasPorCliente'), {
    type:'doughnut',
    data:{labels:['Individual','Pyme','Corporativo'], datasets:[{data:[55,30,15], backgroundColor:['#7aa2d6','#28a745','#c79f2a']}]},
    options:{plugins:{legend:{position:'bottom'}}}
  });
});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>