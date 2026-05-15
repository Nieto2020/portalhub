<?php
require_once "../config/conexion.php";
require_once "../utils/response.php";

$test_email = "admin@consultoria.com";
$test_pass = "password123";

try {
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->execute([$test_email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h1>Diagnóstico de Conexión y Datos</h1>";

    if ($usuario) {
        echo "<p>✅ Usuario <b>$test_email</b> encontrado en la base de datos.</p>";
        echo "<p>Hash en DB: <code>" . $usuario['password_hash'] . "</code></p>";
        
        if (password_verify($test_pass, $usuario['password_hash'])) {
            echo "<p>✅ La contraseña <b>'$test_pass'</b> coincide con el hash.</p>";
        } else {
            echo "<p>❌ La contraseña <b>'$test_pass'</b> NO coincide con el hash de la DB.</p>";
        }
        
        echo "<p>Estado del usuario: <b>" . $usuario['estado'] . "</b></p>";
    } else {
        echo "<p>❌ El usuario <b>$test_email</b> NO existe en la tabla 'usuarios'.</p>";
        echo "<p>Por favor, asegúrate de haber ejecutado <code>database/seed.sql</code> en phpMyAdmin.</p>";
    }

} catch (PDOException $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
}
?>