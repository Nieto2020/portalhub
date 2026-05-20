const input_pass = document.getElementById("password_input");
const input_passConfirm = document.getElementById("password_Confirm");
const Form = document.getElementById("ActualizarPass");

Form.addEventListener("submit", (e) =>{
    const valor_pass = input_pass.value;
const valor_passConfirm = input_passConfirm.value;

    if(valor_pass.length === 0 || valor_passConfirm.length === 0 || valor_pass !== valor_passConfirm ){

        e.preventDefault();
        alert("Los campos estan vacios o no coinciden");
    } else{

        e.preventDefault();
        alert("Se actualizo la contraseña correctamente.");
    };
})

/*Muestra de datos */

let MiUsuario = {

    data1 : {
        nombre:"Victoria Laney",
        correo: "kotonoha459@gmail.com"
    },

    data2:{
        rol: "Cliente",
        num_tel:"4641035040",
        RFC: "4W5890WERU040UUETW4PUJRJERP"
    }
}


function DatosUsuario(nombre, correo, rol, numero_tel, RFC){
    const nombre_usuario = document.getElementById("nombre");
    const correo_usuario = document.getElementById("correo");

    const rol_usuario = document.getElementById("rol");
    const cel_usuario = document.getElementById("num-tel");
    const RFC_usuario = document.getElementById("RFC");

    nombre_usuario.textContent = nombre;
    correo_usuario.textContent = correo;

    rol_usuario.textContent = rol;
    cel_usuario.textContent = numero_tel;
    RFC_usuario.textContent = RFC;

}
DatosUsuario(MiUsuario.data1.nombre,MiUsuario.data1.correo, MiUsuario.data2.rol, MiUsuario.data2.num_tel, MiUsuario.data2.RFC );