<?php
session_start();
require_once "../../config/conexion.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

# Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

# Validar campos requeridos
if (!validateRequired($data, ['correo', 'password'])) {
    sendResponse(400, "Faltan campos requeridos");
}

$correo = $data['correo'];
$password = $data['password'];

try {
    $stmt = $conexion->prepare("SELECT id_usuario, id_rol, password_hash, estado FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        sendResponse(401, "Credenciales incorrectas");
    }

    if ($usuario['estado'] !== 'activo') {
        sendResponse(403, "Usuario inactivo, contacte al administrador");
    }
    
    # Establecer sesión
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['id_rol'] = $usuario['id_rol'];

    # Enviar respuesta exitosa con información del usuario
    sendResponse(200, "Inicio de sesión exitoso", [
        "id_usuario" => $usuario['id_usuario'],
        "id_rol" => $usuario['id_rol']
    ]);
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>