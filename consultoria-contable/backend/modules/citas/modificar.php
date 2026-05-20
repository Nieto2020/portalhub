<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

$id_cita = $data['id_cita'] ?? null;
$nueva_fecha = $data['fecha_hora'] ?? null;

if (!$id_cita || !$nueva_fecha) {
    sendResponse(400, "Faltan campos requeridos");
}

try {
    $buscar = $conexion->prepare("
        SELECT id_cliente, id_asesor, estado 
        FROM citas 
        WHERE id_cita = ?
    ");
    $buscar->execute([$id_cita]);
    $cita = $buscar->fetch(PDO::FETCH_ASSOC);

    if (!$cita) {
        sendResponse(404, "Cita no encontrada");
    }

    $id_usuario = $_SESSION['id_usuario'];
    $rol = $_SESSION['rol'] ?? null;
    
    if (
        ($rol === ROL_CLIENTE && $cita['id_cliente'] != $id_usuario) ||
        ($rol === ROL_ASESOR && $cita['id_asesor'] != $id_usuario)
    ) {
        sendResponse(403, "No tiene permisos para modificar esta cita");
    }

    if ($cita['estado'] === 'Cancelada') {
        sendResponse(400, "No se puede modificar una cita cancelada");
    }

    $validar = $conexion->prepare("
        SELECT COUNT(*) 
        FROM citas 
        WHERE id_asesor = ? 
        AND fecha_hora = ?
        AND id_cita != ?
        AND estado != 'Cancelada'
    ");
    $validar->execute([$cita['id_asesor'], $nueva_fecha, $id_cita]);

    if ($validar->fetchColumn() > 0) {
        sendResponse(409, "El asesor ya tiene una cita en ese horario");
    }

    $sql = "UPDATE citas
            SET fecha_hora = :fecha_hora
            WHERE id_cita = :id_cita";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":fecha_hora", $nueva_fecha, PDO::PARAM_STR);
    $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
    $stmt->execute();

    sendResponse(200, "Cita modificada correctamente");

} catch (PDOException $e) {
    sendResponse(500, "Error al modificar cita", [
        "error" => $e->getMessage()
    ]);
}

?>
