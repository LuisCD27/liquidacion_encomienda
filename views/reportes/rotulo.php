<?php
require_once __DIR__.'/qrlib.php';
require_once __DIR__.'/../../config/conexion.php';
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare("SELECT e.*, emp.razon_social, emp.ruc, emp.logo FROM encomiendas e JOIN empresas emp ON emp.id=e.empresa_id WHERE e.id=?");
$stmt->execute([$id]);
$d=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$d){ die('No encontrado'); }

$qrfolder = __DIR__.'/../../output/qrcodes/';
if(!file_exists($qrfolder)){ mkdir($qrfolder,0777,true); }
$qrfile = $qrfolder.'qr_'.$d['id'].'.png';
$qrtext = "Empresa: {$d['razon_social']}\nRemitente: {$d['remitente']}\nDestinatario: {$d['consignado']}\nOrigen: {$d['origen']}\nDestino: {$d['destino']}\nL.E.: {$d['le_serie']}-{$d['le_numero']}\nFecha: {$d['fecha']}\nCelular: {$d['cel']}";
qr_gen($qrtext,$qrfile);
?>
<!doctype html>
<html lang="es"><head>
<meta charset="utf-8">
<title>Rótulo Encomienda</title>
<link rel="stylesheet" href="../../assets/css/rotulo.css">
</head><body>
<div class="rotulo">
  <div class="header">
    <div>
      <div class="title"><?=htmlspecialchars($d['razon_social'])?></div>
      <div>R.U.C. Nº <?=htmlspecialchars($d['ruc'])?></div>
      <div class="subtext">¡Un mundo servicios a tu servicio!!</div>
    </div>
    <img src="../../assets/img/logos/<?=htmlspecialchars($d['logo'])?>" alt="Logo">
  </div>
  <div class="print-area">
    <div class="section row">
      <div><strong>ORIGEN:</strong> <?=htmlspecialchars($d['origen'])?></div>
      <div><strong>DESTINO:</strong> <?=htmlspecialchars($d['destino'])?></div>
    </div>
    <div class="line"></div>
    <div class="section"><strong>DESTINATARIO:</strong> <?=htmlspecialchars($d['consignado'])?></div>
    <div class="section row">
      <div><strong>DNI:</strong> <?=htmlspecialchars($d['ruc_dni'])?></div>
      <div><strong>CEL:</strong> <?=htmlspecialchars($d['cel'])?></div>
    </div>
    <div class="section">
      <span class="checkbox"></span> ENTREGA A DOMICILIO
    </div>
    <div class="line"></div>
    <div class="section center"><strong>N° GUÍAS</strong></div>
    <div class="large"><?=htmlspecialchars($d['nro_guias'] ?: '-')?></div>
    <div class="qr"><img src="../../output/qrcodes/<?=basename($qrfile)?>" width="60" height="60"></div>

    <div class="section center"><strong>N° DE L. ENCOMIENDA</strong></div>
    <div class="large"><?=htmlspecialchars($d['le_serie'].'-'.$d['le_numero'])?></div>
    <div class="qr"><img src="../../output/qrcodes/<?=basename($qrfile)?>" width="130" height="130"></div>
  </div>
</div>
</body></html>
