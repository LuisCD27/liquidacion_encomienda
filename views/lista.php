<?php
require_once __DIR__ . '/../config/conexion.php';

/* ====== FILTROS ====== */
$q   = isset($_GET['q'])   ? trim($_GET['q'])   : '';
$est = isset($_GET['est']) ? trim($_GET['est']) : '';
$d1  = isset($_GET['d1'])  ? trim($_GET['d1'])  : '';
$d2  = isset($_GET['d2'])  ? trim($_GET['d2'])  : '';

$q = preg_replace("/\r|\n/", "", $q);

/* ====== SELECT BASE ====== */
$sqlBase = " FROM encomiendas e
              JOIN oficinas  o   ON o.id = e.oficina_id
              JOIN empresas  emp ON emp.id = e.empresa_id
             WHERE 1=1";

$params = [];

/* Filtros de fechas */
if ($d1 !== '' && $d2 !== '') {
  $sqlBase .= " AND e.fecha >= ? AND e.fecha < ? ";
  $params[] = $d1 . " 00:00:00";
  $params[] = date('Y-m-d', strtotime($d2 . ' +1 day')) . " 00:00:00";
} elseif ($d1 !== '') {
  $sqlBase .= " AND e.fecha >= ? ";
  $params[] = $d1 . " 00:00:00";
} elseif ($d2 !== '') {
  $sqlBase .= " AND e.fecha < ? ";
  $params[] = date('Y-m-d', strtotime($d2 . ' +1 day')) . " 00:00:00";
}

/* Estado */
$allowed = ['pendiente','entregado','cancelado'];
if ($est !== '' && in_array($est, $allowed, true)) {
  $sqlBase .= " AND e.estado = ?";
  $params[] = $est;
}

/* Búsqueda */
if ($q !== '') {
  $sqlBase .= " AND ( emp.razon_social LIKE ? 
                      OR o.nombre       LIKE ? 
                      OR e.remitente    LIKE ? 
                      OR e.consignado   LIKE ? 
                      OR e.origen       LIKE ? 
                      OR e.destino      LIKE ? 
                      OR e.vendedor     LIKE ?
                      OR CONCAT(e.le_serie,'-',e.le_numero) LIKE ? )";
  $like = "%$q%";
  array_push($params, $like, $like, $like, $like, $like, $like, $like, $like);
}

/* ====== PAGINACIÓN ====== */
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

/* Contar total */
$stmtCount = $pdo->prepare("SELECT COUNT(*) $sqlBase");
$stmtCount->execute($params);
$totalRows = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

/* ====== CONSULTA FINAL ====== */
$sql = "SELECT e.id,
               CONCAT(e.le_serie,'-',e.le_numero) AS le,
               e.fecha, e.remitente, e.consignado, e.origen, e.destino,
               e.cantidad, e.contenido, e.nro_guias, e.peso_kg,
               e.vendedor,
               e.estado, e.emitido_por, e.emitido_en, e.finalizado_por, e.finalizado_en,
               o.nombre AS oficina, emp.razon_social
        $sqlBase
        ORDER BY e.id DESC
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ====== HELPERS ====== */
function badgeClass($estado){
  return match($estado) {
    'pendiente' => 'badge badge-pendiente',
    'entregado' => 'badge badge-entregado',
    'cancelado' => 'badge badge-cancelado',
    default     => 'badge',
  };
}

function fmtDT($dt){ return $dt ? htmlspecialchars($dt) : ''; }

/* Flash message */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$flash = $_SESSION['flash'] ?? null;
if ($flash) unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Listado de Encomiendas</title>
  <link rel="stylesheet" href="../assets/css/estilo.css">
  <link rel="stylesheet" href="../assets/css/lista.css">
</head>
<body>
  <div class="container">
    <h2>Listado de Encomiendas</h2>

    <?php if ($flash): $cls = $flash['type'] ?? 'success'; ?>
      <div class="flash <?=$cls?>"><?= htmlspecialchars($flash['msg'] ?? '') ?></div>
    <?php endif; ?>

    <!-- Filtros -->
    <form method="get" autocomplete="off" class="filters">
      <label>Buscar
        <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="L.E., remitente, destino, vendedor...">
      </label>
      <label>Estado
        <select name="est">
          <option value="">Todos</option>
          <option value="pendiente" <?= $est==='pendiente'?'selected':'' ?>>Pendiente</option>
          <option value="entregado" <?= $est==='entregado'?'selected':'' ?>>Entregado</option>
          <option value="cancelado" <?= $est==='cancelado'?'selected':'' ?>>Cancelado</option>
        </select>
      </label>
      <label>Desde
        <input type="date" name="d1" value="<?=htmlspecialchars($d1)?>">
      </label>
      <label>Hasta
        <input type="date" name="d2" value="<?=htmlspecialchars($d2)?>">
      </label>
      <button class="btn" type="submit">Filtrar</button>
      <a class="btn" href="<?=basename(__FILE__)?>">Limpiar</a>
    </form>

    <!-- Acciones -->
    <div class="group-actions">
      <a class="btn" href="../index.php">Inicio</a>
      <a class="btn" href="formulario.php">Nuevo</a>
      <a class="btn" href="reportes/export_excel.php">Excel</a>
      <a class="btn" href="reportes/export_pdf.php">PDF</a>
      <a class="btn" href="#" onclick="window.print();return false;">Imprimir</a>
    </div>

    <!-- Tabla -->
    <table>
      <thead>
        <tr>
          <th>Empresa</th>
          <th>Oficina</th>
          <th>L.E.</th>
          <th>Fecha</th>
          <th>Remitente</th>
          <th>Consignado</th>
          <th>Origen</th>
          <th>Destino</th>
          <th>Cantidad</th>
          <th>Contenido</th>
          <th>Guías</th>
          <th>Peso (Kg)</th>
          <th>Vendedor</th>
          <th>Estado</th>
          <th>Emitido</th>
          <th>Finalizado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['razon_social'])?></td>
            <td><?=htmlspecialchars($r['oficina'])?></td>
            <td><?=htmlspecialchars($r['le'])?></td>
            <td><?=htmlspecialchars($r['fecha'])?></td>
            <td><?=htmlspecialchars($r['remitente'])?></td>
            <td><?=htmlspecialchars($r['consignado'])?></td>
            <td><?=htmlspecialchars($r['origen'])?></td>
            <td><?=htmlspecialchars($r['destino'])?></td>
            <td><?=htmlspecialchars($r['cantidad'])?></td>
            <td><?=htmlspecialchars($r['contenido'])?></td>
            <td><?=htmlspecialchars($r['nro_guias'])?></td>
            <td><?=htmlspecialchars($r['peso_kg'])?></td>
            <td><?=htmlspecialchars($r['vendedor'])?></td>
            <td><span class="<?=badgeClass($r['estado'])?>"><?=ucfirst(htmlspecialchars($r['estado']))?></span></td>
            <td><?=fmtDT($r['emitido_en'])?></td>
            <td><?=fmtDT($r['finalizado_en'])?></td>
            <td>
              <a class="btn" href="reportes/liquidacion.php?id=<?=$r['id']?>" target="_blank">Ver PDF</a>
              <a class="btn" href="reportes/rotulo.php?id=<?=$r['id']?>" target="_blank">Rótulo</a>
              <a class="btn pdf" href="reportes/liquidacion_pdf.php?id=<?=$r['id']?>&tipo=a5" target="_blank">📄 A5</a>
              <a class="btn ticket" href="reportes/liquidacion_pdf.php?id=<?=$r['id']?>&tipo=ticket" target="_blank">🧾 Ticket</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="17" style="text-align:center;color:#888">No hay resultados</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): 
          $active = $i === $page ? 'class="active"' : '';
          $qs = $_GET; $qs['page'] = $i;
          $url = basename(__FILE__) . '?' . http_build_query($qs);
        ?>
          <a href="<?=$url?>" class="page-link" <?=$active?>><?=$i?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.filters');
  const container = document.querySelector('.container');

  // Filtro AJAX
  form.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    fetch('<?=basename(__FILE__)?>?' + params.toString())
      .then(res => res.text())
      .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        document.querySelector('table').outerHTML = doc.querySelector('table').outerHTML;
        const oldPag = document.querySelector('.pagination');
        if (oldPag) oldPag.remove();
        const newPag = doc.querySelector('.pagination');
        if (newPag) container.insertAdjacentElement('beforeend', newPag);
      });
  });

  // Paginación AJAX
  document.addEventListener('click', e => {
    if (e.target.classList.contains('page-link')) {
      e.preventDefault();
      fetch(e.target.href)
        .then(res => res.text())
        .then(html => {
          const doc = new DOMParser().parseFromString(html, 'text/html');
          document.querySelector('table').outerHTML = doc.querySelector('table').outerHTML;
          const oldPag = document.querySelector('.pagination');
          if (oldPag) oldPag.remove();
          const newPag = doc.querySelector('.pagination');
          if (newPag) container.insertAdjacentElement('beforeend', newPag);
        });
    }
  });
});
</script>
</body>
</html>
