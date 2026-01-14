-- ----------------------
-- Crear base de datos
-- ----------------------
CREATE DATABASE IF NOT EXISTS gestion_vehiculos CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE gestion_vehiculos;

-- ----------------------
-- Tabla: Rol
-- ----------------------
CREATE TABLE IF NOT EXISTS Rol (
    idRol INT AUTO_INCREMENT PRIMARY KEY,
    nombreRol VARCHAR(50) UNIQUE NOT NULL,
    descripcion VARCHAR(255)
);


-- ----------------------
-- Tabla: Usuario
-- ----------------------
CREATE TABLE IF NOT EXISTS Usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    pais VARCHAR(100) NOT NULL,
    codigoPostal VARCHAR(20),
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    fechaNacimiento DATE NOT NULL,
    fechaCarnet DATE NOT NULL,
    sexo VARCHAR(10),
    contrasena VARCHAR(255) NOT NULL,
    idRol INT NOT NULL,
    FOREIGN KEY (idRol) REFERENCES Rol(idRol)
);

-- ----------------------
-- Tabla: Categoria
-- ----------------------
CREATE TABLE IF NOT EXISTS Categoria (
    idCategoria INT AUTO_INCREMENT PRIMARY KEY,
    nombreCategoria VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    incrementoSeguro DECIMAL(5,2) NOT NULL,
    recargoCarnetJoven DECIMAL(5,2) NOT NULL,
    precioBase DECIMAL(10,2) NOT NULL,
    descuentoDia1_3 DECIMAL(5,2) NOT NULL,
    descuentoDia4_6 DECIMAL(5,2) NOT NULL,
    descuentoDia7_10 DECIMAL(5,2) NOT NULL,
    descuentoDia11_19 DECIMAL(5,2) NOT NULL,
    descuentoDia20_mas DECIMAL(5,2) NOT NULL
);

-- ----------------------
-- Tabla: EstadoVehiculo
-- ----------------------
CREATE TABLE IF NOT EXISTS EstadoVehiculo (
    idEstado INT AUTO_INCREMENT PRIMARY KEY,
    nombreEstado VARCHAR(50) UNIQUE NOT NULL
);

-- ----------------------
-- Tabla: Vehiculo
-- ----------------------
CREATE TABLE IF NOT EXISTS Vehiculo (
    idVehiculo INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(20) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anio INT NOT NULL,
    color VARCHAR(20),
    numeroPlazas INT NOT NULL,
    tipoPropulsion VARCHAR(20) NOT NULL,
    transmision VARCHAR(20) NOT NULL,
    idCategoria INT NOT NULL,
    idEstado INT NOT NULL,
    plazaParking INT DEFAULT 0,
    disponibilidad BOOLEAN DEFAULT TRUE,
    kmInicial INT NOT NULL,
    kmActual INT NOT NULL,
    fechaUltimaRevision DATE,
    fechaProximaRevision DATE,
    imagenPrincipal VARCHAR(255),
    precioAdquisicion DECIMAL(10,2),
    fechaAdquisicion DATE,
    contadorReservas INT DEFAULT 0,
    notasInternas TEXT,
    manipuladoPor VARCHAR(20),
    FOREIGN KEY (idCategoria) REFERENCES Categoria(idCategoria),
    FOREIGN KEY (idEstado) REFERENCES EstadoVehiculo(idEstado)
);

-- ----------------------
-- Tabla: Reserva
-- ----------------------
CREATE TABLE IF NOT EXISTS Reserva (
    idReserva INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    idVehiculo INT NOT NULL,
    fechaInicio DATE NOT NULL,
    fechaFin DATE NOT NULL,
    precioDia DECIMAL(10,2) NOT NULL,
    precioTotal DECIMAL(10,2) NOT NULL,
    estadoReserva VARCHAR(50) NOT NULL,
    aplicarSeguro BOOLEAN DEFAULT FALSE,
    recargoCarnetJoven BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario),
    FOREIGN KEY (idVehiculo) REFERENCES Vehiculo(idVehiculo)
);

-- ----------------------
-- Tabla: Incidencia
-- ----------------------
CREATE TABLE IF NOT EXISTS Incidencia (
    idIncidencia INT AUTO_INCREMENT PRIMARY KEY,
    idVehiculo INT NOT NULL,
    idUsuario INT,
    tipoIncidencia VARCHAR(50),
    estadoIncidencia VARCHAR(50) DEFAULT 'Pendiente',
    descripcion TEXT,
    fecha DATE NOT NULL,
    FOREIGN KEY (idVehiculo) REFERENCES Vehiculo(idVehiculo),
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario)
);

-- ----------------------
-- Tabla: Vehiculo_Historial
-- ----------------------
CREATE TABLE IF NOT EXISTS Vehiculo_Historial (
    idHistorial INT AUTO_INCREMENT PRIMARY KEY,
    idVehiculo INT NOT NULL,
    dniTrabajador VARCHAR(20),
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fechaHora DATETIME NOT NULL,
    FOREIGN KEY (idVehiculo) REFERENCES Vehiculo(idVehiculo)
);







USE gestion_vehiculos;

INSERT INTO Categoria 
(nombreCategoria, descripcion, incrementoSeguro, recargoCarnetJoven, precioBase, 
descuentoDia1_3, descuentoDia4_6, descuentoDia7_10, descuentoDia11_19, descuentoDia20_mas)
VALUES
('A – Economy', 'Vehículos pequeños, 2–4 plazas. Bajo consumo, urbanos', 5.00, 20.00, 25.00, 0.00, 5.00, 10.00, 15.00, 20.00),
('B – Compact', 'Sedanes compactos, 4–5 plazas. Más confort', 15.00, 20.00, 30.00, 0.00, 5.00, 10.00, 15.00, 20.00),
('C – Intermediate', 'Sedanes medianos. Más espacio', 25.00, 20.00, 35.00, 0.00, 5.00, 10.00, 15.00, 20.00),
('D – SUV', 'SUV, 5–7 plazas. Posible 4x4', 40.00, 20.00, 50.00, 0.00, 5.00, 10.00, 15.00, 20.00),
('E – Premium', 'Gama alta. Segmento lujo', 55.00, 20.00, 70.00, 0.00, 5.00, 10.00, 15.00, 20.00),
('F – Van', 'Furgonetas 7–9 plazas. Grupos/familias', 45.00, 20.00, 60.00, 0.00, 5.00, 10.00, 15.00, 20.00),
('G – Cargo', 'Furgonetas de carga. Mercancía', 35.00, 20.00, 55.00, 0.00, 5.00, 10.00, 15.00, 20.00),
('H – Classic', 'Vehículos clásicos. Vintage', 65.00, 20.00, 80.00, 0.00, 5.00, 10.00, 15.00, 20.00);


-- Insertar roles iniciales
INSERT INTO Rol (nombreRol, descripcion) VALUES
('admin', 'Rol principal con control total del sistema'),
('cliente', 'Usuario registrado con permisos de cliente'),
('limpieza', 'Encargado de limpieza de vehículos'),
('ventas', 'Encargado de ventas'),
('mecanico', 'Encargado de taller'),
('dropoff', 'Encargado en recibir vehiculos en terminar alquiler'),
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);



INSERT INTO EstadoVehiculo (nombreEstado) VALUES
('LIMPIO'),
('SUCIO'),
('IMPRO'),
('VENTAS'),
('BAJA');


CREATE TABLE IF NOT EXISTS Vehiculo_Imagenes (
    idImagen INT AUTO_INCREMENT PRIMARY KEY,
    idVehiculo INT NOT NULL,
    rutaImagen VARCHAR(255) NOT NULL,
    fechaSubida DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (idVehiculo) REFERENCES Vehiculo(idVehiculo) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS Usuario_Eliminado (
    idUsuarioOriginal INT NOT NULL,
    dni VARCHAR(20) NOT NULL,
    nombre VARCHAR(100),
    apellidos VARCHAR(100),
    fechaEliminacion DATETIME NOT NULL DEFAULT NOW()
);

INSERT INTO Usuario (nombre, apellidos, dni, direccion, ciudad, pais, telefono, email, fechaNacimiento, fechaCarnet, contrasena, idRol)
VALUES ('Usuario', 'Eliminado', '00000000X', 'N/A', 'N/A', 'N/A', '000000000', 'eliminado@system.local', '1900-01-01', '1900-01-01', 'N/A', 2)
ON DUPLICATE KEY UPDATE idUsuario = idUsuario;

ALTER TABLE Vehiculo ADD COLUMN precioVenta DECIMAL(10,2) DEFAULT NULL;

USE gestion_vehiculos;


INSERT INTO EstadoVehiculo (nombreEstado) VALUES
('VENDIDO'),
('ALQUILADO')
ON DUPLICATE KEY UPDATE nombreEstado = nombreEstado;

CREATE TABLE IF NOT EXISTS Compra (
    idCompra INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    idVehiculo INT NOT NULL,
    fechaCompra DATETIME NOT NULL DEFAULT NOW(),
    precio DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario),
    FOREIGN KEY (idVehiculo) REFERENCES Vehiculo(idVehiculo)
);
