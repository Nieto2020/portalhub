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

const TablaHistorial = document.getElementById("HistorialPagos-Consultancy");
const TablaFaltantes = document.getElementById("pagosFaltantes-Consultancy");
function CargarTablasPagos(){

    const DatosSimulados = [

        {
        tipo: "IVA",
        fecha: "30/12/2025",
        fecha_limite : "12/12/2026",
        cantidad :"$3200",
        estado : "Aprobado"
    },

    {
        tipo: "ISR",
        fecha: "15/01/2026",
         fecha_limite : "12/12/2026",
        cantidad: "$1500",
        estado: "Pendiente"
    }
];

DatosSimulados.forEach(pago =>{
    const fila_pago = document.createElement("tr");
    const fila_faltante = document.createElement("tr");
    let claseExtra = "";

    if (pago.estado === "Aprobado") {
        claseExtra = "aprobado"; 
    } else if (pago.estado === "Pendiente") {
        claseExtra = "pendiente";
    } else if (pago.estado === "Vencido") {
        claseExtra = "vencido";
    }

    fila_pago.innerHTML = `<td>${pago.tipo}</td>
                            <td>${pago.fecha}</td>
                            <td>${pago.cantidad}</td>
                            <td><span class="status ${claseExtra}">${pago.estado}</span></td>
                            <td><i class="bx bx-eye" style="color: white;"></i></td>`

                            fila_faltante.innerHTML = `
                             <td>${pago.tipo}</td>
                            <td>${pago.fecha_limite}</td>
                            <td><span class="status ${claseExtra}">${pago.estado}</span></td>
                            <td> <i class="bx bx-alert-circle" /></i> 
                                 <i class="bx bx-arrow-to-bottom-stroke" /></i> 
                            </td>
                            `;
                            TablaHistorial.appendChild(fila_pago);
                            TablaFaltantes.appendChild(fila_faltante);
});
}

CargarTablasPagos();