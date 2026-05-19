<?php
require_once "config.php";

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->exec("set names utf8");
} catch(PDOException $exception) {
    // Evitar echo para no romper el JSON de la respuesta
    if (function_exists('sendResponse')) {
        sendResponse(500, "Error de conexión a la base de datos");
    } else {
        header("Content-Type: application/json");
        http_response_code(500);
        echo json_encode(["status" => 500, "message" => "Error de conexión a la base de datos"]);
        exit();
    }
}
?>