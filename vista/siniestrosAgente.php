<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';

$modeloSiniestro = new ModeloSiniestro();

?>



<div class="container-fluid">
  <div class="d-flex justify-content-between mb-3">
    <h3>Siniestros Reportados</h3>
    <button class="btn btn-primary" data-toggle="modal" data-target="#reportClaimModal">Reportar Siniestro</button>
  </div>

  <div class="card"><div class="card-body">
    <table id="agentClaims" class="table table-bordered w-100">
      <thead><tr><th>ID</th><th>Póliza</th><th>Tipo</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <tr><td>6001</td><td>2001</td><td>Accidente</td><td>2025-07-01</td><td><span class="badge badge-warning">Abierto</span></td>
          <td><button class="btn btn-sm btn-outline-info" data-action="ver" data-id="6001">Ver</button> <button class="btn btn-sm btn-outline-primary" data-action="editar" data-id="6001">Actualizar</button></td></tr>
        <tr><td>6002</td><td>2002</td><td>Robo</td><td>2025-03-15</td><td><span class="badge badge-secondary">En Revisión</span></td>
          <td><button class="btn btn-sm btn-outline-info" data-action="ver" data-id="6002">Ver</button> <button class="btn btn-sm btn-outline-primary" data-action="editar" data-id="6002">Actualizar</button></td></tr>
        <tr><td>6003</td><td>2003</td><td>Incendio</td><td>2024-11-01</td><td><span class="badge badge-success">Cerrado</span></td>
          <td><button class="btn btn-sm btn-outline-info" data-action="ver" data-id="6003">Ver</button> <button class="btn btn-sm btn-outline-primary" data-action="registrar-pago" data-id="6003">Registrar Pago</button></td></tr>
      </tbody>
    </table>
  </div></div>
</div>

<!-- Modal Report -->
<div class="modal fade" id="reportClaimModal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Reportar Siniestro</h5><button class="close" data-dismiss="modal">&times;</button></div>
  <div class="modal-body"><form id="reportForm"><div class="form-group"><label>Póliza asociada</label><input class="form-control" name="poliza"></div><div class="form-group"><label>Tipo</label><input class="form-control" name="tipo"></div><div class="form-group"><label>Descripción</label><textarea class="form-control" name="desc"></textarea></div><div class="form-row"><div class="form-group col"><label>Fecha</label><input type="date" class="form-control" name="fecha"></div><div class="form-group col"><label>Lugar</label><input class="form-control" name="lugar"></div></div></form></div>
  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="sendReport" class="btn btn-primary">Reportar</button></div>
</div></div></div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
\$(function(){ if (\$.fn.DataTable) {\$('#agentClaims').DataTable();} \$('#sendReport').on('click', function(){ alert('Siniestro reportado (simulado).'); \$('#reportClaimModal').modal('hide'); }); });
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
