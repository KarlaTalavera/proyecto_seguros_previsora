<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Generación de Reportes</h3>
  <div class="card mt-3"><div class="card-body">
    <form id="reportForm">
      <div class="form-row">
        <div class="form-group col-md-3"><label>Desde</label><input type="date" class="form-control" name="from"></div>
        <div class="form-group col-md-3"><label>Hasta</label><input type="date" class="form-control" name="to"></div>
        <div class="form-group col-md-3"><label>Asegurador</label><select class="form-control" name="agente"><option>Todos</option></select></div>
        <div class="form-group col-md-3"><label>Ramo</label><select class="form-control" name="ramo"><option>Todos</option></select></div>
      </div>
      <div class="d-flex justify-content-between mt-3">
        <div><button id="genAudit" class="btn btn-outline-primary">Reporte de Auditoría</button> <button id="genFinance" class="btn btn-outline-success">Reporte Financiero Global</button></div>
        <button id="genCustom" class="btn btn-primary">Generar Reporte</button>
      </div>
    </form>
  </div></div>
</div>

<?php
$extra_scripts = <<<EOT
<script>\$(function(){ \$('#reportForm button').on('click', function(e){ e.preventDefault(); alert('Generando reporte (simulado): ' + \$(this).text()); }); });</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
