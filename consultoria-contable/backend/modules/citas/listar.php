<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

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

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Citas obtenidas correctamente", $citas);

} catch (PDOException $e) {

    sendResponse(500, "Error en la consulta", [
        "error" => $e->getMessage()
    ]);

}

?>
