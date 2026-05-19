<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

$id_pago = $data['id_pago'] ?? null;
$estado_pago = $data['estado_pago'] ?? null;

if (!$id_pago || !$estado_pago) {
    sendResponse(400, "Faltan campos requeridos");
}

$estados_validos = ["Pendiente", "Pagado", "Cancelado"];

if (!in_array($estado_pago, $estados_validos)) {
    sendResponse(400, "Estado de pago no válido");
}

try {
    $buscar = $conexion->prepare("
        SELECT id_pago
        FROM pagos_facturacion
        WHERE id_pago = ?
    ");
    $buscar->execute([$id_pago]);

    if (!$buscar->fetch()) {
        sendResponse(404, "Pago no encontrado");
    }

    $fecha_pago_sql = $estado_pago === "Pagado" ? ", fecha_pago = NOW()" : "";

    $sql = "UPDATE pagos_facturacion
            SET estado_pago = :estado_pago
            $fecha_pago_sql
            WHERE id_pago = :id_pago";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":estado_pago", $estado_pago, PDO::PARAM_STR);
    $stmt->bindParam(":id_pago", $id_pago, PDO::PARAM_INT);
    $stmt->execute();

    sendResponse(200, "Estado de pago actualizado correctamente");

} catch (PDOException $e) {
    sendResponse(500, "Error al actualizar estado de pago", [
        "error" => $e->getMessage()
    ]);
}

?>
