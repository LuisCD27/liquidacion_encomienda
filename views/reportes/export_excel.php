<?php
require_once __DIR__ . '/../../config/conexion.php';

/* ====== Encabezados HTTP para Excel ====== */
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=encomiendas_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

/* ====== Consulta completa ====== */
$sql = "SELECT emp.razon_social, 
               o.nombre AS oficina,
               CONCAT(e.le_serie,'-',e.le_numero) AS le,
               e.fecha,
               e.remitente,
               e.consignado,
               e.origen,
               e.destino,
               e.cantidad,
               e.contenido,
               e.nro_guias,
               e.peso_kg,
               e.vendedor,
               e.estado,
               e.emitido_en,
               e.finalizado_en,
               e.total_s
        FROM encomiendas e
        JOIN oficinas o ON o.id = e.oficina_id
        JOIN empresas emp ON emp.id = e.empresa_id
        ORDER BY e.id DESC";

$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

/* ====== Generar tabla HTML (Excel la interpreta automáticamente) ====== */
echo "<table border='1' cellspacing='0' cellpadding='4'>";
echo "<thead style='background:#0078A3;color:#fff;font-weight:bold;'>
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
          <th>Total S/</th>
        </tr>
      </thead><tbody>";

foreach ($rows as $r) {
  echo "<tr>";
  echo "<td>" . htmlspecialchars($r['razon_social']) . "</td>";
  echo "<td>" . htmlspecialchars($r['oficina']) . "</td>";
  echo "<td>" . htmlspecialchars($r['le']) . "</td>";
  echo "<td>" . htmlspecialchars($r['fecha']) . "</td>";
  echo "<td>" . htmlspecialchars($r['remitente']) . "</td>";
  echo "<td>" . htmlspecialchars($r['consignado']) . "</td>";
  echo "<td>" . htmlspecialchars($r['origen']) . "</td>";
  echo "<td>" . htmlspecialchars($r['destino']) . "</td>";
  echo "<td>" . htmlspecialchars($r['cantidad']) . "</td>";
  echo "<td>" . htmlspecialchars($r['contenido']) . "</td>";
  echo "<td>" . htmlspecialchars($r['nro_guias']) . "</td>";
  echo "<td>" . htmlspecialchars($r['peso_kg']) . "</td>";
  echo "<td>" . htmlspecialchars($r['vendedor']) . "</td>";
  echo "<td>" . ucfirst(htmlspecialchars($r['estado'])) . "</td>";
  echo "<td>" . htmlspecialchars($r['emitido_en']) . "</td>";
  echo "<td>" . htmlspecialchars($r['finalizado_en']) . "</td>";
  echo "<td style='text-align:right;'>" . number_format($r['total_s'], 2) . "</td>";
  echo "</tr>";
}

echo "</tbody></table>";
