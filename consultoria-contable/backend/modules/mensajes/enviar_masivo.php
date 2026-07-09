<?php
/**
 * enviar_masivo.php - Envía un mensaje directo a TODOS los usuarios (solo admin)
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

$id_admin = $_SESSION['id_usuario'];
$roles = $data['roles'] ?? [];
// Si no se especifican roles, usar todos (clientes, asesores, admins)
if (empty($roles) || !is_array($roles)) {
    $roles = [1, 2, 3];
}
// Siempre excluir al admin que envía
$placeholders = implode(',', array_fill(0, count($roles), '?'));
$params = array_merge($roles, [$id_admin]);

$mensaje_completo = "📢 " . $titulo . "\n\n" . $contenido;

try {
    $conexion->beginTransaction();

    $stmt = $conexion->prepare("
        SELECT id_usuario FROM usuarios 
        WHERE id_rol IN ($placeholders) AND id_usuario != ?
    ");
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    $insert = $conexion->prepare("
        INSERT INTO mensajes (id_remitente, id_destinatario, tipo, contenido_texto)
        VALUES (?, ?, 'Chat interno', ?)
    ");

    foreach ($usuarios as $u) {
        $insert->execute([$id_admin, $u['id_usuario'], $mensaje_completo]);
        $count++;
    }

    $conexion->commit();

    sendResponse(200, "Mensaje enviado a $count usuarios");
} catch (PDOException $e) {
    $conexion->rollBack();
    sendResponse(500, "Error: " . $e->getMessage());
}
