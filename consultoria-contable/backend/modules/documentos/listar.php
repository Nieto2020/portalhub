<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

try {

    $sql = "
        SELECT
            d.id_documento,
            d.id_usuario_propietario,
            d.id_tipo_doc,
            d.ruta_archivo,
            d.version,
            d.validacion_cfdi,
            d.fecha_subida,
            td.nombre_tipo
        FROM documentos d
        LEFT JOIN cat_tipos_documentos td
            ON d.id_tipo_doc = td.id_tipo_doc
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Documentos obtenidos correctamente", $documentos);

} catch (PDOException $e) {

    sendResponse(500, "Error en la consulta", [
        "error" => $e->getMessage()
    ]);

}

?>
