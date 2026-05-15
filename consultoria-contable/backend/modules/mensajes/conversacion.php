<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario_actual = $_SESSION['id_usuario'];
$id_otro_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;

if (!$id_otro_usuario) {
    sendResponse(400, "ID del otro usuario no proporcionado");
}

try {
    $stmt = $conexion->prepare("SELECT * FROM mensajes 
                                WHERE (id_remitente = ? AND id_destinatario = ?) 
                                OR (id_remitente = ? AND id_destinatario = ?) 
                                ORDER BY fecha_envio ASC");
    $stmt->execute([$id_usuario_actual, $id_otro_usuario, $id_otro_usuario, $id_usuario_actual]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Conversación obtenida", $mensajes);
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>