<?php
require_once __DIR__ . '/../../config/conexion.php';

// Consulta con el campo vendedor incluido
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

ob_start();
?>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Listado de Encomiendas</title>
<style>
  /* ===== Configuración para impresión horizontal ===== */
  @page {
    size: A4 landscape;
    margin: 10mm;
  }

  @media print {
    @page { size: A4 landscape; }
  }

  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9.5px;
    color: #222;
    margin: 10px;
  }

  h3 {
    text-align: center;
    color: #004b5f;
    margin-bottom: 6px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    border: 0.5px solid #888;
    table-layout: fixed;
    word-wrap: break-word;
  }

  th, td {
    border: 0.5px solid #999;
    padding: 3px 4px;
    text-align: left;
  }

  th {
    background: #e6f4f7;
    color: #003b4a;
    font-weight: bold;
  }

  tr:nth-child(even) {
    background: #f9f9f9;
  }

  /* Anchos equilibrados */
  th:nth-child(1) { width: 8%; }   /* Empresa */
  th:nth-child(2) { width: 7%; }   /* Oficina */
  th:nth-child(3) { width: 5%; }   /* L.E. */
  th:nth-child(4) { width: 5%; }   /* Fecha */
  th:nth-child(5) { width: 8%; }   /* Remitente */
  th:nth-child(6) { width: 8%; }   /* Consignado */
  th:nth-child(7) { width: 5%; }   /* Origen */
  th:nth-child(8) { width: 5%; }   /* Destino */
  th:nth-child(9) { width: 4%; }   /* Cantidad */
  th:nth-child(10) { width: 9%; }  /* Contenido */
  th:nth-child(11) { width: 5%; }  /* Guías */
  th:nth-child(12) { width: 4%; }  /* Peso */
  th:nth-child(13) { width: 8%; }  /* Vendedor */
  th:nth-child(14) { width: 5%; }  /* Estado */
  th:nth-child(15) { width: 6%; }  /* Emitido */
  th:nth-child(16) { width: 6%; }  /* Finalizado */
  th:nth-child(17) { width: 4%; }  /* Total */

  td { vertical-align: top; }
  .right { text-align: right; }
</style>
</head>
<body>
  <h3>Listado de Encomiendas</h3>
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
        <th>Cant.</th>
        <th>Contenido</th>
        <th>Guías</th>
        <th>Peso</th>
        <th>Vendedor</th>
        <th>Estado</th>
        <th>Emitido</th>
        <th>Finalizado</th>
        <th>Total S/</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
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
        <td><?=ucfirst(htmlspecialchars($r['estado']))?></td>
        <td><?=htmlspecialchars($r['emitido_en'])?></td>
        <td><?=htmlspecialchars($r['finalizado_en'])?></td>
        <td class="right"><?=number_format($r['total_s'], 2)?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
<?php
$html = ob_get_clean();

// Si Dompdf está instalado => PDF en horizontal
if (class_exists('Dompdf\Dompdf')) {
  $dompdf = new Dompdf\Dompdf();
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'landscape'); // <- orientación horizontal
  $dompdf->render();
  $dompdf->stream('encomiendas.pdf', ['Attachment' => true]);
  exit;
}

// Si no hay Dompdf, mostrar HTML imprimible horizontal
echo $html;
