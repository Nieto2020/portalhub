<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

$id_cliente = $data['id_cliente'] ?? null;
$id_asesor = $data['id_asesor'] ?? null;
$fecha_hora = $data['fecha_hora'] ?? null;

if (!$id_cliente || !$id_asesor || !$fecha_hora) {
    sendResponse(400, "Faltan campos requeridos");
}

$estado = "Programada";

try {

    // Validar disponibilidad del asesor
    $validar = $conexion->prepare("
        SELECT COUNT(*) 
        FROM citas 
        WHERE id_asesor = ? 
        AND fecha_hora = ?
        AND estado != 'Cancelada'
    ");

    $validar->execute([$id_asesor, $fecha_hora]);

    if ($validar->fetchColumn() > 0) {
        sendResponse(409, "El asesor ya tiene una cita en ese horario");
    }

    $sql = "INSERT INTO citas
            (id_cliente, id_asesor, fecha_hora, estado)
            VALUES (:id_cliente, :id_asesor, :fecha_hora, :estado)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(":id_asesor", $id_asesor, PDO::PARAM_INT);
    $stmt->bindParam(":fecha_hora", $fecha_hora, PDO::PARAM_STR);
    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

    $stmt->execute();

    sendResponse(201, "Cita creada correctamente", [
        "id_cita" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {

    sendResponse(500, "Error al crear cita", [
        "error" => $e->getMessage()
    ]);

}

?>
