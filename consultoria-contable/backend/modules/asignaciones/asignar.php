<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";
require_once "../../services/NotificationService.php";

// Solo el administrador puede asignar o cambiar asesores
checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['id_cliente', 'id_asesor'])) {
    sendResponse(400, "Faltan campos requeridos (id_cliente, id_asesor)");
}

$id_cliente = $data['id_cliente'];
$id_asesor = $data['id_asesor'];
$id_asignador = $_SESSION['id_usuario'];
$motivo = $data['motivo_cambio'] ?? "Asignación inicial";

try {
    $conexion->beginTransaction();

    // 1. Validar roles de los usuarios
    $stmt = $conexion->prepare("SELECT id_usuario, id_rol FROM usuarios WHERE id_usuario IN (?, ?)");
    $stmt->execute([$id_cliente, $id_asesor]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) < 2) {
        sendResponse(404, "Uno o ambos usuarios no existen");
    }

    // Identificar quién es quién para validar roles
    $rol_cliente = null;
    $rol_asesor = null;
    foreach ($usuarios as $u) {
        if ($u['id_usuario'] == $id_cliente) $rol_cliente = $u['id_rol'];
        if ($u['id_usuario'] == $id_asesor) $rol_asesor = $u['id_rol'];
    }

    if ($rol_cliente != ROL_CLIENTE) sendResponse(400, "El usuario destino no es un Cliente");
    if ($rol_asesor != ROL_ASESOR) sendResponse(400, "El usuario asignado no es un Asesor");

    // 2. Desactivar asignación actual si existe
    $stmt = $conexion->prepare("UPDATE cliente_asesor SET estado = 'inactivo', fecha_fin = CURRENT_TIMESTAMP WHERE id_cliente = ? AND estado = 'activo'");
    $stmt->execute([$id_cliente]);

    // 3. Crear nueva asignación
    $stmt = $conexion->prepare("INSERT INTO cliente_asesor (id_cliente, id_asesor, id_asignador, motivo_cambio) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_cliente, $id_asesor, $id_asignador, $motivo]);

    // 4. Notificar al cliente
    $notificador = new NotificationService($conexion);
    $notificador->notify($id_cliente, "Asignación", "Se le ha asignado un nuevo asesor contable.");

    $conexion->commit();
    sendResponse(201, "Asesor asignado exitosamente");

} catch (PDOException $e) {
    $conexion->rollBack();
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>