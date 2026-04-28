/*Cambio de pestaña */
const tabSubir = document.getElementById("tab-subir");
const tabRepo = document.getElementById("tab-repo");
const seccionCarga = document.getElementById("seccion-carga");
const seccionRepo = document.getElementById("seccion-repo");

function cambiarTab(tabActiva, tabInactiva, seccionMostrar, seccionOcultar){
    tabActiva.classList.add('active');
    tabInactiva.classList.remove('active');
    seccionMostrar.style.display = 'block';
    seccionOcultar.style.display = 'none';
}

tabSubir.addEventListener('click', () =>{
    cambiarTab(tabSubir, tabRepo, seccionCarga, seccionRepo);

});


tabRepo.addEventListener('click', () =>{
    cambiarTab(tabRepo, tabSubir, seccionRepo, seccionCarga);
});

/* carga y subir documento*/

const fileInput = document.getElementById("factura-XML");
const dropzone = document.querySelector(".upload-dropzone");
const dropzoneText = document.getElementById("dropzone-text");

function NombreArchivo(archivo){

    if(archivo){
        dropzoneText.innerHTML = `Archivo listo: <strong style="color: var(--morado);">${archivo.name}</strong>`;
        dropzone.style.borderColor = 'var(--azul-accion)';
    }
}

fileInput.addEventListener("change", () => {
    if (fileInput.files.length > 0) {
        rNombreArchivo(fileInput.files[0]);
    }
});

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault(); 
    dropzone.style.borderColor = 'var(--morado)';
    dropzone.style.backgroundColor = '#f0f4ff';
});


dropzone.addEventListener('dragleave', () => {
    dropzone.style.borderColor = '#b8c2d1';
    dropzone.style.backgroundColor = '#fafbfc';
});

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.style.borderColor = '#b8c2d1';
    dropzone.style.backgroundColor = '#fafbfc';

    const archivos = e.dataTransfer.files;
    if (archivos.length > 0) {
        fileInput.files = archivos; 
        NombreArchivo(archivos[0]);
    }
});


const btnSubir = document.getElementById("btn-subir-archivo");
const tablaCarga = document.getElementById("tabla-carga-cuerpo");

btnSubir.addEventListener("click", () =>{

    if(fileInput.files.length > 0 ){

        const archivo = fileInput.files[0];
        const nombreArchivo = archivo.name;

        const nuevaFila = document.createElement("tr");

        nuevaFila.innerHTML = `<td>${nombreArchivo}</td>
            <td><span class="status" style="color: #ed8936;">⏳ Pendiente</span></td>
            <td class="acciones-cell">
                <button class="btn-tabla secondary"><i class="bx bx-eye" /></i></button>
                <button class="btn-tabla delete"><i class="bx bx-trash-x" /></i></button>
            </td>`;

            tablaCarga.prepend(nuevaFila);

            fileInput.value = "";
            dropzoneText.innerHTML = `Arrastra tus archivos aquí o <label for="factura-XML" class="btn-link">Seleccionar Archivos</label>`;
            dropzone.style.borderColor = '#b8c2d1';
            
            alert("¡Documento enviado con exito! Tu archivo sera revisado pronto.");
    } else{
        alert("Por favor, selecciona un archivo primero.")
    };
});

/*Eliminar */
document.getElementById('tabla-carga-cuerpo').addEventListener('click', (e) =>{

    if(e.target.classList.contains('delete')){
        const fila = e.target.closest('tr');
        const nombreDoc = fila.cells[0].innerText;

        if(confirm(`Deseas eliminar permanentemente ${nombreDoc}?`)){
            fila.remove()
        };
    };
});