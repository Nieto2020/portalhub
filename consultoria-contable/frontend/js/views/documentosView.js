/* 🌟 FUNCIONES UNIVERSALES */
function cambiarTabUniversal(tabActiva, todasLasTabs, seccionMostrar, todasLasSecciones) {
    if (!tabActiva || !seccionMostrar) return;
    todasLasTabs.forEach(tab => tab?.classList.remove('active'));
    todasLasSecciones.forEach(sec => { if (sec) sec.style.display = 'none'; });
    tabActiva.classList.add('active');
    seccionMostrar.style.display = 'block';
}

/* 🌟 NAVEGACIÓN PRINCIPAL ( Subir y repo */
const tabSubir = document.getElementById("tab-subir");
const tabRepo = document.getElementById("tab-repo");
const seccionCargaPadre = document.getElementById("seccion-carga");
const seccionRepo = document.getElementById("seccion-repo");

const tabsPrincipales = [tabSubir, tabRepo];
const seccionesPrincipales = [seccionCargaPadre, seccionRepo];

tabSubir.onclick = () => cambiarTabUniversal(tabSubir, tabsPrincipales, seccionCargaPadre, seccionesPrincipales);
tabRepo.onclick = () => cambiarTabUniversal(tabRepo, tabsPrincipales, seccionRepo, seccionesPrincipales);

/* 🌟 SUB-NAVEGACIÓN */
const btnSubFacturas = document.getElementById("btn-sub-facturas");
const btnSubBancos = document.getElementById("btn-sub-bancos");
const btnSubNomina = document.getElementById("btn-sub-nomina");

const vistaFacturas = document.getElementById("cont-facturas");
const vistaBancos = document.getElementById("cont-bancos");

const vistaNomina = document.getElementById("cont-nomina");


const subTabs = [btnSubFacturas, btnSubBancos, btnSubNomina];
const subSecciones = [vistaFacturas, vistaBancos, vistaNomina];

if (btnSubFacturas) {
    btnSubFacturas.onclick = () => cambiarTabUniversal(btnSubFacturas, subTabs, vistaFacturas, subSecciones);
}

if (btnSubBancos) {
    btnSubBancos.onclick = () => cambiarTabUniversal(btnSubBancos, subTabs, vistaBancos, subSecciones);
}

if(btnSubNomina){
    btnSubNomina.onclick = () =>{
        cambiarTabUniversal(btnSubNomina, subTabs, vistaNomina, subSecciones);
    }
}

/* 🌟 LÓGICA DE CARGA: FACTURAS */
const fileInput = document.getElementById("factura-XML");
const dropzoneText = document.getElementById("dropzone-text");
const btnSubirFactura = document.getElementById("btn-subir-archivo");
const tablaFacturas = document.getElementById("tabla-carga-cuerpo");

if (fileInput) {
    fileInput.onchange = () => {
        if (fileInput.files.length > 0) {
            dropzoneText.innerHTML = `Archivo listo: <strong style="color: #5a73b3;">${fileInput.files[0].name}</strong>`;
        }
    };
}

if (btnSubirFactura) {
    btnSubirFactura.onclick = () => {
        if (fileInput.files.length > 0) {
            const nombre = fileInput.files[0].name;
            const hoy = new Date().toLocaleDateString();

            agregarAlRepositorio(nombre,hoy);
            const fila = document.createElement("tr");
            fila.innerHTML = `
                <td>${nombre}</td>
                <td><span class="status aprobado">✅ Pendiente</span></td>
                <td>
                    <button class="btn-tabla secondary"><i class="bx bx-eye"></i></button>
                    <button class="btn-tabla delete"><i class="bx bx-trash-x"></i></button>
                </td>`;
            tablaFacturas.prepend(fila);
            fileInput.value = "";
            dropzoneText.innerHTML = `label for="factura-XML" class="btn-link">Seleccionar Archivos</label>`;
            alert("¡Factura enviada con éxito!");
        } else {
            alert("Por favor, selecciona un archivo.");
        }
    };
}

/* 🌟 LÓGICA DE CARGA: BANCOS */
const btnSubirBanco = document.getElementById("btn-subir-banco");
const inputBancoFile = document.getElementById("input-banco");
const selectBanco = document.getElementById("select-banco");
const inputMes = document.getElementById("mes-estado");
const tablaBancos = document.getElementById("tabla-bancos-cuerpo");
const archivo_banco = document.getElementById("text-upload-bancos");

if(inputBancoFile){
    try{
        inputBancoFile.onchange = () =>{

            if(inputBancoFile.files.length > 0){
                archivo_banco.innerHTML = `Archivo listo: <strong style="color: #5a73b3;">${inputBancoFile.files[0].name}</strong>`
            }
        }
    }catch(e){
        console.error(`Hubo un error: ${e}`);
    };
};

if (btnSubirBanco) {
    btnSubirBanco.onclick = () => {
        const banco = selectBanco.options[selectBanco.selectedIndex].text;
        const mes = inputMes.value;
        const fecha_creacion_banco = new Date().toLocaleDateString();

        agregarAlRepositorio(`Edo. Cuenta - ${banco}`, fecha_creacion);

        if (inputBancoFile.files.length > 0 && selectBanco.value !== "" && mes !== "") {
            const fila = document.createElement("tr");
            fila.innerHTML = `
                <td><strong>${banco}</strong></td>
                <td>${mes}</td>
                <td><span class="status aprobado">✅ Enviado</span></td>
                <td class="acciones-cell">
                    <button class="btn-tabla secondary"><i class="bx bx-eye"></i></button>
                    <button class="btn-tabla delete"><i class='bx bx-trash'></i></button>
                </td>`;
            tablaBancos.prepend(fila);
            alert("Estado de cuenta enviado correctamente.");
        } else {
            alert("Por favor completa: Banco, Mes y el PDF.");
        }
    };
}

/* 🌟 ELIMINAR REGISTROS */
document.addEventListener('click', (e) => {
    const btnDelete = e.target.closest('.delete');
    if (btnDelete) {
        if (confirm("¿Estás seguro de eliminar este registro?")) {
            btnDelete.closest('tr').remove();
        }
    }
});

/*🌟LOGICA DE CARGA: NOMINA */

const btnSubirNomina = document.getElementById("btn-subir-nomina");
const inputNominaFile = document.getElementById("input-nomina");
const selectTipoNomina = document.getElementById("tipo-nomina");
const inputMesNomina = document.getElementById("periodo-nomina");
const tablaNomina = document.getElementById("tabla-nomina-cuerpo");
const textUploadNomina = document.getElementById("text-upload-nomina");

if(inputNominaFile){
    inputNominaFile.onchange = () =>{
        const cantidad = inputNominaFile.files.length;


        if(cantidad > 0){
            textUploadNomina.innerHTML = `<strong style="color: var(--azul-accion);">${cantidad} archivo(s) seleccionados</strong>. Listo para enviar.`;
        }
    };
}

if(btnSubirNomina){
    btnSubirNomina.onclick = () =>{
        const tipo = selectTipoNomina.options[selectTipoNomina.selectedIndex].text;
        const periodo = inputMesNomina.value;
        const numArchivos = inputNominaFile.files.length;

        const fecha_creacion_nomina = new Date().toLocaleDateString();

        agregarAlRepositorio( `Nómina: ${tipo}`, fecha_creacion_nomina);

        if(numArchivos > 0 && periodo !== ""){
            const fila = document.createElement("tr");

            fila.innerHTML = `
                <td><strong>${tipo}</strong></td>
                <td>${periodo}</td>
                <td><span class="status aprobado" style="background-color: #ebf8ff; color: #3182ce;">✅ Procesando</span></td>
                <td class="acciones-cell">
                    <button class="btn-tabla secondary"><i class="bx bx-eye"></i></button>
                    <button class="btn-tabla delete"><i class='bx bx-trash'></i></button>
                </td>
            `;

            tablaNomina.prepend(fila);

            inputNominaFile.value = "";
            inputMesNomina.value = "";
            textUploadNomina.innerHTML = `   <p id="text-upload-nomina"> 
    <label for="input-nomina" class="btn-link">Explorar Archivos</label>
</p>
<input type="file" id="input-nomina" multiple hidden>`;

            alert("¡Nomina enviada! El contador validara los sellos digitales.");
        } else {

            alert("Por favor, selecciona al menos un archivo y el mes correspondiente.");
        }
    };
}

/*REPOSITORIO */

function agregarAlRepositorio(nombre, fecha) {

    const cuerpoRepo = document.getElementById("tabla-repo-cuerpo");

    if(!cuerpoRepo) return;

    const nuevaFila = document.createElement("tr");

    nuevaFila.innerHTML = `
    <td>${nombre}</td>
        <td>${fecha}</td>
        <td><a href="#" class="link-accion"><i class='bx bx-download'></i> Descargar</a></td>
    `;

    cuerpoRepo.prepend(nuevaFila);
}