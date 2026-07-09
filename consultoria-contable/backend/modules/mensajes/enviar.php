<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['id_destinatario', 'contenido_texto'])) {
    sendResponse(400, "Faltan campos requeridos");
}

$id_remitente = $_SESSION['id_usuario'];
$id_destinatario = $data['id_destinatario'];
$contenido_texto = trim($data['contenido_texto']);
$tipo = $data['tipo'] ?? 'Chat interno';
$ruta_archivo_adjunto = $data['ruta_archivo_adjunto'] ?? null;

$tipos_validos = ['Chat interno', 'Ticket'];

if (!in_array($tipo, $tipos_validos)) {
    sendResponse(400, "Tipo de mensaje no válido");
}

if ($contenido_texto === '') {
    sendResponse(400, "El mensaje no puede estar vacío");
}

try {
    $validarUsuario = $conexion->prepare("
        SELECT id_usuario, id_rol 
        FROM usuarios 
        WHERE id_usuario = ?
    ");
    $validarUsuario->execute([$id_destinatario]);
    $destinatario = $validarUsuario->fetch(PDO::FETCH_ASSOC);

    if (!$destinatario) {
        sendResponse(404, "Destinatario no encontrado");
    }

    // ── Validar permisos según rol ──
    $id_rol_remitente = $_SESSION['id_rol'];
    $id_rol_destino = $destinatario['id_rol'];

    if ($id_rol_remitente != ROL_ADMIN) {
        if ($id_rol_remitente == ROL_ASESOR) {
            // Asesor → solo puede mensajear admin, otros asesores o sus clientes asignados
            if ($id_rol_destino == ROL_CLIENTE) {
                $checkAsign = $conexion->prepare("
                    SELECT id_asignacion FROM cliente_asesor 
                    WHERE id_cliente = ? AND id_asesor = ? AND estado = 'activo'
                ");
                $checkAsign->execute([$id_destinatario, $id_remitente]);
                if (!$checkAsign->fetch()) {
                    sendResponse(403, "No tienes permiso para enviar mensajes a este cliente (no está asignado a ti)");
                }
            }
        } elseif ($id_rol_remitente == ROL_CLIENTE) {
            // Cliente → solo puede mensajear a su asesor asignado
            if ($id_rol_destino != ROL_ASESOR) {
                sendResponse(403, "Solo puedes enviar mensajes a tu asesor asignado");
            }
            $checkAsign = $conexion->prepare("
                SELECT id_asignacion FROM cliente_asesor 
                WHERE id_cliente = ? AND id_asesor = ? AND estado = 'activo'
            ");
            $checkAsign->execute([$id_remitente, $id_destinatario]);
            if (!$checkAsign->fetch()) {
                sendResponse(403, "No tienes un asesor asignado activo");
            }
        }
    }

    $stmt = $conexion->prepare("
        INSERT INTO mensajes 
        (id_remitente, id_destinatario, tipo, contenido_texto, ruta_archivo_adjunto) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id_remitente,
        $id_destinatario,
        $tipo,
        $contenido_texto,
        $ruta_archivo_adjunto
    ]);

    sendResponse(201, "Mensaje enviado exitosamente", [
        "id_mensaje" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}

?>
