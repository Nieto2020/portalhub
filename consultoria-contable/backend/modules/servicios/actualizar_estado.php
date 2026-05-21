<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../services/NotificationService.php";

checkRole([ROL_ADMIN, ROL_ASESOR]);

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
        SELECT sc.id_cliente, cs.nombre_servicio 
        FROM servicios_contables sc
        JOIN cat_servicios cs ON sc.id_tipo_servicio = cs.id_tipo_servicio
        WHERE sc.id_servicio = ?
    ");

    $buscar->execute([$id_servicio]);
    $servicio = $buscar->fetch(PDO::FETCH_ASSOC);

    if (!$servicio) {
        sendResponse(404, "Servicio no encontrado");
    }

    $sql = "UPDATE servicios_contables
            SET estado = :estado,
                fecha_actualizacion = NOW()
            WHERE id_servicio = :id_servicio";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
    $stmt->bindParam(":id_servicio", $id_servicio, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $notificador = new NotificationService($conexion);
        $notificador->notify(
            $servicio['id_cliente'], 
            "Servicio", 
            "El estado de su servicio '{$servicio['nombre_servicio']}' ha cambiado a: $estado"
        );
    }

    sendResponse(200, "Estado del servicio actualizado correctamente");

} catch (PDOException $e) {
    error_log("Error en servicios/actualizar_estado.php: " . $e->getMessage());
    sendResponse(500, "Error interno al actualizar el estado del servicio");
}

?>
