<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
  <style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2933; margin: 24px; }
    h1 { font-size: 20px; margin-bottom: 6px; }
    p { margin: 4px 0; }
    .small { font-size: 11px; color: #6b7280; }
    .description { margin-top: 10px; color: #374151; }
    .chart-img { display: block; width: 100%; max-width: 720px; margin: 16px auto; border: 1px solid #d1d5db; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 18px; }
    th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
    th { background-color: #eef2ff; font-weight: bold; }
    .muted { color: #9ca3af; font-size: 11px; margin-top: 12px; text-align: center; }
    .alert { background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 8px 12px; border-radius: 4px; font-size: 11px; margin: 12px 0; }
  </style>
</head>
<body>
  <h1><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h1>
  <p class="small">Generado por: <strong><?php echo htmlspecialchars($generadoPor, ENT_QUOTES, 'UTF-8'); ?></strong> el <?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?></p>

  <?php if (!empty($descripcion)): ?>
    <p class="description"><?php echo htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>

  <?php $puedeMostrarGrafico = isset($gdDisponible) ? (bool)$gdDisponible : true; ?>
  <?php if (!$puedeMostrarGrafico): ?>
    <p class="alert">La extensión PHP GD (ext-gd) no está habilitada. Actívela para que la gráfica se incluya en el PDF.</p>
  <?php endif; ?>

  <?php if ($puedeMostrarGrafico && !empty($chartUrl)): ?>
    <img class="chart-img" src="<?php echo htmlspecialchars($chartUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Gráfica generada">
  <?php elseif (!$puedeMostrarGrafico): ?>
    <p class="muted">Sin la extensión GD la gráfica no puede renderizarse dentro del PDF.</p>
  <?php else: ?>
    <p class="muted">No se pudo generar la gráfica por falta de datos.</p>
  <?php endif; ?>

  <?php if (!empty($headers)): ?>
    <table>
      <thead>
        <tr>
          <?php foreach ($headers as $header): ?>
            <th><?php echo htmlspecialchars($header, ENT_QUOTES, 'UTF-8'); ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $row): ?>
            <tr>
              <?php foreach ($row as $cell): ?>
                <td><?php echo htmlspecialchars($cell, ENT_QUOTES, 'UTF-8'); ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="<?php echo count($headers); ?>" class="muted"><?php echo htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8'); ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="muted"><?php echo htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>
</body>
</html>
