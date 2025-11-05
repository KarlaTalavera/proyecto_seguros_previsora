<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

$modeloUsuario = new ModeloUsuario();
$todos_los_usuarios = $modeloUsuario->obtenerTodosLosUsuarios();

// FIX: Hacer el filtro case-insensitive y asegurarse de que es un array
$agentes = [];
if (is_array($todos_los_usuarios)) {
    $agentes = array_filter($todos_los_usuarios, function($usuario) {
        return isset($usuario['rol']) && strtolower($usuario['rol']) === 'agente';
    });
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
  width: 150px;
  height: 45px;
  cursor: pointer;
  display: flex;
  align-items: center;
  border: 1px solid #34974d;
  background-color: #3aa856;
}

.button, .button__icon, .button__text {
  transition: all 0.3s;
}

.button .button__text {

  transform: translateX(30px);
  color: #fff;
  font-weight: 600;
}

.button .button__icon {
  position: absolute;
  transform: translateX(109px);
  height: 100%;
  width: 39px;
  background-color: #34974d;
  display: flex;
  align-items: center;
  justify-content: center;
}

.button .svg {
  width: 30px;
  stroke: #fff;
}

.button:hover {
  background: #34974d;
}

.button:hover .button__text {
  color: transparent;
}

.button:hover .button__icon {
  width: 150px;
  transform: translateX(0);
}

.button:active .button__icon {
  background-color: #2e8644;
}

.button:active {
  border: 1px solid #2e8644;
}
</style>

<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Agentes</h1>
    <button type="button" class="button">
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
                  <button class="btn btn-sm btn-primary" title="Editar Agente">Editar</button>
                  <button class="btn btn-sm btn-danger" title="Eliminar Agente">Eliminar</button>
                  <button class="btn btn-sm btn-info managePermsBtn" 
                          data-cedula="<?php echo htmlspecialchars($agente['cedula']); ?>" 
                          data-nombre="<?php echo htmlspecialchars($agente['nombre'] . ' ' . $agente['apellido']); ?>"
                          title="Gestionar Permisos">
                    Permisos
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
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabelNuevoAgente">Registrar Nuevo Agente</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p>Aquí irá el formulario para un nuevo agente.</p>
        <!-- Próximamente: campos para cédula, nombre, apellido, email, etc. -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
        <button id="btnGuardarAgente" class="btn btn-primary">Guardar</button>
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
});
</script>
