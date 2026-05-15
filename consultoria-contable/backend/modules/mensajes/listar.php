<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario = $_SESSION['id_usuario'];

try {
    // Obtener los últimos mensajes de cada conversación
    $stmt = $conexion->prepare("SELECT m.*, u.correo as otro_usuario_correo 
                                FROM mensajes m
                                JOIN usuarios u ON (m.id_remitente = u.id_usuario OR m.id_destinatario = u.id_usuario)
                                WHERE (m.id_remitente = ? OR m.id_destinatario = ?)
                                AND u.id_usuario != ?
                                ORDER BY m.fecha_envio DESC");
    $stmt->execute([$id_usuario, $id_usuario, $id_usuario]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Mensajes obtenidos", $mensajes);
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>