<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
  <style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #222; }
    h1 { font-size: 20px; margin-bottom: 8px; }
    h2 { font-size: 16px; margin-top: 24px; margin-bottom: 6px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background-color: #f2f4f7; font-weight: bold; }
    .small { font-size: 11px; color: #666; }
    .highlight { font-weight: bold; color: #2d1aff; }
    .section { margin-top: 18px; }
    .chart-img { display: block; max-width: 720px; width: 100%; margin: 8px auto 14px; border: 1px solid #e5e7eb; border-radius: 4px; }
    .muted { color: #6b7280; font-size: 11px; margin: 6px 0 0; }
    .alert { background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 8px 12px; border-radius: 4px; font-size: 11px; margin-bottom: 12px; }
  </style>
</head>
<body>
  <h1><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h1>
  <p class="small">Generado por: <span class="highlight"><?php echo htmlspecialchars($generadoPor, ENT_QUOTES, 'UTF-8'); ?></span> el <?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?></p>

  <?php $puedeMostrarGraficos = isset($gdDisponible) ? (bool)$gdDisponible : true; ?>
  <?php if (!$puedeMostrarGraficos): ?>
    <p class="alert">Para incluir las gráficas en el PDF habilite la extensión PHP GD (ext-gd) y vuelva a generar el reporte.</p>
  <?php endif; ?>

  <div class="section">
    <h2>Indicadores principales</h2>
    <table>
      <tbody>
        <tr><th>Pólizas registradas</th><td><?php echo number_format((float)($datos['kpis']['polizas_total'] ?? 0), 0, ',', '.'); ?></td></tr>
        <tr><th>Pólizas con pagos pendientes</th><td><?php echo number_format((float)($datos['kpis']['polizas_pendientes'] ?? 0), 0, ',', '.'); ?></td></tr>
        <tr><th>% con pagos pendientes</th><td><?php echo number_format((float)($datos['kpis']['polizas_pendientes_pct'] ?? 0), 2, ',', '.'); ?>%</td></tr>
        <tr><th>Primas pagadas</th><td><?php echo number_format((float)($datos['kpis']['primas_pagadas'] ?? 0), 2, ',', '.'); ?></td></tr>
        <tr><th>Agentes activos</th><td><?php echo number_format((float)($datos['kpis']['agentes_activos'] ?? 0), 0, ',', '.'); ?></td></tr>
        <tr><th>Siniestros abiertos</th><td><?php echo number_format((float)($datos['kpis']['siniestros_abiertos'] ?? 0), 0, ',', '.'); ?></td></tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Pólizas por ramo</h2>
    <?php if ($puedeMostrarGraficos && !empty($urls['polizasPorRamo'])): ?>
      <img class="chart-img" src="<?php echo htmlspecialchars($urls['polizasPorRamo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Gráfico de pólizas por ramo">
    <?php elseif (!$puedeMostrarGraficos): ?>
      <p class="muted">Habilite la extensión GD para visualizar esta gráfica en el PDF.</p>
    <?php else: ?>
      <p class="muted">No se pudo generar la gráfica por falta de datos.</p>
    <?php endif; ?>
    <table>
      <thead><tr><th>Ramo</th><th>Total</th></tr></thead>
      <tbody>
        <?php if (!empty($datos['polizasPorRamo'])): ?>
          <?php foreach ($datos['polizasPorRamo'] as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['categoria'] ?? 'Sin categoría', ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo number_format((float)($row['total'] ?? 0), 0, ',', '.'); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="2">Sin datos disponibles.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Pólizas próximas a vencer (30 días)</h2>
    <table>
      <thead>
        <tr><th>#</th><th>Póliza</th><th>Cliente</th><th>Agente</th><th>Vence</th><th>Prima</th></tr>
      </thead>
      <tbody>
        <?php if (!empty($datos['polizasPorVencer'])): ?>
          <?php foreach ($datos['polizasPorVencer'] as $idx => $row): ?>
            <tr>
              <td><?php echo $idx + 1; ?></td>
              <td><?php echo htmlspecialchars($row['numero_poliza'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars(trim(($row['nombre_cliente'] ?? '') . ' ' . ($row['apellido_cliente'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars(trim(($row['nombre_agente'] ?? '') . ' ' . ($row['apellido_agente'] ?? '')) ?: ($row['cedula_agente'] ?? 'N/D'), ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($row['fecha_fin'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo number_format((float)($row['monto_prima_total'] ?? ($row['monto_prima'] ?? 0)), 2, ',', '.'); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6">Sin pólizas por vencer en los próximos 30 días.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Distribución por estado</h2>
    <?php if ($puedeMostrarGraficos && !empty($urls['polizasPorEstado'])): ?>
      <img class="chart-img" src="<?php echo htmlspecialchars($urls['polizasPorEstado'], ENT_QUOTES, 'UTF-8'); ?>" alt="Gráfico de pólizas por estado">
    <?php elseif (!$puedeMostrarGraficos): ?>
      <p class="muted">Habilite la extensión GD para visualizar esta gráfica en el PDF.</p>
    <?php else: ?>
      <p class="muted">No se pudo generar la gráfica por falta de datos.</p>
    <?php endif; ?>
    <table>
      <thead><tr><th>Estado</th><th>Total</th></tr></thead>
      <tbody>
        <?php if (!empty($datos['polizasPorEstado'])): ?>
          <?php foreach ($datos['polizasPorEstado'] as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['estado'] ?? 'Sin estado', ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo number_format((float)($row['total'] ?? 0), 0, ',', '.'); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="2">Sin información disponible.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Ranking de productividad (12 meses)</h2>
    <?php if ($puedeMostrarGraficos && !empty($urls['rankingProductividad'])): ?>
      <img class="chart-img" src="<?php echo htmlspecialchars($urls['rankingProductividad'], ENT_QUOTES, 'UTF-8'); ?>" alt="Gráfico de ranking de productividad">
    <?php elseif (!$puedeMostrarGraficos): ?>
      <p class="muted">Habilite la extensión GD para visualizar esta gráfica en el PDF.</p>
    <?php else: ?>
      <p class="muted">No se pudo generar la gráfica por falta de datos.</p>
    <?php endif; ?>
    <table>
      <thead><tr><th>Posición</th><th>Agente</th><th>Pólizas</th><th>Primas</th></tr></thead>
      <tbody>
        <?php if (!empty($datos['rankingProductividad'])): ?>
          <?php foreach ($datos['rankingProductividad'] as $idx => $row): ?>
            <tr>
              <td><?php echo $idx + 1; ?></td>
              <td><?php echo htmlspecialchars(trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? '')) ?: ($row['cedula_agente'] ?? 'N/D'), ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo number_format((float)($row['num_polizas'] ?? 0), 0, ',', '.'); ?></td>
              <td><?php echo number_format((float)($row['monto_primas'] ?? 0), 2, ',', '.'); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">Sin datos de productividad disponibles.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Tendencia de siniestros</h2>
    <?php if ($puedeMostrarGraficos && !empty($urls['tendenciaSiniestros'])): ?>
      <img class="chart-img" src="<?php echo htmlspecialchars($urls['tendenciaSiniestros'], ENT_QUOTES, 'UTF-8'); ?>" alt="Gráfico de tendencia de siniestros">
    <?php elseif (!$puedeMostrarGraficos): ?>
      <p class="muted">Habilite la extensión GD para visualizar esta gráfica en el PDF.</p>
    <?php else: ?>
      <p class="muted">No se pudo generar la gráfica por falta de datos.</p>
    <?php endif; ?>
    <table>
      <thead><tr><th>Mes</th><th>Total</th></tr></thead>
      <tbody>
        <?php if (!empty($datos['tendenciaSiniestros']['labels'])): ?>
          <?php foreach ($datos['tendenciaSiniestros']['labels'] as $idx => $label): ?>
            <tr>
              <td><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo number_format((float)($datos['tendenciaSiniestros']['data'][$idx] ?? 0), 0, ',', '.'); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="2">Sin registros de siniestros en el periodo solicitado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
