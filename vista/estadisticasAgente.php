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
        <div class="small text-muted">Pólizas</div>
        <h3 id="kpi_polizas">—</h3>
        <div class="small text-muted">Total de pólizas registradas por usted</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 p-3">
        <div class="small text-muted">Prima Suscrita</div>
        <h3 id="kpi_prima">—</h3>
        <div class="small text-muted">Suma de primas asociadas a sus pólizas</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 p-3">
  <div class="small text-muted">Cuotas Vencidas</div>
  <h3 id="kpi_cartera">—</h3>
  <div class="small text-muted">Monto vencido de cuotas asignado a sus pólizas</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 p-3">
        <div class="small text-muted">Nuevas (12m)</div>
        <h3 id="kpi_nuevas">—</h3>
        <div class="small text-muted">Pólizas nuevas en los últimos 12 meses</div>
      </div>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-lg-7">
      <div class="card p-3">
        <h6>Ventas vs Meta (últimos 6 meses)</h6>
        <canvas id="ventasMeta" height="150"></canvas>
      </div>
      <div class="card p-3 mt-3 text-center">
        <h6>Pólizas por tipo de cliente (Natural vs Jurídico)</h6>
        <div style="max-width:300px;margin:0 auto;">
          <canvas id="polizasPorCliente" height="120" style="display:block;margin:0 auto;max-width:300px;"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card p-3">
        <h6>Últimas Cotizaciones / Pólizas</h6>
        <div class="table-responsive">
          <table class="table table-sm" id="polizasVencerTableAgent">
            <thead><tr><th>#</th><th>Póliza</th><th>Agente</th><th>Vence</th><th>Prima</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="card p-3 mt-3">
        <h6>Siniestros gestionados (últimos 12 meses)</h6>
        <canvas id="siniestrosGestionados" height="140"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
$agentCedula = isset($_SESSION['datos_usuario']) ? $_SESSION['datos_usuario']->getCedula() : '';

$extra_scripts = <<<EOT
<script>
\$(function(){
  console.log('estadisticasAgente: extra scripts loaded');
  // We'll initialize DataTable after we populate rows so it renders correctly.
  // Los gráficos se dibujan únicamente si las peticiones R4/R8 devuelven datos. Evitamos mostrar datos demo.
  const palette = ['#d3b8ff','#a98fff','#7e61ff','#513dff','#2d1aff'];
  
  // Cargar reportes: R1 (recientes), tipo cliente, ventas por mes y KPIs del agente
  // include agent cedula explicitly to ensure backend filters correctly even if session parsing fails in AJAX
  $.getJSON('controlador/controladorReporte.php', { accion: 'r1', dias: 30, cedula_agente: '$agentCedula' }, function(res){
    const table = $('#polizasVencerTableAgent');
    const tbody = table.find('tbody');
    // Clear any existing DataTable instance so we can repopulate safely
    if ($.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
      table.DataTable().clear().destroy();
    }
    tbody.empty();
    if (res.success && res.data && res.data.length) {
      res.data.forEach(function(row, idx){
        const agente = (row.nombre_agente ? (row.nombre_agente + ' ' + row.apellido_agente) : (row.cedula_agente||'') );
        tbody.append('<tr><td>' + (idx+1) + '</td><td>' + (row.numero_poliza||'') + '</td><td>' + agente + '</td><td>' + (row.fecha_fin||'') + '</td><td>' + (row.monto_prima||'') + '</td></tr>');
      });
    } else {
      console.debug('R1 response', res);
      tbody.append('<tr><td colspan="5" class="text-center text-muted">No hay cotizaciones/pólizas recientes.</td></tr>');
    }
    // Initialize DataTable after rows are present
    if ($.fn.DataTable) {
      table.DataTable({ pageLength:5, lengthChange:false });
    }
  }).fail(function(){
    const table = $('#polizasVencerTableAgent');
    if ($.fn.DataTable && $.fn.DataTable.isDataTable(table)) { table.DataTable().clear().destroy(); }
    table.find('tbody').empty().append('<tr><td colspan="5" class="text-center text-danger">Error cargando recientes.</td></tr>');
    if ($.fn.DataTable) { table.DataTable({ pageLength:5, lengthChange:false }); }
  });
  // Tipo de cliente (natural vs juridico) para este agente
  $.getJSON('controlador/controladorReporte.php', { accion: 'r_tipo_cliente' }, function(res){
    if (res.success && res.data) {
      const rows = res.data;
      if (!rows.length) {
        $('#polizasPorCliente').parent().append('<div class="text-center text-muted small mt-2">No hay datos.</div>');
        return;
      }
      const labels = rows.map(r => r.tipo_cliente);
      const values = rows.map(r => parseInt(r.total||0));
      new Chart(document.getElementById('polizasPorCliente'), {
        type: 'doughnut',
        data: { labels: labels, datasets:[{data: values, backgroundColor:[palette[1], palette[3]]}] },
        options: { plugins:{legend:{position:'bottom'}} }
      });
    } else {
      $('#polizasPorCliente').parent().append('<div class="text-center text-muted small mt-2">No hay datos de clientes.</div>');
    }
  }).fail(function(){
    $('#polizasPorCliente').parent().append('<div class="text-center text-danger small mt-2">Error cargando clientes.</div>');
  });

  // Ventas por mes (agent) -> mostrar ventas vs meta (meta = avg * 1.2 si hay historial)
  $.getJSON('controlador/controladorReporte.php', { accion: 'r_agente_ventas', months: 6 }, function(res){
    if (res.success && res.data && res.data.labels) {
      const labels = res.data.labels;
      const sales = res.data.data.map(v => parseFloat(v||0));
      const avg = sales.length ? (sales.reduce((a,b)=>a+b,0)/sales.length) : 0;
      const metaVal = Math.round(avg * 1.2) || 1000; // si no hay datos, usar 1000 como referencia
      const meta = labels.map(()=>metaVal);
      new Chart(document.getElementById('ventasMeta'), {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            { label: 'Ventas', data: sales, backgroundColor: palette[4] },
            { label: 'Meta', data: meta, type: 'line', borderColor: palette[2], borderWidth:2, fill:false, tension:0.2 }
          ]
        },
        options: { plugins:{legend:{position:'bottom'}}, scales:{y:{beginAtZero:true}} }
      });
    } else {
      $('#ventasMeta').parent().append('<div class="text-center text-muted small mt-2">No hay datos de ventas.</div>');
    }
  }).fail(function(){
    $('#ventasMeta').parent().append('<div class="text-center text-danger small mt-2">Error cargando ventas.</div>');
  });

  // KPIs del agente
  $.getJSON('controlador/controladorReporte.php', { accion: 'kpis_agente' }, function(res){
    if (res.success && res.data) {
      const d = res.data;
      $('#kpi_polizas').text(d.polizas_count||0);
      $('#kpi_prima').text(new Intl.NumberFormat('es-BO',{style:'currency',currency:'USD',maximumFractionDigits:2}).format(d.prima_suscrita||0));
      $('#kpi_cartera').text(new Intl.NumberFormat('es-BO',{style:'currency',currency:'USD',maximumFractionDigits:2}).format(d.cartera_pendiente||0));
      $('#kpi_nuevas').text(d.nuevas_12m||0);
    }
  }).fail(function(){
    // silencioso: dejar los placeholders
  });

  // Siniestros gestionados por el agente (tendencia)
  $.getJSON('controlador/controladorReporte.php', { accion: 'r_siniestros', months: 12 }, function(res){
    if (res.success && res.data && res.data.labels) {
      const labels = res.data.labels;
      const values = res.data.data.map(v => parseInt(v||0));
      const ctx = document.getElementById('siniestrosGestionados').getContext('2d');
      const grad = ctx.createLinearGradient(0,0,0,140);
      grad.addColorStop(0, 'rgba(125,81,255,0.45)');
      grad.addColorStop(1, 'rgba(125,81,255,0.05)');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Siniestros',
            data: values,
            backgroundColor: grad,
            borderColor: palette[2],
            fill: true,
            tension: 0.3,
            pointRadius: 3
          }]
        },
        options: {
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, precision:0 } }
        }
      });
    } else {
      $('#siniestrosGestionados').parent().append('<div class="text-center text-muted small mt-2">No hay datos de siniestros.</div>');
    }
  }).fail(function(){
    $('#siniestrosGestionados').parent().append('<div class="text-center text-danger small mt-2">Error cargando siniestros.</div>');
  });

});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>