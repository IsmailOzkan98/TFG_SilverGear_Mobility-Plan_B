
function confirmarEliminar(idUsuario, nombreUsuario, dniUsuario) {
    console.log("idUsuario:", idUsuario, "nombreUsuario:", nombreUsuario, "dniUsuario:", dniUsuario);

    if(dniUsuario === "00000000X") {
        alert("Este usuario no puede ser eliminado.");
        return;
    }

    const texto = prompt(`¿Eliminar al usuario ${nombreUsuario}?\n\nEscribe CONFIRMAR para continuar`);
    
    if (texto !== "CONFIRMAR") {
        alert("Operación cancelada.");
        return;
    }

    console.log("Redirigiendo a eliminarUser.php?idUsuario=" + idUsuario);
    window.location.href = "../includes/eliminarUser.php?idUsuario=" + encodeURIComponent(idUsuario);
}
