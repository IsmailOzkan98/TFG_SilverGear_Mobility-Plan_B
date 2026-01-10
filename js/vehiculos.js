
function confirmarBaja(idVehiculo) {
    const texto = prompt(
        "¿Dar de baja el vehiculo?\n\n" +
        "Escribe CONFIRMAR para continuar"
    );


    // cancelar
    if (texto === null) return; 

    if (texto !== "CONFIRMAR") {
        alert("Operacion cancelada.");
        return;
    }

    window.location.href = "darBajaVehiculo.php?idVehiculo=" + idVehiculo;
}
