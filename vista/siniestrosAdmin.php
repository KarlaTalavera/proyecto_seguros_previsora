<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between mb-3">
    <h3>Gestión de Siniestros</h3>
    <button class="btn btn-primary" data-toggle="modal" data-target="#newClaimModal">Añadir Nuevo Siniestro</button>
  </div>

  <div class="card"><div class="card-body">
    <table id="claimsTable" class="table table-striped w-100">
      <thead><tr><th>ID</th><th>Póliza</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <tr><td>6001</td><td>P-3001</td><td>2025-07-01</td><td>Accidente</td><td><span class="badge badge-warning">Abierto</span></td>
          <td><button class="btn btn-sm btn-info" data-action="ver" data-id="6001">Ver</button> <button class="btn btn-sm btn-success" data-action="registrar-pago" data-id="6001">Registrar Pago</button></td></tr>
        <tr><td>6004</td><td>P-3002</td><td>2025-03-15</td><td>Robo</td><td><span class="badge badge-secondary">En Revisión</span></td>
          <td><button class="btn btn-sm btn-info" data-action="ver" data-id="6004">Ver</button> <button class="btn btn-sm btn-success" data-action="registrar-pago" data-id="6004">Registrar Pago</button></td></tr>
      </tbody>
    </table>
  </div></div>
</div>

<!-- Modal Nuevo Siniestro y Registrar Pago (simulado) -->
<div class="modal fade" id="newClaimModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Añadir Nuevo Siniestro</h5><button class="close" data-dismiss="modal">&times;</button></div>
  <div class="modal-body">
    <form id="claimForm">
      <div class="form-group"><label>Póliza</label><input class="form-control" name="poliza"></div>
      <div class="form-group"><label>Tipo</label><input class="form-control" name="tipo"></div>
      <div class="form-group"><label>Fecha</label><input type="date" class="form-control" name="fecha"></div>
      <div class="form-group"><label>Descripción</label><textarea class="form-control" name="desc"></textarea></div>
    </form>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="saveClaim" class="btn btn-primary">Guardar</button></div>
</div></div></div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
\$(function(){ if (\$.fn.DataTable){ \$('#claimsTable').DataTable(); } \$('#saveClaim').on('click', function(){ alert('Siniestro creado (simulado).'); \$('#newClaimModal').modal('hide'); }); });
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
