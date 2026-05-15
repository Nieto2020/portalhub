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
$contenido_texto = $data['contenido_texto'];
$tipo = isset($data['tipo']) ? $data['tipo'] : 'Chat interno';

try {
    $stmt = $conexion->prepare("INSERT INTO mensajes (id_remitente, id_destinatario, tipo, contenido_texto) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_remitente, $id_destinatario, $tipo, $contenido_texto]);

    sendResponse(201, "Mensaje enviado exitosamente");
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>