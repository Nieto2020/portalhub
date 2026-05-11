const Sub_HistorialPagos = document.getElementById("HistorialPagos");
const Sub_PagosFaltantes = document.getElementById("pagos-faltantes");
const Tab_HistorialPagos = document.getElementById("tab-historial");
const Tab_PagosFaltantes = document.getElementById("tab-pagos-faltantes");

function CambiarSub(sub_activo, sub_inactivo){

    sub_inactivo.classList.remove('active');
    sub_inactivo.style.display = "none";

    sub_activo.classList.add('active');

    sub_activo.style.display = "block";
}

Tab_HistorialPagos.onclick = () =>{

    CambiarSub(Sub_HistorialPagos, Sub_PagosFaltantes);

    Tab_HistorialPagos.classList.add('active');
    Tab_PagosFaltantes.classList.remove('active');
}

Tab_PagosFaltantes.onclick = () =>{

    CambiarSub(Sub_PagosFaltantes, Sub_HistorialPagos);

    Tab_PagosFaltantes.classList.add('active');
    Tab_HistorialPagos.classList.remove('active');
}

/*CARGA DE DATOS EN LAS TABLAS */

const TablaHistoria = document.getElementById("HistorialPagos-Consultancy");
function CargarTablasPagos(){

    const DatosSimulados = [

        {
        tipo_de_pago: "IVA",
        fecha: "30/12/2025",
        cantidad :"$3200",
        estado : "Aprobado"
    },

    {
        tipo: "ISR",
        fecha: "15/01/2026",
        cantidad: "$1500",
        estado: "Pendiente"
    }
    
];





}