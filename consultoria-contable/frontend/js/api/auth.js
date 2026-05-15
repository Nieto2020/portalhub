export const login = async (correo, password) => {
    try {
        const response = await fetch('../../../backend/modules/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ correo, password })
        });
        return await response.json();
    } catch (error) {
        console.error("Error en la petición de login:", error);
        return { status: 500, message: "Error de conexión" };
    }
};

export const logout = async () => {
    try {
        const response = await fetch('../../../backend/modules/auth/logout.php');
        return await response.json();
    } catch (error) {
        console.error("Error al cerrar sesión:", error);
    }
};
