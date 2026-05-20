<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario = $_SESSION['id_usuario'];
$rol = $_SESSION['rol'] ?? null;

try {

    $sql = "
        SELECT 
            id_cita,
            id_cliente,
            id_asesor,
            fecha_hora,
            estado,
            motivo_cancelacion
        FROM citas
    ";

    $params = [];

    if ($rol === ROL_CLIENTE) {
        $sql .= " WHERE id_cliente = ?";
        $params[] = $id_usuario;
    } elseif ($rol === ROL_ASESOR) {
        $sql .= " WHERE id_asesor = ?";
        $params[] = $id_usuario;
    }

    $sql .= " ORDER BY fecha_hora DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);

    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Citas obtenidas correctamente", $citas);

} catch (PDOException $e) {

    sendResponse(500, "Error en la consulta", [
        "error" => $e->getMessage()
    ]);

}

?>
