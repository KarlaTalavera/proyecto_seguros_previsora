<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Mis Reportes</h3>
  <div class="card mt-3"><div class="card-body">
    <form id="agentReportForm" class="form-inline">
      <label class="mr-2">Desde</label><input type="date" class="form-control mr-3" name="from">
      <label class="mr-2">Hasta</label><input type="date" class="form-control mr-3" name="to">
      <select class="form-control mr-3" name="type"><option>Reporte de Comisiones</option><option>Reporte de Vencimientos</option></select>
      <button class="btn btn-primary">Generar</button>
    </form>

    <div id="reportResults" class="mt-4" style="display:none;">
      <h6>Resultado (simulado)</h6>
      <table class="table table-sm"><thead><tr><th>Fecha</th><th>Detalle</th><th>Monto</th></tr></thead><tbody><tr><td>2025-07-01</td><td>Comisión Julio</td><td>$1,200</td></tr></tbody></table>
    </div>
  </div></div>
</div>

<?php
$extra_scripts = <<<EOT
<script>
\$(function(){
  \$('#agentReportForm').on('submit', function(e){ e.preventDefault(); \$('#reportResults').show(); });
});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
