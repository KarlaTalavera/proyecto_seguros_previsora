<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Todas las Pólizas</h3>
    <button class="btn btn-primary" data-toggle="modal" data-target="#newPolicyModal">Crear Nueva Póliza</button>
  </div>

  <div class="card"><div class="card-body">
    <div class="form-inline mb-2">
      <select class="form-control mr-2" id="filterRamo"><option value="">Todos los Ramos</option><option>Personas</option><option>Automóvil</option><option>Patrimoniales</option></select>
      <select class="form-control mr-2" id="filterAseg"><option value="">Todos los Aseguradores</option><option>Juan García</option><option>Sara Molina</option></select>
      <select class="form-control mr-2" id="filterEstado"><option value="">Todos los Estados</option><option>Activa</option><option>Renovar</option><option>Cancelada</option></select>
      <button class="btn btn-secondary" id="applyFilters">Filtrar</button>
    </div>

    <table id="allPolicies" class="table table-bordered w-100">
      <thead><tr><th>ID</th><th>Ramo</th><th>Producto</th><th>Asegurador</th><th>Fecha Venc.</th><th>Prima</th><th>Acciones</th></tr></thead>
      <tbody>
        <tr><td>P-3001</td><td>Personas</td><td>RCV</td><td>Juan García</td><td>2025-12-01</td><td>$120</td>
          <td><button class="btn btn-sm btn-primary" data-action="editar" data-id="p-3001">Editar</button> <button class="btn btn-sm btn-outline-secondary" data-action="ver" data-id="p-3001">Ver</button></td></tr>
        <tr><td>P-3002</td><td>Automóvil</td><td>Comprehensive</td><td>Sara Molina</td><td>2025-08-15</td><td>$400</td>
          <td><button class="btn btn-sm btn-primary" data-action="editar" data-id="p-3002">Editar</button> <button class="btn btn-sm btn-outline-secondary" data-action="ver" data-id="p-3002">Ver</button></td></tr>
      </tbody>
    </table>
  </div></div>
</div>

<!-- Modal Crear Póliza -->
<div class="modal fade" id="newPolicyModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Crear Nueva Póliza</h5><button class="close" data-dismiss="modal">&times;</button></div>
  <div class="modal-body">
    <form id="policyForm">
      <div class="form-group"><label>Ramo</label><select class="form-control" name="ramo"><option>Personas</option><option>Automóvil</option><option>Patrimoniales</option></select></div>
      <div class="form-group"><label>Producto</label><input class="form-control" name="producto"></div>
      <div class="form-group"><label>Asegurador</label><input class="form-control" name="asegurador"></div>
      <div class="form-row"><div class="form-group col"><label>Prima</label><input class="form-control" name="prima"></div><div class="form-group col"><label>Vencimiento</label><input type="date" class="form-control" name="vencimiento"></div></div>
    </form>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="savePolicy" class="btn btn-primary">Crear</button></div>
</div></div></div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
\$(function(){
  if (\$.fn.DataTable) {\$('#allPolicies').DataTable(); }
  \$('#savePolicy').on('click', function(){ alert('Póliza creada (simulado).'); \$('#newPolicyModal').modal('hide'); });
  \$('#applyFilters').on('click', function(){ alert('Filtros aplicados (simulado).'); });
});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
