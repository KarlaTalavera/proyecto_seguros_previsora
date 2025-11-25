<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Dashboard Gerencial</h3>

  <div class="row mt-3">
    <div class="col-md-3">
      <div class="card p-3 border-0 shadow-sm bg-white rounded">
        <div class="small text-muted">Pólizas con Pagos Pendientes</div>
        <h4 id="k_pendientes_pct" class="text-secondary">—</h4>
        <div class="mt-2" style="height:4px;width:40px;background:#f1f3f5;border-radius:2px;"></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 border-0 shadow-sm bg-white rounded">
        <div class="small text-muted">Primas Pagadas</div>
        <h4 id="k_primas_pagadas" class="text-secondary">—</h4>
        <div class="mt-2" style="height:4px;width:40px;background:#f1f3f5;border-radius:2px;"></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 border-0 shadow-sm bg-white rounded">
        <div class="small text-muted">Agentes Activos</div>
        <h4 id="k_agentes_activos" class="text-secondary">—</h4>
        <div class="mt-2" style="height:4px;width:40px;background:#f1f3f5;border-radius:2px;"></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 border-0 shadow-sm bg-white rounded">
        <div class="small text-muted">Siniestros Abiertos</div>
        <h4 id="k_siniestros_abiertos" class="text-secondary">—</h4>
        <div class="mt-2" style="height:4px;width:40px;background:#f1f3f5;border-radius:2px;"></div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
  <div class="col-lg-6"><div class="card p-3"><h6>Pólizas por Ramo</h6><div class="chart-wrapper" style="height:320px;"><canvas id="ramoChart" style="width:100%;height:100%;"></canvas></div></div></div>
    <div class="col-lg-6"><div class="card p-3"><h6>Tendencia de Siniestros (últimos 12 meses)</h6><canvas id="siniestrosTrend"></canvas></div></div>
  </div>

    <div class="row mt-4">
      <div class="col-lg-6">
        <div class="card p-3">
          <h6>Pólizas por Vencer (próx. 30 días)</h6>
          <div class="table-responsive"><table class="table table-sm" id="polizasVencerTable"><thead><tr><th>#</th><th>Póliza</th><th>Agente</th><th>Vence</th><th>Prima</th></tr></thead><tbody></tbody></table></div>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="card p-3"><h6>Estado de las Pólizas</h6><div class="chart-wrapper" style="height:240px;"><canvas id="r4Chart" style="width:100%;height:100%;"></canvas></div></div>
      </div>
      <div class="col-lg-3">
        <div class="card p-3"><h6>Ranking Productividad</h6><div class="chart-wrapper" style="height:240px;"><canvas id="r8Chart" style="width:100%;height:100%;"></canvas></div></div>
      </div>
    </div>
  </div>

  <?php
  $extra_scripts = <<<EOT
  <script>
  // Cargar R1, R4, R8 y dibujar gráficos
  \$(function(){
    console.log('estadisticasAdmin: extra scripts loaded');
    // R1: pólizas por vencer
    $.getJSON('controlador/controladorReporte.php', { accion: 'r1', dias: 30 }, function(res){
      const tbody = $('#polizasVencerTable tbody');
      tbody.empty();
      if (res.success && res.data && res.data.length) {
        res.data.forEach(function(row, idx){
          const agente = (row.nombre_agente ? (row.nombre_agente + ' ' + row.apellido_agente) : row.cedula_agente);
          const prima = row.monto_prima_total !== undefined ? row.monto_prima_total : (row.monto_prima || '');
          tbody.append('<tr><td>' + (idx+1) + '</td><td>' + row.numero_poliza + '</td><td>' + agente + '</td><td>' + row.fecha_fin + '</td><td>' + prima + '</td></tr>');
        });
      } else {
        tbody.append('<tr><td colspan="5" class="text-center text-muted">No se encontraron pólizas por vencer en los próximos 30 días.</td></tr>');
      }
    }).fail(function(){
      const tbody = $('#polizasVencerTable tbody'); tbody.empty(); tbody.append('<tr><td colspan="5" class="text-center text-danger">Error cargando datos.</td></tr>');
    });

  // Palette provided by user
  const palette = ['#d3b8ff','#a98fff','#7e61ff','#513dff','#2d1aff'];

  // R4: distribución de estados de póliza
    $.getJSON('controlador/controladorReporte.php', { accion: 'r4' }, function(res){
      const container = $('#r4Chart').parent();
      container.find('.chart-note').remove();
      if (res.success && Array.isArray(res.data) && res.data.length) {
        const rows = res.data;
        const labelizeEstado = function(estado){
          if (!estado) return 'Sin estado';
          return estado.toString().toLowerCase()
            .split(/[_\s]+/)
            .filter(Boolean)
            .map(function(part){ return part.charAt(0).toUpperCase() + part.slice(1); })
            .join(' ');
        };
        const labels = rows.map(function(row){ return labelizeEstado(row.estado); });
        const data = rows.map(function(row){ return parseInt(row.total || 0, 10); });
        const hasData = data.some(function(value){ return value > 0; });
        if (!hasData) {
          try { if (window.r4ChartInstance) { window.r4ChartInstance.destroy(); } } catch (e) {}
          window.r4ChartInstance = null;
          container.append('<div class="text-center text-muted small mt-2 chart-note">No hay pólizas registradas.</div>');
          return;
        }
        const colors = labels.map(function(_, idx){ return palette[idx % palette.length]; });
        try { if (window.r4ChartInstance) { window.r4ChartInstance.destroy(); } } catch (e) {}
        const ctxEl = document.getElementById('r4Chart');
        window.r4ChartInstance = new Chart(ctxEl, {
          type: 'doughnut',
          data: { labels: labels, datasets: [{ data: data, backgroundColor: colors }] },
          options: {
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
          }
        });
        container.append('<div class="small text-muted mt-2 chart-note">Distribución por estado de las pólizas registradas.</div>');
      } else {
        try { if (window.r4ChartInstance) { window.r4ChartInstance.destroy(); } } catch (e) {}
        window.r4ChartInstance = null;
        container.append('<div class="text-center text-muted small mt-2 chart-note">No hay datos de estados de póliza.</div>');
      }
    }).fail(function(){
      const container = $('#r4Chart').parent();
      container.find('.chart-note').remove();
      try { if (window.r4ChartInstance) { window.r4ChartInstance.destroy(); } } catch (e) {}
      window.r4ChartInstance = null;
      container.append('<div class="text-center text-danger small mt-2 chart-note">Error cargando estados de póliza.</div>');
    });

    // R8: ranking productividad
    $.getJSON('controlador/controladorReporte.php', { accion: 'r8', months: 12, limit: 10 }, function(res){
      if (res.success && res.data && res.data.length) {
        const labels = res.data.map(r => (r.nombre ? (r.nombre + ' ' + r.apellido) : r.cedula_agente));
        const values = res.data.map(r => parseInt(r.num_polizas||0));
        new Chart(document.getElementById('r8Chart'), {
          type: 'bar',
          data: { labels: labels, datasets:[{label:'Pólizas', data: values, backgroundColor: palette[4]}] },
          options: { indexAxis: 'y', maintainAspectRatio: false, plugins:{legend:{display:false}} }
        });
      } else {
        $('#r8Chart').parent().append('<div class="text-center text-muted small mt-2">No hay datos de productividad.</div>');
      }
    }).fail(function(){
      $('#r8Chart').parent().append('<div class="text-center text-danger small mt-2">Error cargando ranking.</div>');
    });
    // R: Pólizas por Ramo (barra)
    $.getJSON('controlador/controladorReporte.php', { accion: 'r_ramo' }, function(res){
        if (res.success && res.data && res.data.length) {
        const labels = res.data.map(r => r.categoria || 'Otro');
        const values = res.data.map(r => parseInt(r.total || 0));
        // Debug: log labels/values and Chart.js version to investigate missing bars
        try { console.log('ramoChart labels:', labels); console.log('ramoChart values:', values); console.log('Chart.version:', Chart && Chart.version); } catch(e) { console.warn('ramoChart debug log failed', e); }
  // Ensure visible colors and basic options for compatibility across Chart.js versions
  // If a label looks like 'automóvil' (case-insensitive, partial match), highlight it for visibility
  // Use palette-consistent color for 'Automóvil' so it matches other charts
  const bg = labels.map((label,i) => (String(label||'').toLowerCase().includes('autom') ? palette[2] : palette[i % palette.length]));
  const border = bg.map(c => c);
        // Render like `r8Chart` (horizontal bars) — keep config minimal to match existing working charts
        const ctxEl = document.getElementById('ramoChart');
        const cfg = {
          type: 'bar',
          data: { labels: labels, datasets: [{ label: 'Pólizas', data: values, backgroundColor: bg, borderColor: border, borderWidth: 1 }] },
          options: {
            // vertical bars (default)
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            // Chart.js v3+ style
            scales: {
              y: {
                beginAtZero: true,
                min: 0,
                ticks: { precision: 0, stepSize: 1 }
              }
            },
            // Also add v2-compatible scale options in case the project uses Chart.js v2
            // (some builds in this repo include an older Chart.js). Chart.js v2 expects
            // options.scales.yAxes as an array.
            // We'll attach these under a separate property and merge at runtime when creating the chart.
            _v2_scales: {
              yAxes: [{ ticks: { beginAtZero: true, min: 0, stepSize: 1 } }]
            },
            elements: { bar: { maxBarThickness: 60 } }
          }
        };
        try { if (window.ramoChartInstance) { window.ramoChartInstance.destroy(); } } catch(e) {}
        // If Chart.js is v2, it won't understand scales.y. Detect and adapt by copying
        // our _v2_scales into cfg.options.scales before creating the chart.
        try {
          // Chart global version may be in Chart.version (v3) or undefined (v2);
          // detect v2 by checking if Chart.defaults and Chart.defaults.global exist
          const isV2 = !!(Chart && Chart.defaults && Chart.defaults.global);
          if (isV2) {
            cfg.options.scales = cfg.options.scales || {};
            // copy v2 axes
            cfg.options.scales.yAxes = cfg.options._v2_scales.yAxes;
          }
        } catch(e) { /* ignore detection errors */ }
        window.ramoChartInstance = new Chart(ctxEl, cfg);
      } else {
        $('#ramoChart').parent().append('<div class="text-center text-muted small mt-2">No hay datos de pólizas por ramo.</div>');
      }
    }).fail(function(){
      $('#ramoChart').parent().append('<div class="text-center text-danger small mt-2">Error cargando pólizas por ramo.</div>');
    });

    // R: Tendencia de Siniestros (últimos meses) - área
    $.getJSON('controlador/controladorReporte.php', { accion: 'r_siniestros', months: 12 }, function(res){
      if (res.success && res.data && res.data.labels && res.data.data) {
        const ctx = document.getElementById('siniestrosTrend');
        const g = ctx.getContext('2d').createLinearGradient(0,0,0,200);
        g.addColorStop(0, 'rgba(211,184,255,0.22)');
        g.addColorStop(1, 'rgba(45,26,255,0.04)');
        new Chart(ctx, {
          type: 'line',
          data: { labels: res.data.labels, datasets: [{ label: 'Siniestros', data: res.data.data, fill: true, backgroundColor: g, borderColor: palette[3], tension: 0.3, pointBackgroundColor: palette[2] }] },
          options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
      } else {
        $('#siniestrosTrend').parent().append('<div class="text-center text-muted small mt-2">No hay datos de siniestros para mostrar.</div>');
      }
    }).fail(function(){
      $('#siniestrosTrend').parent().append('<div class="text-center text-danger small mt-2">Error cargando tendencia de siniestros.</div>');
    });

    // KPIs superiores
    $.getJSON('controlador/controladorReporte.php', { accion: 'kpis' }, function(res){
      if (res.success && res.data) {
        const totalPolizas = Number(res.data.polizas_total || 0);
        const pendientes = Number(res.data.polizas_pendientes || 0);
        const pct = Number(res.data.polizas_pendientes_pct || 0);
        const pctDisplay = Number.isFinite(pct) ? (pct >= 10 ? pct.toFixed(1) : pct.toFixed(2)) : '0.00';
        const totalFormatted = Number.isFinite(totalPolizas) ? totalPolizas.toLocaleString('es-VE') : '0';
        const pendientesFormatted = Number.isFinite(pendientes) ? pendientes.toLocaleString('es-VE') : '0';
        const titleText = totalPolizas > 0
          ? pendientesFormatted + ' de ' + totalFormatted + ' pólizas con cuotas pendientes.'
          : 'Sin pólizas registradas.';
        $('#k_pendientes_pct').text(pctDisplay + '%').attr('title', titleText);
        $('#k_primas_pagadas').text(Number(res.data.primas_pagadas || 0).toLocaleString('es-VE'));
        $('#k_agentes_activos').text(res.data.agentes_activos || 0);
        $('#k_siniestros_abiertos').text(res.data.siniestros_abiertos || 0);
      } else {
        // dejar guiones si no hay datos
      }
    }).fail(function(){
      // no mostrar error visible para KPIs, solo logging opcional
      console.error('Error cargando KPIs');
    });

    // No renderizamos gráficos demo si no hay datos reales; los gráficos R1/R4/R8 se dibujan arriba cuando corresponda.
  });
  </script>
  EOT;
  require_once __DIR__ . "/parte_inferior.php";
  ?>

  
