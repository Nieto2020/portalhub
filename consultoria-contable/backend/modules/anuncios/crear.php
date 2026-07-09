<?php
/**
 * crear.php - Crea un nuevo anuncio (solo admin)
 */
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['titulo', 'contenido'])) {
    sendResponse(400, "Faltan campos requeridos (titulo, contenido)");
}

$id_autor = $_SESSION['id_usuario'];
$titulo = trim($data['titulo']);
$contenido = trim($data['contenido']);

if ($titulo === '' || $contenido === '') {
    sendResponse(400, "Título y contenido no pueden estar vacíos");
}

try {
    $stmt = $conexion->prepare("
        INSERT INTO anuncios (id_autor, titulo, contenido)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$id_autor, $titulo, $contenido]);

    sendResponse(201, "Anuncio creado", [
        "id_anuncio" => $conexion->lastInsertId()
    ]);
} catch (PDOException $e) {
    sendResponse(500, "Error: " . $e->getMessage());
}
