<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

// Solo Asesores pueden crear reportes
checkRole([ROL_ASESOR]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['titulo', 'descripcion'])) {
    sendResponse(400, "Faltan campos requeridos: titulo, descripcion");
}

$id_asesor    = $_SESSION['id_usuario'];
$titulo       = trim($data['titulo']);
$descripcion  = trim($data['descripcion']);
$contenido    = trim($data['contenido'] ?? '');
$tipo         = $data['tipo'] ?? 'Mensual';
$estado       = $data['estado'] ?? 'Borrador';

$tipos_validos  = ['Mensual', 'Trimestral', 'Anual', 'Especial'];
$estados_validos = ['Borrador', 'Publicado'];

if (!in_array($tipo, $tipos_validos)) {
    sendResponse(400, "Tipo no válido. Opciones: " . implode(', ', $tipos_validos));
}
if (!in_array($estado, $estados_validos)) {
    sendResponse(400, "Estado no válido. Opciones: " . implode(', ', $estados_validos));
}

if ($titulo === '' || $descripcion === '') {
    sendResponse(400, "Título y descripción no pueden estar vacíos");
}

try {
    $stmt = $conexion->prepare("
        INSERT INTO reportes (id_asesor, titulo, descripcion, contenido, tipo, estado, fecha_creacion)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$id_asesor, $titulo, $descripcion, $contenido, $tipo, $estado]);

    sendResponse(201, "Reporte creado exitosamente", [
        "id_reporte" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}

?>
