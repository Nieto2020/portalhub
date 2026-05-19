async function logout() {
    try {
        const response = await fetch('../../../backend/modules/auth/logout.php');
        const res = await response.json();
        
        if (response.ok) {
            window.location.href = "../auth/login.html";
        } else {
            console.error("Error en el servidor al cerrar sesión:", res.message);
            // Forzamos redirección aunque falle el backend por seguridad del lado del cliente
            window.location.href = "../auth/login.html";
        }
    } catch (error) {
        console.error("Error de conexión al cerrar sesión:", error);
        // Forzamos redirección en caso de error de red
        window.location.href = "../auth/login.html";
    }
}
