

<?php
// controllers/EncomiendaController.php

// ======= Desarrollo: mostrar errores (comenta en producción) =======
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../config/conexion.php';

// Ajusta a tu zona si quieres registrar horas locales siempre igual
date_default_timezone_set('America/Lima');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$userId   = $_SESSION['user_id']   ?? null;
$userName = $_SESSION['user_name'] ?? 'Sistema';

try {

    /* =========================================================
     * 1) OBTENER SERIE Y SIGUIENTE NÚMERO POR OFICINA
     * ========================================================= */
    if ($action === 'siguiente') {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $oficinaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            if ($oficinaId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de oficina inválido']);
                exit;
            }

            // Buscar la serie y el último número en numeraciones
            $stmt = $pdo->prepare("SELECT serie, ultimo_numero FROM numeraciones WHERE oficina_id = ?");
            $stmt->execute([$oficinaId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Si existe, devolver el siguiente número
                echo json_encode([
                    'serie'  => $row['serie'],
                    'numero' => (int)$row['ultimo_numero'] + 1
                ]);
            } else {
                // Si no existe registro para esta oficina, crear uno y devolver 1
                $serieStmt = $pdo->prepare("SELECT serie FROM oficinas WHERE id = ?");
                $serieStmt->execute([$oficinaId]);
                $serie = $serieStmt->fetchColumn() ?: '';

                // Crear registro inicial en numeraciones
                $pdo->prepare("INSERT INTO numeraciones (oficina_id, serie, ultimo_numero) VALUES (?, ?, 0)")
                    ->execute([$oficinaId, $serie]);

                echo json_encode([
                    'serie'  => $serie,
                    'numero' => 1
                ]);
            }

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
        }
        exit;
    }

    /* =========================================================
     * 2) GUARDAR ENCOMIENDA
     * ========================================================= */
    if ($action === 'guardar') {

        // --- Datos principales ---
        $empresa_id  = (int)($_POST['empresa_id'] ?? 0);
        $oficina_id  = (int)($_POST['oficina_id'] ?? 0);
        $remitente   = trim($_POST['remitente'] ?? '');
        $consignado  = trim($_POST['consignado'] ?? '');
        $ruc_dni     = trim($_POST['ruc_dni'] ?? '');
        $direccion   = trim($_POST['direccion'] ?? '');
        $cel         = trim($_POST['cel'] ?? '');
        $origen      = trim($_POST['origen'] ?? '');
        $destino     = trim($_POST['destino'] ?? '');
        $tipo_pago   = trim($_POST['tipo_pago'] ?? '');
        $tipo_serv   = trim($_POST['tipo_servicio'] ?? '');
        $contenido   = trim($_POST['contenido'] ?? '');
        $cantidad    = (int)($_POST['cantidad'] ?? 0);
        $unidad      = trim($_POST['unidad'] ?? '');
        $nro_guias   = trim($_POST['nro_guias'] ?? '');
        $peso_kg     = (float)($_POST['peso_kg'] ?? 0);
        $precio_unit = (float)($_POST['precio_unit'] ?? 0);
        $precio_total= (float)($_POST['precio_total'] ?? 0);
        $total_s     = (float)($_POST['total_s'] ?? 0);
        $vendedor_id = (int)($_POST['vendedor_id'] ?? 0);
        $responsable_id = (int)($_POST['responsable_id'] ?? 0);
        $recibi_conforme = trim($_POST['recibi_conforme'] ?? '');
        $dni_responsable = trim($_POST['dni_responsable'] ?? '');
        $t_servicio  = trim($_POST['t_servicio'] ?? '');

        if ($empresa_id <= 0 || $oficina_id <= 0 || $remitente === '' || $consignado === '') {
            throw new Exception('Faltan datos obligatorios (empresa, oficina, remitente o consignado).');
        }

        // --- Transacción segura ---
        $pdo->beginTransaction();

        // 1️⃣ Obtener o crear serie + correlativo
        $stmt = $pdo->prepare("SELECT serie, ultimo_numero FROM numeraciones WHERE oficina_id=? FOR UPDATE");
        $stmt->execute([$oficina_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $le_serie  = $row['serie'];
            $le_numero = $row['ultimo_numero'] + 1;
            $pdo->prepare("UPDATE numeraciones SET ultimo_numero=? WHERE oficina_id=?")
                ->execute([$le_numero, $oficina_id]);
        } else {
            // Si no existe, crear uno
            $le_serie = '001';
            $le_numero = 1;
            $pdo->prepare("INSERT INTO numeraciones (oficina_id, serie, ultimo_numero) VALUES (?, ?, ?)")
                ->execute([$oficina_id, $le_serie, $le_numero]);
        }

        // 2️⃣ Insertar encomienda
        $sql = "INSERT INTO encomiendas (
            empresa_id, oficina_id, vendedor_id, responsable_id,
            le_serie, le_numero, fecha, hora, remitente, consignado,
            ruc_dni, direccion, cel, origen, destino, tipo_pago,
            tipo_servicio, contenido, cantidad, unidad, nro_guias,
            peso_kg, precio_unit, precio_total, total_s,
            recibi_conforme, dni_responsable, t_servicio,
            estado, created_at
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, CURDATE(), CURTIME(), ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            'pendiente', NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([ 
            $empresa_id, $oficina_id, $vendedor_id, $responsable_id,
            $le_serie, $le_numero, $remitente, $consignado,
            $ruc_dni, $direccion, $cel, $origen, $destino, $tipo_pago,
            $tipo_serv, $contenido, $cantidad, $unidad, $nro_guias,
            $peso_kg, $precio_unit, $precio_total, $total_s,
            $recibi_conforme, $dni_responsable, $t_servicio
        ]);

        $id = $pdo->lastInsertId();
        $pdo->commit();

        // ✅ Redirige al PDF
        header('Location: ../views/reportes/liquidacion.php?id=' . $id);
        exit;
    }

    /* =========================================================
     * 3) CAMBIAR ESTADO
     * ========================================================= */
    if ($action === 'cambiar_estado') {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $estado = $_GET['estado'] ?? $_POST['estado'] ?? '';
        $permitidos = ['pendiente','entregado','cancelado'];

        if (!in_array($estado, $permitidos, true)) {
            throw new Exception('Estado no válido.');
        }

        $stmt = $pdo->prepare("UPDATE encomiendas SET estado=? WHERE id=?");
        $stmt->execute([$estado, $id]);

        $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Estado actualizado a $estado"];
        header('Location: ../views/lista.php');
        exit;
    }

    /* =========================================================
     * 4) SIN ACCIÓN → volver al listado
     * ========================================================= */
    header('Location: ../views/lista.php');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash'] = ['type'=>'error', 'msg'=>$e->getMessage()];
    header('Location: ../views/lista.php');
    exit;
}

