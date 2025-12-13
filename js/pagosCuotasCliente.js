(function (window, document, $) {
  'use strict';

  if (!window.PagosCuotasConfig || !document.getElementById('tablaCuotasCliente')) {
    return;
  }

  var endpoint = window.PagosCuotasConfig.endpoint || 'controlador/controladorPagoCuota.php';
  var $tablaCuotas = $('#tablaCuotasCliente');
  var $tablaReportes = $('#tablaReportesCliente');
  var $modalReporte = $('#modalReportePago');
  var $formReporte = $('#formReportePago');
  var $btnEnviarReporte = $('#btnEnviarReporte');
  var $fileInput = $('#reporteComprobante');

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

  var tablaCuotas = $tablaCuotas.DataTable({
    paging: true,
    searching: true,
    responsive: true,
    order: [[3, 'asc']],
    language: lenguajeTabla,
    columns: [
      { data: 'numero_poliza' },
      { data: 'producto' },
      {
        data: 'numero_cuota',
        render: function (data, type) {
          if (type === 'display') {
            return 'Cuota #' + data;
          }
          return data;
        }
      },
      {
        data: 'fecha_vencimiento',
        render: function (data, type) {
          if (!data) {
            return type === 'display' ? '—' : '';
          }
          if (type === 'display') {
            return formatearFecha(data);
          }
          return data;
        }
      },
      {
        data: 'monto_programado',
        className: 'text-right',
        render: function (data, type) {
          return type === 'display' ? formatearMoneda(data) : data;
        }
      },
      {
        data: 'monto_pagado',
        className: 'text-right',
        render: function (data, type) {
          return type === 'display' ? formatearMoneda(data) : data;
        }
      },
      {
        data: 'monto_pendiente',
        className: 'text-right',
        render: function (data, type) {
          return type === 'display' ? formatearMoneda(data) : data;
        }
      },
      {
        data: 'estado',
        render: function (data) {
          return renderBadgeEstadoCuota(data);
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          if (type !== 'display') {
            return '';
          }

          if (!row) {
            return '';
          }

          var pendiente = Number(row.monto_pendiente || 0);
          var montoReportable = Number(row.monto_reportable || pendiente);
          var montoRevision = Number(row.monto_reportado_pendiente || 0);
          var permiteReportar = Boolean(row.permite_reporte);
          var motivoBloqueo = (row.motivo_bloqueo || '').trim();

          if (pendiente <= 0.0001) {
            return '<span class="badge-soft" data-variant="aprobado">Saldada</span>';
          }

          if (!permiteReportar) {
            var mensajeBloqueo = motivoBloqueo || 'Completa la cuota anterior.';
            var variante = mensajeBloqueo.toLowerCase().indexOf('revisi') !== -1 ? 'pendiente' : 'neutral';
            return '<span class="badge-soft" data-variant="' + variante + '">' + escaparHtml(mensajeBloqueo) + '</span>';
          }

          var notaPendiente = '';
          if (montoRevision > 0.0001) {
            notaPendiente = '<small class="d-block text-muted mt-1">' + formatearMoneda(montoRevision) + ' en revisión</small>';
          }

          return '' +
            '<button type="button" class="btn btn-sm btn-primary js-reportar-pago"' +
            ' data-id="' + row.id_cuota + '"' +
            ' data-poliza="' + escaparHtml(row.numero_poliza) + '"' +
            ' data-producto="' + escaparHtml(row.producto) + '"' +
            ' data-cuota="' + row.numero_cuota + '"' +
            ' data-saldo="' + (montoReportable > 0 ? montoReportable : pendiente) + '"' +
            '>' +
            '<i class="fas fa-receipt mr-1"></i> Reportar pago' +
            '</button>' + notaPendiente;
        }
      }
    ]
  });

  var tablaReportes = $tablaReportes.DataTable({
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
          return '<div><strong>' + escaparHtml(data.numero_poliza || '—') + '</strong><div class="text-muted small">' + escaparHtml(data.producto || '') + '</div></div>';
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
        data: 'referencia_pago',
        render: function (data) {
          return data ? escaparHtml(data) : '—';
        }
      },
      {
        data: 'estado',
        render: function (data, type, row) {
          return renderBadgeEstadoReporte(data, row);
        }
      },
      {
        data: 'ruta_comprobante',
        orderable: false,
        render: function (data) {
          if (!data) {
            return '<span class="badge-soft" data-variant="neutral">No disponible</span>';
          }
          return '<a class="btn btn-sm btn-outline-primary" href="' + escaparHtml(data) + '" target="_blank">Ver</a>';
        }
      },
      {
        data: null,
        render: function (data) {
          if (!data) {
            return '—';
          }
          var notas = [];
          if (data.nota_cliente) {
            notas.push('<span class="text-muted">Cliente:</span> ' + escaparHtml(data.nota_cliente));
          }
          if (data.motivo_rechazo) {
            notas.push('<span class="text-danger">Revisor:</span> ' + escaparHtml(data.motivo_rechazo));
          }
          return notas.length ? notas.join('<br>') : '—';
        }
      }
    ]
  });

  cargarCuotas();
  cargarReportes();

  $tablaCuotas.on('click', '.js-reportar-pago', function () {
    var $btn = $(this);
    var cuotaId = Number($btn.data('id')) || 0;
    if (!cuotaId) {
      toastr.error('No se pudo identificar la cuota seleccionada.');
      return;
    }

    $formReporte[0].reset();
    $('#reporteIdCuota').val(cuotaId);
    $('#reportePoliza').val(($btn.data('poliza') || '') + ' · ' + ($btn.data('producto') || ''));
    $('#reporteNumeroCuota').val('Cuota #' + ($btn.data('cuota') || ''));
    $('#reporteSaldoPendiente').val(formatearMoneda($btn.data('saldo')));
    $('.custom-file-label[for="reporteComprobante"]').text('Selecciona un comprobante...');
    $modalReporte.modal('show');
  });

  $formReporte.on('submit', function (event) {
    event.preventDefault();
    if (!$formReporte[0].checkValidity()) {
      $formReporte[0].reportValidity();
      return;
    }

    var formData = new FormData($formReporte[0]);
    formData.append('accion', 'reportar_pago');

    $btnEnviarReporte.prop('disabled', true);

    fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(manejarRespuestaJson)
      .then(function (json) {
        if (!json.success) {
          throw new Error(json.message || 'No se pudo registrar el pago.');
        }
        toastr.success(json.message || 'Pago reportado correctamente.');
        $modalReporte.modal('hide');
        cargarCuotas();
        cargarReportes();
      })
      .catch(function (error) {
        toastr.error(error.message || 'Se produjo un error al reportar el pago.');
      })
      .finally(function () {
        $btnEnviarReporte.prop('disabled', false);
      });
  });

  $fileInput.on('change', function () {
    var fileName = this.files && this.files.length ? this.files[0].name : 'Selecciona un comprobante...';
    $('.custom-file-label[for="reporteComprobante"]').text(fileName);
  });

  function cargarCuotas() {
    fetch(endpoint + '?accion=listar_cuotas_cliente', { credentials: 'same-origin' })
      .then(manejarRespuestaJson)
      .then(function (json) {
        if (!json.success) {
          throw new Error(json.message || 'No se pudieron obtener las cuotas.');
        }
        tablaCuotas.clear();
        tablaCuotas.rows.add(json.cuotas || []);
        tablaCuotas.draw();
      })
      .catch(function (error) {
        toastr.error(error.message || 'Error al cargar las cuotas.');
      });
  }

  function cargarReportes() {
    fetch(endpoint + '?accion=listar_reportes_cliente', { credentials: 'same-origin' })
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
        toastr.error(error.message || 'Error al cargar los reportes.');
      });
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

  function renderBadgeEstadoCuota(estado) {
    var estadoNormalizado = (estado || '').toUpperCase();
    var definiciones = {
      'PAGADO': { label: 'Pagado', variant: 'aprobado' },
      'PENDIENTE': { label: 'Pendiente', variant: 'pendiente' },
      'ATRASADO': { label: 'Atrasado', variant: 'rechazado' },
      'CONDONADO': { label: 'Condonado', variant: 'info' }
    };
    var def = definiciones[estadoNormalizado] || { label: estadoNormalizado || '—', variant: 'neutral' };
    return '<span class="badge-soft" data-variant="' + def.variant + '">' + def.label + '</span>';
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
    if (estadoNormalizado === 'RECHAZADO' && row && row.motivo_rechazo) {
      badge += '<div class="small text-danger mt-1">' + escaparHtml(row.motivo_rechazo) + '</div>';
    }
    return badge;
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
