<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';
require_once dirname(__DIR__) . '/modelo/modeloSolicitud.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function vistaSolicitudEsCliente($usuario): bool
{
    if (!$usuario) {
        return false;
    }
    if (is_object($usuario) && method_exists($usuario, 'getNombreRol')) {
        $rol = strtolower((string)$usuario->getNombreRol());
        return in_array($rol, ['asegurado', 'cliente'], true);
    }
    if (is_array($usuario) && isset($usuario['rol'])) {
        $rol = strtolower((string)$usuario['rol']);
        return in_array($rol, ['asegurado', 'cliente'], true);
    }
    return false;
}

function vistaSolicitudObtenerCedula($usuario): ?string
{
    if (!$usuario) {
        return null;
    }
    if (is_object($usuario) && method_exists($usuario, 'getCedula')) {
        return (string)$usuario->getCedula();
    }
    if (is_array($usuario) && isset($usuario['cedula'])) {
        return (string)$usuario['cedula'];
    }
    return null;
}

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
if (!vistaSolicitudEsCliente($usuarioActual)) {
    header('Location: ../index.php');
    exit;
}

$cedulaCliente = vistaSolicitudObtenerCedula($usuarioActual);

$modeloPoliza = new ModeloPoliza();
$modeloSolicitud = new ModeloSolicitud();

$categoriasPoliza = $modeloPoliza->obtenerCategorias() ?: [];
$tiposPolizaPorCategoria = [];

foreach ($categoriasPoliza as $categoria) {
    $idCat = isset($categoria['id_categoria']) ? (int)$categoria['id_categoria'] : 0;
    if ($idCat <= 0) {
        continue;
    }
    $tipos = $modeloPoliza->obtenerRamosPorCategoria($idCat);
    if ($tipos) {
        $tiposPolizaPorCategoria[(string)$idCat] = array_map(static function ($row) {
            return [
                'id_tipo_poliza' => isset($row['id_tipo_poliza']) ? (int)$row['id_tipo_poliza'] : 0,
                'nombre' => $row['nombre'] ?? '',
            ];
        }, $tipos);
    }
}

$tipoPolizaMapaJson = json_encode($tiposPolizaPorCategoria, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!$tipoPolizaMapaJson) {
    $tipoPolizaMapaJson = '{}';
}

$initialCategoriaId = null;
if (!empty($categoriasPoliza)) {
    $initialCategoriaId = isset($categoriasPoliza[0]['id_categoria']) ? (int)$categoriasPoliza[0]['id_categoria'] : null;
}

$initialRamos = [];
if ($initialCategoriaId !== null) {
    $key = (string)$initialCategoriaId;
    if (isset($tiposPolizaPorCategoria[$key])) {
        $initialRamos = $tiposPolizaPorCategoria[$key];
    }
}

$polizasActivasCliente = [];
if ($cedulaCliente) {
    $polizasActivasCliente = $modeloSolicitud->obtenerPolizasActivasPorCliente($cedulaCliente) ?: [];
}

$hayPolizasActivas = !empty($polizasActivasCliente);
$puedeCrearPoliza = !empty($categoriasPoliza);

require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div class="mb-3 mb-md-0">
      <h1 class="h3 mb-1 text-gray-800">Mis solicitudes</h1>
      <p class="text-muted mb-0">Gestiona tus solicitudes de nuevas pólizas y reportes de siniestros desde un solo lugar.</p>
    </div>
    <div class="d-flex flex-wrap">
      <button type="button" class="btn btn-primary mr-2 mb-2" data-toggle="modal" data-target="#solicitarPolizaModal" <?php echo $puedeCrearPoliza ? '' : 'disabled'; ?>>
        <i class="fas fa-plus mr-1"></i> Solicitar póliza
      </button>
      <button type="button" class="btn btn-outline-primary mb-2" data-toggle="modal" data-target="#reportarSiniestroModal" <?php echo $hayPolizasActivas ? '' : 'disabled'; ?>>
        <i class="fas fa-flag mr-1"></i> Reportar siniestro
      </button>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="solicitudesTable">
          <thead>
            <tr>
              <th>Código</th>
              <th>Solicitud</th>
              <th>Resumen</th>
              <th>Creada</th>
              <th>Estado</th>
              <th>Seguimiento</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <p class="text-muted small mb-0">Los registros se actualizan automáticamente al enviar o cancelar solicitudes.</p>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="solicitarPolizaModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva solicitud de póliza</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formSolicitarPoliza">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="solicitudCategoria">Categoría</label>
              <select class="form-control" id="solicitudCategoria" name="categoria" <?php echo $puedeCrearPoliza ? '' : 'disabled'; ?>>
                <?php if ($puedeCrearPoliza): ?>
                  <?php foreach ($categoriasPoliza as $categoria): ?>
                    <?php $idCat = isset($categoria['id_categoria']) ? (int)$categoria['id_categoria'] : 0; ?>
                    <option value="<?php echo $idCat; ?>" <?php echo ($initialCategoriaId !== null && $initialCategoriaId === $idCat) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="">No hay categorías disponibles</option>
                <?php endif; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="solicitudRamo">Ramo</label>
              <select class="form-control" id="solicitudRamo" name="ramo" <?php echo !empty($initialRamos) ? '' : 'disabled'; ?>>
                <?php if (!empty($initialRamos)): ?>
                  <?php foreach ($initialRamos as $indice => $ramo): ?>
                    <?php $ramoId = isset($ramo['id_tipo_poliza']) ? (int)$ramo['id_tipo_poliza'] : 0; ?>
                    <option value="<?php echo $ramoId; ?>" <?php echo $indice === 0 ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($ramo['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="">Selecciona una categoría</option>
                <?php endif; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="solicitudDescripcion">Descripción</label>
            <textarea class="form-control" id="solicitudDescripcion" rows="3" placeholder="Describe lo que necesitas (opcional)"></textarea>
          </div>
          <div class="form-group">
            <label for="solicitudContacto">Contacto preferido</label>
            <input type="text" class="form-control" id="solicitudContacto" placeholder="Ej: correo o teléfono">
          </div>
        </form>
        <?php if (!$puedeCrearPoliza): ?>
          <div class="alert alert-warning mb-0" role="alert">
            Aún no hay categorías configuradas. Comunícate con soporte para completar la solicitud.
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="sendPoliza" <?php echo $puedeCrearPoliza ? '' : 'disabled'; ?>>Enviar solicitud</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="reportarSiniestroModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reportar siniestro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formReportarSiniestro">
          <div class="form-group">
            <label for="siniestroPoliza">Póliza relacionada</label>
            <select class="form-control" id="siniestroPoliza" name="poliza" <?php echo $hayPolizasActivas ? '' : 'disabled'; ?>>
              <?php if ($hayPolizasActivas): ?>
                <?php foreach ($polizasActivasCliente as $poliza): ?>
                  <?php $idPoliza = isset($poliza['id_poliza']) ? (int)$poliza['id_poliza'] : 0; ?>
                  <?php
                    $numero = htmlspecialchars($poliza['numero_poliza'] ?? '', ENT_QUOTES, 'UTF-8');
                    $ramo = htmlspecialchars($poliza['ramo'] ?? '', ENT_QUOTES, 'UTF-8');
                  ?>
                  <option value="<?php echo $idPoliza; ?>"><?php echo $numero ? $numero . ' - ' . $ramo : $ramo; ?></option>
                <?php endforeach; ?>
              <?php else: ?>
                <option value="">No tienes pólizas activas registradas</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="siniestroTipo">Tipo de incidente</label>
              <input type="text" class="form-control" id="siniestroTipo" placeholder="Ej: Colisión vehicular" <?php echo $hayPolizasActivas ? '' : 'disabled'; ?>>
            </div>
            <div class="form-group col-md-6">
              <label for="siniestroFecha">Fecha del incidente</label>
              <input type="date" class="form-control" id="siniestroFecha" <?php echo $hayPolizasActivas ? '' : 'disabled'; ?>>
            </div>
          </div>
          <div class="form-group">
            <label for="siniestroLugar">Lugar del incidente</label>
            <input type="text" class="form-control" id="siniestroLugar" placeholder="Ciudad o dirección" <?php echo $hayPolizasActivas ? '' : 'disabled'; ?>>
          </div>
          <div class="form-group">
            <label for="siniestroDescripcion">Descripción</label>
            <textarea class="form-control" id="siniestroDescripcion" rows="3" placeholder="Cuéntanos qué ocurrió" <?php echo $hayPolizasActivas ? '' : 'disabled'; ?>></textarea>
          </div>
        </form>
        <?php if (!$hayPolizasActivas): ?>
          <div class="alert alert-info mb-0" role="alert">
            Para reportar un siniestro necesitas tener al menos una póliza activa registrada.
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="sendSiniestro" <?php echo $hayPolizasActivas ? '' : 'disabled'; ?>>Enviar reporte</button>
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
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Solicitud</dt>
          <dd class="col-sm-8" id="detalleTipo">N/A</dd>
          <dt class="col-sm-4">Resumen</dt>
          <dd class="col-sm-8" id="detalleResumen">N/A</dd>
          <dt class="col-sm-4">Estado</dt>
          <dd class="col-sm-8" id="detalleEstado">N/A</dd>
          <dt class="col-sm-4">Creada</dt>
          <dd class="col-sm-8" id="detalleFecha">N/A</dd>
          <dt class="col-sm-4">Asignado</dt>
          <dd class="col-sm-8" id="detalleAsignado">N/A</dd>
          <dt class="col-sm-4">Contacto</dt>
          <dd class="col-sm-8" id="detalleContacto">N/A</dd>
          <dt class="col-sm-4">Descripción</dt>
          <dd class="col-sm-8" id="detalleDescripcion">N/A</dd>
          <dt class="col-sm-4">Seguimiento del agente</dt>
          <dd class="col-sm-8" id="detalleNotas">N/A</dd>
          <dt class="col-sm-4 siniestro-only">Incidente</dt>
          <dd class="col-sm-8 siniestro-only" id="detalleIncidente">N/A</dd>
          <dt class="col-sm-4 siniestro-only">Lugar</dt>
          <dd class="col-sm-8 siniestro-only" id="detalleLugar">N/A</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php
ob_start();
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  function buildControladorUrl() {
    var path = window.location.pathname || '';
    var vistaIndex = path.indexOf('/vista/');
    var base = '';
    if (vistaIndex !== -1) {
      base = path.substring(0, vistaIndex);
    } else {
      var lastSlash = path.lastIndexOf('/');
      base = lastSlash > -1 ? path.substring(0, lastSlash) : '';
    }
    base = base.replace(/\/+$/, '');
    return (base ? base : '') + '/controlador/controladorSolicitud.php';
  }

  var controladorSolicitudUrl = buildControladorUrl();
  if (!controladorSolicitudUrl) {
    console.error('No fue posible determinar la ruta del controlador de solicitudes.');
    return;
  }

  var $ = window.jQuery;
  var solicitudesTable = null;

  if ($ && $.fn && $.fn.DataTable) {
    solicitudesTable = $('#solicitudesTable').DataTable({
      ajax: {
        url: controladorSolicitudUrl,
        type: 'GET',
        data: { accion: 'listar_cliente' },
        dataSrc: function (resp) {
          if (!resp) { return []; }
          if (resp.success === false) {
            if (resp.message) {
              console.warn(resp.message);
            }
            if (!window.__solicitudesClienteError) {
              window.__solicitudesClienteError = true;
              alert(resp.message || 'No se pudieron cargar las solicitudes.');
            }
            return resp.data || [];
          }
          return resp.data || [];
        }
      },
      pageLength: 5,
      order: [[3, 'desc']],
      columns: [
        {
          data: 'id',
          render: function (data, type, row) {
            if (type === 'display') {
              var prefix = row.origen === 'siniestro' ? 'SS-' : 'SP-';
              var valor = String(data || '0');
              while (valor.length < 4) {
                valor = '0' + valor;
              }
              return prefix + valor;
            }
            return data;
          }
        },
        {
          data: 'origen',
          render: function (data) {
            return data === 'poliza' ? 'Solicitud de póliza' : 'Reporte de siniestro';
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            if (type !== 'display') { return row.descripcion || ''; }
            if (row.origen === 'poliza') {
              return (row.categoria || '') + ' - ' + (row.ramo || '');
            }
            var base = row.numero_poliza ? row.numero_poliza + ' - ' : '';
            return base + (row.tipo_incidente || row.ramo || '');
          }
        },
        {
          data: 'fecha',
          render: function (data) {
            if (!data) { return 'N/A'; }
            var parseable = typeof data === 'string' ? data.replace(' ', 'T') : data;
            var fecha = new Date(parseable);
            if (isNaN(fecha.getTime())) {
              return data;
            }
            return fecha.toLocaleString('es-VE', { dateStyle: 'short', timeStyle: 'short' });
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            if (type !== 'display') { return row.estado; }
            var variant = row.estado_variant || 'neutral';
            var label = row.estado_label || row.estado;
            return '<span class="badge-soft" data-variant="' + variant + '">' + label + '</span>';
          }
        },
        {
          data: 'nota_interna',
          render: function (data, type) {
            if (!data) { return type === 'display' ? '—' : ''; }
            if (type !== 'display') { return data; }
            var texto = String(data);
            var necesitaRecorte = texto.length > 60;
            var mostrar = necesitaRecorte ? texto.slice(0, 57) + '...' : texto;
            var escape = function (str) {
              return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            };
            var mostrarEscapado = escape(mostrar);
            if (necesitaRecorte) {
              return '<span title="' + escape(texto) + '">' + mostrarEscapado + '</span>';
            }
            return mostrarEscapado;
          }
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            if (type !== 'display') { return ''; }
            var buttons = '<div class="table-action-buttons">';
            buttons += '<button class="action-icon action-icon--perm" data-action="ver" title="Ver detalle"><i class="fas fa-eye"></i></button>';
            var cancelables = ['EN_REVISION', 'CONTACTADO', 'CITA_PENDIENTE'];
            if (cancelables.indexOf(row.estado) !== -1) {
              buttons += '<button class="action-icon action-icon--delete" data-action="cancelar" title="Cancelar solicitud"><i class="fas fa-times"></i></button>';
            }
            buttons += '</div>';
            return buttons;
          }
        }
      ],
      language: {
        sProcessing: 'Procesando...',
        sLengthMenu: 'Mostrar _MENU_ registros',
        sZeroRecords: 'No se encontraron resultados',
        sEmptyTable: 'Aun no has generado solicitudes',
        sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ solicitudes',
        sInfoEmpty: 'Mostrando 0 a 0 de 0 solicitudes',
        sInfoFiltered: '(filtrado de _MAX_ registros)',
        sSearch: 'Buscar:',
        sLoadingRecords: 'Cargando...',
        oPaginate: {
          sFirst: 'Primero',
          sLast: 'Ultimo',
          sNext: 'Siguiente',
          sPrevious: 'Anterior'
        }
      }
    });
  }

  var tipoPolizaPorCategoria = <?php echo $tipoPolizaMapaJson; ?>;
  var categoriaSelect = document.getElementById('solicitudCategoria');
  var ramoSelect = document.getElementById('solicitudRamo');
  var siniestroPolizaSelect = document.getElementById('siniestroPoliza');

  if ($) {
    $('.siniestro-only').hide();
  }

  function poblarRamos(categoriaId) {
    if (!ramoSelect) {
      return;
    }

    var opciones = [];
    if (categoriaId && Object.prototype.hasOwnProperty.call(tipoPolizaPorCategoria, categoriaId)) {
      opciones = tipoPolizaPorCategoria[categoriaId] || [];
    }

    ramoSelect.innerHTML = '';

    if (!opciones.length) {
      var placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = categoriaId ? 'Sin ramos disponibles' : 'Selecciona una categoria';
      placeholder.disabled = true;
      placeholder.selected = true;
      ramoSelect.appendChild(placeholder);
      ramoSelect.disabled = true;
      return;
    }

    ramoSelect.disabled = false;
    opciones.forEach(function (opcion, indice) {
      var optionEl = document.createElement('option');
      optionEl.value = opcion.id_tipo_poliza;
      optionEl.textContent = opcion.nombre;
      if (indice === 0) {
        optionEl.selected = true;
      }
      ramoSelect.appendChild(optionEl);
    });
  }

  if (categoriaSelect) {
    categoriaSelect.addEventListener('change', function () {
      poblarRamos(this.value);
    });
    poblarRamos(categoriaSelect.value || '');
  }

  if ($) {
    $('#sendPoliza').on('click', function () {
      var categoriaSeleccionada = categoriaSelect ? categoriaSelect.value : '';
      var ramoSeleccionado = ramoSelect ? ramoSelect.value : '';
      if (!categoriaSeleccionada) {
        alert('Selecciona una categoria para la poliza.');
        return;
      }
      if (!ramoSeleccionado || (ramoSelect && ramoSelect.disabled)) {
        alert('Selecciona un ramo disponible antes de enviar la solicitud.');
        return;
      }

      var payload = new URLSearchParams();
      payload.append('accion', 'crear_poliza');
      payload.append('categoria', categoriaSeleccionada);
      payload.append('ramo', ramoSeleccionado);
      var descripcionEl = document.getElementById('solicitudDescripcion');
      var contactoEl = document.getElementById('solicitudContacto');
      payload.append('descripcion', descripcionEl ? descripcionEl.value : '');
      payload.append('contacto', contactoEl ? contactoEl.value : '');

      fetch(controladorSolicitudUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload.toString()
      }).then(function (res) { return res.json(); }).then(function (resp) {
        if (!resp.success) {
          alert(resp.message || 'No se pudo registrar la solicitud.');
          return;
        }
        alert(resp.message || 'Solicitud registrada.');
        $('#solicitarPolizaModal').modal('hide');
        if (solicitudesTable) {
          solicitudesTable.ajax.reload(null, false);
        }
        var formPoliza = document.getElementById('formSolicitarPoliza');
        if (formPoliza) {
          formPoliza.reset();
          if (categoriaSelect) {
            poblarRamos(categoriaSelect.value || '');
          }
        }
      }).catch(function (err) {
        console.error(err);
        alert('Ocurrio un error al enviar la solicitud.');
      });
    });

    $('#sendSiniestro').on('click', function () {
      if (!siniestroPolizaSelect || siniestroPolizaSelect.disabled || !siniestroPolizaSelect.value) {
        alert('Debes seleccionar una poliza activa para reportar el siniestro.');
        return;
      }
      var payload = new URLSearchParams();
      payload.append('accion', 'crear_siniestro');
      payload.append('poliza', siniestroPolizaSelect.value);
      var tipoEl = document.getElementById('siniestroTipo');
      var descEl = document.getElementById('siniestroDescripcion');
      var fechaEl = document.getElementById('siniestroFecha');
      var lugarEl = document.getElementById('siniestroLugar');
      payload.append('tipo', tipoEl ? tipoEl.value : '');
      payload.append('descripcion', descEl ? descEl.value : '');
      payload.append('fecha', fechaEl ? fechaEl.value : '');
      payload.append('lugar', lugarEl ? lugarEl.value : '');

      fetch(controladorSolicitudUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload.toString()
      }).then(function (res) { return res.json(); }).then(function (resp) {
        if (!resp.success) {
          alert(resp.message || 'No se pudo registrar el siniestro.');
          return;
        }
        alert(resp.message || 'Siniestro reportado.');
        $('#reportarSiniestroModal').modal('hide');
        if (solicitudesTable) {
          solicitudesTable.ajax.reload(null, false);
        }
        var formSiniestro = document.getElementById('formReportarSiniestro');
        if (formSiniestro) {
          formSiniestro.reset();
          if (siniestroPolizaSelect && !siniestroPolizaSelect.disabled) {
            siniestroPolizaSelect.selectedIndex = 0;
          }
        }
      }).catch(function (err) {
        console.error(err);
        alert('Ocurrio un error al reportar el siniestro.');
      });
    });

    $('#solicitudesTable').on('click', 'button[data-action]', function () {
      var action = $(this).data('action');
      var fila = $(this).closest('tr');
      if (fila.hasClass('child')) {
        fila = fila.prev('.parent');
      }
      var row = solicitudesTable ? solicitudesTable.row(fila) : null;
      if (!row) { return; }
      var data = row.data();
      if (!data) { return; }

      if (action === 'ver') {
        var badgeHtml = '<span class="badge-soft" data-variant="' + (data.estado_variant || 'neutral') + '">' + (data.estado_label || data.estado) + '</span>';
        var tipoTexto = data.origen === 'poliza' ? 'Solicitud de póliza' : 'Reporte de siniestro';
        $('#detalleTipo').text(tipoTexto);
        var resumen = data.origen === 'poliza'
          ? ((data.categoria || '') + ' - ' + (data.ramo || ''))
          : ((data.numero_poliza ? data.numero_poliza + ' - ' : '') + (data.tipo_incidente || data.ramo || ''));
        $('#detalleResumen').text(resumen || 'N/A');
        $('#detalleEstado').html(badgeHtml);
        var fechaTexto = 'N/A';
        if (data.fecha) {
          var baseFecha = typeof data.fecha === 'string' ? data.fecha.replace(' ', 'T') : data.fecha;
          var fechaObj = new Date(baseFecha);
          if (!isNaN(fechaObj.getTime())) {
            fechaTexto = fechaObj.toLocaleString('es-VE', { dateStyle: 'medium', timeStyle: 'short' });
          } else {
            fechaTexto = data.fecha;
          }
        }
        $('#detalleFecha').text(fechaTexto);
        $('#detalleAsignado').text(data.asignado || 'Pendiente de asignacion');
        $('#detalleContacto').text(data.contacto || 'N/A');
        $('#detalleDescripcion').text(data.descripcion || 'N/A');
        $('#detalleNotas').text(data.nota_interna || 'N/A');
        if (data.origen === 'siniestro') {
          $('.siniestro-only').show();
          var fechaIncidente = '';
          if (data.fecha_incidente) {
            var baseIncidente = typeof data.fecha_incidente === 'string' ? data.fecha_incidente.replace(' ', 'T') : data.fecha_incidente;
            var fechaIncidenteObj = new Date(baseIncidente);
            if (!isNaN(fechaIncidenteObj.getTime())) {
              fechaIncidente = ' - ' + fechaIncidenteObj.toLocaleDateString('es-VE', { dateStyle: 'medium' });
            } else {
              fechaIncidente = ' - ' + data.fecha_incidente;
            }
          }
          var incidente = (data.tipo_incidente || 'N/A') + fechaIncidente;
          $('#detalleIncidente').text(incidente);
          $('#detalleLugar').text(data.lugar_incidente || 'N/A');
        } else {
          $('.siniestro-only').hide();
        }
        $('#detalleSolicitudModal').modal('show');
      }

      if (action === 'cancelar') {
        if (!confirm('¿Cancelar la solicitud seleccionada?')) {
          return;
        }
        var payload = new URLSearchParams();
        payload.append('accion', 'cancelar_cliente');
        payload.append('origen', data.origen);
        payload.append('id', data.id);
        fetch(controladorSolicitudUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: payload.toString()
        }).then(function (res) { return res.json(); }).then(function (resp) {
          if (!resp.success) {
            alert(resp.message || 'No se pudo cancelar la solicitud.');
            return;
          }
          alert(resp.message || 'Solicitud cancelada.');
          if (solicitudesTable) {
            solicitudesTable.ajax.reload(null, false);
          }
        }).catch(function (err) {
          console.error(err);
          alert('Error al cancelar la solicitud.');
        });
      }
    });
  }
});
</script>
<?php
$extra_scripts = ob_get_clean();
require_once __DIR__ . '/parte_inferior.php';
?>
