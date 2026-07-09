<?php
/**
 * listar.php - Lista los anuncios activos
 */
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

try {
    $stmt = $conexion->prepare("
        SELECT id_anuncio, id_autor, titulo, contenido, fecha_creacion
        FROM anuncios
        WHERE activo = 1
        ORDER BY fecha_creacion DESC
        LIMIT 10
    ");
    $stmt->execute();
    $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "OK", $anuncios);
} catch (PDOException $e) {
    sendResponse(500, "Error: " . $e->getMessage());
}
