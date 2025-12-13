(function (window, document, $) {
  'use strict';

  if (!window.PagosCuotasGestionConfig || !document.getElementById('tablaReportesGestion')) {
    return;
  }

  var endpoint = window.PagosCuotasGestionConfig.endpoint || 'controlador/controladorPagoCuota.php';
  var $tabla = $('#tablaReportesGestion');
  var $selectEstado = $('#filtroEstado');
  var $modalDetalle = $('#modalDetalleReporte');
  var $modalRechazo = $('#modalRechazoReporte');
  var $formRechazo = $('#formRechazoReporte');
  var $btnAprobar = $('#btnAprobarReporte');
  var $btnAbrirRechazo = $('#btnAbrirRechazo');
  var $totalPendientes = $('#totalPendientes');
  var $totalAprobadosHoy = $('#totalAprobadosHoy');
  var $totalRechazadosHoy = $('#totalRechazadosHoy');

  var reporteSeleccionado = null;

  var lenguajeTabla = {
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
  };

  var tablaReportes = $tabla.DataTable({
    paging: true,
    searching: true,
    responsive: true,
    order: [[0, 'desc']],
    language: lenguajeTabla,
    columnDefs: [{ targets: '_all', defaultContent: '—' }],
    columns: [
      {
        data: 'fecha_reporte',
        render: function (data, type) {
          return type === 'display' ? formatearFechaHora(data) : data;
        }
      },
      {
        data: null,
        render: function (data) {
          if (!data) {
            return '—';
          }
          var nombre = escaparHtml(data.cliente_nombre || 'Sin nombre');
          var correo = data.cliente_email ? '<div class="small text-muted">' + escaparHtml(data.cliente_email) + '</div>' : '';
          return '<div><strong>' + nombre + '</strong>' + correo + '</div>';
        }
      },
      {
        data: null,
        render: function (data) {
          if (!data) {
            return '—';
          }
          return '<div><strong>' + escaparHtml(data.numero_poliza || '—') + '</strong><div class="small text-muted">' + escaparHtml(data.producto || '') + '</div></div>';
        }
      },
      {
        data: 'numero_cuota',
        render: function (data) {
          return data ? 'Cuota #' + data : '—';
        }
      },
      {
        data: 'monto_reportado',
        className: 'text-right',
        render: function (data, type) {
          return type === 'display' ? formatearMoneda(data) : data;
        }
      },
      {
        data: 'estado',
        render: function (data, type, row) {
          return renderBadgeEstadoReporte(data, row);
        }
      },
      {
        data: 'referencia_pago',
        render: function (data) {
          return data ? escaparHtml(data) : '—';
        }
      },
      {
        data: 'ruta_comprobante',
        orderable: false,
        render: function (data) {
          if (!data) {
            return '<span class="badge-soft" data-variant="neutral">No adjunto</span>';
          }
          return '<a class="btn btn-sm btn-outline-primary" href="' + escaparHtml(data) + '" target="_blank">Abrir</a>';
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data) {
          if (!data) {
            return '';
          }
          var botones = '<div class="btn-group btn-group-sm pagos-acciones" role="group">';
          botones += '<button type="button" class="btn btn-sm btn-info js-ver-detalle" data-id="' + data.id_reporte + '"><i class="fas fa-eye"></i></button>';
          if ((data.estado || '').toUpperCase() === 'PENDIENTE') {
            botones += '<button type="button" class="btn btn-sm btn-success js-aprobar-directo" data-id="' + data.id_reporte + '"><i class="fas fa-check"></i></button>';
            botones += '<button type="button" class="btn btn-sm btn-danger js-rechazar-directo" data-id="' + data.id_reporte + '"><i class="fas fa-times"></i></button>';
          }
          botones += '</div>';
          return botones;
        }
      }
    ]
  });

  cargarMetricas();
  cargarReportes($selectEstado.val());

  $selectEstado.on('change', function () {
    var estado = $(this).val() || 'pendiente';
    cargarReportes(estado);
  });

  $tabla.on('click', '.js-ver-detalle', function () {
    var id = Number($(this).data('id')) || 0;
    if (!id) {
      toastr.error('No fue posible identificar el reporte seleccionado.');
      return;
    }
    abrirDetalle(id, false);
  });

  $tabla.on('click', '.js-aprobar-directo', function () {
    var id = Number($(this).data('id')) || 0;
    if (!id) {
      toastr.error('No fue posible identificar el reporte.');
      return;
    }
    var data = obtenerDatosFila($(this)) || {};
    if ((data.estado || '').toUpperCase() !== 'PENDIENTE') {
      toastr.info('El reporte ya fue procesado.');
      return;
    }
    if (!window.confirm('¿Confirmas la aprobación de este pago?')) {
      return;
    }
    aprobarReporte(id, { accionRapida: true });
  });

  $tabla.on('click', '.js-rechazar-directo', function () {
    var id = Number($(this).data('id')) || 0;
    if (!id) {
      toastr.error('No fue posible identificar el reporte.');
      return;
    }
    var data = obtenerDatosFila($(this)) || {};
    if ((data.estado || '').toUpperCase() !== 'PENDIENTE') {
      toastr.info('El reporte ya fue procesado.');
      return;
    }
    $('#rechazoIdReporte').val(id);
    $('#rechazoMotivo').val('');
    $modalRechazo.modal('show');
  });

  $btnAbrirRechazo.on('click', function () {
    if (!reporteSeleccionado || !reporteSeleccionado.id_reporte) {
      toastr.warning('Primero consulta un reporte.');
      return;
    }
    $('#rechazoIdReporte').val(reporteSeleccionado.id_reporte);
    $('#rechazoMotivo').val('');
    $modalRechazo.modal('show');
  });

  $btnAprobar.on('click', function () {
    if (!reporteSeleccionado || !reporteSeleccionado.id_reporte) {
      toastr.warning('Selecciona un reporte pendiente para aprobar.');
      return;
    }
    aprobarReporte(reporteSeleccionado.id_reporte);
  });

  $formRechazo.on('submit', function (event) {
    event.preventDefault();
    var id = Number($('#rechazoIdReporte').val()) || 0;
    var motivo = ($('#rechazoMotivo').val() || '').trim();
    if (!id || motivo.length < 10) {
      toastr.warning('Describe el motivo de rechazo (al menos 10 caracteres).');
      return;
    }
    rechazarReporte(id, motivo);
  });

  function obtenerDatosFila($elemento) {
    var $fila = $elemento.closest('tr');
    if ($fila.hasClass('child')) {
      $fila = $fila.prev();
    }
    return tablaReportes.row($fila).data() || null;
  }

  function cargarReportes(estadoSeleccion) {
    var estado = (estadoSeleccion || 'pendiente').toUpperCase();
    fetch(endpoint + '?accion=listar_reportes_gestion&estado=' + encodeURIComponent(estado), {
      credentials: 'same-origin'
    })
      .then(manejarRespuestaJson)
      .then(function (json) {
        if (!json.success) {
          throw new Error(json.message || 'No se pudieron obtener los reportes.');
        }
        tablaReportes.clear();
        tablaReportes.rows.add(json.reportes || []);
        tablaReportes.draw();
      })
      .catch(function (error) {
        toastr.error(error.message || 'Error al cargar reportes.');
      });
  }

  function cargarMetricas() {
    fetch(endpoint + '?accion=obtener_metricas_gestion', { credentials: 'same-origin' })
      .then(manejarRespuestaJson)
      .then(function (json) {
        if (!json.success || !json.metricas) {
          throw new Error(json.message || 'No se pudieron obtener las métricas.');
        }
        $totalPendientes.text(json.metricas.pendientes);
        $totalAprobadosHoy.text(json.metricas.aprobados_hoy);
        $totalRechazadosHoy.text(json.metricas.rechazados_hoy);
      })
      .catch(function (error) {
        $totalPendientes.text('—');
        $totalAprobadosHoy.text('—');
        $totalRechazadosHoy.text('—');
        toastr.error(error.message || 'Error al cargar métricas.');
      });
  }

  function abrirDetalle(idReporte, forzarRechazo) {
    fetch(endpoint + '?accion=obtener_reporte&id_reporte=' + encodeURIComponent(idReporte), {
      credentials: 'same-origin'
    })
      .then(manejarRespuestaJson)
      .then(function (json) {
        if (!json.success || !json.reporte) {
          throw new Error(json.message || 'No se encontró el reporte.');
        }
        reporteSeleccionado = json.reporte;
        popularDetalle(json.reporte);
        var esPendiente = (json.reporte.estado || '').toUpperCase() === 'PENDIENTE';
        $btnAprobar.prop('disabled', !esPendiente);
        $btnAbrirRechazo.prop('disabled', !esPendiente);
        if (forzarRechazo && esPendiente) {
          $('#rechazoIdReporte').val(json.reporte.id_reporte);
          $('#rechazoMotivo').val('');
          $modalRechazo.modal('show');
        }
        $modalDetalle.modal('show');
      })
      .catch(function (error) {
        toastr.error(error.message || 'Error al obtener detalle del reporte.');
      });
  }

  function popularDetalle(reporte) {
    $('#detalleCliente').text(reporte.cliente_nombre || '—');
    $('#detalleContacto').text(reporte.cliente_email || '—');
    $('#detallePoliza').text(reporte.numero_poliza || '—');
    $('#detalleProducto').text(reporte.producto || '—');
    $('#detalleCuota').text(reporte.numero_cuota ? 'Cuota #' + reporte.numero_cuota : '—');
    $('#detalleVencimiento').text(formatearFecha(reporte.fecha_vencimiento));
    $('#detalleMonto').text(formatearMoneda(reporte.monto_reportado));
    var montoPendiente = calcularPendiente(reporte);
    $('#detallePendiente').text(formatearMoneda(montoPendiente));
    $('#detalleReferencia').text(reporte.referencia_pago || '—');
    $('#detalleEstado').html(renderBadgeEstadoReporte(reporte.estado, reporte));
    $('#detalleFecha').text(formatearFechaHora(reporte.fecha_reporte));
    $('#detalleNota').text(reporte.nota_cliente ? reporte.nota_cliente : 'Sin comentarios');
    if (reporte.ruta_comprobante) {
      $('#detalleComprobante').attr('href', reporte.ruta_comprobante).removeClass('disabled');
    } else {
      $('#detalleComprobante').attr('href', '#').addClass('disabled');
    }
  }

  function aprobarReporte(idReporte, opciones) {
    var config = Object.assign({ accionRapida: false }, opciones || {});
    var formData = new FormData();
    formData.append('accion', 'aprobar_reporte');
    formData.append('id_reporte', idReporte);

    if (!config.accionRapida) {
      bloquearBotonesModal(true);
    }

    fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(manejarRespuestaJson)
      .then(function (json) {
        if (!json.success) {
          throw new Error(json.message || 'No se pudo aprobar el reporte.');
        }
        toastr.success(json.message || 'Reporte aprobado.');
        if (!config.accionRapida) {
          $modalDetalle.modal('hide');
        }
        actualizarListas();
      })
      .catch(function (error) {
        toastr.error(error.message || 'Error al aprobar el reporte.');
      })
      .finally(function () {
        if (!config.accionRapida) {
          bloquearBotonesModal(false);
        }
      });
  }

  function rechazarReporte(idReporte, motivo) {
    var formData = new FormData();
    formData.append('accion', 'rechazar_reporte');
    formData.append('id_reporte', idReporte);
    formData.append('motivo', motivo);

    bloquearBotonesModal(true);

    fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(manejarRespuestaJson)
      .then(function (json) {
        if (!json.success) {
          throw new Error(json.message || 'No se pudo rechazar el reporte.');
        }
        toastr.info(json.message || 'Reporte rechazado.');
        $modalRechazo.modal('hide');
        $modalDetalle.modal('hide');
        actualizarListas();
      })
      .catch(function (error) {
        toastr.error(error.message || 'Error al rechazar el reporte.');
      })
      .finally(function () {
        bloquearBotonesModal(false);
      });
  }

  function actualizarListas() {
    cargarMetricas();
    cargarReportes($selectEstado.val());
  }

  function bloquearBotonesModal(bloquear) {
    $btnAprobar.prop('disabled', bloquear);
    $btnAbrirRechazo.prop('disabled', bloquear);
    $formRechazo.find('button, textarea').prop('disabled', bloquear);
  }

  function manejarRespuestaJson(respuesta) {
    if (!respuesta.ok) {
      throw new Error('Respuesta no válida del servidor.');
    }
    return respuesta.json();
  }

  function formatearMoneda(valor) {
    if (valor === null || valor === undefined || valor === '') {
      return '—';
    }
    var numero = Number(valor);
    if (!Number.isFinite(numero)) {
      return valor;
    }
    return '$' + numero.toLocaleString('es-VE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function formatearFecha(fecha) {
    if (!fecha) {
      return '—';
    }
    var d = new Date(fecha);
    if (Number.isNaN(d.getTime())) {
      return fecha;
    }
    return d.toLocaleDateString('es-VE', { year: 'numeric', month: 'short', day: '2-digit' });
  }

  function formatearFechaHora(fecha) {
    if (!fecha) {
      return '—';
    }
    var d = new Date(fecha.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) {
      return fecha;
    }
    return d.toLocaleString('es-VE', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function calcularPendiente(reporte) {
    var programado = Number(reporte.monto_programado || 0);
    var pagado = Number(reporte.monto_pagado || 0);
    var restante = Math.max(programado - pagado, 0);
    return restante;
  }

  function renderBadgeEstadoReporte(estado, row) {
    var estadoNormalizado = (estado || '').toUpperCase();
    var definiciones = {
      'PENDIENTE': { label: 'Pendiente', variant: 'pendiente' },
      'APROBADO': { label: 'Aprobado', variant: 'aprobado' },
      'RECHAZADO': { label: 'Rechazado', variant: 'rechazado' }
    };
    var def = definiciones[estadoNormalizado] || { label: estadoNormalizado || 'Desconocido', variant: 'neutral' };
    var badge = '<span class="badge-soft" data-variant="' + def.variant + '">' + def.label + '</span>';
    var detalle = '';

    if (row && row.fecha_revision && estadoNormalizado !== 'PENDIENTE') {
      detalle += '<div class="small text-muted">' + formatearFechaHora(row.fecha_revision) + '</div>';
    }
    if (row && row.motivo_rechazo && estadoNormalizado === 'RECHAZADO') {
      detalle += '<div class="small text-danger">' + escaparHtml(row.motivo_rechazo) + '</div>';
    }

    return '<div>' + badge + (detalle ? detalle : '') + '</div>';
  }

  function escaparHtml(texto) {
    if (texto === null || texto === undefined) {
      return '';
    }
    return String(texto)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
})(window, document, window.jQuery);
