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
$id_servicio = $data['id_servicio'] ?? null;
$monto = $data['monto'] ?? null;

if (!$id_cliente || !$id_servicio || !$monto) {
    sendResponse(400, "Faltan campos requeridos");
}

if (!is_numeric($monto) || $monto <= 0) {
    sendResponse(400, "Monto no válido");
}

$estado_pago = "Pendiente";

try {

    // Validar servicio existente
    $validar = $conexion->prepare("
        SELECT COUNT(*) 
        FROM servicios_contables 
        WHERE id_servicio = ?
    ");

    $validar->execute([$id_servicio]);

    if ($validar->fetchColumn() == 0) {
        sendResponse(404, "Servicio no encontrado");
    }

    $sql = "INSERT INTO pagos_facturacion
            (id_cliente, id_servicio, monto, estado_pago)
            VALUES (:id_cliente, :id_servicio, :monto, :estado_pago)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(":id_servicio", $id_servicio, PDO::PARAM_INT);
    $stmt->bindParam(":monto", $monto);
    $stmt->bindParam(":estado_pago", $estado_pago, PDO::PARAM_STR);

    $stmt->execute();

    sendResponse(201, "Pago registrado correctamente", [
        "id_pago" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {

    sendResponse(500, "Error al registrar pago", [
        "error" => $e->getMessage()
    ]);

}

?>
