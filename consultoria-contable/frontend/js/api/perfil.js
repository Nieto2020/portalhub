/* 🌟Mostrar los datos del usuario */

export async function ObtenerPerfilUsuario(id_usuario) {

    try{

        const APIUsuario = await fetch(`URL_BACKEND_${id_usuario}`,{
            method:'GET'
        });

        const datos = await APIUsuario.json();

        return datos;
    }catch(e){
        console.error(`Hubo un error al mostrar los datos del Perfil: ${e}`);
    }
}

/* 🌟Cambio de contraseña */

export async function CambioPass(id_usuario, nueva_pass) {
    
    try{
        
        const APIUsuario = await fetch('URL_BACKEND', {
            method: 'POST',
            headers:{
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id:id_usuario,
                password:nueva_pass
            })
        });

        const datos = await APIUsuario.json();

        return datos;
    }catch(e){
        console.error(`Hubo un error al cambiar la contraseña: ${e}`);
    }
}