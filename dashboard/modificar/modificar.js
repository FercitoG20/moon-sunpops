/**
 * Moon & Sun - Sistema de edición Inline
 * Fernando, este código maneja el cambio visual de texto a input
 */

function activarEdicion(codigo) {
    // 1. Ocultamos el texto estático de los precios
    document.getElementById('txt_costo_' + codigo).style.display = 'none';
    document.getElementById('txt_venta_' + codigo).style.display = 'none';
    
    // 2. Mostramos los campos de entrada (inputs)
    document.getElementById('inp_costo_' + codigo).style.display = 'block';
    document.getElementById('inp_venta_' + codigo).style.display = 'block';
    
    // 3. Intercambiamos los grupos de botones (Editar/Borrar por Guardar/Cancelar)
    document.getElementById('box_v_' + codigo).style.display = 'none';
    document.getElementById('box_e_' + codigo).style.display = 'flex';
}

function cancelarEdicion(codigo) {
    // 1. Mostramos de nuevo el texto estático
    document.getElementById('txt_costo_' + codigo).style.display = 'block';
    document.getElementById('txt_venta_' + codigo).style.display = 'block';
    
    // 2. Ocultamos los inputs
    document.getElementById('inp_costo_' + codigo).style.display = 'none';
    document.getElementById('inp_venta_' + codigo).style.display = 'none';
    
    // 3. Volvemos a los botones de vista normal
    document.getElementById('box_v_' + codigo).style.display = 'flex';
    document.getElementById('box_e_' + codigo).style.display = 'none';
}