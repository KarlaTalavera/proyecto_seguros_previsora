<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>gestión de clientes</h3>
    <button class="btn btn-primary" data-toggle="modal" data-target="#newClientModal">registrar nuevo cliente</button>
  </div>

  <div class="card"><div class="card-body">
    <table id="clientesTable" class="table table-striped w-100">
      <thead><tr><th>id</th><th>nombre / empresa</th><th>tipo</th><th>contacto</th><th>acciones</th></tr></thead>
      <tbody>
        <tr><td>c-100</td><td>lucía fernández</td><td>fisico</td><td>lucia@mail.com</td><td><button class="btn btn-sm btn-primary" data-action="editar" data-id="c-100">editar</button> <button class="btn btn-sm btn-danger" data-action="eliminar" data-id="c-100">eliminar</button></td></tr>
        <tr><td>c-101</td><td>construcciones sa</td><td>empresa</td><td>info@construcciones.sa</td><td><button class="btn btn-sm btn-primary" data-action="editar" data-id="c-101">editar</button> <button class="btn btn-sm btn-danger" data-action="eliminar" data-id="c-101">eliminar</button></td></tr>
        <tr><td>c-102</td><td>roberto gómez</td><td>fisico</td><td>rob@gomez.com</td><td><button class="btn btn-sm btn-primary" data-action="editar" data-id="c-102">editar</button> <button class="btn btn-sm btn-danger" data-action="eliminar" data-id="c-102">eliminar</button></td></tr>
      </tbody>
    </table>
  </div></div>
</div>

<!-- modal nuevo cliente -->
<div class="modal fade" id="newClientModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">registrar nuevo cliente</h5><button class="close" data-dismiss="modal">&times;</button></div>
  <div class="modal-body">
    <form id="clientForm">
      <div class="form-group"><label>tipo</label><select class="form-control" name="tipo"><option>fisico</option><option>empresa</option></select></div>
      <div class="form-group"><label>nombre / empresa</label><input class="form-control" name="nombre"></div>
      <div class="form-group"><label>contacto (email)</label><input class="form-control" name="email" type="email"></div>
      <div class="form-group"><label>telefono</label><input class="form-control" name="telefono"></div>
    </form>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">cancelar</button><button id="saveClient" class="btn btn-primary">registrar</button></div>
</div></div></div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
\$(function(){
  if (\$.fn.DataTable){ \$('#clientesTable').DataTable(); }
  \$('#saveClient').on('click', function(){ alert('cliente registrado (simulado).'); \$('#newClientModal').modal('hide'); });
});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>