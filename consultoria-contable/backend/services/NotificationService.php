<?php
require_once __DIR__ . "/../config/conexion.php";

class NotificationService {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function notify($userId, $type, $message) {
        try {
            $stmt = $this->conexion->prepare("INSERT INTO notificaciones (id_usuario_destino, tipo_evento, mensaje_texto) VALUES (?, ?, ?)");
            return $stmt->execute([$userId, $type, $message]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>