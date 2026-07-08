<?php
/**
 * no_leidos.php - Cuenta mensajes no leídos del usuario actual
 */
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario = $_SESSION['id_usuario'];

try {
    $stmt = $conexion->prepare("
        SELECT COUNT(*) AS total 
        FROM mensajes 
        WHERE id_destinatario = ? AND leida = 0
    ");
    $stmt->execute([$id_usuario]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    sendResponse(200, "OK", [
        "no_leidos" => (int)$row['total']
    ]);
} catch (PDOException $e) {
    sendResponse(500, "Error: " . $e->getMessage());
}
