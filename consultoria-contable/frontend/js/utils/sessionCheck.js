async function validarSesion() {
    try {
        // Usamos el nuevo endpoint que no reinicia el tiempo de inactividad
        const response = await fetch('../../../backend/modules/auth/check_session.php');
        const data = await response.json();

        if (data.status === 401) {
            alert("Tu sesión ha expirado por inactividad. Por favor, ingresa de nuevo.");
            // Redirigir a login usando ruta relativa
            window.location.href = "../auth/login.html";
        }
    } catch (error) {
        console.error("Error al validar sesión:", error);
    }
}

// Validar inmediatamente al cargar
document.addEventListener('DOMContentLoaded', validarSesion);

// Validar cada 30 segundos (ajustado para que sea eficiente y notar el cambio)
setInterval(validarSesion, 30000); 
