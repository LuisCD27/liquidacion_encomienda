<?php
require __DIR__.'/config/conexion.php';

/* ====== FILTRO RÁPIDO (opcional) ====== */
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$q = preg_replace("/\r|\n/", "", $q);

$params = [];
$sql = "SELECT e.id, e.le_serie, e.le_numero, e.fecha, e.destino, e.remitente, e.consignado, 
               o.nombre AS oficina, emp.razon_social 
        FROM encomiendas e 
        JOIN oficinas o ON o.id = e.oficina_id
        JOIN empresas emp ON emp.id = e.empresa_id
        WHERE 1=1";

if ($q !== '') {
  $sql .= " AND (emp.razon_social LIKE ? 
                 OR o.nombre LIKE ? 
                 OR e.remitente LIKE ? 
                 OR e.consignado LIKE ? 
                 OR e.destino LIKE ? 
                 OR CONCAT(e.le_serie,'-',e.le_numero) LIKE ?)";
  $like = "%$q%";
  array_push($params, $like, $like, $like, $like, $like, $like);
}

$sql .= " ORDER BY e.id DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Hunos | Encomiendas</title>
  <link rel="stylesheet" href="assets/css/estilo.css">
  <link rel="stylesheet" href="assets/css/lista.css">
</head>
<body>
  <!-- Header unificado -->
  <header class="topbar">
    <div class="brand">HUNOS – Encomiendas</div>
    <nav class="topbar-nav">
      <a href="views/formulario.php" class="btn">Nueva L.E.</a>
      <a href="views/lista.php" class="btn secondary">Ver listado</a>
    </nav>
  </header>

  <main class="container">
    <h2>Últimas Liquidaciones</h2>

    <!-- Buscador rápido -->
    <form method="get" autocomplete="off" id="buscador-form" class="filters">
      <label>Buscar
        <input type="text" name="q" id="buscador" value="<?=htmlspecialchars($q)?>" placeholder="L.E., remitente, destino...">
      </label>
      <button class="btn" type="submit">🔍 Buscar</button>
      <a href="index.php" class="btn">Limpiar</a>
    </form>

    <!-- Tabla -->
    <div id="tabla-container">
      <table class="tabla">
        <thead>
          <tr>
            <th>Empresa</th>
            <th>Oficina</th>
            <th>L.E.</th>
            <th>Fecha</th>
            <th>Remitente</th>
            <th>Consignado</th>
            <th>Destino</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td data-label="Empresa"><?=htmlspecialchars($r['razon_social'])?></td>
              <td data-label="Oficina"><?=htmlspecialchars($r['oficina'])?></td>
              <td data-label="L.E."><?=htmlspecialchars($r['le_serie'].'-'.$r['le_numero'])?></td>
              <td data-label="Fecha"><?=htmlspecialchars($r['fecha'])?></td>
              <td data-label="Remitente"><?=htmlspecialchars($r['remitente'])?></td>
              <td data-label="Consignado"><?=htmlspecialchars($r['consignado'])?></td>
              <td data-label="Destino"><?=htmlspecialchars($r['destino'])?></td>
              <td data-label="Acciones">
                <a href="views/reportes/liquidacion.php?id=<?=$r['id']?>" class="btn">📄 PDF</a>
                <a href="views/reportes/rotulo.php?id=<?=$r['id']?>" class="btn secondary">🏷️ Rótulo</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?>
            <tr><td colspan="8" style="text-align:center;color:#888;">No hay resultados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- AJAX del buscador (igual que te dejé antes) -->
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('buscador-form');
    const input = document.getElementById('buscador');
    const tabla = document.getElementById('tabla-container');

    form.addEventListener('submit', e => {
      e.preventDefault();
      const params = new URLSearchParams(new FormData(form));
      fetch('index.php?' + params.toString())
        .then(res => res.text())
        .then(html => {
          const doc = new DOMParser().parseFromString(html, 'text/html');
          tabla.innerHTML = doc.querySelector('#tabla-container').innerHTML;
        });
    });

    let delay;
    input.addEventListener('keyup', () => {
      clearTimeout(delay);
      delay = setTimeout(() => {
        const params = new URLSearchParams(new FormData(form));
        fetch('index.php?' + params.toString())
          .then(res => res.text())
          .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            tabla.innerHTML = doc.querySelector('#tabla-container').innerHTML;
          });
      }, 400);
    });
  });
  </script>
</body>
</html>
