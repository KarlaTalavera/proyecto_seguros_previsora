<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
require_once dirname(__DIR__) . '/modelo/modeloCliente.php';

$modeloCliente = new ModeloCliente();
$clientes = $modeloCliente->obtenerTodosLosClientes();

// Definir la ruta base del proyecto
$rutaBase = dirname($_SERVER['SCRIPT_NAME']);
$controladorPath = $rutaBase . '/controlador/controladorCliente.php';
?>

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Clientes</h1>
    <button type="button" class="btn-main-action" data-toggle="modal" data-target="#modalNuevoCliente">
      <span class="btn-main-action__label">Registrar Nuevo Cliente</span>
      <span class="btn-main-action__icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
      </span>
    </button>
  </div>

  <div class="card card-neo mb-4">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="clientesTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cédula</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Fecha de Nacimiento</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clientes as $cliente): ?>
              <tr>
                <td><?= htmlspecialchars($cliente['id_cliente'] ?? ''); ?></td>
                <td><?= htmlspecialchars($cliente['cedula_asegurado'] ?? ''); ?></td>
                <td><?= htmlspecialchars(trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''))); ?></td>
                <td><?= htmlspecialchars($cliente['email'] ?? ''); ?></td>
                <td><?= htmlspecialchars($cliente['telefono'] ?? ''); ?></td>
                <td><?= htmlspecialchars($cliente['fecha_nacimiento'] ?? ''); ?></td>
                <td class="table-action-buttons">
                  <button type="button"
                          class="action-icon action-icon--edit editClientBtn"
                          data-id="<?= $cliente['id_cliente']; ?>"
                          data-cedula="<?= htmlspecialchars($cliente['cedula_asegurado'] ?? ''); ?>"
                          data-nombre="<?= htmlspecialchars($cliente['nombre'] ?? ''); ?>"
                          data-apellido="<?= htmlspecialchars($cliente['apellido'] ?? ''); ?>"
                          data-email="<?= htmlspecialchars($cliente['email'] ?? ''); ?>"
                          data-telefono="<?= htmlspecialchars($cliente['telefono'] ?? ''); ?>"
                          data-direccion="<?= htmlspecialchars($cliente['direccion'] ?? ''); ?>"
                          data-fecha_nacimiento="<?= htmlspecialchars($cliente['fecha_nacimiento'] ?? ''); ?>"
                          data-toggle="modal"
                          data-target="#modalEditarCliente"
                          title="Editar Cliente"
                          aria-label="Editar cliente">
                    <i class="fas fa-pencil-alt"></i>
                  </button>
                  <button type="button"
                          class="action-icon action-icon--delete deleteClientBtn"
                          data-id="<?= $cliente['id_cliente']; ?>"
                          data-nombre="<?= htmlspecialchars(trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''))); ?>"
                          title="Eliminar Cliente"
                          aria-label="Eliminar cliente">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Nuevo Cliente -->
<div class="modal fade modal-consistent" id="modalNuevoCliente" tabindex="-1" role="dialog" aria-labelledby="modalLabelNuevoCliente" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabelNuevoCliente">Registrar Nuevo Cliente</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="nuevoClienteForm" novalidate>
        <div class="modal-body">
          <input type="hidden" name="accion" value="crear_cliente">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="cedula_asegurado">Cédula <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cedula_asegurado" name="cedula_asegurado" placeholder="V12345678" required>
              <div class="invalid-feedback">Ingrese una cédula válida.</div>
            </div>
            <div class="form-group col-md-6">
              <label for="telefono">Teléfono <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="telefono" name="telefono" placeholder="0414xxxxxxx" required>
              <div class="invalid-feedback">Ingrese un teléfono válido.</div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="nombre">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="form-group col-md-6">
              <label for="apellido">Apellido <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="apellido" name="apellido" required>
            </div>
          </div>
          <div class="form-group">
            <label for="email">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" placeholder="cliente@correo.com" required>
          </div>
          <div class="form-group">
            <label for="direccion">Dirección</label>
            <textarea class="form-control" id="direccion" name="direccion" rows="2" placeholder="Dirección completa"></textarea>
          </div>
          <div class="form-group">
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
          </div>
          <div id="respuestaCliente" style="display:none;" class="mt-2"></div>
        </div>
        <div class="modal-footer">
          <button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-neo btn-neo--primary">Guardar Cliente</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade modal-consistent" id="modalEditarCliente" tabindex="-1" role="dialog" aria-labelledby="modalLabelEditarCliente" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabelEditarCliente">Editar Cliente</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editarClienteForm" novalidate>
        <div class="modal-body">
          <input type="hidden" name="accion" value="actualizar_cliente">
          <input type="hidden" id="edit_id_cliente" name="id_cliente">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="edit_cedula_asegurado">Cédula</label>
              <input type="text" class="form-control" id="edit_cedula_asegurado" name="cedula_asegurado" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="edit_telefono">Teléfono</label>
              <input type="text" class="form-control" id="edit_telefono" name="telefono" placeholder="0414xxxxxxx" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="edit_nombre">Nombre</label>
              <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
            </div>
            <div class="form-group col-md-6">
              <label for="edit_apellido">Apellido</label>
              <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_email">Email</label>
            <input type="email" class="form-control" id="edit_email" name="email" required>
          </div>
          <div class="form-group">
            <label for="edit_direccion">Dirección</label>
            <textarea class="form-control" id="edit_direccion" name="direccion" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="edit_fecha_nacimiento">Fecha de Nacimiento</label>
            <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento">
          </div>
          <div id="respuestaEditarCliente" style="display:none;" class="mt-2"></div>
        </div>
        <div class="modal-footer">
          <button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-neo btn-neo--primary">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // Variable global con la ruta del controlador
    window.controladorUrl = '{$controladorPath}';
    console.log('URL del controlador:', window.controladorUrl);
    
    // Inicializar DataTable con configuración manual en español
    var table = $('#clientesTable').DataTable({
        "language": {
            "decimal": ",",
            "thousands": ".",
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "order": [[0, "desc"]]
    });
    
    // Crear cliente
    $('#nuevoClienteForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const boton = $(this).find('button[type="submit"]');
        
        console.log("Datos a enviar para crear:", formData);
        console.log("URL del controlador:", window.controladorUrl);
        
        boton.prop('disabled', true).text('Guardando...');
        $('#respuestaCliente').hide().html('');
        
        $.ajax({
            url: window.controladorUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                console.log("Respuesta del servidor:", res);
                if (res.success) {
                    $('#respuestaCliente').show().html('<div class="alert alert-success">' + res.message + '</div>');
                    setTimeout(function() {
                        $('#modalNuevoCliente').modal('hide');
                        location.reload();
                    }, 1500);
                } else {
                    $('#respuestaCliente').show().html('<div class="alert alert-danger">' + res.message + '</div>');
                    boton.prop('disabled', false).text('Guardar Cliente');
                }
            },
            error: function(xhr, status, error) {
                console.log("Error en AJAX:", status, error);
                console.log("Respuesta del servidor:", xhr.responseText);
                $('#respuestaCliente').show().html('<div class="alert alert-danger">Error de conexión: ' + error + '</div>');
                boton.prop('disabled', false).text('Guardar Cliente');
            }
        });
    });
    
    // Cargar datos en modal de edición
    $(document).on('click', '.editClientBtn', function() {
        const btn = $(this);
        console.log("Datos del cliente:", btn.data());
        
        $('#edit_id_cliente').val(btn.data('id'));
        $('#edit_cedula_asegurado').val(btn.data('cedula'));
        $('#edit_nombre').val(btn.data('nombre'));
        $('#edit_apellido').val(btn.data('apellido'));
        $('#edit_email').val(btn.data('email'));
        $('#edit_telefono').val(btn.data('telefono'));
        $('#edit_direccion').val(btn.data('direccion'));
        
        // Cargar fecha de nacimiento
        var fechaNacimiento = btn.data('fecha_nacimiento') || '';
        console.log("Fecha de nacimiento a cargar:", fechaNacimiento);
        $('#edit_fecha_nacimiento').val(fechaNacimiento);
    });
    
    // Actualizar cliente
    $('#editarClienteForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const boton = $(this).find('button[type="submit"]');
        
        console.log("Datos a enviar para actualizar:", formData);
        console.log("URL del controlador:", window.controladorUrl);
        
        boton.prop('disabled', true).text('Guardando...');
        $('#respuestaEditarCliente').hide().html('');
        
        $.ajax({
            url: window.controladorUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                console.log("Respuesta del servidor:", res);
                if (res.success) {
                    $('#respuestaEditarCliente').show().html('<div class="alert alert-success">' + res.message + '</div>');
                    setTimeout(function() {
                        $('#modalEditarCliente').modal('hide');
                        location.reload();
                    }, 1500);
                } else {
                    $('#respuestaEditarCliente').show().html('<div class="alert alert-danger">' + res.message + '</div>');
                    boton.prop('disabled', false).text('Guardar Cambios');
                }
            },
            error: function(xhr, status, error) {
                console.log("Error en AJAX:", status, error);
                console.log("Respuesta del servidor:", xhr.responseText);
                $('#respuestaEditarCliente').show().html('<div class="alert alert-danger">Error de conexión: ' + error + '</div>');
                boton.prop('disabled', false).text('Guardar Cambios');
            }
        });
    });
    
    // Eliminar cliente
    $(document).on('click', '.deleteClientBtn', function() {
        const btn = $(this);
        const idCliente = btn.data('id');
        const nombreCliente = btn.data('nombre');
        
        if (confirm('¿Está seguro de eliminar al cliente "' + nombreCliente + '"?')) {
            btn.prop('disabled', true);
            
            $.ajax({
                url: window.controladorUrl,
                type: 'POST',
                data: {
                    accion: 'eliminar_cliente',
                    id_cliente: idCliente
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        alert(res.message);
                        location.reload();
                    } else {
                        alert('Error: ' + res.message);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.log("Error en AJAX:", status, error);
                    console.log("Respuesta del servidor:", xhr.responseText);
                    alert('Error de conexión: ' + error);
                    btn.prop('disabled', false);
                }
            });
        }
    });
    
    // Limpiar formulario al cerrar modal
    $('#modalNuevoCliente').on('hidden.bs.modal', function () {
        $('#nuevoClienteForm')[0].reset();
        $('#respuestaCliente').hide().html('');
        $('#nuevoClienteForm button[type="submit"]').prop('disabled', false).text('Guardar Cliente');
    });
    
    $('#modalEditarCliente').on('hidden.bs.modal', function () {
        $('#respuestaEditarCliente').hide().html('');
        $('#editarClienteForm button[type="submit"]').prop('disabled', false).text('Guardar Cambios');
    });
});
</script>
EOT;
require_once __DIR__ . '/parte_inferior.php';
?>