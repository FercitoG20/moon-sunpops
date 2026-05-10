function solicitarAnulacion(ventaId, codigoProducto, nombreProducto) {
    Swal.fire({
        title: `⚠️ ¿Anular ${nombreProducto}?`,
        text: `Esto devolverá el stock de este producto al inventario. Ingresa la contraseña de Super Admin:`,
        input: 'password',
        inputAttributes: {
            autocapitalize: 'off',
            placeholder: 'Contraseña de administrador',
            required: 'true'
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Sí, Quitar Producto',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: (password) => {
            if (!password) {
                Swal.showValidationMessage('¡No puedes dejarla en blanco papá!');
                return false;
            }
            
            return fetch('ventas-modificadas/procesar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'eliminar_producto',
                    'venta_id': ventaId,
                    'codigo': codigoProducto,
                    'password': password
                })
            })
            .then(response => {
                if (!response.ok) throw new Error(response.statusText);
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`Fallo la conexión: ${error}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Producto Anulado!',
                    text: result.value.message,
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: result.value.message,
                    confirmButtonColor: '#2d1b14'
                });
            }
        }
    });
}