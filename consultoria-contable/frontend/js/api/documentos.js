/*🌟Subir Documentos */
export async function DocumentosAPI(archivoFisico, nombre_doc, tipoCateogoria) {

    try{
        const formData = new FormData();

        formData.append('archivo', archivoFisico);
        formData.append('nombre', nombre_doc);
        formData.append('categoria', tipoCateogoria);

        const APIDoc = await fetch('', {
            method:'POST',

            body:formData
        });

        const datos = await APIDoc.json();
        return datos;
    }catch(e){

        console.error(`Hubo un error al subir el Documento: ${e}`);

    }
};

/*🌟 Mostrar Documentos */

export async function ObtenerDocumentos() {

    try{

        const APIDoc = await fetch('', {

            method: 'GET',
        });

        const datos = await APIDoc.json();
        return datos;
    }catch(e){
        console.error(`Hubo un error al mostrar los Documentos: ${e}`);
    }
}

/*🌟 Eliminar Documentos */
export async function EliminarDoc(id_documento) {
    
    try{

        const APIDoc = await fetch('', {
            method: 'POST',

            headers:{
                'Content-Type': 'application/json'
            },

            body:JSON.stringify({id:id_documento})
        });

        const datos = await APIDoc.json();

        return datos;
    }catch(e){
        console.error(`Hubo un error al eliminar el documento: ${e}`);
    }
}

/* 🌟Ver Documentos */

export function verDocumentos(rutaArchivo){

    try{
        window.open()
    }catch(e){
        console.error(`Hubo un error al ver el archivo: ${e}`);
    }
}