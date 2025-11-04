<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>
<div class="container-fluid">
  <h3>Mis Pólizas</h3>
  <div class="card"><div class="card-body">
    <table id="myPolicies" class="table table-bordered w-100">
      <thead><tr><th>Producto</th><th>Vencimiento</th><th>Prima</th><th>Acciones</th></tr></thead>
      <tbody><tr><td>Combinado Residencial</td><td>2026-01-10</td><td>$350</td><td><button class="btn btn-sm btn-outline-primary download">Descargar PDF</button></td></tr></tbody>
    </table>
  </div></div>
</div>
<?php
$extra_scripts = <<<EOT
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script>\$(function(){ if (\$.fn.DataTable) {\$('#myPolicies').DataTable();} \$(document).on('click','.download', function(){ alert('Generando PDF (simulado)...'); }); });</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
</body></html>
