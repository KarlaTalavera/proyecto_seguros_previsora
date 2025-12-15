<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once __DIR__ . '/parte_superior.php';

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
$permisosSesion = isset($_SESSION['permisos_usuario']) && is_array($_SESSION['permisos_usuario']) ? $_SESSION['permisos_usuario'] : [];

$rol = '';
if (is_object($usuarioActual) && method_exists($usuarioActual, 'getNombreRol')) {
    $rol = strtolower((string)$usuarioActual->getNombreRol());
} elseif (is_array($usuarioActual) && isset($usuarioActual['rol'])) {
    $rol = strtolower((string)$usuarioActual['rol']);
}

$esAdmin = $rol === 'administrador';
$esAgente = $rol === 'agente';
$tienePermisoAgente = $esAgente && in_array('solicitud_gestionar', $permisosSesion, true);

if (!$esAdmin && !$tienePermisoAgente) {
    header('Location: ../index.php');
    exit;
}

$estadosPoliza = [
    ['value' => 'EN_REVISION', 'label' => 'En revisión'],
    ['value' => 'CONTACTADO', 'label' => 'Contactado'],
    ['value' => 'EN_PROCESO', 'label' => 'En proceso'],
    ['value' => 'APROBADO', 'label' => 'Aprobado'],
    ['value' => 'RECHAZADO', 'label' => 'Rechazado'],
    ['value' => 'CANCELADO', 'label' => 'Cancelado'],
];

$estadosSiniestro = [
    ['value' => 'EN_REVISION', 'label' => 'En revisión'],
    ['value' => 'CITA_PENDIENTE', 'label' => 'Cita pendiente'],
    ['value' => 'EN_GESTION', 'label' => 'En gestión'],
    ['value' => 'CERRADO', 'label' => 'Cerrado'],
    ['value' => 'CANCELADO', 'label' => 'Cancelado'],
];

$estadosConfig = [
    'poliza' => $estadosPoliza,
    'siniestro' => $estadosSiniestro,
];

$agentesDisponibles = [];
if ($esAdmin) {
    $modeloUsuario = new ModeloUsuario();
    $agentes = $modeloUsuario->obtenerAgentesAsignables();
    if (is_array($agentes)) {
        foreach ($agentes as $agente) {
            $agentesDisponibles[] = [
                'cedula' => $agente['cedula'] ?? '',
                'nombre' => trim(($agente['nombre'] ?? '') . ' ' . ($agente['apellido'] ?? '')),
            ];
        }
    }
}

$agentesJson = json_encode($agentesDisponibles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$estadosJson = json_encode($estadosConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
// Obtener cédula del usuario en sesión de forma segura (evitar dependencias a funciones de controlador)
$cedulaSesion = '';
if (is_object($usuarioActual) && method_exists($usuarioActual, 'getCedula')) {
  $cedulaSesion = $usuarioActual->getCedula();
} elseif (is_array($usuarioActual) && isset($usuarioActual['cedula'])) {
  $cedulaSesion = $usuarioActual['cedula'];
}
?>

<div class="container-fluid">
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-1 text-gray-800">Gestión de solicitudes</h1>
      <p class="text-muted mb-0">Supervisa, actualiza y coordina las solicitudes de pólizas y siniestros con tus clientes.</p>
    </div>
    <?php if ($esAdmin): ?>
      <span class="badge badge-primary p-2">Modo administrador</span>
    <?php else: ?>
      <span class="badge badge-info p-2">Solicitudes asignadas</span>
    <?php endif; ?>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="row align-items-end">
        <div class="col-md-3 mb-3">
          <label for="filtroTipo">Tipo</label>
          <select class="form-control" id="filtroTipo">
            <option value="">Todos</option>
            <option value="poliza">Pólizas</option>
            <option value="siniestro">Siniestros</option>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label for="filtroEstado">Estado</label>
          <select class="form-control" id="filtroEstado">
            <option value="">Todos</option>
          </select>
        </div>
        <?php if ($esAdmin): ?>
        <div class="col-md-3 mb-3">
          <label for="filtroAgente">Agente asignado</label>
          <select class="form-control" id="filtroAgente">
            <option value="">Todos</option>
            <?php foreach ($agentesDisponibles as $agente): ?>
              <option value="<?php echo htmlspecialchars($agente['cedula'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($agente['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="col-md-3 mb-3">
          <label for="filtroBusqueda">Búsqueda rápida</label>
          <input type="search" class="form-control" id="filtroBusqueda" placeholder="Cliente, código o detalle">
        </div>
      </div>
    </div>
  </div>

      <div class="card shadow-sm">
    <div class="card-body">
      <div id="solicitudesAlert" class="mb-3"></div>
      <div class="table-responsive">
        
        <table class="table table-striped table-hover" id="tablaSolicitudesGestion" width="100%">
          <thead class="thead-light">
            <tr>
              <th>Código</th>
              <th>Cliente</th>
              <th>Tipo</th>
              <th>Resumen</th>
              <th>Asignado</th>
              <th>Estado</th>
              <th>Actualización</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php
          // Renderizado server-side: obtener solicitudes asignadas y volcar filas HTML
          require_once dirname(__DIR__) . '/modelo/modeloSolicitud.php';
          $modeloSolicitud = new ModeloSolicitud();
          $ced = trim((string)($cedulaSesion ?? '')) ?: null;
          $filas = $modeloSolicitud->obtenerSolicitudesAsignadas($ced, $esAdmin);
            if (!is_array($filas)) $filas = [];
            // show count for debugging
              if (empty($filas)) {
                // Output one row with the same number of TDs as THs to avoid DataTables "Incorrect column count" warning
                echo '<tr>';
                echo '<td><em>No hay solicitudes devueltas por el modelo. Filas=0</em></td>';
                // add empty cells to match header columns
                for ($i = 0; $i < 7; $i++) echo '<td></td>';
                echo '</tr>';
              }
            foreach ($filas as $item):
              $codigo = ($item['origen'] === 'poliza' ? 'SP-' : 'SS-') . str_pad((string)$item['id'], 5, '0', STR_PAD_LEFT);
              $cliente = htmlspecialchars($item['cliente'] ?? 'Desconocido', ENT_QUOTES, 'UTF-8');
              $tipo = ($item['origen'] === 'poliza') ? 'Solicitud de póliza' : 'Reporte de siniestro';
              $resumen = htmlspecialchars(mb_strimwidth($item['descripcion'] ?? ($item['tipo_incidente'] ?? ''), 0, 120, '...'), ENT_QUOTES, 'UTF-8');
              $asignado = htmlspecialchars($item['asignado'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8');
              $estado_label = htmlspecialchars($item['estado_label'] ?? $item['estado'] ?? '', ENT_QUOTES, 'UTF-8');
              $fecha_raw = $item['fecha_actualizacion'] ?? $item['fecha'] ?? null;
              $fecha_display = $fecha_raw ? date('d/m/Y H:i', strtotime($fecha_raw)) : '';
              $origen = htmlspecialchars($item['origen'], ENT_QUOTES, 'UTF-8');
              $id = (int)$item['id'];
          ?>
            <tr>
              <td><?php echo $codigo; ?></td>
              <td><?php echo $cliente; ?></td>
              <td><?php echo $tipo; ?></td>
              <td><?php echo $resumen; ?></td>
              <td><?php echo $asignado; ?></td>
              <td><?php echo $estado_label; ?></td>
              <td><?php echo htmlspecialchars($fecha_display, ENT_QUOTES, 'UTF-8'); ?></td>
              <td>
                <a class="btn btn-sm btn-light" href="#" onclick="return false;">Ver</a>
                <a class="btn btn-sm btn-secondary" href="#" onclick="return false;">Estado</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="detalleSolicitudModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de la solicitud</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="detalleSolicitudContenido"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="cambiarEstadoModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Actualizar estado</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formActualizarEstado">
          <input type="hidden" id="estadoSolicitudId">
          <input type="hidden" id="estadoSolicitudOrigen">
          <div class="form-group">
            <label for="estadoNuevo">Nuevo estado</label>
            <select class="form-control" id="estadoNuevo" required></select>
          </div>
          <div class="form-group">
            <label for="estadoNota">Nota interna</label>
            <textarea class="form-control" id="estadoNota" rows="3" placeholder="Comparte próximas citas, acuerdos o mensajes para el cliente"></textarea>
            <small class="form-text text-muted">Este mensaje se mostrará en el historial del cliente junto al estado actualizado.</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarEstado">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="reasignarSolicitudModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reasignar solicitud</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formReasignarSolicitud">
          <input type="hidden" id="reasignarSolicitudId">
          <input type="hidden" id="reasignarSolicitudOrigen">
          <div class="form-group">
            <label for="reasignarAgente">Asignar a</label>
            <select class="form-control" id="reasignarAgente" required>
              <option value="">Selecciona un agente</option>
              <?php if ($esAdmin): ?>
                <?php foreach ($agentesDisponibles as $agente): ?>
                  <option value="<?php echo htmlspecialchars($agente['cedula'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($agente['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              <?php elseif ($esAgente): ?>
                <option value="<?php echo htmlspecialchars($cedulaSesion, ENT_QUOTES, 'UTF-8'); ?>">
                  Mí mismo
                </option>
              <?php endif; ?>
            </select>
            <small class="form-text text-muted">
              <?php if ($esAdmin): ?>
                Selecciona el agente que gestionará esta solicitud
              <?php elseif ($esAgente): ?>
                Solo puedes asignar solicitudes a tu propia cuenta
              <?php endif; ?>
            </small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarReasignacion">Guardar</button>
      </div>
    </div>
  </div>
</div>

<?php
$dataTablesCore = resolveAssetPath(
  'vendor/datatables/jquery.dataTables.min.js',
  'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js'
);
$dataTablesBootstrap = resolveAssetPath(
  'vendor/datatables/dataTables.bootstrap4.min.js',
  'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js'
);

ob_start();
?>
<script>
  window.SolicitudesGestionConfig = {
    esAdmin: <?php echo $esAdmin ? 'true' : 'false'; ?>,
    estados: <?php echo $estadosJson ?: '{}'; ?>,
    agentes: <?php echo $agentesJson ?: '[]'; ?>
  };
</script>
<script src="<?php echo htmlspecialchars($dataTablesCore, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($dataTablesBootstrap, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="js/solicitudesGestion.js"></script>
<?php
$scriptsBuffer = ob_get_clean();
if (isset($extra_scripts) && is_string($extra_scripts)) {
    $extra_scripts .= $scriptsBuffer;
} else {
    $extra_scripts = $scriptsBuffer;
}
require_once __DIR__ . '/parte_inferior.php';
?>