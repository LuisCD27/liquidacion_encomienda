<?php
require_once __DIR__.'/../config/conexion.php';

if ($_GET['action'] === 'info') {
  $oficinaId = (int)($_GET['id'] ?? 0);

  if ($oficinaId <= 0) {
    echo json_encode(['error' => 'ID de oficina inválido']);
    exit;
  }

  // Obtener información de la oficina
  $stmt = $pdo->prepare("SELECT * FROM oficinas WHERE id = ?");
  $stmt->execute([$oficinaId]);
  $oficina = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$oficina) {
    echo json_encode(['error' => 'Oficina no encontrada']);
    exit;
  }

  // Obtener número de correlativo de la oficina
  $stmt2 = $pdo->prepare("SELECT serie, ultimo_numero FROM numeraciones WHERE oficina_id = ?");
  $stmt2->execute([$oficinaId]);
  $numeracion = $stmt2->fetch(PDO::FETCH_ASSOC);

  // Si no existe numeración, creamos la numeración con valor inicial
  if (!$numeracion) {
    // Insertamos la numeración con el valor 1
    $pdo->prepare("INSERT INTO numeraciones (oficina_id, serie, ultimo_numero) VALUES (?, ?, 0)")
      ->execute([$oficinaId, $oficina['serie']]);
    $numero = 1; // Primer número
  } else {
    $numero = $numeracion['ultimo_numero'] + 1; // El siguiente número
  }

  // Responder con los datos de la oficina y la siguiente numeración
  echo json_encode([
    'direccion'   => $oficina['direccion'],
    'telefono1'   => $oficina['telefono1'],
    'telefono2'   => $oficina['telefono2'],
    'serie'       => $oficina['serie'],
    'numero'      => $numero
  ]);
  exit;
}
