<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

if (!isset($_FILES['archivo'])) {
    sendResponse(400, "No se recibió archivo");
}

$archivo = $_FILES['archivo'];

$nombreArchivo = time() . "_" . basename($archivo['name']);
$rutaDestino = "../../uploads/" . $nombreArchivo;

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    sendResponse(500, "Error al mover el archivo");
}

$id_usuario_propietario = 1;
$id_tipo_doc = 1;
$version = 1;
$validacion_cfdi = 1;

try {

    $sql = "INSERT INTO documentos
            (id_usuario_propietario, id_tipo_doc, ruta_archivo, version, validacion_cfdi)
            VALUES (:id_usuario_propietario, :id_tipo_doc, :ruta_archivo, :version, :validacion_cfdi)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_usuario_propietario", $id_usuario_propietario, PDO::PARAM_INT);
    $stmt->bindParam(":id_tipo_doc", $id_tipo_doc, PDO::PARAM_INT);
    $stmt->bindParam(":ruta_archivo", $rutaDestino, PDO::PARAM_STR);
    $stmt->bindParam(":version", $version, PDO::PARAM_INT);
    $stmt->bindParam(":validacion_cfdi", $validacion_cfdi, PDO::PARAM_INT);

    $stmt->execute();

    sendResponse(201, "Archivo subido correctamente", [
        "id_documento" => $conexion->lastInsertId(),
        "ruta_archivo" => $rutaDestino
    ]);

} catch (PDOException $e) {

    sendResponse(500, "Error al registrar archivo", [
        "error" => $e->getMessage()
    ]);

}

?>
