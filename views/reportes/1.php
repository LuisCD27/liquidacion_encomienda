<?php
require_once __DIR__.'/../../config/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Configurar opciones DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true); // permite cargar imágenes remotas o rutas relativas

$id = (int)($_GET['id'] ?? 0);

// Consulta
$stmt = $pdo->prepare("
  SELECT e.*, emp.razon_social, emp.ruc, emp.logo, 
         o.nombre AS oficina, o.direccion AS of_dir, o.telefono1, o.telefono2
  FROM encomiendas e
  JOIN empresas emp ON emp.id = e.empresa_id
  JOIN oficinas o ON o.id = e.oficina_id
  WHERE e.id = ?
");
$stmt->execute([$id]);
$d = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$d) {
    die('No encontrado');
}

// HTML del PDF
$html = '
<html>
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
<div class="printable">
  <div class="header-pdf">
    <div>
      <img src="../../assets/img/logos/'.htmlspecialchars($d['logo']).'" height="60"/>
    </div>
    <div class="titulo">LIQUIDACIÓN DE ENCOMIENDA</div>
    <div class="box">
      <b>R.U.C. Nº</b><br>'.htmlspecialchars($d['ruc']).'
    </div>
  </div>
  <div class="hr"></div>
  <div class="box">
    <b>OFICINA:</b> '.htmlspecialchars($d['oficina']).' &nbsp; 
    <b>DIRECCIÓN:</b> '.htmlspecialchars($d['of_dir']).'<br>
    <b>TELEFONO:</b> '.htmlspecialchars($d['telefono1']).' &nbsp; 
    <b>TELEFONO2:</b> '.htmlspecialchars($d['telefono2']).'
  </div>
  <div class="box">
    <b>L.E Nº</b> '.htmlspecialchars($d['le_serie']).' - '.htmlspecialchars($d['le_numero']).' &nbsp; 
    <b>FECHA:</b> '.htmlspecialchars($d['fecha']).' &nbsp; 
    <b>HORA:</b> '.htmlspecialchars($d['hora']).'
  </div>
  <div class="box">
    <b>REMITENTE:</b> '.htmlspecialchars($d['remitente']).' &nbsp; 
    <b>RUC/DNI:</b> '.htmlspecialchars($d['ruc_dni']).'<br>
    <b>CONSIGNADO:</b> '.htmlspecialchars($d['consignado']).' &nbsp; 
    <b>CEL:</b> '.htmlspecialchars($d['cel']).'
  </div>

  <table class="tabla">
    <thead>
      <tr>
        <th>CANT.</th><th>UNIDAD</th><th>CONTENIDO</th><th>GRR</th>
        <th>PESO KG.</th><th>PRECIO UNIT.</th><th>PRECIO T.</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>'.(int)$d['cantidad'].'</td>
        <td>UND</td>
        <td>'.nl2br(htmlspecialchars($d['contenido'])).'</td>
        <td></td>
        <td>'.htmlspecialchars($d['peso_kg']).'</td>
        <td>'.htmlspecialchars($d['precio_unit']).'</td>
        <td>'.htmlspecialchars($d['precio_total']).'</td>
      </tr>
    </tbody>
  </table>

  <div class="box" style="display:flex;justify-content:space-between">
    <div><b>RECIBÍ CONFORME</b></div>
    <div><b>PESO TOTAL KG.:</b> '.htmlspecialchars($d['peso_kg']).' &nbsp; 
         <b>TOTAL S/:</b> '.htmlspecialchars($d['total_s']).'</div>
  </div>

  <div class="box">
    <b>'.htmlspecialchars($d['razon_social']).'</b> &nbsp; 
    <b>T. SERVICIO:</b> '.htmlspecialchars($d['tipo_servicio']).'
  </div>
</div>
</body>
</html>
';

// Crear PDF
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('liquidacion_'.$d['le_serie'].'_'.$d['le_numero'].'.pdf', ['Attachment' => false]);
exit;
?>

