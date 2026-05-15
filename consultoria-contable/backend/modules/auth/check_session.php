<?php
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

# Verificar sesión sin actualizar el tiempo de actividad
# Esto permite que el frontend pregunte si la sesión sigue viva sin reiniciarla
checkAuth(false);

sendResponse(200, "Sesión activa");
?>