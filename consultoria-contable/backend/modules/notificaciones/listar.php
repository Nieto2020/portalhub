<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario = $_SESSION['id_usuario'];

try {
    $stmt = $conexion->prepare("SELECT * FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC");
    $stmt->execute([$id_usuario]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Notificaciones obtenidas", $notificaciones);
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>