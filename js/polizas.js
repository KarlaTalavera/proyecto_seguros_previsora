(function (window, $) {
    'use strict';

    var API_BASE = 'controlador/controladorPoliza.php';
    var DATATABLE_LANG = 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json';
    var ESTADO_DEFINICIONES = {
        'ACTIVA': { badge: 'success', label: 'Activa', textClass: 'text-success' },
        'RENOVAR': { badge: 'warning', label: 'Por vencer', textClass: 'text-warning' },
        'CANCELADA': { badge: 'secondary', label: 'Cancelada', textClass: 'text-secondary' },
        'ELIMINADA': { badge: 'dark', label: 'Eliminada', textClass: 'text-muted' }
    };

    function formatCurrency(value) {
        if (value === null || value === undefined || value === '') {
            return '—';
        }
        var number = Number(value);
        if (!Number.isFinite(number)) {
            return value;
        }
        return '$' + number.toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) {
            return '—';
        }
        var date = new Date(dateStr + 'T00:00:00');
        if (Number.isNaN(date.getTime())) {
            return dateStr;
        }
        return date.toLocaleDateString('es-VE');
    }

    function normalizeEstado(estado) {
        return (estado || '').toString().trim().toUpperCase();
    }

    function obtenerEstadoDefinicion(estado) {
        var normalized = normalizeEstado(estado);
        var fallback = {
            badge: 'info',
            label: normalized || '—',
            textClass: 'text-info'
        };
        return ESTADO_DEFINICIONES[normalized] || fallback;
    }

    function estadoBadge(estado) {
        var entry = obtenerEstadoDefinicion(estado);
        return '<span class="badge badge-' + entry.badge + '">' + entry.label + '</span>';
    }

    function limpiarSelect($select) {
        $select.empty();
        $select.append('<option value="">Seleccione...</option>');
    }

    function toggleLoading($element, isLoading) {
        if (!$element.length) {
            return;
        }
        if (isLoading) {
            $element.attr('disabled', true);
        } else {
            $element.removeAttr('disabled');
        }
    }

    function mostrarAlerta($container, tipo, mensaje) {
        if (!$container.length) {
            return;
        }
        if (!mensaje) {
            $container.addClass('d-none').text('');
            return;
        }
        var clase = tipo === 'success' ? 'alert-success' : 'alert-danger';
        var html = '<div class="alert ' + clase + ' alert-dismissible fade show" role="alert">' +
            mensaje +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>';
        $container.html(html).removeClass('d-none');
    }

    function mostrarAlertaInline($container, tipo, mensaje) {
        if (!$container.length) {
            return;
        }
        if (!mensaje) {
            $container.addClass('d-none').text('');
            return;
        }
        var clase = tipo === 'success' ? 'alert-success' : 'alert-danger';
        $container.removeClass('d-none alert-success alert-danger')
            .addClass('alert ' + clase)
            .text(mensaje);
    }

    function hoyISO() {
        return (new Date()).toISOString().slice(0, 10);
    }

    function masMesesISO(meses) {
        var fecha = new Date();
        fecha.setMonth(fecha.getMonth() + meses);
        return fecha.toISOString().slice(0, 10);
    }

    window.initPolizasUI = function (userConfig) {
        if (!window.jQuery) {
            throw new Error('Se requiere jQuery para inicializar el módulo de pólizas.');
        }
        var config = $.extend({
            rol: '',
            cedulaActual: '',
            nombreActual: '',
            permisos: []
        }, userConfig || {});

        var permisosAsignados = Array.isArray(config.permisos) ? config.permisos : [];
        var isAdmin = config.rol === 'administrador';
        var puedeVerLista = isAdmin || permisosAsignados.indexOf('poliza_ver_lista') !== -1;
        var puedeCrear = isAdmin || permisosAsignados.indexOf('poliza_crear') !== -1;
        var puedeEditar = isAdmin || permisosAsignados.indexOf('poliza_editar') !== -1;
        var puedeEliminar = isAdmin || permisosAsignados.indexOf('poliza_eliminar') !== -1;
        var $tabla = $('#tablaPolizas');
        var $modal = $('#modalPoliza');
        var $form = $('#formPoliza');
        var $formAlert = $('#polizaFormAlert');
        var $pageAlert = $('#polizaPageAlert');
        var $numeroPolizaPreview = $('#numeroPolizaPreview');
        var $categoriaSelect = $('#categoriaSelect');
        var $ramoSelect = $('#ramoSelect');
        var $coberturasContainer = $('#coberturasContainer');
        var $agenteGroup = $('#agenteGroup');
        var $agenteSelect = $('#agenteSelect');
        var $agenteResumenWrapper = $('#agenteResumenWrapper');
        var $agenteResumenTexto = $('#agenteResumenTexto');
        var $clienteSelect = $('#clienteSelect');
        var $montoPrima = $('#montoPrimaTotal');
        var $numeroCuotas = $('#numeroCuotas');
        var $cuotaResumen = $('#montoCuotaResumen');
        var $fechaInicio = $('#fechaInicio');
        var $fechaFin = $('#fechaFin');
        var $fechaPrimerVenc = $('#fechaPrimerVencimiento');
        var $frecuenciaPago = $('#frecuenciaPago');
        var $guardarBtn = $('#guardarPolizaBtn');
     var $estadoSelect = $('#estadoPolizaSelect');
     var dataTable = null;
     var polizaEnEdicion = null;
     var modoEdicion = false;
     var saltarResetModal = false;
     var tituloModalOriginal = $modal.find('.modal-title').text();
     var textoGuardarOriginal = $guardarBtn.text();
     var textoGuardarCambios = 'Guardar cambios';
        var $detalleModal = $('#modalDetallePoliza');
        var detalleUI = {
            numero: $('#detalleNumero'),
            estado: $('#detalleEstado'),
            categoria: $('#detalleCategoria'),
            ramo: $('#detalleRamo'),
            cliente: $('#detalleCliente'),
            clienteCedula: $('#detalleClienteCedula'),
            agente: $('#detalleAgente'),
            fechaInicio: $('#detalleFechaInicio'),
            fechaFin: $('#detalleFechaFin'),
            primaTotal: $('#detallePrimaTotal'),
            numeroCuotas: $('#detalleNumeroCuotas'),
            montoCuota: $('#detalleMontoCuota'),
            frecuencia: $('#detalleFrecuencia'),
            primerVencimiento: $('#detallePrimerVencimiento'),
            coberturas: $('#detalleCoberturas')
        };

        if (!isAdmin) {
            $agenteGroup.addClass('d-none');
            if ($agenteResumenWrapper.length) {
                var texto = config.nombreActual && config.cedulaActual
                    ? config.nombreActual + ' (' + config.cedulaActual + ')'
                    : config.cedulaActual;
                $agenteResumenTexto.text(texto || 'Agente actual');
                $agenteResumenWrapper.removeClass('d-none');
            }
        } else {
            $agenteResumenWrapper.addClass('d-none');
            $agenteGroup.removeClass('d-none');
        }

        function resolvedPromise(value) {
            var deferred = $.Deferred();
            deferred.resolve(value);
            return deferred.promise();
        }

        function formatearFrecuenciaPago(frecuencia) {
            var normalized = normalizeEstado(frecuencia);
            var mapping = {
                'MENSUAL': 'Mensual',
                'TRIMESTRAL': 'Trimestral',
                'SEMESTRAL': 'Semestral',
                'ANUAL': 'Anual'
            };
            return mapping[normalized] || (normalized || '—');
        }

        function setEstadoFormulario(estado, opciones) {
            if (!$estadoSelect.length) {
                return;
            }
            var normalizado = normalizeEstado(estado);
            if (!normalizado || !ESTADO_DEFINICIONES[normalizado]) {
                normalizado = 'ACTIVA';
            }
            var def = obtenerEstadoDefinicion(normalizado);
            asegurarOpcion($estadoSelect, normalizado, def.label);
            $estadoSelect.val(normalizado);
            var config = $.extend({ habilitar: false }, opciones || {});
            $estadoSelect.prop('disabled', !config.habilitar);
        }

        function limpiarDetalleModal() {
            if (!$detalleModal.length) {
                return;
            }
            Object.keys(detalleUI).forEach(function (key) {
                var $el = detalleUI[key];
                if (!$el || !$el.length) {
                    return;
                }
                if (key === 'estado') {
                    $el.html('—');
                } else {
                    $el.text('—');
                }
            });
        }

        function poblarDetalleModal(detalle) {
            if (!$detalleModal.length || !detalle) {
                return;
            }
            detalleUI.numero.text(detalle.numero_poliza || '—');
            detalleUI.estado.html(estadoBadge(detalle.estado));
            detalleUI.categoria.text(detalle.categoria || '—');
            detalleUI.ramo.text(detalle.ramo || '—');
            detalleUI.cliente.text(detalle.cliente_nombre || '—');
            detalleUI.clienteCedula.text(detalle.cliente_cedula || '—');
            detalleUI.agente.text(detalle.agente_nombre || detalle.cedula_agente || '—');
            detalleUI.fechaInicio.text(formatDate(detalle.fecha_inicio));
            detalleUI.fechaFin.text(formatDate(detalle.fecha_fin));
            detalleUI.primaTotal.text(formatCurrency(detalle.monto_prima_total));
            detalleUI.numeroCuotas.text(detalle.numero_cuotas || '—');
            detalleUI.montoCuota.text(formatCurrency(detalle.monto_cuota));
            detalleUI.frecuencia.text(formatearFrecuenciaPago(detalle.frecuencia_pago));
            detalleUI.primerVencimiento.text(formatDate(detalle.fecha_primer_vencimiento));
            var coberturas = Array.isArray(detalle.coberturas_detalle) && detalle.coberturas_detalle.length
                ? detalle.coberturas_detalle.map(function (cob) { return cob.nombre; }).join(', ')
                : '—';
            detalleUI.coberturas.text(coberturas);
        }

        function solicitarDetallePoliza(id) {
            return $.ajax({
                url: API_BASE,
                data: { accion: 'detalle', id_poliza: id },
                method: 'GET',
                dataType: 'json'
            });
        }

        function renderCoberturas(coberturas) {
            $coberturasContainer.empty();
            if (!Array.isArray(coberturas) || !coberturas.length) {
                $coberturasContainer.append('<p class="text-muted mb-0">Seleccione un ramo para ver coberturas disponibles.</p>');
                return;
            }
            var row = $('<div class="row"></div>');
            coberturas.forEach(function (cob) {
                var col = $('<div class="col-md-6"></div>');
                var id = 'cobertura-' + cob.id_cobertura;
                var checkbox = '<div class="custom-control custom-checkbox mb-2">' +
                    '<input type="checkbox" class="custom-control-input" id="' + id + '" name="coberturas[]" value="' + cob.id_cobertura + '">' +
                    '<label class="custom-control-label" for="' + id + '">' + cob.nombre + '</label>' +
                    '</div>';
                col.append(checkbox);
                row.append(col);
            });
            $coberturasContainer.append(row);
        }

        function marcarCoberturasSeleccionadas(ids) {
            if (!Array.isArray(ids) || !ids.length) {
                return;
            }
            var lookup = ids.map(function (valor) { return String(valor); });
            $coberturasContainer.find('input[name="coberturas[]"]').each(function () {
                var $checkbox = $(this);
                if (lookup.indexOf($checkbox.val()) !== -1) {
                    $checkbox.prop('checked', true);
                }
            });
        }

        function asegurarOpcion($select, value, label) {
            if (!$select.length || value === null || value === undefined || value === '') {
                return;
            }
            var valorStr = String(value);
            if (!$select.find('option[value="' + valorStr.replace(/"/g, '&quot;') + '"]').length) {
                var texto = label || valorStr;
                $select.append('<option value="' + valorStr + '">' + texto + '</option>');
            }
        }

        function cargarNumeroPoliza() {
            if (!$numeroPolizaPreview.length) {
                return resolvedPromise();
            }
            if (!puedeCrear && !modoEdicion) {
                $numeroPolizaPreview.val('').attr('placeholder', 'Sin permisos para crear nuevas pólizas');
                return resolvedPromise();
            }
            $numeroPolizaPreview.val('').attr('placeholder', 'Generando número...');
            return $.ajax({
                url: API_BASE,
                data: { accion: 'siguiente_numero' },
                method: 'GET',
                dataType: 'json'
            }).done(function (res) {
                if (res && res.success && res.numero) {
                    $numeroPolizaPreview.val(res.numero);
                } else {
                    $numeroPolizaPreview.val('').attr('placeholder', 'Número no disponible');
                }
            }).fail(function () {
                $numeroPolizaPreview.val('').attr('placeholder', 'Número no disponible');
            });
        }

        function cargarCategorias() {
            toggleLoading($categoriaSelect, true);
            return $.ajax({
                url: API_BASE,
                data: { accion: 'categorias' },
                method: 'GET',
                dataType: 'json'
            }).done(function (res) {
                limpiarSelect($categoriaSelect);
                if (res && res.success && Array.isArray(res.data)) {
                    res.data.forEach(function (cat) {
                        $categoriaSelect.append('<option value="' + cat.id_categoria + '">' + cat.nombre + '</option>');
                    });
                }
            }).fail(function () {
                mostrarAlertaInline($formAlert, 'error', 'No se pudieron cargar las categorías.');
            }).always(function () {
                toggleLoading($categoriaSelect, false);
            });
        }

        function cargarRamos(idCategoria) {
            limpiarSelect($ramoSelect);
            renderCoberturas([]);
            if (!idCategoria) {
                $ramoSelect.attr('disabled', true);
                return resolvedPromise();
            }
            $ramoSelect.attr('disabled', true);
            return $.ajax({
                url: API_BASE,
                data: { accion: 'ramos', id_categoria: idCategoria },
                method: 'GET',
                dataType: 'json'
            }).done(function (res) {
                limpiarSelect($ramoSelect);
                if (res && res.success && Array.isArray(res.data) && res.data.length) {
                    res.data.forEach(function (ramo) {
                        $ramoSelect.append('<option value="' + ramo.id_tipo_poliza + '">' + ramo.nombre + '</option>');
                    });
                    $ramoSelect.removeAttr('disabled');
                } else {
                    renderCoberturas([]);
                    $ramoSelect.attr('disabled', true);
                }
            }).fail(function () {
                mostrarAlertaInline($formAlert, 'error', 'No se pudieron cargar los ramos.');
            });
        }

        function cargarCoberturas(idRamo) {
            renderCoberturas([]);
            if (!idRamo) {
                return resolvedPromise();
            }
            return $.ajax({
                url: API_BASE,
                data: { accion: 'coberturas', id_tipo_poliza: idRamo },
                method: 'GET',
                dataType: 'json'
            }).done(function (res) {
                if (res && res.success) {
                    renderCoberturas(res.data || []);
                }
            }).fail(function () {
                mostrarAlertaInline($formAlert, 'error', 'No se pudieron cargar las coberturas.');
            });
        }

        function cargarAgentes() {
            if (!isAdmin) {
                return resolvedPromise();
            }
            toggleLoading($agenteSelect, true);
            return $.ajax({
                url: API_BASE,
                data: { accion: 'agentes' },
                method: 'GET',
                dataType: 'json'
            }).done(function (res) {
                limpiarSelect($agenteSelect);
                if (res && res.success && Array.isArray(res.data)) {
                    res.data.forEach(function (ag) {
                        var nombre = (ag.nombre || '') + ' ' + (ag.apellido || '');
                        $agenteSelect.append('<option value="' + ag.cedula + '">' + nombre.trim() + ' (' + ag.cedula + ')</option>');
                    });
                }
            }).fail(function () {
                mostrarAlertaInline($formAlert, 'error', 'No se pudieron cargar los agentes.');
            }).always(function () {
                toggleLoading($agenteSelect, false);
            });
        }

        function cargarClientes() {
            toggleLoading($clienteSelect, true);
            return $.ajax({
                url: API_BASE,
                data: { accion: 'clientes' },
                method: 'GET',
                dataType: 'json'
            }).done(function (res) {
                limpiarSelect($clienteSelect);
                if (res && res.success && Array.isArray(res.data)) {
                    res.data.forEach(function (cl) {
                        var nombre = (cl.nombre || '') + ' ' + (cl.apellido || '');
                        $clienteSelect.append('<option value="' + cl.id_cliente + '">' + nombre.trim() + ' (' + cl.cedula + ')</option>');
                    });
                }
            }).fail(function () {
                mostrarAlertaInline($formAlert, 'error', 'No se pudieron cargar los clientes.');
            }).always(function () {
                toggleLoading($clienteSelect, false);
            });
        }

        function datosTabla(polizas) {
            var registros = Array.isArray(polizas) ? polizas : [];
            return registros.map(function (item) {
                if (normalizeEstado(item.estado) === 'ELIMINADA') {
                    return null;
                }
                return {
                    id_poliza: item.id_poliza,
                    numero_poliza: item.numero_poliza || '—',
                    categoria: item.categoria || '—',
                    ramo: item.ramo || '—',
                    coberturas: item.coberturas || '—',
                    cliente: item.cliente || '—',
                    agente: item.agente || '—',
                    fecha_inicio: formatDate(item.fecha_inicio),
                    fecha_fin: formatDate(item.fecha_fin),
                    monto_prima_total: formatCurrency(item.monto_prima_total),
                    estado: estadoBadge(item.estado),
                    acciones: item.acciones || ''
                };
            });
        }

        function limpiarDatos(datos) {
            if (!Array.isArray(datos)) {
                return [];
            }
            return datos.filter(function (item) { return item !== null; });
        }

        function cargarPolizas() {
            if (!puedeVerLista) {
                mostrarAlerta($pageAlert, 'error', 'No tiene permiso para consultar las pólizas.');
                if (dataTable) {
                    dataTable.clear().draw();
                }
                return;
            }
            var params = { accion: 'listar' };
            if (!isAdmin && config.cedulaActual) {
                params.cedula_agente = config.cedulaActual;
            }
            $.ajax({
                url: API_BASE,
                data: params,
                method: 'GET',
                dataType: 'json'
            }).done(function (res) {
                if (!(res && res.success)) {
                    mostrarAlerta($pageAlert, 'error', res && res.message ? res.message : 'No se pudieron cargar las pólizas.');
                    return;
                }
                mostrarAlerta($pageAlert, null, null);
                var data = limpiarDatos(datosTabla(res.data));
                if (!dataTable) {
                    dataTable = $tabla.DataTable({
                        data: data,
                        columns: [
                            { title: 'Número', data: 'numero_poliza' },
                            { title: 'Categoría', data: 'categoria' },
                            { title: 'Ramo', data: 'ramo' },
                            { title: 'Coberturas', data: 'coberturas' },
                            { title: 'Cliente', data: 'cliente' },
                            { title: 'Agente', data: 'agente' },
                            { title: 'Inicio', data: 'fecha_inicio' },
                            { title: 'Fin', data: 'fecha_fin' },
                            { title: 'Prima total', data: 'monto_prima_total' },
                            { title: 'Estado', data: 'estado', orderable: false },
                            {
                                title: 'Acciones',
                                data: 'acciones',
                                orderable: false,
                                searchable: false,
                                className: 'text-center text-nowrap',
                                defaultContent: '',
                                render: function (data) {
                                    return data || '';
                                }
                            }
                        ],
                        language: { url: DATATABLE_LANG },
                        order: [[0, 'desc']],
                        responsive: false,
                        autoWidth: false,
                        scrollX: true
                    });
                } else {
                    dataTable.clear();
                    dataTable.rows.add(data).draw();
                }
                if (dataTable) {
                    dataTable.columns.adjust().draw(false);
                }
            }).fail(function (jqXHR) {
                var mensajeError = 'No se pudieron obtener las pólizas.';
                if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    mensajeError = jqXHR.responseJSON.message;
                } else if (jqXHR && typeof jqXHR.responseText === 'string' && jqXHR.responseText.trim()) {
                    mensajeError = jqXHR.responseText.trim();
                }
                mostrarAlerta($pageAlert, 'error', mensajeError);
            });
        }

        function calcularCuota() {
            var total = parseFloat($montoPrima.val());
            var cuotas = parseInt($numeroCuotas.val(), 10);
            if (!Number.isFinite(total) || !Number.isFinite(cuotas) || cuotas <= 0 || total <= 0) {
                $cuotaResumen.text('--');
                return;
            }
            var monto = total / cuotas;
            $cuotaResumen.text(formatCurrency(monto));
        }

        function configurarFormularioEdicion(detalle) {
            modoEdicion = true;
            polizaEnEdicion = detalle.id_poliza;

            $modal.find('.modal-title').text('Editar póliza');
            $guardarBtn.text('Guardar cambios');

            if ($numeroPolizaPreview.length) {
                var numero = detalle.numero_poliza || '';
                $numeroPolizaPreview.val(numero);
                if (numero) {
                    $numeroPolizaPreview.attr('placeholder', numero);
                }
            }

            setEstadoFormulario(detalle.estado, { habilitar: true });

            if (isAdmin) {
                var agenteLabel = detalle.agente_nombre
                    ? detalle.agente_nombre + (detalle.cedula_agente ? ' (' + detalle.cedula_agente + ')' : '')
                    : detalle.cedula_agente;
                asegurarOpcion($agenteSelect, detalle.cedula_agente, agenteLabel);
                $agenteSelect.val(detalle.cedula_agente || '');
            } else if ($agenteResumenWrapper.length) {
                var resumen = detalle.agente_nombre
                    ? detalle.agente_nombre + (detalle.cedula_agente ? ' (' + detalle.cedula_agente + ')' : '')
                    : ($agenteResumenTexto.text() || 'Agente actual');
                $agenteResumenTexto.text(resumen);
            }

            if (detalle.id_cliente) {
                var clienteLabel = detalle.cliente_nombre
                    ? detalle.cliente_nombre + (detalle.cliente_cedula ? ' (' + detalle.cliente_cedula + ')' : '')
                    : detalle.cliente_cedula;
                asegurarOpcion($clienteSelect, detalle.id_cliente, clienteLabel);
                $clienteSelect.val(String(detalle.id_cliente));
            }

            $fechaInicio.removeAttr('min').val(detalle.fecha_inicio || '');
            $fechaFin.removeAttr('min').val(detalle.fecha_fin || '');
            $fechaPrimerVenc.removeAttr('min').val(detalle.fecha_primer_vencimiento || '');

            if (detalle.monto_prima_total !== undefined && detalle.monto_prima_total !== null) {
                $montoPrima.val(detalle.monto_prima_total);
            }
            if (detalle.numero_cuotas) {
                $numeroCuotas.val(detalle.numero_cuotas);
            }

            var frecuencia = normalizeEstado(detalle.frecuencia_pago);
            if (!frecuencia) {
                frecuencia = 'MENSUAL';
            }
            $frecuenciaPago.val(frecuencia);

            calcularCuota();
            if (detalle.monto_cuota !== undefined && detalle.monto_cuota !== null) {
                $cuotaResumen.text(formatCurrency(detalle.monto_cuota));
            }

            var promesaRamos = resolvedPromise();
            if (detalle.id_categoria) {
                $categoriaSelect.val(String(detalle.id_categoria));
                promesaRamos = cargarRamos(detalle.id_categoria).then(function () {
                    if (detalle.id_tipo_poliza) {
                        $ramoSelect.val(String(detalle.id_tipo_poliza)).removeAttr('disabled');
                        return cargarCoberturas(detalle.id_tipo_poliza).then(function () {
                            marcarCoberturasSeleccionadas(detalle.coberturas || []);
                        });
                    }
                    return resolvedPromise();
                });
            } else {
                $categoriaSelect.val('');
            }

            return promesaRamos;
        }

        function resetFormulario(options) {
            options = $.extend({
                skipAutoNumero: false,
                preserveModo: false,
                skipAlerts: false
            }, options || {});

            if (!options.preserveModo) {
                modoEdicion = false;
                polizaEnEdicion = null;
            }

            if (!options.skipAlerts) {
                mostrarAlertaInline($formAlert, null, null);
            }

            if ($form.length && $form[0]) {
                $form[0].reset();
            }

            limpiarSelect($categoriaSelect);
            limpiarSelect($ramoSelect);
            $ramoSelect.attr('disabled', true);
            renderCoberturas([]);
            $montoPrima.val('');
            $numeroCuotas.val('1');
            $frecuenciaPago.val('MENSUAL');
            var hoy = hoyISO();
            $fechaInicio.val(hoy);
            $fechaFin.val(masMesesISO(12));
            $fechaInicio.attr('min', hoy);
            $fechaFin.attr('min', hoy);
            $fechaPrimerVenc.val(hoy).attr('min', hoy);
            $cuotaResumen.text('--');

            if (isAdmin) {
                limpiarSelect($agenteSelect);
            }
            limpiarSelect($clienteSelect);

            if (!isAdmin && $agenteResumenWrapper.length) {
                var resumenDefault = config.nombreActual && config.cedulaActual
                    ? config.nombreActual + ' (' + config.cedulaActual + ')'
                    : (config.cedulaActual || 'Agente actual');
                $agenteResumenTexto.text(resumenDefault || 'Agente actual');
            }

            if ($numeroPolizaPreview.length) {
                if (options.skipAutoNumero) {
                    $numeroPolizaPreview.val('').attr('placeholder', 'Número asignado');
                } else {
                    $numeroPolizaPreview.val('').attr('placeholder', 'Generando número...');
                }
            }

            setEstadoFormulario('ACTIVA');

            $modal.find('.modal-title').text(tituloModalOriginal);
            $guardarBtn.text(textoGuardarOriginal);

            var loaders = [cargarCategorias(), cargarClientes()];
            if (isAdmin) {
                loaders.push(cargarAgentes());
            }
            if (!options.skipAutoNumero && $numeroPolizaPreview.length) {
                loaders.push(cargarNumeroPoliza());
            }

            if (!loaders.length) {
                return resolvedPromise();
            }
            return $.when.apply($, loaders);
        }

        function recolectarCoberturas() {
            var seleccionadas = [];
            $coberturasContainer.find('input[name="coberturas[]"]:checked').each(function () {
                var valor = parseInt($(this).val(), 10);
                if (Number.isInteger(valor)) {
                    seleccionadas.push(valor);
                }
            });
            return seleccionadas;
        }

        function recolectarDatos() {
            var payload = {
                id_tipo_poliza: parseInt($ramoSelect.val(), 10) || 0,
                cedula_agente: isAdmin ? ($agenteSelect.val() || '').trim() : (config.cedulaActual || '').trim(),
                id_cliente: parseInt($clienteSelect.val(), 10) || 0,
                fecha_inicio: $fechaInicio.val(),
                fecha_fin: $fechaFin.val(),
                monto_prima_total: parseFloat($montoPrima.val()) || 0,
                numero_cuotas: parseInt($numeroCuotas.val(), 10) || 0,
                frecuencia_pago: normalizeEstado($frecuenciaPago.val()),
                fecha_primer_vencimiento: $fechaPrimerVenc.val(),
                coberturas: recolectarCoberturas()
            };
            payload.cedula_agente = payload.cedula_agente || '';
            var estadoSeleccionado = $estadoSelect.length ? normalizeEstado($estadoSelect.val()) : '';
            if (!estadoSeleccionado || !ESTADO_DEFINICIONES[estadoSeleccionado]) {
                estadoSeleccionado = 'ACTIVA';
            }
            payload.estado = estadoSeleccionado;
            return payload;
        }

        function validarDatos(data) {
            if (!data.id_tipo_poliza) {
                return 'Seleccione un ramo.';
            }
            if (!data.cedula_agente) {
                return 'Seleccione un agente.';
            }
            if (!data.id_cliente) {
                return 'Seleccione un cliente.';
            }
            if (!data.fecha_inicio) {
                return 'Indique la fecha de inicio.';
            }
            if (!data.fecha_fin) {
                return 'Indique la fecha de fin.';
            }
            if (data.fecha_fin < data.fecha_inicio) {
                return 'La fecha de fin debe ser posterior a la fecha de inicio.';
            }
            if (!Number.isFinite(data.monto_prima_total) || data.monto_prima_total <= 0) {
                return 'Ingrese la prima total.';
            }
            if (!Number.isInteger(data.numero_cuotas) || data.numero_cuotas <= 0) {
                return 'Las cuotas deben ser un número entero mayor que cero.';
            }
            if (!data.fecha_primer_vencimiento) {
                return 'Indique la fecha del primer vencimiento.';
            }
            return null;
        }

        function guardarPoliza() {
            if (modoEdicion && !puedeEditar) {
                mostrarAlertaInline($formAlert, 'error', 'No tiene permiso para editar pólizas.');
                return;
            }
            if (!modoEdicion && !puedeCrear) {
                mostrarAlertaInline($formAlert, 'error', 'No tiene permiso para registrar pólizas.');
                return;
            }
            var datos = recolectarDatos();
            var error = validarDatos(datos);
            if (error) {
                mostrarAlertaInline($formAlert, 'error', error);
                return;
            }
            mostrarAlertaInline($formAlert, null, null);
            toggleLoading($guardarBtn, true);
            var accion = modoEdicion ? 'actualizar' : 'crear';
            var textoBoton = modoEdicion ? textoGuardarCambios : textoGuardarOriginal;
            var textoProceso = modoEdicion ? 'Guardando cambios...' : 'Guardando...';
            $guardarBtn.data('original-text', textoBoton);
            $guardarBtn.text(textoProceso);

            var payload = $.extend({}, datos);
            if (modoEdicion && polizaEnEdicion) {
                payload.id_poliza = polizaEnEdicion;
            }

            $.ajax({
                url: API_BASE + '?accion=' + accion,
                method: 'POST',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify(payload)
            }).done(function (res) {
                if (res && res.success) {
                    var numeroReferencia = res.numero_poliza || ($numeroPolizaPreview.length ? $numeroPolizaPreview.val() : '');
                    $('#modalPoliza').modal('hide');
                    var mensaje;
                    if (modoEdicion) {
                        mensaje = numeroReferencia ? 'Póliza ' + numeroReferencia + ' actualizada correctamente.' : 'Póliza actualizada correctamente.';
                    } else {
                        mensaje = numeroReferencia ? 'Póliza ' + numeroReferencia + ' creada correctamente.' : 'Póliza creada correctamente.';
                    }
                    mostrarAlerta($pageAlert, 'success', mensaje);
                    modoEdicion = false;
                    polizaEnEdicion = null;
                    saltarResetModal = false;
                    cargarPolizas();
                } else {
                    var mensaje = (res && res.message) ? res.message : (modoEdicion ? 'No se pudo actualizar la póliza.' : 'No se pudo crear la póliza.');
                    mostrarAlertaInline($formAlert, 'error', mensaje);
                }
            }).fail(function () {
                var mensajeFallo = modoEdicion ? 'Ocurrió un error al actualizar la póliza.' : 'Ocurrió un error al guardar la póliza.';
                mostrarAlertaInline($formAlert, 'error', mensajeFallo);
            }).always(function () {
                toggleLoading($guardarBtn, false);
                $guardarBtn.text($guardarBtn.data('original-text') || textoBoton);
            });
        }

        function iniciarEdicionPoliza(id) {
            if (!puedeEditar) {
                mostrarAlerta($pageAlert, 'error', 'No tiene permiso para editar pólizas.');
                return;
            }
            modoEdicion = true;
            polizaEnEdicion = id;
            mostrarAlertaInline($formAlert, null, null);
            solicitarDetallePoliza(id).done(function (res) {
                if (res && res.success && res.data) {
                    var detalle = res.data;
                    $.when(resetFormulario({ skipAutoNumero: true, preserveModo: true, skipAlerts: true }))
                        .then(function () {
                            return configurarFormularioEdicion(detalle);
                        })
                        .done(function () {
                            saltarResetModal = true;
                            $modal.modal('show');
                        })
                        .fail(function () {
                            mostrarAlerta($pageAlert, 'error', 'No se pudo preparar el formulario de edición.');
                            modoEdicion = false;
                            polizaEnEdicion = null;
                            saltarResetModal = false;
                        });
                } else {
                    mostrarAlerta($pageAlert, 'error', (res && res.message) ? res.message : 'No se pudo obtener la póliza para editar.');
                    modoEdicion = false;
                    polizaEnEdicion = null;
                }
            }).fail(function () {
                mostrarAlerta($pageAlert, 'error', 'Error al obtener los datos de la póliza.');
                modoEdicion = false;
                polizaEnEdicion = null;
            });
        }

        function mostrarDetallePoliza(id) {
            if (!puedeVerLista) {
                mostrarAlerta($pageAlert, 'error', 'No tiene permiso para consultar pólizas.');
                return;
            }
            if (!$detalleModal.length) {
                mostrarAlerta($pageAlert, 'error', 'No se encuentra configurado el modal de detalle.');
                return;
            }
            limpiarDetalleModal();
            solicitarDetallePoliza(id).done(function (res) {
                if (res && res.success && res.data) {
                    poblarDetalleModal(res.data);
                    $detalleModal.modal('show');
                } else {
                    mostrarAlerta($pageAlert, 'error', (res && res.message) ? res.message : 'No se pudo obtener el detalle de la póliza.');
                }
            }).fail(function () {
                mostrarAlerta($pageAlert, 'error', 'Error al consultar el detalle de la póliza.');
            });
        }

        $categoriaSelect.on('change', function () {
            cargarRamos($(this).val());
        });

        $ramoSelect.on('change', function () {
            cargarCoberturas($(this).val());
        });

        $montoPrima.on('input', calcularCuota);
        $numeroCuotas.on('input', calcularCuota);

        $fechaInicio.on('change', function () {
            var inicio = $(this).val();
            if (inicio) {
                $fechaFin.attr('min', inicio);
            }
        });

        $form.on('submit', function (event) {
            event.preventDefault();
            guardarPoliza();
        });

        $modal.on('show.bs.modal', function () {
            if (saltarResetModal) {
                return;
            }
            resetFormulario();
        });

        $modal.on('shown.bs.modal', function () {
            if (saltarResetModal) {
                saltarResetModal = false;
            }
        });

        $modal.on('hidden.bs.modal', function () {
            modoEdicion = false;
            polizaEnEdicion = null;
            saltarResetModal = false;
            setEstadoFormulario('ACTIVA');
        });

        $tabla.on('click', '.poliza-accion', function () {
            var action = $(this).data('action');
            var id = parseInt($(this).data('id'), 10);
            if (!id) {
                return;
            }
            if (action === 'eliminar') {
                if (!puedeEliminar) {
                    mostrarAlerta($pageAlert, 'error', 'No tiene permiso para eliminar pólizas.');
                    return;
                }
                if (!confirm('¿Desea marcar esta póliza como eliminada?')) {
                    return;
                }
                $.ajax({
                    url: API_BASE,
                    method: 'POST',
                    dataType: 'json',
                    data: { accion: 'eliminar', id_poliza: id }
                }).done(function (res) {
                    if (res && res.success) {
                        mostrarAlerta($pageAlert, 'success', res.message || 'Póliza eliminada.');
                        cargarPolizas();
                    } else {
                        mostrarAlerta($pageAlert, 'error', (res && res.message) ? res.message : 'No se pudo eliminar la póliza.');
                    }
                }).fail(function () {
                    mostrarAlerta($pageAlert, 'error', 'Error al eliminar la póliza.');
                });
            } else if (action === 'editar') {
                if (!puedeEditar) {
                    mostrarAlerta($pageAlert, 'error', 'No tiene permiso para editar pólizas.');
                    return;
                }
                iniciarEdicionPoliza(id);
            } else if (action === 'detalle') {
                if (!puedeVerLista) {
                    mostrarAlerta($pageAlert, 'error', 'No tiene permiso para consultar pólizas.');
                    return;
                }
                mostrarDetallePoliza(id);
            }
        });

        cargarPolizas();
        if (puedeCrear || puedeEditar) {
            cargarCategorias();
            if (isAdmin) {
                cargarAgentes();
            }
            cargarClientes();
        }
    };

})(window, window.jQuery);
