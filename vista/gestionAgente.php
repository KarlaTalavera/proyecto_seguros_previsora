<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Aseguradores</h1>
    <button id="btnNewAgent" class="btn btn-primary">Registrar Nuevo Asegurador</button>
  </div>

  <div class="card shadow mb-4"><div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="agentsTable" width="100%">
        <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Activo</th><th>Acciones</th></tr></thead>
        <tbody>
          <tr><td>A-100</td><td>Juan García</td><td>juan@seg.com</td><td><span class="badge badge-success">Sí</span></td>
            <td>
              <button class="btn btn-sm btn-primary" data-action="editar" data-id="a-100">Editar</button>
              <button class="btn btn-sm btn-danger" data-action="eliminar" data-id="a-100">Eliminar</button>
              <button class="btn btn-sm btn-outline-secondary managePermBtn" data-action="gestionar-permisos" data-id="a-100">Gestionar Permisos</button>
            </td></tr>
          <tr><td>A-101</td><td>Sara Molina</td><td>sara@seg.com</td><td><span class="badge badge-success">Sí</span></td>
            <td><button class="btn btn-sm btn-primary" data-action="editar" data-id="a-101">Editar</button> <button class="btn btn-sm btn-danger" data-action="eliminar" data-id="a-101">Eliminar</button> <button class="btn btn-sm btn-outline-secondary managePermBtn" data-action="gestionar-permisos" data-id="a-101">Gestionar Permisos</button></td></tr>
          <tr><td>A-102</td><td>Carlos Vélez</td><td>carlos@seg.com</td><td><span class="badge badge-secondary">No</span></td>
            <td><button class="btn btn-sm btn-primary" data-action="editar" data-id="a-102">Editar</button> <button class="btn btn-sm btn-danger" data-action="eliminar" data-id="a-102">Eliminar</button> <button class="btn btn-sm btn-outline-secondary managePermBtn" data-action="gestionar-permisos" data-id="a-102">Gestionar Permisos</button></td></tr>
        </tbody>
      </table>
    </div>
  </div></div>
</div>

<!-- Modal Permisos -->
<div class="modal fade" id="permModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Configurar Permisos</h5><button class="close" data-dismiss="modal">&times;</button></div>
  <div class="modal-body">
    <form id="permForm">
      <input type="hidden" name="agentId" id="agentId">
      <div class="form-group form-check"><input type="checkbox" class="form-check-input" id="permReports" name="reports"><label class="form-check-label" for="permReports">Acceso a Reportes</label></div>
      <div class="form-group form-check"><input type="checkbox" class="form-check-input" id="permStats" name="stats"><label class="form-check-label" for="permStats">Acceso a Estadísticas</label></div>
      <div class="form-group form-check"><input type="checkbox" class="form-check-input" id="permOther" name="other"><label class="form-check-label" for="permOther">Otros Módulos</label></div>
    </form>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="savePerms" class="btn btn-primary">Guardar</button></div>
</div></div></div>

<!-- Modal: Registrar Nuevo Asegurador (nuevo) -->
<div class="modal fade" id="newAgentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Nuevo Asegurador</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="newAgentForm" novalidate>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Nombre</label><input class="form-control" name="name" required></div>
            <div class="form-group col-md-6"><label>Email</label><input type="email" class="form-control" name="email" required></div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Teléfono</label><input class="form-control" name="phone"></div>
            <div class="form-group col-md-6"><label>Estado</label>
              <select class="form-control" name="status"><option>Activo</option><option>Inactivo</option></select>
            </div>
          </div>
          <div class="form-group"><label>Notas</label><textarea class="form-control" name="notes"></textarea></div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button id="saveNewAgent" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<!-- DataTables (jQuery se carga en parte_inferior.php) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script>
\$(function(){
  var dt = null;
  if (\$.fn.DataTable) {
    dt = \$('#agentsTable').DataTable({pageLength:10});
  }

  // Permisos
  \$(document).on('click','.btn-perms', function(){
    var name = \$(this).data('name') || 'Asegurador';
    \$('#permAgentName').text(name);
    \$('#permissionsModal').modal('show');
  });
  \$('#savePerms').on('click', function(){
    alert('Permisos guardados (simulado).');
    \$('#permissionsModal').modal('hide');
  });

  // Abrir modal de nuevo agente
  \$('#btnNewAgent').on('click', function(){
    \$('#newAgentForm')[0].reset();
    \$('#newAgentModal').modal('show');
  });

  // Guardar nuevo agente (simulado, añade fila a la tabla)
  \$('#saveNewAgent').on('click', function(){
    var form = \$('#newAgentForm')[0];
    if (!form.checkValidity()) { form.reportValidity(); return; }
    var \$f = \$('#newAgentForm');
    var name = \$f.find('[name=name]').val();
    var email = \$f.find('[name=email]').val();
    var phone = \$f.find('[name=phone]').val() || '';
    var status = \$f.find('[name=status]').val() || 'Activo';

    var actions = '<button class="btn btn-sm btn-info">Editar</button> <button class="btn btn-sm btn-danger">Eliminar</button> <button class="btn btn-sm btn-secondary btn-perms" data-name="'+name+'">Gestionar Permisos</button>';

    if (dt) {
      dt.row.add([ '—', name, email, phone, status, actions ]).draw(false);
    } else {
      \$('#agentsTable tbody').append('<tr><td>—</td><td>'+name+'</td><td>'+email+'</td><td>'+phone+'</td><td>'+status+'</td><td>'+actions+'</td></tr>');
    }

    \$('#newAgentModal').modal('hide');
  });
});
</script>
EOT;

require_once __DIR__ . "/parte_inferior.php";
?>
