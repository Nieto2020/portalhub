<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

$id_servicio = $data['id_servicio'] ?? null;
$estado = $data['estado'] ?? null;

if (!$id_servicio || !$estado) {
    sendResponse(400, "Faltan campos requeridos");
}

$estados_validos = ["Pendiente", "En proceso", "Completado"];

if (!in_array($estado, $estados_validos)) {
    sendResponse(400, "Estado no válido");
}

try {

    $buscar = $conexion->prepare("
        SELECT id_servicio 
        FROM servicios_contables 
        WHERE id_servicio = ?
    ");

    $buscar->execute([$id_servicio]);

    if (!$buscar->fetch()) {
        sendResponse(404, "Servicio no encontrado");
    }

    $sql = "UPDATE servicios_contables
            SET estado = :estado,
                fecha_actualizacion = NOW()
            WHERE id_servicio = :id_servicio";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
    $stmt->bindParam(":id_servicio", $id_servicio, PDO::PARAM_INT);

    $stmt->execute();

    sendResponse(200, "Estado actualizado correctamente");

} catch (PDOException $e) {

    sendResponse(500, "Error al actualizar estado", [
        "error" => $e->getMessage()
    ]);

}

?>
