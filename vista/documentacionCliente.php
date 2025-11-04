<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Mi Cuenta y Documentación</h3>

  <ul class="nav nav-tabs mt-3" id="docTabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" id="polizas-tab" data-toggle="tab" href="#polizas" role="tab">Mis Pólizas</a></li>
    <li class="nav-item"><a class="nav-link" id="siniestros-tab" data-toggle="tab" href="#siniestros" role="tab">Mis Siniestros</a></li>
  </ul>
  <div class="tab-content mt-3">
    <div class="tab-pane fade show active" id="polizas" role="tabpanel">
      <table class="table table-striped">
        <thead><tr><th>Producto</th><th>Vencimiento</th><th>Prima</th><th>Acción</th></tr></thead>
        <tbody>
          <tr><td>RCV</td><td>2025-12-01</td><td>$120</td><td><button class="btn btn-sm btn-outline-secondary" data-action="pdf" data-id="pcliente-2001">Descargar PDF</button></td></tr>
          <tr><td>Combinado Residencial</td><td>2026-02-14</td><td>$450</td><td><button class="btn btn-sm btn-outline-secondary" data-action="pdf" data-id="pcliente-2003">Descargar PDF</button></td></tr>
        </tbody>
      </table>
    </div>

    <div class="tab-pane fade" id="siniestros" role="tabpanel">
      <table class="table table-striped">
        <thead><tr><th>Fecha</th><th>Estado</th><th>Monto Estimado</th></tr></thead>
        <tbody>
          <tr><td>2025-07-01</td><td><span class="badge badge-warning">Abierto</span></td><td>$2,400</td></tr>
          <tr><td>2024-11-01</td><td><span class="badge badge-success">Cerrado</span></td><td>$0</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<script>
\$(function(){
  // tabs funcionan con Bootstrap incluido en layout
});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
</body></html>
