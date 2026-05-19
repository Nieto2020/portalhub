<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);
$id_usuario = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'];

if (!validateRequired($data, ['nombre_completo'])) {
    sendResponse(400, "El nombre completo es obligatorio");
}

try {
    // Definimos los campos que se pueden actualizar de forma general
    $campos = [
        'nombre_completo' => $data['nombre_completo'],
        'telefono' => $data['telefono'] ?? null
    ];

    // Agregamos campos específicos según el rol
    if ($id_rol == ROL_ASESOR) {
        $campos['especialidad'] = $data['especialidad'] ?? null;
        $campos['biografia'] = $data['biografia'] ?? null;
    } elseif ($id_rol == ROL_CLIENTE) {
        $campos['rfc'] = $data['rfc'] ?? null;
        $campos['razon_social'] = $data['razon_social'] ?? null;
        $campos['direccion_fiscal'] = $data['direccion_fiscal'] ?? null;
    }

    // Construcción dinámica del SQL
    $cols = array_keys($campos);
    $placeholders = array_fill(0, count($cols), "?");
    $updates = array_map(function($col) { return "$col = VALUES($col)"; }, $cols);

    $sql = "INSERT INTO perfiles (id_usuario, " . implode(", ", $cols) . ") 
            VALUES (?, " . implode(", ", $placeholders) . ") 
            ON DUPLICATE KEY UPDATE " . implode(", ", $updates);
            
    $stmt = $conexion->prepare($sql);
    
    $params = array_values($campos);
    array_unshift($params, $id_usuario); // Agregar id_usuario al inicio para el VALUES

    $stmt->execute($params);

    sendResponse(200, "Perfil actualizado exitosamente");
} catch (PDOException $e) {
    sendResponse(500, "Error al guardar el perfil: " . $e->getMessage());
}
?>