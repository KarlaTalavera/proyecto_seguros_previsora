<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Prueba reportes - Seguros La Previsora</title>
  <link rel="stylesheet" href="vendor/datatables/dataTables.bootstrap4.min.css">
  <style>body{font-family:Arial,Helvetica,sans-serif;margin:20px} pre{background:#f7f7f7;padding:10px;border:1px solid #e1e1e1;max-height:240px;overflow:auto} .card{border:1px solid #ddd;padding:12px;margin-bottom:12px;border-radius:6px}</style>
</head>
<body>
  <h2>Prueba de endpoints de reportes</h2>
  <p>Esta página hace llamadas a los endpoints R1/R4/R8 y muestra el JSON crudo y un gráfico simple (si hay datos).</p>

  <div class="card">
    <h4>R1 - Pólizas por vencer</h4>
    <div id="r1_area"><em>Cargando...</em></div>
    <pre id="r1_json"></pre>
  </div>

  <div class="card">
    <h4>R4 - Cartera pendiente</h4>
    <div id="r4_area"><em>Cargando...</em></div>
    <pre id="r4_json"></pre>
  </div>

  <div class="card">
    <h4>R8 - Ranking productividad</h4>
    <canvas id="r8_debug" style="max-width:700px; height:240px"></canvas>
    <pre id="r8_json"></pre>
  </div>

  <div class="card">
    <h4>SQL útil para marcar pagos como vencidos (ejecuta en phpMyAdmin)</h4>
    <pre>
-- Ajusta las fechas de los pagos para testing R4 (marcar vencidos)
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 10 DAY) WHERE id_pago_prima = 1000;
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 35 DAY) WHERE id_pago_prima = 1001;
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 65 DAY) WHERE id_pago_prima = 1002;
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 120 DAY) WHERE id_pago_prima = 1003;
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 200 DAY) WHERE id_pago_prima = 1004;
    </pre>
  </div>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/chart.js/Chart.min.js"></script>
  <script>
    function showJson(selector, obj){ document.querySelector(selector).textContent = JSON.stringify(obj, null, 2); }

    // R1
    $.getJSON('controlador/controladorReporte.php', { accion: 'r1', dias: 30 })
      .done(function(res){
        showJson('#r1_json', res);
        const area = $('#r1_area'); area.empty();
        if (res.success && res.data && res.data.length){
          const table = $('<table border="1" cellpadding="6"></table>');
          table.append('<tr><th>#</th><th>Póliza</th><th>Agente</th><th>Vence</th><th>Prima</th></tr>');
          res.data.forEach(function(r,i){ table.append('<tr><td>'+(i+1)+'</td><td>'+r.numero_poliza+'</td><td>'+(r.nombre_agente||r.cedula_agente)+' '+(r.apellido_agente||'')+'</td><td>'+r.fecha_fin+'</td><td>'+ (r.monto_prima||'') +'</td></tr>'); });
          area.append(table);
        } else {
          area.append('<div style="color:#666">No hay pólizas próximas a vencer (respuesta vacía)</div>');
        }
      }).fail(function(xhr){ $('#r1_area').text('Error al cargar R1: '+xhr.statusText); });

    // R4
    $.getJSON('controlador/controladorReporte.php', { accion: 'r4' })
      .done(function(res){
        showJson('#r4_json', res);
        const area = $('#r4_area'); area.empty();
        if (res.success && res.data && res.data.buckets){
          const buckets = res.data.buckets;
          const total = res.data.total && (res.data.total.total_pending || res.data.total.total_pending === 0) ? res.data.total.total_pending : null;
          area.append('<div>Count pendientes: '+(res.data.total.count_pending||0) +'</div>');
          area.append('<div>Total (suma): '+(total===null? 'NULL' : total) +'</div>');
          area.append('<div>Buckets:</div>');
          const ul = $('<ul></ul>'); ul.append('<li>1-30d: '+(buckets.b_0_30||0)+'</li>'); ul.append('<li>31-60d: '+(buckets.b_31_60||0)+'</li>'); ul.append('<li>61-90d: '+(buckets.b_61_90||0)+'</li>'); ul.append('<li>>90d: '+(buckets.b_90p||0)+'</li>'); area.append(ul);
        } else {
          area.append('<div style="color:#666">No hay datos de cartera vencida.</div>');
        }
      }).fail(function(xhr){ $('#r4_area').text('Error al cargar R4: '+xhr.statusText); });

    // R8
    $.getJSON('controlador/controladorReporte.php', { accion: 'r8', months: 12, limit: 10 })
      .done(function(res){
        showJson('#r8_json', res);
        if (res.success && res.data && res.data.length){
          const labels = res.data.map(r => (r.nombre ? r.nombre + ' ' + r.apellido : r.cedula_agente));
          const values = res.data.map(r => parseInt(r.num_polizas||0));

          const ctx = document.getElementById('r8_debug').getContext('2d');
          new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Pólizas', data: values, backgroundColor:'#007bff' }] },
            options: { indexAxis: 'y', plugins:{legend:{display:false}} }
          });
        } else {
          $('#r8_debug').replaceWith('<div style="color:#666">No hay datos de productividad.</div>');
        }
      }).fail(function(xhr){ $('#r8_json').text('Error al cargar R8: '+xhr.statusText); });
  </script>
</body>
</html>