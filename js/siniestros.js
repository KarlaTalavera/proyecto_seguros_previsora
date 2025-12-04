'use strict';
(function (window, $) {
  if (!$ || !$.fn) {
    return;
  }

  function escapeHtml(value) {
    if (value === null || value === undefined) {
      return '';
    }
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatMoney(value) {
    var amount = Number.parseFloat(value);
    if (!Number.isFinite(amount)) {
      amount = 0;
    }
    return amount.toFixed(2);
  }

  function renderDetalle(data) {
    var descripcion = data && data.descripcion ? escapeHtml(data.descripcion).replace(/\r?\n/g, '<br>') : '-';
    return [
      '<div class="row">',
        '<div class="col-md-6">',
          '<p><strong>Número:</strong> ' + escapeHtml(data && data.numero_siniestro ? data.numero_siniestro : '-') + '</p>',
          '<p><strong>Póliza:</strong> ' + escapeHtml(data && data.numero_poliza ? data.numero_poliza : '-') + '</p>',
          '<p><strong>Cliente:</strong> ' + escapeHtml(data && data.nombre_cliente ? data.nombre_cliente : '-') + '</p>',
          '<p><strong>Agente:</strong> ' + escapeHtml(data && data.nombre_agente ? data.nombre_agente : '-') + '</p>',
        '</div>',
        '<div class="col-md-6">',
          '<p><strong>Fecha reporte:</strong> ' + escapeHtml(data && data.fecha_reporte ? data.fecha_reporte : '-') + '</p>',
          '<p><strong>Monto estimado:</strong> $' + formatMoney(data && data.monto_estimado) + '</p>',
          '<p><strong>Estado:</strong> ' + escapeHtml(data && data.estado ? data.estado : '-') + '</p>',
        '</div>',
      '</div>',
      '<hr>',
      '<p><strong>Descripción del siniestro</strong></p>',
      '<div class="alert alert-light" role="alert">' + descripcion + '</div>'
    ].join('');
  }

  function mostrarMensaje($target, mensaje, tipo) {
    if (!$target || !$target.length) {
      window.alert(mensaje);
      return;
    }
    var clase = tipo === 'success' ? 'alert-success' : 'alert-danger';
    $target
      .html('<div class="alert ' + clase + ' mb-0">' + escapeHtml(mensaje || '') + '</div>')
      .show();
  }

  function cargarDetalleSiniestro(id) {
    return $.ajax({
      url: 'controlador/controladorSiniestro.php',
      type: 'GET',
      dataType: 'json',
      data: { accion: 'obtener_siniestro', id_siniestro: id }
    });
  }

  function handleAjaxForm($form, opciones) {
    if (!$form.length) {
      return;
    }
    var cfg = $.extend({
      url: '',
      accion: '',
      boton: null,
      respuesta: null,
      enExito: null
    }, opciones || {});

    var $boton = cfg.boton ? $(cfg.boton) : null;
    var $respuesta = cfg.respuesta ? $(cfg.respuesta) : null;

    $form.on('submit', function (evento) {
      evento.preventDefault();
      if ($respuesta) {
        $respuesta.hide().empty();
      }
      if ($boton && $boton.length) {
        $boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Procesando...');
      }
      var datos = $form.serialize();
      if (cfg.accion) {
        datos += '&accion=' + encodeURIComponent(cfg.accion);
      }
      $.ajax({
        url: cfg.url,
        type: 'POST',
        dataType: 'json',
        data: datos
      })
        .done(function (respuesta) {
          if (respuesta && respuesta.success) {
            if (cfg.enExito) {
              cfg.enExito(respuesta);
            } else {
              mostrarMensaje($respuesta, respuesta.message || 'Operación realizada correctamente.', 'success');
              window.setTimeout(function () { window.location.reload(); }, 1200);
            }
          } else {
            mostrarMensaje($respuesta, (respuesta && respuesta.message) || 'Ocurrió un error al procesar la solicitud.', 'error');
          }
        })
        .fail(function () {
          mostrarMensaje($respuesta, 'Error de conexión con el servidor.', 'error');
        })
        .always(function () {
          if ($boton && $boton.length) {
            $boton.prop('disabled', false).text($boton.data('texto-original') || 'Guardar');
          }
        });
    });
  }

  $(function () {
    if (!$.fn.DataTable) {
      return;
    }

    var $tabla = $('#siniestrosTable');
    if (!$tabla.length) {
      return;
    }

    var tablaSiniestros = $tabla.DataTable({
      dom: 'Bfrtip',
      buttons: [
        { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar' },
        { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV' },
        { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel' },
        { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir' }
      ],
      language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
      pageLength: 10,
      order: [[0, 'desc']],
      scrollX: true,
      autoWidth: false
    });

    window.setTimeout(function () {
      tablaSiniestros.columns.adjust();
    }, 100);

    $('#exportarSiniestrosCsv').on('click', function () {
      tablaSiniestros.button('.buttons-csv').trigger('click');
    });

    $('#registrarSiniestroModal').on('show.bs.modal', function () {
      var formulario = document.getElementById('registrarSiniestroForm');
      if (formulario) {
        formulario.reset();
      }
      var $respuesta = $('#registrarSiniestroRespuesta');
      $respuesta.hide().empty();
      var hoy = new Date().toISOString().split('T')[0];
      $('#fecha_incidente').val(hoy);
      $('#cedula_agente_gestion').trigger('change');
    });

    $('#pagoSiniestroModal').on('show.bs.modal', function () {
      var hoy = new Date().toISOString().split('T')[0];
      $('#fecha_pago').val(hoy);
      $('#monto_pago').val('');
      $('#comentario_pago').val('');
      $('#pagoSiniestroRespuesta').hide().empty();
    });

    $(document).on('click', '.btn-ver-siniestro', function () {
      var id = $(this).data('id');
      $('#detalleSiniestroBody').html('<p class="text-center mb-0"><span class="spinner-border spinner-border-sm"></span> Cargando información...</p>');
      $('#detalleSiniestroModal').modal('show');
      cargarDetalleSiniestro(id)
        .done(function (respuesta) {
          if (respuesta && respuesta.success && respuesta.data) {
            $('#detalleSiniestroBody').html(renderDetalle(respuesta.data));
          } else {
            window.alert((respuesta && respuesta.message) || 'No se pudo obtener el siniestro.');
          }
        })
        .fail(function () {
          window.alert('Error de conexión con el servidor.');
        });
    });

    $(document).on('click', '.btn-editar-siniestro', function () {
      var id = $(this).data('id');
      $('#editarSiniestroRespuesta').hide().empty();
      cargarDetalleSiniestro(id)
        .done(function (respuesta) {
          if (!respuesta || !respuesta.success || !respuesta.data) {
            mostrarMensaje($('#editarSiniestroRespuesta'), (respuesta && respuesta.message) || 'No se pudo obtener el siniestro.', 'error');
            return;
          }
          var data = respuesta.data;
          $('#id_siniestro_edit').val(data.id_siniestro || id);
          $('#numero_siniestro_edit').val(data.numero_siniestro || '');
          $('#poliza_edit').val(data.numero_poliza || '');
          var fecha = data.fecha_reporte ? data.fecha_reporte.substr(0, 10) : '';
          $('#fecha_incidente_edit').val(fecha);
          $('#estado_edit').val((data.estado || 'ABIERTO').toUpperCase());
          $('#monto_reclamo_edit').val(data.monto_estimado || 0);
          $('#descripcion_edit').val(data.descripcion || '');
          $('#editarSiniestroModal').modal('show');
        })
        .fail(function () {
          mostrarMensaje($('#editarSiniestroRespuesta'), 'Error de conexión con el servidor.', 'error');
        });
    });

    $(document).on('click', '.btn-registrar-pago', function () {
      var id = $(this).data('id');
      $('#id_siniestro_pago').val(id);
      $('#pagoSiniestroRespuesta').hide().empty();
      $('#pagoSiniestroModal').modal('show');
    });

    var $registrarForm = $('#registrarSiniestroForm');
    if ($registrarForm.length) {
      var $registrarBtn = $('#guardarSiniestroBtn');
      if ($registrarBtn.length) {
        $registrarBtn.data('texto-original', $registrarBtn.text());
      }
      handleAjaxForm($registrarForm, {
        url: 'controlador/controladorSiniestro.php',
        accion: 'crear_siniestro',
        boton: $registrarBtn,
        respuesta: '#registrarSiniestroRespuesta',
        enExito: function (respuesta) {
          mostrarMensaje($('#registrarSiniestroRespuesta'), (respuesta && respuesta.message) || 'Siniestro registrado correctamente.', 'success');
          window.setTimeout(function () { window.location.reload(); }, 1200);
        }
      });
    }

    var $editarForm = $('#editarSiniestroForm');
    if ($editarForm.length) {
      var $editarBtn = $('#guardarCambiosSiniestroBtn');
      if ($editarBtn.length) {
        $editarBtn.data('texto-original', $editarBtn.text());
      }
      handleAjaxForm($editarForm, {
        url: 'controlador/controladorSiniestro.php',
        accion: 'actualizar_siniestro',
        boton: $editarBtn,
        respuesta: '#editarSiniestroRespuesta'
      });
    }

    var $pagoForm = $('#pagoSiniestroForm');
    if ($pagoForm.length) {
      var $pagoBtn = $('#registrarPagoBtn');
      if ($pagoBtn.length) {
        $pagoBtn.data('texto-original', $pagoBtn.text());
      }
      handleAjaxForm($pagoForm, {
        url: 'controlador/controladorSiniestro.php',
        accion: 'registrar_pago',
        boton: $pagoBtn,
        respuesta: '#pagoSiniestroRespuesta'
      });
    }
  });
})(window, window.jQuery);
