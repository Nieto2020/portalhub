<?php
/**
 * crear.php - Cliente califica a su asesor asignado (1-5)
 */
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkRole([ROL_CLIENTE]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);
$puntuacion = intval($data['puntuacion'] ?? 0);
$id_cliente = $_SESSION['id_usuario'];

if ($puntuacion < 1 || $puntuacion > 5) {
    sendResponse(400, "Puntuación debe ser entre 1 y 5");
}

try {
    // Verificar que el cliente tenga un asesor asignado activo
    $stmt = $conexion->prepare("
        SELECT id_asesor FROM cliente_asesor 
        WHERE id_cliente = ? AND estado = 'activo'
    ");
    $stmt->execute([$id_cliente]);
    $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$asignacion) {
        sendResponse(400, "No tienes un asesor asignado activo");
    }

    $id_asesor = $asignacion['id_asesor'];

    // Verificar si ya calificó hoy
    $stmt = $conexion->prepare("
        SELECT id_calificacion FROM calificaciones 
        WHERE id_cliente = ? AND DATE(fecha) = CURDATE()
    ");
    $stmt->execute([$id_cliente]);
    if ($stmt->fetch()) {
        sendResponse(400, "Ya has calificado a tu asesor hoy");
    }

    $stmt = $conexion->prepare("
        INSERT INTO calificaciones (id_cliente, id_asesor, puntuacion)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$id_cliente, $id_asesor, $puntuacion]);

    sendResponse(201, "Calificación registrada");
} catch (PDOException $e) {
    sendResponse(500, "Error: " . $e->getMessage());
}
