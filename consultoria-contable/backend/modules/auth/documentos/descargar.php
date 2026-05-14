<?php

require_once "../../config/conexion.php";

$id_documento = 5;

try {
    $sql = "SELECT ruta_archivo FROM documentos WHERE id_documento = :id_documento";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":id_documento", $id_documento, PDO::PARAM_INT);
    $stmt->execute();

    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$documento) {
        die("Documento no encontrado");
    }

    $ruta = $documento["ruta_archivo"];

    if (!file_exists($ruta)) {
        die("Archivo no encontrado en el servidor");
    }

    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . basename($ruta) . "\"");
    header("Content-Length: " . filesize($ruta));

    readfile($ruta);
    exit;

} catch (PDOException $e) {
    die("Error al descargar documento: " . $e->getMessage());
}

?>
