<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_usuario_propietario = 1;
$id_tipo_doc = 1;
$ruta_archivo = "uploads/factura1.pdf";
$version = 1;
$validacion_cfdi = 1;

try {

    $sql = "INSERT INTO documentos
            (id_usuario_propietario, id_tipo_doc, ruta_archivo, version, validacion_cfdi)
            VALUES (:id_usuario_propietario, :id_tipo_doc, :ruta_archivo, :version, :validacion_cfdi)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_usuario_propietario", $id_usuario_propietario, PDO::PARAM_INT);
    $stmt->bindParam(":id_tipo_doc", $id_tipo_doc, PDO::PARAM_INT);
    $stmt->bindParam(":ruta_archivo", $ruta_archivo, PDO::PARAM_STR);
    $stmt->bindParam(":version", $version, PDO::PARAM_INT);
    $stmt->bindParam(":validacion_cfdi", $validacion_cfdi, PDO::PARAM_INT);

    $stmt->execute();

    sendResponse(201, "Documento registrado correctamente", [
        "id_documento" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {

    sendResponse(500, "Error al registrar documento", [
        "error" => $e->getMessage()
    ]);

}

?>
