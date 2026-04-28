/*MODAL */
const modal = document.getElementById("modal-subir");
const btnSubir = document.querySelector(".btn-primary");
const btnCerrar = document.querySelector(".close-btn");
const fileInput = document.getElementById("file-input");
const btnEnviar = document.getElementById('btn-enviar-doc');

btnSubir.onclick = function(){
    modal.style.display = "block";
}

btnCerrar.onclick = function(){
    modal.style.display = "none";
}

window.onclick = function(event){

    if(event.target == modal){
        modal.style.display="none";
    }
}

fileInput.onchange =function(){
    if(fileInput.files.length > 0){
        const fileName = fileInput.files[0].name;

        document.querySelector('.modal-content p').innerText = `Archivo seleccionado: ${fileName}`;
        document.querySelector('.modal-content p').style.color = "var(--morado)";
    }
}


btnEnviar.onclick = function(){
    const file = fileInput.files[0];

    if(file){
        alert(`¡Éxito! El documento "${file.name}" ha sido enviado a revisión.`);
        modal.style.display = "none";
        fileInput.value = "";
        document.querySelector('.modal-content p').innerText = "Selecciona el archivo a subir.";
    }else{
        alert("Porfavor, selecciona un archivo primero");
    }
}


/*🌟ACTUALIZAR DATOS(Declaracion Mensual, Declaracion Anual) */

function ActualizarDatos(datos){

    try{
         const estadoMensual = document.getElementById("estado-mensual");

        if(datos.declaracionMensual.completada){

        estadoMensual.innerHTML = '<span class="icon-green">✅</span>Listo';
        document.getElementById('card-mensual').classList.add('completado');

        }

       const barraAnual = document.getElementById("barra-progreso-anual");
       barraAnual.style.width = datos.declaracionAnual.porcentaje + "%";

       document.getElementById("fecha-mensual").innerText = `Entregar Antes: ${datos.declaracionMensual.fechaLimite}`;
    }catch(e){
        console.error(`Hubo un error: ${e}`);
    }
    
}


/*🌟MOSTRAR PAGOS(Proximo Pago) */

function mostrarPagos(datosPago){

    try{
        
       const PagoMonto = document.getElementById("pago-monto");
       const PagoFecha = document.getElementById("pago-fecha");
    
       PagoMonto.innerText = `$ ${datosPago.monto.toLocaleString()}`;

       PagoFecha.innerText = ` ${datosPago.fechaLimite}`;

       if(datosPago.vencido){
        document.querySelector('.card.proximo-pago').classList.add('pago-vencido');
        }

    }catch(e){
        console.error(`Hubo un error: ${e}`);
    }
}

/* 🌟 MOSTRAR TAREAS */

function mostrarTareas(tareas) {
    try {
        const contenedor = document.getElementById("lista-tareas");
        if (!contenedor) return;

        const titulo = contenedor.querySelector('.titulo-tareas');
        contenedor.innerHTML = '';
        if (titulo) contenedor.appendChild(titulo);

        tareas.forEach(tarea => {
            const div = document.createElement('div');
            div.className = 'tarea-item';

            div.innerHTML = `
                <div class="icon-square ${tarea.categoria === 'urgente' ? 'bg-morado' : 'bg-azul'}">
                    <i class="bx bx-widget-vertical"></i>
                </div>
                <input type="checkbox" class="task-check" ${tarea.completada ? 'checked' : ''}>
                <p class="task-text">${tarea.nombre}</p>
            `;

            contenedor.appendChild(div);
            contenedor.appendChild(document.createElement('hr'));
        });
    } catch (e) {
        console.error(`Error en mostrarTareas: ${e}`);
    }
}
/* 🌟 CARGAR LOS DATOS */
async function init() {
    try {
        console.log("Cargando datos del Dashboard...");

      
        const datosSimulados = {
            declaraciones: {
                declaracionMensual: {
                    completada: false,
                    fechaLimite: "20/04"
                },
                declaracionAnual: {
                    porcentaje: 60
                }
            },
            pagos: {
                monto: 5800,
                fechaLimite: "20/04",
                vencido: false
            },
            tareas: [
                { nombre: "Subir CFDI", completada: true, categoria: "urgente" },
                { nombre: "Enviar Estado de Cuenta", completada: false, categoria: "normal" }
            ]
        };

        // Ejecución de funciones
        ActualizarDatos(datosSimulados.declaraciones);
        mostrarPagos(datosSimulados.pagos);
        mostrarTareas(datosSimulados.tareas);

    } catch (e) {
        console.error(`Hubo un error en la carga inicial: ${e}`);
    }
}

document.addEventListener('DOMContentLoaded', init);