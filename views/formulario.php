<?php
require_once __DIR__.'/../config/conexion.php';

/* === CARGAR DATOS BASE === */
$empresas   = $pdo->query("SELECT * FROM empresas WHERE activo=1")->fetchAll(PDO::FETCH_ASSOC);
$oficinas   = $pdo->query("SELECT * FROM oficinas")->fetchAll(PDO::FETCH_ASSOC);
$destinos   = $pdo->query("SELECT * FROM catalogos WHERE tipo='destino'")->fetchAll(PDO::FETCH_ASSOC);
$tpagos     = $pdo->query("SELECT * FROM catalogos WHERE tipo='tipo_pago'")->fetchAll(PDO::FETCH_ASSOC);
$tserv      = $pdo->query("SELECT * FROM catalogos WHERE tipo='tipo_servicio'")->fetchAll(PDO::FETCH_ASSOC);

/* === NUEVO: USUARIOS === */
$vendedores = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol='vendedor' AND activo=1")->fetchAll(PDO::FETCH_ASSOC);
$responsables = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol='responsable' AND activo=1")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Liquidación de Encomienda</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
<script src="../assets/js/funciones.js"></script>
</head>

<body>
<header class="topbar">
  <div class="brand">📦 Nueva Liquidación de Encomienda</div>
  <nav><a href="../index.php" class="btn">🏠 Inicio</a></nav>
</header>

<main class="container">
<form method="post" action="../controllers/EncomiendaController.php">
<input type="hidden" name="action" value="guardar">

<h2>Datos Generales</h2>
<div class="form-grid">

  <label>Empresa
    <select name="empresa_id" required>
      <?php foreach($empresas as $e): ?>
        <option value="<?=$e['id']?>"><?=$e['razon_social']?> (<?=$e['ruc']?>)</option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>Oficina
    <select name="oficina_id" id="oficina_id" required onchange="cargarNumeracion(this.value)">
      <option value="">Seleccione...</option>
      <?php foreach($oficinas as $o): ?>
        <option value="<?=$o['id']?>"><?=$o['nombre']?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>Serie
    <input type="text" name="le_serie" id="le_serie" readonly>
  </label>

  <label>Número
    <input type="text" name="le_numero" id="le_numero" readonly>
  </label>
</div>

<h2>Datos del Cliente</h2>
<div class="form-grid">
  <label>Remitente <input type="text" name="remitente" required></label>
  <label>Consignado <input type="text" name="consignado" required></label>
  <label>RUC/DNI <input type="text" name="ruc_dni"></label>
  <label>Celular <input type="text" name="cel"></label>
  <label>Dirección <input type="text" name="direccion"></label>
</div>

<h2>Ruta y Servicio</h2>
<div class="form-grid">
  <label>Origen <input type="text" name="origen"></label>
  <label>Destino
    <select name="destino" required>
      <?php foreach($destinos as $d): ?>
        <option value="<?=$d['valor']?>"><?=$d['valor']?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>Tipo de Pago
    <select name="tipo_pago">
      <?php foreach($tpagos as $p): ?>
        <option value="<?=$p['valor']?>"><?=$p['valor']?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>Tipo de Servicio
    <select name="tipo_servicio">
      <?php foreach($tserv as $s): ?>
        <option value="<?=$s['valor']?>"><?=$s['valor']?></option>
      <?php endforeach; ?>
    </select>
  </label>
</div>

<h2>Detalle de Envío</h2>
<div class="form-grid">
  <label>Vendedor
    <select name="vendedor_id" required>
      <option value="">Seleccione...</option>
      <?php foreach($vendedores as $v): ?>
        <option value="<?=$v['id']?>"><?=$v['nombre']?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>Responsable Agencia
    <select name="vendedor_id" required>
      <option value="">Seleccione...</option>
      <?php foreach($vendedores as $r): ?>
        <option value="<?=$r['id']?>"><?=$r['nombre']?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>Cantidad <input type="number" name="cantidad" value="1"></label>
  <label>Unidad <input type="text" name="unidad" placeholder="bulto, caja..."></label>
  <label>N° Guías <input type="text" name="nro_guias"></label>
  <label>Peso (kg) <input type="number" step="0.01" name="peso_kg" value="0"></label>
  <label>Precio Unitario <input type="number" step="0.01" name="precio_unit" value="0"></label>
  <label>Precio Total <input type="number" step="0.01" name="precio_total" value="0"></label>
  <label>Total S/ <input type="number" step="0.01" name="total_s" value="0"></label>

  <label style="grid-column:1/-1">Contenido / Descripción
    <textarea name="contenido" rows="3" placeholder="Descripción del contenido o notas..."></textarea>
  </label>
</div>

<h2>Confirmación</h2>
<div class="form-grid">
  <label>Recibí Conforme <input type="text" name="recibi_conforme"></label>
  <label>DNI Responsable <input type="text" name="dni_responsable"></label>
  <label>T. Servicio (pie de boleta) <input type="text" name="t_servicio"></label>
</div>

<p class="acciones">
  <button class="btn" type="submit">💾 Guardar y generar PDF</button>
  <a href="lista.php" class="btn secondary">📋 Ver Listado</a>
</p>

</form>
</main>

<script>
async function cargarNumeracion(oficinaId){
  if(!oficinaId) return;
  const resp = await fetch('../controllers/getNumeracion.php?oficina_id='+oficinaId);
  const data = await resp.json();
  document.getElementById('le_serie').value = data.serie;
  document.getElementById('le_numero').value = data.numero;
}
</script>

</body>
</html>
