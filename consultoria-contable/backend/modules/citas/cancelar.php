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
$motivo_cancelacion = $data['motivo_cancelacion'] ?? null;

if (!$id_cita || !$motivo_cancelacion) {
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
        sendResponse(403, "No tiene permisos para cancelar esta cita");
    }

    if ($cita['estado'] === 'Cancelada') {
        sendResponse(400, "La cita ya se encuentra cancelada");
    }

    $estado = "Cancelada";

    $sql = "UPDATE citas
            SET estado = :estado,
                motivo_cancelacion = :motivo_cancelacion
            WHERE id_cita = :id_cita";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
    $stmt->bindParam(":motivo_cancelacion", $motivo_cancelacion, PDO::PARAM_STR);
    $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
    $stmt->execute();

    sendResponse(200, "Cita cancelada correctamente");

} catch (PDOException $e) {
    sendResponse(500, "Error al cancelar cita", [
        "error" => $e->getMessage()
    ]);
}

?>
