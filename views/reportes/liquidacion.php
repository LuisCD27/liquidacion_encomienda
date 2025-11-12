<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/qrlib.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ====== Parámetros ======
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('ID inválido.');

// ====== Consulta SQL ======
$sql = "SELECT e.*, 
               emp.razon_social, emp.ruc, emp.logo,
               o.nombre AS oficina, o.direccion AS of_dir, o.telefono1, o.telefono2
        FROM encomiendas e
        JOIN empresas emp ON emp.id = e.empresa_id
        JOIN oficinas o   ON o.id = e.oficina_id
        WHERE e.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$d = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$d) die('Encomienda no encontrada.');

// ====== QR ======
$qrfolder = __DIR__ . '/../../output/qrcodes/';
if (!is_dir($qrfolder)) mkdir($qrfolder, 0777, true);
$qrfile = $qrfolder . 'qr_' . $d['id'] . '.png';
$qrtext = "HUNOS CARGO EXPRESS\nL.E.: {$d['le_serie']}-{$d['le_numero']}\nRemitente: {$d['remitente']}\nDestino: {$d['destino']}\nFecha: {$d['fecha']}";
qr_gen($qrtext, $qrfile);

// ====== Recursos ======
function path_to_data_uri($path) {
  if (!is_file($path)) return '';
  $mime = mime_content_type($path) ?: 'application/octet-stream';
  $data = base64_encode(file_get_contents($path));
  return "data:$mime;base64,$data";
}

$logoPath = __DIR__ . '/../../assets/img/logos/' . $d['logo'];
$logoData = path_to_data_uri($logoPath);
$qrData   = path_to_data_uri($qrfile);

// ====== CSS externo ======
$cssPath = __DIR__ . '/../../assets/css/liquidacion_a5.css';
$css = is_file($cssPath) ? file_get_contents($cssPath) : '';

// ===================================================
// =============== BLOQUE DE LIQUIDACIÓN ==============
// ===================================================
function bloque_liquidacion($d, $logoData, $qrData, $isCopy = false) {
?>
<div class="bloque <?= $isCopy ? 'copia' : 'original' ?>">
  <header>
    <div class="izq">
      <?php if ($logoData): ?>
        <img src="<?= $logoData ?>" class="logo" alt="Logo">
      <?php endif; ?>
      <h1>HUNOS <span>CARGO EXPRESS</span></h1>
      <p>OFICINA: <strong><?= htmlspecialchars($d['oficina']) ?></strong></p>
    </div>
    <div class="der">
      <p>R.U.C. Nº <strong><?= htmlspecialchars($d['ruc']) ?></strong></p>
      <p><strong><?= htmlspecialchars($d['le_serie']) ?> - <?= str_pad($d['le_numero'], 4, '0', STR_PAD_LEFT) ?></strong></p>
      <?php if ($qrData): ?>
        <img src="<?= $qrData ?>" class="qr" alt="QR">
      <?php endif; ?>
    </div>
  </header>

  <section class="info">
    <table>
      <tr>
        <th>FECHA:</th><td><?= date('d/m/Y', strtotime($d['fecha'])) ?> <?= htmlspecialchars($d['hora']) ?></td>
        <th>DNI:</th><td><?= htmlspecialchars($d['dni_responsable']) ?></td>
        <th>CELULAR:</th><td><?= htmlspecialchars($d['cel']) ?></td>
      </tr>
      <tr>
        <th>REMITENTE:</th><td colspan="2"><?= htmlspecialchars($d['remitente']) ?></td>
        <th>CONSIGNADO:</th><td colspan="2"><?= htmlspecialchars($d['consignado']) ?></td>
      </tr>
      <tr>
        <th>ORIGEN:</th><td><?= htmlspecialchars($d['origen']) ?></td>
        <th>DESTINO:</th><td><?= htmlspecialchars($d['destino']) ?></td>
        <th>DIRECCIÓN:</th><td><?= htmlspecialchars($d['direccion']) ?></td>
      </tr>
    </table>
  </section>

  <section class="detalle">
    <table>
      <thead>
        <tr>
          <th>CANT.</th>
          <th>UNIDAD</th>
          <th>CONTENIDO</th>
          <th>GRR</th>
          <th>PESO KG.</th>
          <th>PRECIO UNIT.</th>
          <th>PRECIO TOTAL</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= htmlspecialchars($d['cantidad']) ?></td>
          <td><?= htmlspecialchars($d['unidad']) ?></td>
          <td><?= htmlspecialchars($d['contenido']) ?></td>
          <td><?= htmlspecialchars($d['nro_guias']) ?></td>
          <td><?= htmlspecialchars($d['peso_kg']) ?></td>
          <td>S/ <?= number_format($d['precio_unit'], 2) ?></td>
          <td>S/ <?= number_format($d['precio_total'], 2) ?></td>
        </tr>
      </tbody>
    </table>
  </section>

  <footer>
    <div class="firma">___________________________<br>Recibí conforme</div>
    <?php if ($isCopy): ?>
      <div class="marca-copia">COPIA</div>
    <?php endif; ?>
  </footer>
</div>
<?php } ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Liquidación de Encomienda</title>
  <style><?= $css ?></style>
</head>
<body>
  <div class="a5-landscape">
    <?php bloque_liquidacion($d, $logoData, $qrData, false); ?>
    <hr class="division">
    <?php bloque_liquidacion($d, $logoData, $qrData, true); ?>
  </div>
</body>
</html>
<?php
$html = ob_get_clean();

// ====== DOMPDF ======
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'landscape');
$dompdf->render();
$dompdf->stream("liquidacion_{$d['le_serie']}-{$d['le_numero']}.pdf", ['Attachment' => false]);
exit;

