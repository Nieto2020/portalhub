<?php
/**
 * crear_masivo.php - Crea una notificación para TODOS los usuarios (solo admin)
 */
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);
$titulo = trim($data['titulo'] ?? '');
$contenido = trim($data['contenido'] ?? '');

if ($titulo === '' || $contenido === '') {
    sendResponse(400, "Asunto y contenido son requeridos");
}

$mensaje = $titulo . ': ' . $contenido;
$roles = $data['roles'] ?? [];
if (empty($roles) || !is_array($roles)) {
    $roles = [1, 2, 3];
}

$placeholders = implode(',', array_fill(0, count($roles), '?'));

try {
    $conexion->beginTransaction();

    $stmt = $conexion->prepare("
        SELECT id_usuario FROM usuarios 
        WHERE id_rol IN ($placeholders)
    ");
    $stmt->execute($roles);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    $insert = $conexion->prepare("
        INSERT INTO notificaciones (id_usuario_destino, tipo_evento, mensaje_texto)
        VALUES (?, 'sistema', ?)
    ");

    foreach ($usuarios as $u) {
        $insert->execute([$u['id_usuario'], $mensaje]);
        $count++;
    }

    $conexion->commit();

    sendResponse(200, "Notificación enviada a $count usuarios");
} catch (PDOException $e) {
    $conexion->rollBack();
    sendResponse(500, "Error: " . $e->getMessage());
}
