/**
 * Moon & Sun - Alertas de confirmación para Pedidos
 */

function confirmarIngreso(titulo) {
    return confirm(`📥 ¿Dar entrada a "${titulo}"?\n\nLas paletas se sumarán a tu inventario.`);
}

function confirmarEliminacion(titulo) {
    return confirm(`🗑️ ¡CUIDADO! ¿Estás seguro de eliminar el pedido "${titulo}"?\n\nEsto borrará todos los cálculos de este pedido y NO se puede deshacer.`);
}

function confirmarReversion(titulo) {
    return confirm(`↩️ ¿Deshacer el ingreso de "${titulo}"?\n\nLas paletas se RESTARÁN de tu inventario actual y el pedido volverá a estado Pendiente. Usa esto solo si te equivocaste al ingresarlo.`);
}