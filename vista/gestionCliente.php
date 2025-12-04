<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

$modeloUsuario = new ModeloUsuario();
$todosLosUsuarios = $modeloUsuario->obtenerTodosLosUsuarios();
$clientes = [];

if (is_array($todosLosUsuarios)) {
    $clientes = array_values(array_filter($todosLosUsuarios, function ($usuario) {
        $rol = strtolower($usuario['rol'] ?? ($usuario['nombre_rol'] ?? ''));
        $activo = isset($usuario['activo']) ? (int) $usuario['activo'] : 1;
        return ($rol === 'cliente' || $rol === 'asegurado') && $activo === 1;
    }));
}
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
        <table class="table table-striped table-hover align-middle" id="clientsTable">
          <thead>
            <tr>
              <th>Cédula</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clientes as $cliente): ?>
              <?php $nombreCompletoCliente = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')); ?>
              <tr>
                <td><?= htmlspecialchars($cliente['cedula'] ?? ''); ?></td>
                <td><?= htmlspecialchars($nombreCompletoCliente); ?></td>
                <td><?= htmlspecialchars($cliente['email'] ?? ''); ?></td>
                <td><?= htmlspecialchars($cliente['telefono'] ?? ''); ?></td>
                <td class="table-action-buttons">
                  <button type="button"
                          class="action-icon action-icon--edit editClientBtn"
                          data-cedula="<?= htmlspecialchars($cliente['cedula'] ?? ''); ?>"
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
                          data-cedula="<?= htmlspecialchars($cliente['cedula'] ?? ''); ?>"
                          data-nombre="<?= htmlspecialchars($nombreCompletoCliente); ?>"
                          title="Desactivar Cliente"
                          aria-label="Desactivar cliente">
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

<div class="modal fade modal-consistent" id="modalNuevoCliente" tabindex="-1" role="dialog" aria-labelledby="modalLabelNuevoCliente" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabelNuevoCliente">Registrar Nuevo Cliente</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form id="nuevoClienteForm" novalidate>
          <input type="hidden" name="rol" value="cliente">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="clienteCedula">Cédula <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="clienteCedula" name="cedula" placeholder="V12345678" required>
              <div class="invalid-feedback">Ingrese una cédula válida (V12345678).</div>
            </div>
            <div class="form-group col-md-6">
              <label for="clienteTelefono">Teléfono <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="clienteTelefono" name="telefono" placeholder="0414xxxxxxx" required>
              <div class="invalid-feedback">Ingrese un teléfono válido.</div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="clienteNombre">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="clienteNombre" name="nombre" required>
            </div>
            <div class="form-group col-md-6">
              <label for="clienteApellido">Apellido <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="clienteApellido" name="apellido" required>
            </div>
          </div>
          <div class="form-group">
            <label for="clienteEmail">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="clienteEmail" name="email" placeholder="cliente@correo.com" required>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="clientePassword">Contraseña</label>
              <input type="password" class="form-control" id="clientePassword" name="password" placeholder="Opcional (mínimo 8 caracteres)">
              <small class="form-text text-muted">Déjelo vacío para generar una contraseña temporal.</small>
            </div>
            <div class="form-group col-md-6">
              <label for="clientePasswordConfirm">Confirmar Contraseña</label>
              <input type="password" class="form-control" id="clientePasswordConfirm" placeholder="Repita la contraseña">
            </div>
          </div>
          <div class="form-group">
            <label for="clienteDireccion">Dirección</label>
            <textarea class="form-control" id="clienteDireccion" name="direccion" rows="2" placeholder="Dirección completa"></textarea>
          </div>
          <div class="form-group">
            <label for="clienteFechaNacimiento">Fecha de nacimiento</label>
            <input type="date" class="form-control" id="clienteFechaNacimiento" name="fecha_nacimiento">
          </div>
        </form>
        <div id="respuestaCrearCliente" style="display:none;" class="mt-2"></div>
      </div>
      <div class="modal-footer">
        <button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
        <button id="btnGuardarCliente" class="btn-neo btn-neo--primary" type="button">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="modalEditarCliente" tabindex="-1" role="dialog" aria-labelledby="modalLabelEditarCliente" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabelEditarCliente">Editar Cliente</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form id="editarClienteForm" novalidate>
          <input type="hidden" id="editClienteCedulaOriginal" name="cedula_original">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="editClienteCedula">Cédula</label>
              <input type="text" class="form-control" id="editClienteCedula" name="cedula" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="editClienteTelefono">Teléfono</label>
              <input type="text" class="form-control" id="editClienteTelefono" name="telefono" placeholder="0414xxxxxxx">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="editClienteNombre">Nombre</label>
              <input type="text" class="form-control" id="editClienteNombre" name="nombre" required>
            </div>
            <div class="form-group col-md-6">
              <label for="editClienteApellido">Apellido</label>
              <input type="text" class="form-control" id="editClienteApellido" name="apellido" required>
            </div>
          </div>
          <div class="form-group">
            <label for="editClienteEmail">Email</label>
            <input type="email" class="form-control" id="editClienteEmail" name="email" required>
          </div>
          <div class="form-group">
            <label for="editClienteDireccion">Dirección</label>
            <textarea class="form-control" id="editClienteDireccion" name="direccion" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="editClienteFechaNacimiento">Fecha de nacimiento</label>
            <input type="date" class="form-control" id="editClienteFechaNacimiento" name="fecha_nacimiento">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="editClientePassword">Nueva Contraseña</label>
              <input type="password" class="form-control" id="editClientePassword" name="password" placeholder="Opcional">
              <small class="form-text text-muted">Al menos 8 caracteres si desea cambiarla.</small>
            </div>
            <div class="form-group col-md-6">
              <label for="editClientePasswordConfirm">Confirmar Nueva Contraseña</label>
              <input type="password" class="form-control" id="editClientePasswordConfirm" placeholder="Repita la contraseña">
            </div>
          </div>
        </form>
        <div id="respuestaEditarCliente" style="display:none;" class="mt-2"></div>
      </div>
      <div class="modal-footer">
        <button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
        <button id="btnActualizarCliente" class="btn-neo btn-neo--primary">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable) {
      $('#clientsTable').DataTable({
        pageLength: 10,
        order: [[1, 'asc']],
        language: {
          sProcessing: 'Procesando...',
          sLengthMenu: 'Mostrar _MENU_ registros',
          sZeroRecords: 'No se encontraron resultados',
          sEmptyTable: 'Ningún dato disponible en esta tabla',
          sInfo: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
          sInfoEmpty: 'Mostrando registros del 0 al 0 de un total de 0 registros',
          sInfoFiltered: '(filtrado de un total de _MAX_ registros)',
          sSearch: 'Buscar:',
          sLoadingRecords: 'Cargando...',
          oPaginate: {
            sFirst: 'Primero',
            sLast: 'Último',
            sNext: 'Siguiente',
            sPrevious: 'Anterior'
          },
          oAria: {
            sSortAscending: ': Activar para ordenar la columna de manera ascendente',
            sSortDescending: ': Activar para ordenar la columna de manera descendente'
          }
        }
      });
    }

    $('#modalNuevoCliente').on('show.bs.modal', function() {
      const form = $('#nuevoClienteForm');
      if (form.length) {
        form[0].reset();
      }
      $('#respuestaCrearCliente').hide().html('');
    });

    $('#btnGuardarCliente').on('click', function() {
      const form = $('#nuevoClienteForm');
      const boton = $(this);
      $('#respuestaCrearCliente').hide().html('');
      boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

      const cedula = $('#clienteCedula').val().trim();
      const nombre = $('#clienteNombre').val().trim();
      const apellido = $('#clienteApellido').val().trim();
      const email = $('#clienteEmail').val().trim();
      const telefono = $('#clienteTelefono').val().trim();
      const password = $('#clientePassword').val() || '';
      const passwordConfirm = $('#clientePasswordConfirm').val() || '';
      const direccion = $('#clienteDireccion').val().trim();
      const fechaNacimiento = $('#clienteFechaNacimiento').val().trim();

      const rePersona = /^V\d{7,8}$/i;
      const reEntidad = /^(J|G|E|EM)\d{7,8}-\d{1}$/i;
      const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const reTelefono = /^[0-9\-\s\+]{7,20}$/;
      const reFecha = /^\d{4}-\d{2}-\d{2}$/;

      function showCreateClientError(msg) {
        $('#respuestaCrearCliente').show().html('<div class="alert alert-danger">' + msg + '</div>');
        boton.prop('disabled', false).text('Guardar');
      }

      if (!cedula) { showCreateClientError('Complete la cédula.'); return; }
      if (!nombre) { showCreateClientError('Complete el nombre.'); return; }
      if (!apellido) { showCreateClientError('Complete el apellido.'); return; }
      if (!email) { showCreateClientError('Complete el email.'); return; }
      if (!telefono) { showCreateClientError('Complete el teléfono.'); return; }

      if (!(rePersona.test(cedula) || reEntidad.test(cedula))) { showCreateClientError('Formato de cédula inválido. Ej: V12345678 o J12345678-9'); return; }
      if (!reEmail.test(email)) { showCreateClientError('Email inválido.'); return; }
      if (!reTelefono.test(telefono)) { showCreateClientError('Teléfono inválido.'); return; }
      if (fechaNacimiento && !reFecha.test(fechaNacimiento)) { showCreateClientError('Use el formato AAAA-MM-DD.'); return; }

      if (password && password.length < 8) { showCreateClientError('La contraseña debe tener al menos 8 caracteres.'); return; }
      if (password && password !== passwordConfirm) { showCreateClientError('Las contraseñas no coinciden.'); return; }

      $.ajax({
        url: 'controlador/controladorUsuario.php',
        type: 'POST',
        data: form.serialize() + '&accion=crear_usuario',
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#modalNuevoCliente').modal('hide');
            let msg = res.message || 'Cliente creado correctamente.';
            if (res.password) {
              msg += '\nContraseña asignada: ' + res.password;
            }
            alert(msg);
            window.location.reload();
          } else {
            showCreateClientError(res.message || 'Error al crear cliente.');
          }
        },
        error: function() {
          showCreateClientError('Error de conexión al servidor.');
        },
        complete: function() {
          boton.prop('disabled', false).text('Guardar');
        }
      });
    });

    $(document).on('click', '.editClientBtn', function() {
      const btn = $(this);
      const cedula = btn.data('cedula') || '';
      const nombre = btn.data('nombre') || '';
      const apellido = btn.data('apellido') || '';
      const email = btn.data('email') || '';
      const telefono = btn.data('telefono') || '';
      const direccion = btn.data('direccion') || '';
      const fechaNacimiento = btn.data('fecha_nacimiento') || '';

      $('#modalLabelEditarCliente').text('Editar Cliente: ' + [nombre, apellido].filter(Boolean).join(' '));
      $('#editClienteCedulaOriginal').val(cedula);
      $('#editClienteCedula').val(cedula);
      $('#editClienteNombre').val(nombre);
      $('#editClienteApellido').val(apellido);
      $('#editClienteEmail').val(email);
      $('#editClienteTelefono').val(telefono);
      $('#editClienteDireccion').val(direccion);
      $('#editClienteFechaNacimiento').val(fechaNacimiento);
      $('#editClientePassword').val('');
      $('#editClientePasswordConfirm').val('');
      $('#respuestaEditarCliente').hide().html('');
    });

    $('#btnActualizarCliente').on('click', function() {
      const form = $('#editarClienteForm');
      const boton = $(this);
      $('#respuestaEditarCliente').hide().html('');
      boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

      const nombre = $('#editClienteNombre').val().trim();
      const apellido = $('#editClienteApellido').val().trim();
      const email = $('#editClienteEmail').val().trim();
      const telefono = $('#editClienteTelefono').val().trim();
      const password = $('#editClientePassword').val() || '';
      const passwordConfirm = $('#editClientePasswordConfirm').val() || '';

      const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const reTelefono = /^[0-9\-\s\+]{7,20}$/;

      function showClientError(msg) {
        $('#respuestaEditarCliente').show().html('<div class="alert alert-danger">' + msg + '</div>');
        boton.prop('disabled', false).text('Guardar Cambios');
      }

      if (!nombre) { showClientError('Complete el nombre.'); return; }
      if (!apellido) { showClientError('Complete el apellido.'); return; }
      if (!email || !reEmail.test(email)) { showClientError('Email inválido.'); return; }
      if (telefono && !reTelefono.test(telefono)) { showClientError('Teléfono inválido.'); return; }
      if (password && password.length < 8) { showClientError('La contraseña debe tener al menos 8 caracteres.'); return; }
      if (password && password !== passwordConfirm) { showClientError('Las contraseñas no coinciden.'); return; }

      $.ajax({
        url: 'controlador/controladorUsuario.php',
        type: 'POST',
        data: form.serialize() + '&accion=actualizar_usuario',
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#modalEditarCliente').modal('hide');
            alert(res.message || 'Cliente actualizado correctamente.');
            window.location.reload();
          } else {
            showClientError(res.message || 'Error al actualizar cliente.');
          }
        },
        error: function() {
          showClientError('Error de conexión al servidor.');
        },
        complete: function() {
          boton.prop('disabled', false).text('Guardar Cambios');
        }
      });
    });

    $(document).on('click', '.deleteClientBtn', function() {
      const button = $(this);
      const cedula = button.data('cedula');
      const nombre = button.data('nombre') || '';
      if (!cedula) {
        alert('No se pudo identificar al cliente.');
        return;
      }
      const mensajeConfirmacion = nombre ? '¿Desea desactivar al cliente ' + nombre + '?' : '¿Desea desactivar a este cliente?';
      if (!confirm(mensajeConfirmacion)) {
        return;
      }

      button.prop('disabled', true);

      $.ajax({
        url: 'controlador/controladorUsuario.php',
        type: 'POST',
        dataType: 'json',
        data: { accion: 'desactivar_usuario', cedula: cedula },
        success: function(res) {
          if (res.success) {
            alert(res.message || 'Cliente desactivado correctamente.');
            window.location.reload();
          } else {
            alert(res.message || 'No se pudo desactivar el cliente.');
          }
        },
        error: function() {
          alert('Error de conexión al servidor.');
        },
        complete: function() {
          button.prop('disabled', false);
        }
      });
    });
  });
</script>
EOT;
require_once __DIR__ . '/parte_inferior.php';
?>
