<?php
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth(false);
sendResponse(200, "Sesión activa");
?>
// Verificar sesión sin actualizar el tiempo de actividad