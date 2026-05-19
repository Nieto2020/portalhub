<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

// Solo Admin (id_rol 1) puede crear usuarios directamente
checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['correo', 'password', 'id_rol'])) {
    sendResponse(400, "Faltan campos requeridos");
}

if (!validateEmail($data['correo'])) {
    sendResponse(400, "Formato de correo inválido");
}

$correo = $data['correo'];
$password = password_hash($data['password'], PASSWORD_BCRYPT);
$id_rol = $data['id_rol'];
$numero_cliente = isset($data['numero_cliente']) ? $data['numero_cliente'] : null;

try {
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) {
        sendResponse(400, "El correo ya está registrado");
    }

    $stmt = $conexion->prepare("INSERT INTO usuarios (id_rol, correo, password_hash, numero_cliente, require_password_change) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$id_rol, $correo, $password, $numero_cliente]);

    sendResponse(201, "Usuario creado exitosamente");
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>