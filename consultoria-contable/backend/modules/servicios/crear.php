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
$id_tipo_servicio = $data['id_tipo_servicio'] ?? null;

if (!$id_cliente || !$id_tipo_servicio) {
    sendResponse(400, "Faltan campos requeridos");
}

$estado = "Pendiente";

try {

    // Validar tipo de servicio
    $validar = $conexion->prepare("
        SELECT COUNT(*) 
        FROM cat_servicios 
        WHERE id_tipo_servicio = ?
    ");

    $validar->execute([$id_tipo_servicio]);

    if ($validar->fetchColumn() == 0) {
        sendResponse(404, "Tipo de servicio no válido");
    }

    $sql = "INSERT INTO servicios_contables
            (id_cliente, id_tipo_servicio, estado)
            VALUES (:id_cliente, :id_tipo_servicio, :estado)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(":id_tipo_servicio", $id_tipo_servicio, PDO::PARAM_INT);
    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

    $stmt->execute();

    sendResponse(201, "Servicio creado correctamente", [
        "id_servicio" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {

    sendResponse(500, "Error al crear servicio", [
        "error" => $e->getMessage()
    ]);

}

?>
