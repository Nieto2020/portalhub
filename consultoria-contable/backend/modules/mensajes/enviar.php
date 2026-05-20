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
        SELECT id_usuario 
        FROM usuarios 
        WHERE id_usuario = ?
    ");
    $validarUsuario->execute([$id_destinatario]);

    if (!$validarUsuario->fetch()) {
        sendResponse(404, "Destinatario no encontrado");
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
