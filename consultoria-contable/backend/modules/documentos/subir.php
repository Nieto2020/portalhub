<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

if (!isset($_FILES['archivo']) || !isset($_POST['id_tipo_doc'])) {
    sendResponse(400, "Faltan datos obligatorios (archivo o tipo de documento)");
}

$archivo = $_FILES['archivo'];
$id_tipo_doc = intval($_POST['id_tipo_doc']);

$maxSize = 10 * 1024 * 1024; // Limite 10 Megabytes
if ($archivo['size'] > $maxSize) {
    sendResponse(400, "El archivo excede el límite de tamaño permitido (10MB)");
}

$id_rol_sesion = $_SESSION['id_rol'];
$id_usuario_sesion = $_SESSION['id_usuario'];

if ($id_rol_sesion == 1 || $id_rol_sesion == 2) {
    if (!isset($_POST['id_cliente'])) {
        sendResponse(400, "Debe especificar el ID del cliente");
    }
    $id_usuario_propietario = intval($_POST['id_cliente']);

    // Asesor - Cliente
    if ($id_rol_sesion == 2) {
        $sqlCheck = "SELECT id_asignacion FROM cliente_asesor 
                     WHERE id_asesor = :id_asesor AND id_cliente = :id_cliente AND estado = 'activo'";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->execute([
            ':id_asesor' => $id_usuario_sesion,
            ':id_cliente' => $id_usuario_propietario
        ]);
        if (!$stmtCheck->fetch()) {
            sendResponse(403, "No tiene permiso para subir archivos a este cliente");
        }
    }
} else {
    $id_usuario_propietario = $id_usuario_sesion;
}

//(RCE) (Path Traversal) 
$nombreOriginal = basename($archivo['name']);
$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

$extensionesPermitidas = [
    'pdf'  => 'application/pdf',
    'xml'  => ['text/xml', 'application/xml'],
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'jpg'  => ['image/jpeg', 'image/pjpeg'],
    'png'  => 'image/png'
];

if (!array_key_exists($extension, $extensionesPermitidas)) {
    sendResponse(400, "Extensión de archivo no permitida");
}

// Validacion de MIME-Type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeTypeReal = $finfo->file($archivo['tmp_name']);

$esperado = $extensionesPermitidas[$extension];
if (is_array($esperado)) {
    if (!in_array($mimeTypeReal, $esperado)) {
        sendResponse(400, "El contenido del archivo no coincide con su extensión");
    }
} else {
    if ($mimeTypeReal !== $esperado) {
        sendResponse(400, "El contenido del archivo no coincide con su extensión");
    }
}

// Versionado
try {
    $sqlVersion = "SELECT MAX(version) as ultima_version FROM documentos 
                   WHERE id_usuario_propietario = :id_user 
                   AND id_tipo_doc = :id_tipo 
                   AND nombre_original = :nombre_orig";
    
    $stmtV = $conexion->prepare($sqlVersion);
    $stmtV->execute([
        ':id_user' => $id_usuario_propietario,
        ':id_tipo' => $id_tipo_doc,
        ':nombre_orig' => $nombreOriginal
    ]);
    
    $resultadoV = $stmtV->fetch(PDO::FETCH_ASSOC);
    $version = ($resultadoV['ultima_version'] ?? 0) + 1;

    // Ofuscación del nombre en disco
    $nombreHash = bin2hex(random_bytes(16)) . "_v" . $version . "." . $extension;
    $rutaRelativa = "uploads/" . $nombreHash;
    $rutaDestino = "../../" . $rutaRelativa;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        sendResponse(500, "Error al guardar el archivo");
    }

    $sql = "INSERT INTO documentos 
            (id_usuario_propietario, id_tipo_doc, ruta_archivo, nombre_original, version, validacion_cfdi) 
            VALUES (:id_user, :id_tipo, :ruta, :nombre_orig, :version, :cfdi)";

    $cfdi = ($extension === 'xml') ? 1 : 0;

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':id_user' => $id_usuario_propietario,
        ':id_tipo' => $id_tipo_doc,
        ':ruta' => $rutaRelativa,
        ':nombre_orig' => $nombreOriginal,
        ':version' => $version,
        ':cfdi' => $cfdi
    ]);

    sendResponse(201, "Archivo subido correctamente", [
        "id_documento" => $conexion->lastInsertId(),
        "nombre" => $nombreOriginal,
        "version" => $version
    ]);

} catch (PDOException $e) {
    sendResponse(500, "Error en la base de datos", ["error" => $e->getMessage()]);
}