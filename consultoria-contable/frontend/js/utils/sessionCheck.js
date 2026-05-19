async function validarSesion() {
    try {
        const response = await fetch('../../../backend/modules/auth/check_session.php');
        const data = await response.json();

        if (data.status === 401) {
            // Redirigir a la página visual de sesión expirada
            window.location.href = "../auth/session_expired.html";
        }
    } catch (error) {
        console.error("Error al validar sesión:", error);
    }
}


document.addEventListener('DOMContentLoaded', validarSesion);

// Validar cada 30 segundos (ajustado para que sea efectivo y no demasiado frecuente)
setInterval(validarSesion, 30000); 
