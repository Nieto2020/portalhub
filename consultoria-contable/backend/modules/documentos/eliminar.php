<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_documento = 4;

try {

    $sql = "DELETE FROM documentos
            WHERE id_documento = :id_documento";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_documento", $id_documento, PDO::PARAM_INT);

    $stmt->execute();

    sendResponse(200, "Documento eliminado correctamente");

} catch (PDOException $e) {

    sendResponse(500, "Error al eliminar documento", [
        "error" => $e->getMessage()
    ]);

}

?>
