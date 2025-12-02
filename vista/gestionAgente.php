<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

$modeloUsuario = new ModeloUsuario();
// Usamos el nuevo método específico para agentes
$agentes = $modeloUsuario->obtenerTodosLosAgentes();

// Depuración: Muestra si hay error o no hay datos
if ($agentes === false) {
    echo '<div class="alert alert-danger">Error al cargar los agentes.</div>';
    $agentes = []; // Para evitar errores en el foreach
} elseif (empty($agentes)) {
    echo '<div class="alert alert-info">No hay agentes registrados.</div>';
}

?>

<style>
 
  :root {
    --perm-modal-header-bg: #93BFC7; 
    --perm-modal-header-color: #fff;
    --perm-card-header-bg: #DEDED1; 
    --perm-card-header-color: #333; 
    --perm-hover-bg: #e9e9e3;
  }

  #permisosModal .modal-content {
    border-radius: 1rem; 
    overflow: hidden; /* Para que el header no se salga de los bordes redondeados */
  }

  #permisosModal .modal-header {
    background-color: var(--perm-modal-header-bg);
    color: var(--perm-modal-header-color);
    border-bottom: none;
  }
  #permisosModal .modal-header .close {
    color: var(--perm-modal-header-color);
    opacity: 0.8;
  }
  #permisosModal .modal-title strong {
    font-weight: 500;
  }

  /* Hacer que el modal de "Registrar Nuevo Agente" use los mismos estilos que el de permisos */
  #modalNuevoAgente .modal-content {
    border-radius: 1rem;
    overflow: hidden;
  }
  #modalNuevoAgente .modal-header {
    background-color: var(--perm-modal-header-bg);
    color: var(--perm-modal-header-color);
    border-bottom: none;
  }
  #modalNuevoAgente .modal-header .close {
    color: var(--perm-modal-header-color);
    opacity: 0.8;
  }

  /* Espaciado interior para que el contenido no quede pegado a los bordes */
  #modalNuevoAgente .modal-body {
    padding: 1.5rem; /* mayor padding interior */
  }
  #modalNuevoAgente .modal-footer {
    padding: 1rem 1.5rem; /* espacio alrededor de los botones */
    gap: 0.5rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
  }
  #modalNuevoAgente .form-group {
    margin-bottom: 1rem; /* separar campos */
  }
  #modalNuevoAgente .input-group .input-group-prepend .input-group-text {
    padding: 0.55rem 0.75rem; /* hacer los iconos menos compactos */
  }
  #modalNuevoAgente .form-row {
    margin-left: -0.25rem;
    margin-right: -0.25rem;
  }
  #modalNuevoAgente .form-row > .col-md-6 {
    padding-left: 0.25rem;
    padding-right: 0.25rem;
  }
  /* Ajuste visual para el mensaje de respuesta */
  #respuestaCrearAgente .alert {
    margin-bottom: 0;
  }

  .perm-group-card {
     border: 1px solid #ccc; /* Borde sutil para las tarjetas */
  }
  
  .perm-group-card .card-header {
    background-color: var(--perm-card-header-bg);
    color: var(--perm-card-header-color);
    font-weight: 500;
    border-bottom: 1px solid #ccc;
  }
  .perm-group-card .card-body {
    padding: 0.5rem;
  }
  .perm-check {
    padding: .5rem 1rem;
    border-radius: .25rem;
    transition: background-color 0.15s ease-in-out;
  }
  .perm-check:hover {
    background-color: var(--perm-hover-bg);
  }
  .perm-check label {
    margin-bottom: 0;
    cursor: pointer;
  }
  .perm-check input:checked + label {
    font-weight: bold;
    color: #2c8a99; /* Un azul más oscuro para el texto del check */
  }

  /* From Uiverse.io by adamgiebl */ 
  .neu-button {
    background-color: #e0e0e0;
    border-radius: 50px;
    box-shadow: inset 4px 4px 10px #bcbcbc, inset -4px -4px 10px #ffffff;
    color: #4d4d4d;
    cursor: pointer;
    font-size: 16px; /* Reducido para que quepa mejor */
    padding: 10px 25px; /* Reducido para que quepa mejor */
    transition: all 0.2s ease-in-out;
    border: 2px solid rgb(206, 206, 206);
    margin: 0 5px; /* Espacio entre botones */
  }

  .neu-button:hover {
    box-shadow: inset 2px 2px 5px #bcbcbc, inset -2px -2px 5px #ffffff, 2px 2px 5px #bcbcbc, -2px -2px 5px #ffffff;
  }

  .neu-button:focus {
    outline: none;
    box-shadow: inset 2px 2px 5px #bcbcbc, inset -2px -2px 5px #ffffff, 2px 2px 5px #bcbcbc, -2px -2px 5px #ffffff;
  }

  /* Estilos para el botón de Añadir Agente */

.button {
  position: relative;
  width: auto;
  min-width: 220px; /* asegurar espacio para texto largo */
  height: 40px;
  cursor: pointer;
  display: flex;
  align-items: center;
  border: 1px solid #007bff;
  background-color: #007bff; /* azul */
  padding: 0 56px 0 14px; /* reservar espacio a la derecha igual al ancho del icono */
  overflow: hidden;
  border-radius: 0; /* bordes cuadrados */
}

.button, .button__icon, .button__text {
  transition: all 0.28s ease;
}

.button .button__text {
  color: #fff;
  font-weight: 600;
  white-space: nowrap;
  margin-left: 6px; /* separa el texto del borde izquierdo */
  transition: color 0.28s ease;
}

.button .button__icon {
  position: absolute;
  right: 0;
  top: 0;
  height: 100%;
  width: 48px;
  background-color: #0069d9; /* tono más oscuro para el icono */
  display: flex;
  align-items: center;
  justify-content: center;
  transition: width 0.28s ease, background-color 0.18s ease;
}

.button .svg {
  width: 20px;
  stroke: #fff;
}

.button:hover {
  background: #0069d9; /* hover azul oscuro */
}

/* En hover el icono se expande hacia la izquierda cubriendo el texto (comportamiento original) */
.button:hover .button__text {
  color: transparent;
}

.button:hover .button__icon {
  width: 100%;
  right: 0; /* mantener el ancla a la derecha para que la expansión ocurra hacia la izquierda */
}

.button:active .button__icon {
  background-color: #0056b3; /* más oscuro al hacer click */
}

.button:active {
  border: 1px solid #0056b3;
}

<style>
  :root {
    --perm-modal-header-bg: #93BFC7;
    --perm-modal-header-color: #fff;
    --perm-card-header-bg: #DEDED1;
    --perm-card-header-color: #333;
    --perm-hover-bg: #e9e9e3;
  }

  /* Botones minimalistas para acciones de la tabla */
  .btn-minimal {
    background: transparent;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 6px 12px;
    color: #555;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 2px;
    cursor: pointer;
  }

  .btn-minimal:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }

  .btn-minimal-edit {
    color: #4a6baf;
    border-color: #4a6baf;
  }

  .btn-minimal-edit:hover {
    background-color: rgba(74, 107, 175, 0.05);
    color: #3a5a9f;
    border-color: #3a5a9f;
  }

  .btn-minimal-delete {
    color: #e74c3c;
    border-color: #e74c3c;
  }

  .btn-minimal-delete:hover {
    background-color: rgba(231, 76, 60, 0.05);
    color: #d62c1a;
    border-color: #d62c1a;
  }

  .btn-minimal-perms {
    color: #2ecc71;
    border-color: #2ecc71;
  }

  .btn-minimal-perms:hover {
    background-color: rgba(46, 204, 113, 0.05);
    color: #27ae60;
    border-color: #27ae60;
  }

  .btn-minimal i {
    font-size: 0.9rem;
  }

  /* Contenedor de botones */
  .btn-group-minimal {
    display: flex;
    flex-wrap: nowrap;
    gap: 6px;
  }

  /* Botones de modales minimalistas */
  .btn-modal {
    background: transparent;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px 20px;
    color: #555;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .btn-modal:hover {
    background-color: #f8f9fa;
    border-color: #ccc;
  }

  .btn-modal-primary {
    color: #4a6baf;
    border-color: #4a6baf;
  }

  .btn-modal-primary:hover {
    background-color: rgba(74, 107, 175, 0.1);
  }

  /* Estilos para DataTable minimalista */
  #agentsTable {
    border-collapse: separate;
    border-spacing: 0;
  }

  #agentsTable thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e0e0e0;
    color: #555;
    font-weight: 600;
    padding: 12px 15px;
  }

  #agentsTable tbody td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
  }

  #agentsTable tbody tr:hover {
    background-color: #fafafa;
  }

  /* Estilos para modales minimalistas */
  .modal-content {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  }

  .modal-header {
    border-bottom: 1px solid #eaeaea;
    background-color: #f8f9fa;
    border-radius: 10px 10px 0 0;
    padding: 15px 20px;
  }

  .modal-body {
    padding: 20px;
  }

  .modal-footer {
    border-top: 1px solid #eaeaea;
    padding: 15px 20px;
  }

  /* Inputs minimalistas */
  .form-control {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px 12px;
    transition: border-color 0.2s ease;
  }

  .form-control:focus {
    border-color: #4a6baf;
    box-shadow: 0 0 0 0.2rem rgba(74, 107, 175, 0.1);
  }

  /* Botón de añadir agente minimalista */
  .btn-add-minimal {
    background: transparent;
    border: 2px dashed #4a6baf;
    color: #4a6baf;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
  }

  .btn-add-minimal:hover {
    background-color: rgba(74, 107, 175, 0.05);
    border-style: solid;
    transform: translateY(-1px);
  }

  .btn-add-minimal i {
    font-size: 1rem;
  }
</style>
</style>

<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Agentes</h1>
    <button type="button" class="button" data-toggle="modal" data-target="#modalNuevoAgente">
  <span class="button__text">Registrar Nuevo Agente</span>
  <span class="button__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" viewBox="0 0 24 24" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" stroke="currentColor" height="24" fill="none" class="svg"><line y2="19" y1="5" x2="12" x1="12"></line><line y2="12" y1="12" x2="19" x1="5"></line></svg></span>
</button>
  </div>

  <div class="card shadow mb-4">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="agentsTable" width="100%">
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
            <?php foreach ($agentes as $agente): ?>
              <tr>
                <td><?php echo htmlspecialchars($agente['cedula']); ?></td>
                <td><?php echo htmlspecialchars($agente['nombre'] . ' ' . $agente['apellido']); ?></td>
                <td><?php echo htmlspecialchars($agente['email']); ?></td>
                <td><?php echo htmlspecialchars($agente['telefono']); ?></td>
                <td>
                  <div class="btn-group-minimal">
                    <button class="btn-minimal btn-minimal-edit editAgentBtn" 
                            data-cedula="<?php echo htmlspecialchars($agente['cedula']); ?>"
                            data-nombre="<?php echo htmlspecialchars($agente['nombre']); ?>"
                            data-apellido="<?php echo htmlspecialchars($agente['apellido']); ?>"
                            data-email="<?php echo htmlspecialchars($agente['email']); ?>"
                            data-telefono="<?php echo htmlspecialchars($agente['telefono']); ?>"
                            data-toggle="modal" 
                            data-target="#modalEditarAgente"
                            title="Editar Agente">
                      <i class="fas fa-edit"></i>
                      <span></span>
                    </button>
                    
                    <button class="btn-minimal btn-minimal-delete deleteAgentBtn" 
                            data-cedula="<?php echo htmlspecialchars($agente['cedula']); ?>"
                            title="Eliminar Agente">
                      <i class="fas fa-trash-alt"></i>
                      <span></span>
                    </button>
                    
                    <button class="btn-minimal btn-minimal-perms managePermsBtn" 
                            data-cedula="<?php echo htmlspecialchars($agente['cedula']); ?>" 
                            data-nombre="<?php echo htmlspecialchars($agente['nombre'] . ' ' . $agente['apellido']); ?>"
                            title="Gestionar Permisos">
                      <i class="fas fa-key"></i>
                      <span></span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- /.container-fluid -->

<!-- Modal de Permisos -->
<div class="modal fade" id="permisosModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Gestionar Permisos para <strong id="nombreAgentePermisos"></strong></h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form id="permisosForm">
          <input type="hidden" id="cedulaAgentePermisos" name="cedula_agente">
          <div id="listaPermisos" class="container-fluid">
            <!-- Checkboxes se cargarán aquí dinámicamente -->
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="neu-button" type="button" data-dismiss="modal">Cancelar</button>
        <button id="btnGuardarPermisos" class="neu-button">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- NEW: Modal Registrar Nuevo Agente -->
<div class="modal fade" id="modalNuevoAgente" tabindex="-1" role="dialog" aria-labelledby="modalLabelNuevoAgente" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabelNuevoAgente">Registrar Nuevo Agente</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      <div class="modal-body">
            <form id="nuevoAgenteForm" novalidate>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="agenteCedula">Cédula <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                    <input type="text" class="form-control" id="agenteCedula" name="cedula" required placeholder="V12345678">
                  </div>
                  <div class="invalid-feedback">Cédula requerida (ej: V12345678).</div>
                </div>
                <div class="form-group col-md-6">
                  <label for="agenteTelefono">Teléfono <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                    <input type="text" class="form-control" id="agenteTelefono" name="telefono" required placeholder="0414xxxxxxx">
                  </div>
                  <div class="invalid-feedback">Teléfono requerido.</div>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="agenteNombre">Nombre <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="agenteNombre" name="nombre" required placeholder="Nombre">
                  <div class="invalid-feedback">Nombre requerido.</div>
                </div>
                <div class="form-group col-md-6">
                  <label for="agenteApellido">Apellido <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="agenteApellido" name="apellido" required placeholder="Apellido">
                  <div class="invalid-feedback">Apellido requerido.</div>
                </div>
              </div>
              <div class="form-group">
                <label for="agenteEmail">Email <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                  <input type="email" class="form-control" id="agenteEmail" name="email" required placeholder="correo@dominio.tld">
                </div>
                <div class="invalid-feedback">Email válido requerido.</div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="agentePassword">Contraseña <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="agentePassword" name="password" required placeholder="Mínimo 8 caracteres">
                  <small class="form-text text-muted">La contraseña debe tener al menos 8 caracteres.</small>
                  <div class="invalid-feedback">Contraseña requerida (mínimo 8 caracteres).</div>
                </div>
                <div class="form-group col-md-6">
                  <label for="agentePasswordConfirm">Confirmar Contraseña <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="agentePasswordConfirm" required placeholder="Repita la contraseña">
                  <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                </div>
              </div>
            </form>
            <div id="respuestaCrearAgente" style="display:none;" class="mt-2"></div>
          </div>
          <div class="modal-footer">
            <button class="neu-button" type="button" data-dismiss="modal">Cancelar</button>
            <button id="btnGuardarAgente" class="neu-button">Crear Agente</button>
          </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEditarAgente" tabindex="-1" role="dialog" aria-labelledby="modalLabelEditarAgente" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabelEditarAgente">Editar Agente: </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editarAgenteForm" novalidate>
                    <input type="hidden" id="editCedulaOriginal" name="cedula_original"> 
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="editAgenteCedula">Cédula <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                                <input type="text" class="form-control" id="editAgenteCedula" name="cedula" required readonly> 
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="editAgenteTelefono">Teléfono <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                <input type="text" class="form-control" id="editAgenteTelefono" name="telefono" required placeholder="0414xxxxxxx">
                            </div>
                            <div class="invalid-feedback">Teléfono requerido.</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="editAgenteNombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editAgenteNombre" name="nombre" required placeholder="Nombre">
                            <div class="invalid-feedback">Nombre requerido.</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="editAgenteApellido">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editAgenteApellido" name="apellido" required placeholder="Apellido">
                            <div class="invalid-feedback">Apellido requerido.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editAgenteEmail">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                            <input type="email" class="form-control" id="editAgenteEmail" name="email" required placeholder="correo@dominio.tld">
                        </div>
                        <div class="invalid-feedback">Email válido requerido.</div>
                    </div>
                    <div class="alert alert-info" role="alert">
                      Deje los campos de contraseña en blanco si no desea cambiarlos.
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="editAgentePassword">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="editAgentePassword" name="password" placeholder="Mínimo 8 caracteres">
                            <small class="form-text text-muted">Mínimo 8 caracteres si se provee.</small>
                            <div class="invalid-feedback">Contraseña debe tener al menos 8 caracteres.</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="editAgentePasswordConfirm">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="editAgentePasswordConfirm" placeholder="Repita la contraseña">
                            <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                        </div>
                    </div>
                </form>
                <div id="respuestaEditarAgente" style="display:none;" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button class="neu-button" type="button" data-dismiss="modal">Cancelar</button>
                <button id="btnActualizarAgente" class="neu-button">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/parte_inferior.php';
?>

<!-- Page level plugins -->
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script>
$(document).ready(function() {
  // Inicializar DataTable
  $('#agentsTable').DataTable({
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
    }
  });

  /**
   * Formatea un nombre de permiso (ej. 'polizas_crear') a un texto legible ('Crear').
   * @param {string} nombrePermiso - El nombre del permiso.
   * @returns {string} El texto formateado.
   */
  function formatPermisoLabel(nombrePermiso) {
    const parts = nombrePermiso.split('_');
    if (parts.length > 1) {
      const action = parts.slice(1).join(' ');
      return action.charAt(0).toUpperCase() + action.slice(1);
    }
    return nombrePermiso;
  }

  /**
   * Formatea un nombre de grupo (ej. 'polizas') a un texto legible ('Pólizas').
   * @param {string} nombreGrupo - El nombre del grupo.
   * @returns {string} El texto formateado.
   */
  function formatGrupoLabel(nombreGrupo) {
    return nombreGrupo.charAt(0).toUpperCase() + nombreGrupo.slice(1);
  }

  // --- LÓGICA PARA GESTIONAR PERMISOS ---
  $(document).on('click', '.managePermsBtn', function() {
    const cedula = $(this).data('cedula');
    const nombre = $(this).data('nombre');
    $('#nombreAgentePermisos').text(nombre);
    $('#cedulaAgentePermisos').val(cedula);
    $('#permisosModal').modal('show');
    $('#listaPermisos').html('<div class="text-center"><div class="spinner-border text-primary"></div></div>');
    
    $.ajax({
      url: 'controlador/controladorPermisoAgente.php',
      type: 'GET',
      data: { accion: 'obtener_permisos_agente', cedula_agente: cedula },
      dataType: 'json',
      success: function(respuesta) {
        if (respuesta.estado === 'exito' && respuesta.permisos.length > 0) {
          const grupos = {};
          
          // 1. Agrupar permisos
          respuesta.permisos.forEach(function(permiso) {
            const parts = permiso.nombre_permiso.split('_');
            const grupo = parts[0] || 'general';
            if (!grupos[grupo]) {
              grupos[grupo] = [];
            }
            grupos[grupo].push(permiso);
          });

          // 2. Construir el HTML
          let html = '<div class="row">';
          for (const nombreGrupo in grupos) {
            html += `
              <div class="col-md-6 mb-4">
                <div class="card perm-group-card">
                  <div class="card-header">${formatGrupoLabel(nombreGrupo)}</div>
                  <div class="card-body">`;
            
            grupos[nombreGrupo].forEach(function(permiso) {
              const isChecked = permiso.activo ? 'checked' : '';
              const label = formatPermisoLabel(permiso.nombre_permiso);
              html += `
                <div class="form-check perm-check">
                  <input type="checkbox" class="form-check-input" name="permisos[]" value="${permiso.id_permiso}" id="perm-${permiso.id_permiso}" ${isChecked}>
                  <label class="form-check-label w-100" for="perm-${permiso.id_permiso}">
                    ${label}
                  </label>
                </div>`;
            });

            html += '</div></div></div>'; // Cierre de card-body, card y col
          }
          html += '</div>'; // Cierre de row

          $('#listaPermisos').html(html);

        } else if (respuesta.permisos.length === 0) {
          $('#listaPermisos').html('<p class="text-center">No hay permisos definidos en el sistema.</p>');
        } else {
          $('#listaPermisos').html(`<p class="text-danger text-center">Error: ${respuesta.mensaje}</p>`);
        }
      },
      error: function() {
        $('#listaPermisos').html('<p class="text-danger text-center">Error de conexión al cargar los permisos.</p>');
      }
    });
  });

  // --- LÓGICA PARA GUARDAR PERMISOS ---
  $('#btnGuardarPermisos').on('click', function() {
    const form = $('#permisosForm');
    const boton = $(this);
    boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
    
    $.ajax({
      url: 'controlador/controladorPermisoAgente.php',
      type: 'POST',
      data: form.serialize() + '&accion=actualizar_permisos_agente',
      dataType: 'json',
      success: function(respuesta) {
        if (respuesta.estado === 'exito') {
          $('#permisosModal').modal('hide');
          // Opcional: mostrar una notificación de éxito más elegante
          alert(respuesta.mensaje);
        } else {
          alert(`Error al guardar: ${respuesta.mensaje}`);
        }
      },
      error: function() {
        alert('Error de conexión al guardar los permisos.');
      },
      complete: function() {
        boton.prop('disabled', false).text('Guardar Cambios');
      }
    });
  });
  
  // --- LÓGICA PARA CREAR NUEVO AGENTE ---
  $('#btnGuardarAgente').on('click', function() {
    const form = $('#nuevoAgenteForm');
    const boton = $(this);
    $('#respuestaCrearAgente').hide().html('');
    boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

    // Client-side validations antes de enviar
    const cedula = $('#agenteCedula').val().trim();
    const nombre = $('#agenteNombre').val().trim();
    const apellido = $('#agenteApellido').val().trim();
    const email = $('#agenteEmail').val().trim();
    const password = $('#agentePassword').val() || '';
    const passwordConfirm = $('#agentePasswordConfirm').val() || '';
    const telefono = $('#agenteTelefono').val().trim();

    // Patterns: V + 7-8 digitos; or (J|G|E|EM) + 7-8 digitos + - + dijito de chekeo
    const rePersona = /^V\d{7,8}$/i;
    const reEntidad = /^(J|G|E|EM)\d{7,8}-\d{1}$/i;
    const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const reTelefono = /^[0-9\-\s\+]{7,20}$/;

    // Required fields validation
    if (!cedula) { showCreateError('Complete la cédula.'); return; }
    if (!nombre) { showCreateError('Complete el nombre.'); return; }
    if (!apellido) { showCreateError('Complete el apellido.'); return; }
    if (!email) { showCreateError('Complete el email.'); return; }
    if (!telefono) { showCreateError('Complete el teléfono.'); return; }

    if (!(rePersona.test(cedula) || reEntidad.test(cedula))) { showCreateError('Formato de cédula inválido. Ej: V12345678 o J12345678-9'); return; }
    if (!reEmail.test(email)) { showCreateError('Email inválido.'); return; }
    if (!reTelefono.test(telefono)) { showCreateError('Teléfono inválido.'); return; }

    // Password rules: required and match
    if (!password || password.length < 8) { showCreateError('La contraseña debe tener al menos 8 caracteres.'); return; }
    if (password !== passwordConfirm) { showCreateError('Las contraseñas no coinciden.'); return; }

    function showCreateError(msg) {
      $('#respuestaCrearAgente').show().html('<div class="alert alert-danger">' + msg + '</div>');
      boton.prop('disabled', false).text('Crear Agente');
    }

    // Enviar si todo ok
    $.ajax({
      url: 'controlador/controladorUsuario.php',
      type: 'POST',
      data: form.serialize() + '&accion=crear_usuario',
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#modalNuevoAgente').modal('hide');
          let msg = res.message || 'Agente creado correctamente.';
          if (res.password) {
            msg += '\nContraseña generada: ' + res.password;
          }
          alert(msg);
          // Recargar la página para actualizar la tabla (fácil y seguro)
          location.reload();
        } else {
          $('#respuestaCrearAgente').show().html('<div class="alert alert-danger">' + (res.message || 'Error al crear agente') + '</div>');
        }
      },
      error: function() {
        $('#respuestaCrearAgente').show().html('<div class="alert alert-danger">Error de conexión al servidor.</div>');
      },
      complete: function() {
        boton.prop('disabled', false).text('Crear Agente');
      }
    });
  });

  // --- LÓGICA PARA EDITAR AGENTE ---
  $(document).on('click', '.editAgentBtn', function() {
    const btn = $(this);
    const cedula = btn.data('cedula');
    const nombre = btn.data('nombre');
    const apellido = btn.data('apellido');
    const email = btn.data('email');
    const telefono = btn.data('telefono');

    // 1. Rellenar el título del modal
    $('#modalLabelEditarAgente').text('Editar Agente: ' + nombre + ' ' + apellido);

    // 2. Rellenar los campos del formulario
    $('#editCedulaOriginal').val(cedula); 
    $('#editAgenteCedula').val(cedula); 
    $('#editAgenteNombre').val(nombre);
    $('#editAgenteApellido').val(apellido);
    $('#editAgenteEmail').val(email);
    $('#editAgenteTelefono').val(telefono);
    
    // 3. Limpiar campos de contraseña y mensajes
    $('#editAgentePassword').val('');
    $('#editAgentePasswordConfirm').val('');
    $('#respuestaEditarAgente').hide().html('');
    
    // 4. Limpiar posibles estados de validación
    $('#editarAgenteForm').removeClass('was-validated');
  });

  // Lógica para guardar cambios en edición
  $('#btnActualizarAgente').on('click', function() {
    const form = $('#editarAgenteForm');
    const boton = $(this);
    $('#respuestaEditarAgente').hide().html('');
    boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

    // Client-side validations
    const cedulaOriginal = $('#editCedulaOriginal').val();
    const cedula = $('#editAgenteCedula').val().trim();
    const nombre = $('#editAgenteNombre').val().trim();
    const apellido = $('#editAgenteApellido').val().trim();
    const email = $('#editAgenteEmail').val().trim();
    const telefono = $('#editAgenteTelefono').val().trim();
    const password = $('#editAgentePassword').val() || '';
    const passwordConfirm = $('#editAgentePasswordConfirm').val() || '';

    // Required fields validation
    if (!cedula) { showEditError('Complete la cédula.'); return; }
    if (!nombre) { showEditError('Complete el nombre.'); return; }
    if (!apellido) { showEditError('Complete el apellido.'); return; }
    if (!email) { showEditError('Complete el email.'); return; }
    if (!telefono) { showEditError('Complete el teléfono.'); return; }

    // Validar formato de cédula
    const rePersona = /^V\d{7,8}$/i;
    const reEntidad = /^(J|G|E|EM)\d{7,8}-\d{1}$/i;
    if (!(rePersona.test(cedula) || reEntidad.test(cedula))) { 
      showEditError('Formato de cédula inválido. Ej: V12345678 o J12345678-9'); 
      return; 
    }

    // Validar email
    const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!reEmail.test(email)) { showEditError('Email inválido.'); return; }

    // Validar teléfono
    const reTelefono = /^[0-9\-\s\+]{7,20}$/;
    if (!reTelefono.test(telefono)) { showEditError('Teléfono inválido.'); return; }

    // Validar contraseña si se proporciona
    if (password && password.length < 8) { 
      showEditError('La nueva contraseña debe tener al menos 8 caracteres.'); 
      return; 
    }
    if (password && password !== passwordConfirm) { 
      showEditError('Las contraseñas no coinciden.'); 
      return; 
    }

    function showEditError(msg) {
      $('#respuestaEditarAgente').show().html('<div class="alert alert-danger">' + msg + '</div>');
      boton.prop('disabled', false).text('Guardar Cambios');
    }

    // Enviar datos
    const formData = new FormData();
    formData.append('accion', 'actualizar_usuario');
    formData.append('cedula_original', cedulaOriginal);
    formData.append('cedula', cedula);
    formData.append('nombre', nombre);
    formData.append('apellido', apellido);
    formData.append('email', email);
    formData.append('telefono', telefono);
    if (password) {
      formData.append('password', password);
    }

    $.ajax({
      url: 'controlador/controladorUsuario.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#modalEditarAgente').modal('hide');
          alert(res.message || 'Agente actualizado correctamente.');
          location.reload();
        } else {
          $('#respuestaEditarAgente').show().html('<div class="alert alert-danger">' + (res.message || 'Error al actualizar agente') + '</div>');
        }
      },
      error: function() {
        $('#respuestaEditarAgente').show().html('<div class="alert alert-danger">Error de conexión al servidor.</div>');
      },
      complete: function() {
        boton.prop('disabled', false).text('Guardar Cambios');
      }
    });
  });

  // --- LÓGICA PARA ELIMINAR AGENTE ---
  $(document).on('click', '.btn-danger', function() {
    const cedula = $(this).closest('tr').find('td:first').text().trim();
    const nombre = $(this).closest('tr').find('td:nth-child(2)').text().trim();
    
    if (confirm(`¿Está seguro de eliminar al agente ${nombre} (${cedula})? Esta acción no se puede deshacer.`)) {
      const boton = $(this);
      boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
      
      $.ajax({
        url: 'controlador/controladorUsuario.php',
        type: 'POST',
        data: { 
          accion: 'eliminar_usuario',
          cedula: cedula
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            alert(res.message || 'Agente eliminado correctamente.');
            location.reload();
          } else {
            alert(res.message || 'Error al eliminar agente.');
            boton.prop('disabled', false).text('Eliminar');
          }
        },
        error: function() {
          alert('Error de conexión al servidor.');
          boton.prop('disabled', false).text('Eliminar');
        }
      });
    }
  });

  // --- VALIDACIÓN DE FORMULARIOS ---
  // Validación en tiempo real para nuevo agente
  $('#nuevoAgenteForm input').on('blur', function() {
    const field = $(this);
    if (field.is('#agentePassword') || field.is('#agentePasswordConfirm')) {
      validatePasswords();
    } else if (field.is('#agenteCedula')) {
      validateCedula(field.val().trim());
    } else if (field.is('#agenteEmail')) {
      validateEmail(field.val().trim());
    }
  });

  function validatePasswords() {
    const pass = $('#agentePassword').val();
    const confirm = $('#agentePasswordConfirm').val();
    
    if (pass && confirm && pass !== confirm) {
      $('#agentePasswordConfirm').addClass('is-invalid');
      $('#agentePasswordConfirm').next('.invalid-feedback').text('Las contraseñas no coinciden.');
    } else {
      $('#agentePasswordConfirm').removeClass('is-invalid');
    }
  }

  function validateCedula(cedula) {
    const rePersona = /^V\d{7,8}$/i;
    const reEntidad = /^(J|G|E|EM)\d{7,8}-\d{1}$/i;
    
    if (cedula && !(rePersona.test(cedula) || reEntidad.test(cedula))) {
      $('#agenteCedula').addClass('is-invalid');
    } else {
      $('#agenteCedula').removeClass('is-invalid');
    }
  }

  function validateEmail(email) {
    const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !reEmail.test(email)) {
      $('#agenteEmail').addClass('is-invalid');
    } else {
      $('#agenteEmail').removeClass('is-invalid');
    }
  }
});
</script>