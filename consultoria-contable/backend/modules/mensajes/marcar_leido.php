<?php
/**
 * marcar_leido.php - Marca como leídos los mensajes de una conversación
 * POST: { id_otro_usuario }
 */
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);
$id_otro = $data['id_otro_usuario'] ?? null;
$id_usuario = $_SESSION['id_usuario'];

if (!$id_otro || !is_numeric($id_otro)) {
    sendResponse(400, "ID de usuario inválido");
}

try {
    $stmt = $conexion->prepare("
        UPDATE mensajes 
        SET leida = 1 
        WHERE id_destinatario = ? AND id_remitente = ? AND leida = 0
    ");
    $stmt->execute([$id_usuario, $id_otro]);

    sendResponse(200, "Mensajes marcados como leídos", [
        "afectados" => $stmt->rowCount()
    ]);
} catch (PDOException $e) {
    sendResponse(500, "Error: " . $e->getMessage());
}
