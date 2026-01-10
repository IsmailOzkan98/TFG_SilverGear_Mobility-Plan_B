function confirmarEliminar(idUsuario, nombreUsuario) {
    const texto = prompt(
        `¿Eliminar al usuario ${nombreUsuario}?\n\n` +
        "Escribe CONFIRMAR para continuar"
    );

    //proteccion de usuario especial
    if(dniUsuario === "00000000X") {
        alert("Este usuario no puede ser eliminado.");
        return;
    }

    // Cancelar
    if (texto === null) return;

    if (texto !== "CONFIRMAR") {
        alert("Operación cancelada.");
        return;
    }

    // Redirigir a eliminarUser.php con el id
    window.location.href = "../includes/eliminarUser.php?idUsuario=" + idUsuario;
}