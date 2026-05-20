<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if (!isset($_GET['id'])) {
    sendResponse(400, "ID de documento no proporcionado");
}

$id_documento = intval($_GET['id']);
$id_usuario_sesion = $_SESSION['id_usuario'];
$id_rol_sesion = $_SESSION['id_rol'];

try {
    $sql = "SELECT * FROM documentos WHERE id_documento = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":id", $id_documento, PDO::PARAM_INT);
    $stmt->execute();

    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$documento) {
        sendResponse(404, "Documento no encontrado");
    }

    // Verificar permisos
    $permitido = false;

    if ($id_rol_sesion == 1) {
        $permitido = true; // Admin puede todo
    } elseif ($id_rol_sesion == 3) {
        if ($documento['id_usuario_propietario'] == $id_usuario_sesion) {
            $permitido = true; // Cliente es dueño
        }
    } elseif ($id_rol_sesion == 2) {
        // Asesor: Verificar si el propietario es su cliente asignado
        $sqlAsig = "SELECT id_asignacion FROM cliente_asesor 
                    WHERE id_asesor = :id_asesor 
                    AND id_cliente = :id_cliente 
                    AND estado = 'activo'";
        $stmtA = $conexion->prepare($sqlAsig);
        $stmtA->execute([
            ':id_asesor' => $id_usuario_sesion,
            ':id_cliente' => $documento['id_usuario_propietario']
        ]);
        if ($stmtA->fetch()) {
            $permitido = true;
        }
    }

    if (!$permitido) {
        sendResponse(403, "No tiene permisos para descargar este documento");
    }

    $rutaRelativa = $documento["ruta_archivo"];
    $rutaAbsoluta = "../../" . $rutaRelativa;

    if (!file_exists($rutaAbsoluta)) {
        sendResponse(404, "Archivo físico no encontrado en el servidor");
    }

    if (ob_get_level()) ob_end_clean();

    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . $documento['nombre_original'] . "\"");
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Cache-Control: must-revalidate");
    header("Pragma: public");
    header("Content-Length: " . filesize($rutaAbsoluta));

    readfile($rutaAbsoluta);
    exit;

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor", ["error" => $e->getMessage()]);
}
