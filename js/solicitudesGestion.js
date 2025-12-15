(function () {
  const config = window.SolicitudesGestionConfig || { esAdmin: false, estados: {}, agentes: [] };
  const $ = window.jQuery;
  if (!$) {
    console.error('jQuery no está disponible al inicializar la gestión de solicitudes.');
    return;
  }
  const estadoSelect = document.getElementById('filtroEstado');
  const tipoSelect = document.getElementById('filtroTipo');
  const agenteSelect = document.getElementById('filtroAgente');
  const busquedaInput = document.getElementById('filtroBusqueda');
  const alertContainer = document.getElementById('solicitudesAlert');
  const modalDetalle = $('#detalleSolicitudModal');
  const modalEstado = $('#cambiarEstadoModal');
  const modalReasignar = $('#reasignarSolicitudModal');
  const estadoForm = document.getElementById('formActualizarEstado');
  const reasignarForm = document.getElementById('formReasignarSolicitud');
  const estadoNuevoSelect = document.getElementById('estadoNuevo');
  const estadoNotaInput = document.getElementById('estadoNota');
  const estadoIdInput = document.getElementById('estadoSolicitudId');
  const estadoOrigenInput = document.getElementById('estadoSolicitudOrigen');
  const reasignarIdInput = document.getElementById('reasignarSolicitudId');
  const reasignarOrigenInput = document.getElementById('reasignarSolicitudOrigen');
  const reasignarAgenteSelect = document.getElementById('reasignarAgente');
  const btnGuardarEstado = document.getElementById('btnGuardarEstado');
  const btnGuardarReasignacion = document.getElementById('btnGuardarReasignacion');

  function buildControladorSolicitudUrl() {
    const path = window.location && window.location.pathname ? window.location.pathname : '';
    if (!path) {
      return null;
    }
    const vistaIndex = path.indexOf('/vista/');
    let base = '';
    if (vistaIndex !== -1) {
      base = path.substring(0, vistaIndex);
    } else {
      const lastSlash = path.lastIndexOf('/');
      base = lastSlash > -1 ? path.substring(0, lastSlash) : '';
    }
    base = base.replace(/\/+$/, '');
    return (base ? base : '') + '/controlador/controladorSolicitud.php';
  }

  const controladorSolicitudUrl = buildControladorSolicitudUrl();
  if (!controladorSolicitudUrl) {
    console.error('No se pudo determinar la ruta del controlador de solicitudes.');
    return;
  }

  let solicitudesCache = [];
  const solicitudesIndex = new Map();

  function esc(value) {
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

  function buildCodigo(item) {
    const prefix = item.origen === 'poliza' ? 'SP-' : 'SS-';
    return prefix + String(item.id).padStart(5, '0');
  }

  function parseDate(value) {
    if (!value) {
      return { sort: '', display: '' };
    }
    const normalized = value.replace(' ', 'T');
    const date = new Date(normalized);
    if (Number.isNaN(date.getTime())) {
      return { sort: value, display: value };
    }
    return {
      sort: date.toISOString(),
      display: date.toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })
    };
  }

  function resumirConEllipsis(texto, maxWords) {
    if (!texto) {
      return '';
    }
    const palabras = texto.trim().split(/\s+/);
    if (palabras.length <= maxWords) {
      return palabras.join(' ');
    }
    return palabras.slice(0, maxWords).join(' ') + '...';
  }

  function resumirSolicitud(item) {
    if (item.descripcion) {
      return item.descripcion;
    }
    if (item.origen === 'siniestro') {
      if (item.tipo_incidente) {
        return item.tipo_incidente;
      }
      if (item.numero_poliza) {
        return 'Póliza ' + item.numero_poliza;
      }
      return 'Reporte de siniestro';
    }
    if (item.origen === 'poliza') {
      return 'Solicitud de póliza';
    }
    return '';
  }

  function keyFor(item) {
    return item.origen + '-' + item.id;
  }

  function mostrarMensaje(tipo, mensaje) {
    if (!alertContainer) {
      return;
    }
    const html = [
      '<div class="alert alert-' + tipo + ' alert-dismissible fade show" role="alert">',
      esc(mensaje),
      '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">',
      '<span aria-hidden="true">&times;</span>',
      '</button>',
      '</div>'
    ].join('');
    alertContainer.innerHTML = html;
  }

  function limpiarMensaje() {
    if (alertContainer) {
      alertContainer.innerHTML = '';
    }
  }

  function poblarFiltroEstado() {
    if (!estadoSelect) {
      return;
    }
    const estadoMap = new Map();
    Object.keys(config.estados || {}).forEach(function (origen) {
      const lista = config.estados[origen];
      if (Array.isArray(lista)) {
        lista.forEach(function (estado) {
          if (!estadoMap.has(estado.value)) {
            estadoMap.set(estado.value, estado.label);
          }
        });
      }
    });
    estadoSelect.innerHTML = '';
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Todos';
    estadoSelect.appendChild(defaultOption);
    estadoMap.forEach(function (label, value) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = label;
      estadoSelect.appendChild(option);
    });
  }

  // Configuración unificada de columnas.
  // Usamos 'data' siempre para que DataTables sepa mapear los objetos JSON.
  // Los renderers se ajustan para manejar strings (caso inicial DOM) y objetos (caso JSON).
  const columnsUnified = [
    { data: 'codigo' },
    { data: 'cliente' },
    { data: 'tipo' },
    { data: 'resumen' },
    { data: 'asignado' },
    {
      data: 'estado',
      orderable: false,
      render: function (data, type) {
        // Si data es objeto (JSON {display, sort}), accedemos a sus props.
        // Si es string (DOM inicial), retornamos el string.
        if (data && typeof data === 'object') {
          if (type === 'display') {
            return data.display;
          }
          return data.sort;
        }
        return data;
      }
    },
    {
      data: 'actualizacion',
      render: function (data, type) {
        if (data && typeof data === 'object') {
          if (type === 'display') {
            return data.display;
          }
          return data.sort;
        }
        return data;
      }
    },
    {
      data: 'acciones',
      orderable: false,
      searchable: false
    }
  ];

  const tablaSolicitudes = $('#tablaSolicitudesGestion').DataTable({
    language: {
      url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
    },
    processing: true,
    autoWidth: false,
    scrollY: '420px',
    scrollCollapse: true,
    deferRender: true,
    paging: false,
    info: false,
    dom: '<"datatable-toolbar d-flex justify-content-end mb-3"f>rt',
    order: [[6, 'desc']],
    // Usamos SIEMPRE la configuración unificada que mapea propiedades (data: 'nombre').
    // Esto previene el error 'unknown parameter 0' cuando se añaden objetos vía JS.
    columns: columnsUnified
  });

  function aplicarFiltros(data) {
    const tipo = tipoSelect ? tipoSelect.value : '';
    const estado = estadoSelect ? estadoSelect.value : '';
    const agente = agenteSelect ? agenteSelect.value : '';
    const busqueda = busquedaInput ? busquedaInput.value.trim().toLowerCase() : '';

    return data.filter(function (item) {
      if (tipo && item.origen !== tipo) {
        return false;
      }
      if (estado && item.estado !== estado) {
        return false;
      }
      if (agente && (item.cedula_asignado || '') !== agente) {
        return false;
      }
      if (busqueda) {
        const fuente = [
          item.cliente || '',
          item.descripcion || '',
          item.ramo || '',
          item.categoria || '',
          item.numero_poliza || '',
          buildCodigo(item)
        ].join(' ').toLowerCase();
        if (!fuente.includes(busqueda)) {
          return false;
        }
      }
      return true;
    });
  }

  function construirFila(item) {
    const codigo = buildCodigo(item);
    const estadoBadge = '<span class="badge-soft" data-variant="' + esc(item.estado_variant || 'neutral') + '">' + esc(item.estado_label || item.estado) + '</span>';
    const acciones = ['<div class="table-action-buttons">'];
    acciones.push('<button type="button" class="action-icon action-icon--perm ver-detalle" data-id="' + item.id + '" data-origen="' + esc(item.origen) + '" title="Ver detalle"><i class="fas fa-eye"></i></button>');
    acciones.push('<button type="button" class="action-icon action-icon--edit editar-estado" data-id="' + item.id + '" data-origen="' + esc(item.origen) + '" title="Actualizar estado"><i class="fas fa-edit"></i></button>');
    if (config.esAdmin) {
      acciones.push('<button type="button" class="action-icon action-icon--perm reasignar-solicitud" data-id="' + item.id + '" data-origen="' + esc(item.origen) + '" title="Reasignar solicitud"><i class="fas fa-random"></i></button>');
    }
    acciones.push('</div>');
    const resumenCompleto = resumirSolicitud(item) || 'Sin detalles';
    const resumenLimitado = resumirConEllipsis(resumenCompleto, 10);
    return {
      codigo: codigo,
      cliente: esc(item.cliente || 'Desconocido'),
      tipo: item.origen === 'poliza' ? 'Solicitud de póliza' : 'Reporte de siniestro',
      resumen: '<span class="solicitud-resumen" title="' + esc(resumenCompleto) + '">' + esc(resumenLimitado) + '</span>',
      asignado: esc(item.asignado || 'Sin asignar'),
      estado: { display: estadoBadge, sort: item.estado_label || item.estado },
      actualizacion: parseDate(item.fecha_actualizacion || item.fecha),
      acciones: acciones.join('')
    };
  }

  function renderTabla() {
    const filtradas = aplicarFiltros(solicitudesCache);
    tablaSolicitudes.clear();
    const filas = filtradas.map(construirFila);
    tablaSolicitudes.rows.add(filas);
    tablaSolicitudes.draw();
  }

  function cargarSolicitudes() {
    limpiarMensaje();
    if (tablaSolicitudes && typeof tablaSolicitudes.processing === 'function') {
      tablaSolicitudes.processing(true);
    }
    fetch(controladorSolicitudUrl + '?accion=listar_asignadas', {
      credentials: 'same-origin'
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (tablaSolicitudes && typeof tablaSolicitudes.processing === 'function') {
          tablaSolicitudes.processing(false);
        }
        if (!data || !data.success) {
          mostrarMensaje('danger', data && data.message ? data.message : 'No se pudo obtener la información.');
          return;
        }
        solicitudesCache = Array.isArray(data.data) ? data.data : [];
        solicitudesIndex.clear();
        solicitudesCache.forEach(function (item) {
          solicitudesIndex.set(keyFor(item), item);
        });
        renderTabla();
      })
      .catch(function () {
        if (tablaSolicitudes && typeof tablaSolicitudes.processing === 'function') {
          tablaSolicitudes.processing(false);
        }
        mostrarMensaje('danger', 'Ocurrió un error al recuperar las solicitudes.');
      });
  }

  function obtenerSolicitud(origen, id) {
    return solicitudesIndex.get(origen + '-' + id) || null;
  }

  function detalleHtml(item) {
    const pares = [
      ['Código', buildCodigo(item)],
      ['Cliente', item.cliente || 'Desconocido'],
      ['Solicitud', item.origen === 'poliza' ? 'Solicitud de póliza' : 'Reporte de siniestro'],
      ['Estado', item.estado_label || item.estado],
      ['Asignado', item.asignado || 'Sin asignar'],
      ['Creación', parseDate(item.fecha).display || item.fecha],
      ['Actualización', parseDate(item.fecha_actualizacion || item.fecha).display || ''],
      ['Seguimiento para el cliente', item.nota_interna || 'Sin registros']
    ];
    if (item.origen === 'poliza') {
      pares.splice(3, 0, ['Categoría', item.categoria || '']);
      pares.splice(4, 0, ['Ramo', item.ramo || '']);
      if (item.contacto) {
        pares.push(['Contacto preferido', item.contacto]);
      }
    } else if (item.origen === 'siniestro') {
      if (item.numero_poliza) {
        pares.splice(3, 0, ['Póliza asociada', item.numero_poliza]);
      }
      if (item.tipo_incidente) {
        pares.push(['Tipo de incidente', item.tipo_incidente]);
      }
      if (item.fecha_incidente) {
        pares.push(['Fecha de incidente', item.fecha_incidente]);
      }
      if (item.lugar_incidente) {
        pares.push(['Lugar del incidente', item.lugar_incidente]);
      }
    }
    if (item.descripcion) {
      pares.push(['Descripción', item.descripcion]);
    }
    const contenido = ['<div class="container-fluid">'];
    pares.forEach(function (par) {
      if (!par[1]) {
        return;
      }
      contenido.push('<div class="row mb-2"><div class="col-sm-4 font-weight-bold">' + esc(par[0]) + '</div><div class="col-sm-8">' + esc(par[1]) + '</div></div>');
    });
    contenido.push('</div>');
    return contenido.join('');
  }

  function abrirDetalle(origen, id) {
    const solicitud = obtenerSolicitud(origen, id);
    if (!solicitud) {
      mostrarMensaje('warning', 'No se encontró la solicitud seleccionada.');
      return;
    }
    $('#detalleSolicitudContenido').html(detalleHtml(solicitud));
    modalDetalle.modal('show');
  }

  function abrirEstado(origen, id) {
    const solicitud = obtenerSolicitud(origen, id);
    if (!solicitud) {
      mostrarMensaje('warning', 'No se encontró la solicitud seleccionada.');
      return;
    }
    estadoIdInput.value = id;
    estadoOrigenInput.value = origen;
    estadoNotaInput.value = solicitud.nota_interna || '';
    while (estadoNuevoSelect.firstChild) {
      estadoNuevoSelect.removeChild(estadoNuevoSelect.firstChild);
    }
    const opciones = config.estados[origen] || [];
    opciones.forEach(function (estado) {
      const option = document.createElement('option');
      option.value = estado.value;
      option.textContent = estado.label;
      if (estado.value === solicitud.estado) {
        option.selected = true;
      }
      estadoNuevoSelect.appendChild(option);
    });
    modalEstado.modal('show');
  }

  function abrirReasignar(origen, id) {
    const solicitud = obtenerSolicitud(origen, id);
    if (!solicitud) {
      mostrarMensaje('warning', 'No se encontró la solicitud seleccionada.');
      return;
    }
    reasignarIdInput.value = id;
    reasignarOrigenInput.value = origen;
    if (reasignarAgenteSelect) {
      reasignarAgenteSelect.value = solicitud.cedula_asignado || '';
    }
    modalReasignar.modal('show');
  }

  function actualizarEstado() {
    if (!estadoForm.checkValidity()) {
      estadoForm.classList.add('was-validated');
      return;
    }
    const origen = estadoOrigenInput.value;
    const id = estadoIdInput.value;
    const estado = estadoNuevoSelect.value;
    const nota = estadoNotaInput.value.trim();
    btnGuardarEstado.disabled = true;
    const payload = new URLSearchParams();
    payload.append('accion', 'actualizar_estado');
    payload.append('origen', origen);
    payload.append('id', id);
    payload.append('estado', estado);
    payload.append('nota', nota);
    fetch(controladorSolicitudUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: payload.toString(),
      credentials: 'same-origin'
    })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        btnGuardarEstado.disabled = false;
        if (!data || !data.success) {
          mostrarMensaje('danger', data && data.message ? data.message : 'No fue posible actualizar el estado.');
          return;
        }
        modalEstado.modal('hide');
        mostrarMensaje('success', data.message || 'Estado actualizado correctamente.');
        cargarSolicitudes();
      })
      .catch(function () {
        btnGuardarEstado.disabled = false;
        mostrarMensaje('danger', 'Ocurrió un error al actualizar el estado.');
      });
  }

  function reasignarSolicitud() {
    if (!config.esAdmin) {
      return;
    }
    if (!reasignarForm.checkValidity()) {
      reasignarForm.classList.add('was-validated');
      return;
    }
    const origen = (reasignarOrigenInput.value || '').trim();
    const idRaw = (reasignarIdInput.value || '').trim();
    const idNumeric = Number.parseInt(idRaw, 10);
    const cedulaAgente = reasignarAgenteSelect && reasignarAgenteSelect.value ? reasignarAgenteSelect.value.trim() : '';
    if (!origen || !idRaw || (!Number.isNaN(idNumeric) && idNumeric <= 0) || !cedulaAgente) {
      mostrarMensaje('warning', 'Selecciona un agente válido antes de guardar.');
      return;
    }
    btnGuardarReasignacion.disabled = true;
    const payload = new URLSearchParams();
    payload.append('accion', 'asignar_agente');
    payload.append('origen', origen);
    payload.append('id', idRaw);
    payload.append('cedula_agente', cedulaAgente);
    fetch(controladorSolicitudUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: payload.toString(),
      credentials: 'same-origin'
    })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        btnGuardarReasignacion.disabled = false;
        if (!data || !data.success) {
          mostrarMensaje('danger', data && data.message ? data.message : 'No se pudo reasignar la solicitud.');
          return;
        }
        modalReasignar.modal('hide');
        mostrarMensaje('success', data.message || 'Reasignación completada.');
        cargarSolicitudes();
      })
      .catch(function () {
        btnGuardarReasignacion.disabled = false;
        mostrarMensaje('danger', 'Ocurrió un error al reasignar la solicitud.');
      });
  }

  function registrarEventos() {
    $('#tablaSolicitudesGestion tbody').on('click', '.ver-detalle', function () {
      const origen = this.getAttribute('data-origen');
      const id = this.getAttribute('data-id');
      abrirDetalle(origen, id);
    });

    $('#tablaSolicitudesGestion tbody').on('click', '.editar-estado', function () {
      const origen = this.getAttribute('data-origen');
      const id = this.getAttribute('data-id');
      abrirEstado(origen, id);
    });

    $('#tablaSolicitudesGestion tbody').on('click', '.reasignar-solicitud', function () {
      const origen = this.getAttribute('data-origen');
      const id = this.getAttribute('data-id');
      abrirReasignar(origen, id);
    });

    if (tipoSelect) {
      tipoSelect.addEventListener('change', renderTabla);
    }
    if (estadoSelect) {
      estadoSelect.addEventListener('change', renderTabla);
    }
    if (agenteSelect) {
      agenteSelect.addEventListener('change', renderTabla);
    }
    if (busquedaInput) {
      busquedaInput.addEventListener('input', function () {
        renderTabla();
      });
    }
    if (btnGuardarEstado) {
      btnGuardarEstado.addEventListener('click', actualizarEstado);
    }
    if (btnGuardarReasignacion) {
      btnGuardarReasignacion.addEventListener('click', reasignarSolicitud);
    }

    if (modalEstado) {
      modalEstado.on('hidden.bs.modal', function () {
        if (estadoForm) {
          estadoForm.reset();
          estadoForm.classList.remove('was-validated');
        }
      });
    }

    if (modalReasignar) {
      modalReasignar.on('hidden.bs.modal', function () {
        if (reasignarForm) {
          reasignarForm.reset();
          reasignarForm.classList.remove('was-validated');
        }
      });
    }
  }

  poblarFiltroEstado();
  registrarEventos();
  cargarSolicitudes();
})();