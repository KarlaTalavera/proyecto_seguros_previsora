  <?php
  require_once dirname(__DIR__) . '/modelo/modeloCliente.php';
  if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }
  require_once __DIR__ . '/parte_superior.php';

  $modeloCliente = new ModeloCliente();

  // Obtener todos los clientes (o filtrar por agente si es necesario)
  $clientes = $modeloCliente->obtenerTodosLosClientes();

  // Obtener agentes para asignar (si es necesario)
  $modeloUsuario = new ModeloUsuario();
  $usuarios = $modeloUsuario->obtenerTodosLosUsuarios();

  ?>

  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Gestión de Clientes</h3>
      <button class="btn btn-primary" data-toggle="modal" data-target="#newClientModal">Registrar Nuevo Cliente</button>
    </div>

    <div class="card">
      <div class="card-body">
        <table id="clientesTable" class="table table-striped w-100">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cédula</th>
              <th>Nombre / Empresa</th>
              <th>Tipo</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clientes as $cliente): ?>
            <tr>
              <td><?php echo htmlspecialchars($cliente['id_cliente'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($cliente['cedula_asegurado'] ?? ''); ?></td>
              <td>
                <?php 
                  echo htmlspecialchars($cliente['nombre'] ?? '' . ' ' . $cliente['apellido'] ?? '');
                  if (!empty($cliente['nombre_o_empresa'])) {
                      echo '<br><small class="text-muted">' . htmlspecialchars($cliente['nombre_o_empresa']) . '</small>';
                  }
                ?>
              </td>
              <td><?php echo htmlspecialchars($cliente['tipo'] ?? 'Natural'); ?></td>
              <td><?php echo htmlspecialchars($cliente['email'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?></td>
              <td>
                <button class="btn btn-sm btn-primary edit-client" 
                        data-id="<?php echo $cliente['id_cliente']; ?>"
                        data-cedula="<?php echo htmlspecialchars($cliente['cedula_asegurado'] ?? ''); ?>"
                        data-nombre="<?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?>"
                        data-apellido="<?php echo htmlspecialchars($cliente['apellido'] ?? ''); ?>"
                        data-nombre_empresa="<?php echo htmlspecialchars($cliente['nombre_o_empresa'] ?? ''); ?>"
                        data-email="<?php echo htmlspecialchars($cliente['email'] ?? ''); ?>"
                        data-telefono="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>"
                        data-tipo="<?php echo htmlspecialchars($cliente['tipo'] ?? 'Natural'); ?>"
                        data-direccion="<?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?>"
                        data-toggle="modal" 
                        data-target="#editClientModal"
                        title="Editar cliente">
                  <i class="fas fa-edit fa-xs"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-outline-danger delete-client" 
                        data-id="<?php echo $cliente['id_cliente']; ?>"
                        title="Eliminar cliente">
                  <i class="fas fa-trash fa-xs"></i>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal Nuevo Cliente -->
  <div class="modal fade" id="newClientModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Registrar Nuevo Cliente</h5>
          <button class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <form id="clientForm">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="tipoCliente">Tipo *</label>
                <select class="form-control" id="tipoCliente" name="tipo" required>
                  <option value="Natural">Persona Natural</option>
                  <option value="Juridica">Persona Jurídica</option>
                  <option value="Empresa">Empresa</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="cedula_asegurado">Cédula/RIF *</label>
                <input type="text" class="form-control" id="cedula_asegurado" name="cedula_asegurado" 
                      required placeholder="V12345678 o J12345678-9">
              </div>
            </div>
            
            <div class="form-group" id="nombreNaturalGroup">
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="nombre">Nombre *</label>
                  <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre">
                </div>
                <div class="form-group col-md-6">
                  <label for="apellido">Apellido *</label>
                  <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido">
                </div>
              </div>
            </div>
            
            <div class="form-group" id="nombreEmpresaGroup" style="display: none;">
              <label for="nombre_o_empresa">Nombre de la Empresa *</label>
              <input type="text" class="form-control" id="nombre_o_empresa" name="nombre_o_empresa" 
                    placeholder="Nombre de la empresa">
            </div>
            
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="email">Email *</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="correo@dominio.com">
              </div>
              <div class="form-group col-md-6">
                <label for="telefono">Teléfono *</label>
                <input type="text" class="form-control" id="telefono" name="telefono" required placeholder="04141234567">
              </div>
            </div>
            
            <div class="form-group">
              <label for="direccion">Dirección</label>
              <textarea class="form-control" id="direccion" name="direccion" rows="2" 
                        placeholder="Dirección completa"></textarea>
            </div>
            
            <input type="hidden" name="accion" value="crear_cliente">
          </form>
          <div id="respuestaCliente" class="mt-3" style="display: none;"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button id="saveClient" class="btn btn-primary">Registrar Cliente</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Editar Cliente -->
  <div class="modal fade" id="editClientModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Cliente</h5>
          <button class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <form id="editClientForm">
            <input type="hidden" id="edit_id_cliente" name="id_cliente">
            
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="edit_tipoCliente">Tipo *</label>
                <select class="form-control" id="edit_tipoCliente" name="tipo" required>
                  <option value="Natural">Persona Natural</option>
                  <option value="Juridica">Persona Jurídica</option>
                  <option value="Empresa">Empresa</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="edit_cedula_asegurado">Cédula/RIF *</label>
                <input type="text" class="form-control" id="edit_cedula_asegurado" name="cedula_asegurado" 
                      required placeholder="V12345678 o J12345678-9">
              </div>
            </div>
            
            <div class="form-group" id="edit_nombreNaturalGroup">
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="edit_nombre">Nombre *</label>
                  <input type="text" class="form-control" id="edit_nombre" name="nombre" placeholder="Nombre">
                </div>
                <div class="form-group col-md-6">
                  <label for="edit_apellido">Apellido *</label>
                  <input type="text" class="form-control" id="edit_apellido" name="apellido" placeholder="Apellido">
                </div>
              </div>
            </div>
            
            <div class="form-group" id="edit_nombreEmpresaGroup" style="display: none;">
              <label for="edit_nombre_o_empresa">Nombre de la Empresa *</label>
              <input type="text" class="form-control" id="edit_nombre_o_empresa" name="nombre_o_empresa" 
                    placeholder="Nombre de la empresa">
            </div>
            
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="edit_email">Email *</label>
                <input type="email" class="form-control" id="edit_email" name="email" required placeholder="correo@dominio.com">
              </div>
              <div class="form-group col-md-6">
                <label for="edit_telefono">Teléfono *</label>
                <input type="text" class="form-control" id="edit_telefono" name="telefono" required placeholder="04141234567">
              </div>
            </div>
            
            <div class="form-group">
              <label for="edit_direccion">Dirección</label>
              <textarea class="form-control" id="edit_direccion" name="direccion" rows="2" 
                        placeholder="Dirección completa"></textarea>
            </div>
            
            <input type="hidden" name="accion" value="actualizar_cliente">
          </form>
          <div id="respuestaEditarCliente" class="mt-3" style="display: none;"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button id="updateClient" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  $extra_scripts = <<<EOT
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
  $(function(){
      // Inicializar DataTable
      $('#clientesTable').DataTable({
          "language": {
              "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
          }
      });
      
      // Manejar cambio de tipo de cliente
      $('#tipoCliente').change(function() {
          const tipo = $(this).val();
          if (tipo === 'Natural') {
              $('#nombreNaturalGroup').show();
              $('#nombreEmpresaGroup').hide();
          } else {
              $('#nombreNaturalGroup').hide();
              $('#nombreEmpresaGroup').show();
          }
      });
      
      $('#edit_tipoCliente').change(function() {
          const tipo = $(this).val();
          if (tipo === 'Natural') {
              $('#edit_nombreNaturalGroup').show();
              $('#edit_nombreEmpresaGroup').hide();
          } else {
              $('#edit_nombreNaturalGroup').hide();
              $('#edit_nombreEmpresaGroup').show();
          }
      });
      
      // Crear cliente
      $('#saveClient').on('click', function() {
          const formData = $('#clientForm').serialize();
          const boton = $(this);
          boton.prop('disabled', true).text('Guardando...');
          $('#respuestaCliente').hide().html('');
          
          $.ajax({
              url: 'controlador/controladorCliente.php',
              type: 'POST',
              data: formData,
              dataType: 'json',
              success: function(res) {
                  if (res.success) {
                      $('#respuestaCliente').show().html('<div class="alert alert-success">' + res.message + '</div>');
                      setTimeout(function() {
                          $('#newClientModal').modal('hide');
                          location.reload();
                      }, 1500);
                  } else {
                      $('#respuestaCliente').show().html('<div class="alert alert-danger">' + res.message + '</div>');
                      boton.prop('disabled', false).text('Registrar Cliente');
                  }
              },
              error: function() {
                  $('#respuestaCliente').show().html('<div class="alert alert-danger">Error de conexión</div>');
                  boton.prop('disabled', false).text('Registrar Cliente');
              }
          });
      });
      
      // Editar cliente - cargar datos
      $(document).on('click', '.edit-client', function() {
          const btn = $(this);
          $('#edit_id_cliente').val(btn.data('id'));
          $('#edit_cedula_asegurado').val(btn.data('cedula'));
          $('#edit_nombre').val(btn.data('nombre'));
          $('#edit_apellido').val(btn.data('apellido'));
          $('#edit_nombre_o_empresa').val(btn.data('nombre_empresa'));
          $('#edit_email').val(btn.data('email'));
          $('#edit_telefono').val(btn.data('telefono'));
          $('#edit_tipoCliente').val(btn.data('tipo'));
          $('#edit_direccion').val(btn.data('direccion'));
          
          // Mostrar/ocultar campos según tipo
          if (btn.data('tipo') === 'Natural') {
              $('#edit_nombreNaturalGroup').show();
              $('#edit_nombreEmpresaGroup').hide();
          } else {
              $('#edit_nombreNaturalGroup').hide();
              $('#edit_nombreEmpresaGroup').show();
          }
      });
      
      // Actualizar cliente
      $('#updateClient').on('click', function() {
          const formData = $('#editClientForm').serialize();
          const boton = $(this);
          boton.prop('disabled', true).text('Guardando...');
          $('#respuestaEditarCliente').hide().html('');
          
          $.ajax({
              url: 'controlador/controladorCliente.php',
              type: 'POST',
              data: formData,
              dataType: 'json',
              success: function(res) {
                  if (res.success) {
                      $('#respuestaEditarCliente').show().html('<div class="alert alert-success">' + res.message + '</div>');
                      setTimeout(function() {
                          $('#editClientModal').modal('hide');
                          location.reload();
                      }, 1500);
                  } else {
                      $('#respuestaEditarCliente').show().html('<div class="alert alert-danger">' + res.message + '</div>');
                      boton.prop('disabled', false).text('Guardar Cambios');
                  }
              },
              error: function() {
                  $('#respuestaEditarCliente').show().html('<div class="alert alert-danger">Error de conexión</div>');
                  boton.prop('disabled', false).text('Guardar Cambios');
              }
          });
      });
      
      // Eliminar cliente
      $(document).on('click', '.delete-client', function() {
          const idCliente = $(this).data('id');
          if (confirm('¿Está seguro de eliminar este cliente?')) {
              const boton = $(this);
              boton.prop('disabled', true).text('Eliminando...');
              
              $.ajax({
                  url: 'controlador/controladorCliente.php',
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
                          boton.prop('disabled', false).text('Eliminar');
                      }
                  },
                  error: function() {
                      alert('Error de conexión');
                      boton.prop('disabled', false).text('Eliminar');
                  }
              });
          }
      });
  });
  </script>
  EOT;

  require_once __DIR__ . "/parte_inferior.php";
  ?>