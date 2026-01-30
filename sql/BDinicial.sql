-- CREACIÓN BASE DE DATOS
CREATE DATABASE IF NOT EXISTS gestion_vehiculos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE gestion_vehiculos;

-- TABLA: Categoria
CREATE TABLE Categoria (
  idCategoria INT AUTO_INCREMENT PRIMARY KEY,
  nombreCategoria VARCHAR(50) NOT NULL,
  descripcion TEXT,
  incrementoSeguro DECIMAL(5,2) NOT NULL,
  recargoCarnetJoven DECIMAL(5,2) NOT NULL,
  precioBase DECIMAL(10,2) NOT NULL,
  descuentoDia1_3 DECIMAL(5,2) NOT NULL,
  descuentoDia4_6 DECIMAL(5,2) NOT NULL,
  descuentoDia7_10 DECIMAL(5,2) NOT NULL,
  descuentoDia11_19 DECIMAL(5,2) NOT NULL,
  descuentoDia20_mas DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB;

-- TABLA: EstadoVehiculo
CREATE TABLE EstadoVehiculo (
  idEstado INT AUTO_INCREMENT PRIMARY KEY,
  nombreEstado VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- TABLA: Rol
CREATE TABLE Rol (
  idRol INT AUTO_INCREMENT PRIMARY KEY,
  nombreRol VARCHAR(50) NOT NULL,
  descripcion VARCHAR(255)
) ENGINE=InnoDB;

-- TABLA: Usuario
CREATE TABLE Usuario (
  idUsuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  dni VARCHAR(20) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  ciudad VARCHAR(100) NOT NULL,
  pais VARCHAR(100) NOT NULL,
  codigoPostal VARCHAR(20),
  telefono VARCHAR(20) NOT NULL,
  email VARCHAR(150) NOT NULL,
  fechaNacimiento DATE NOT NULL,
  fechaCarnet DATE NOT NULL,
  sexo VARCHAR(10),
  contrasena VARCHAR(255) NOT NULL,
  idRol INT NOT NULL,
  FOREIGN KEY (idRol) REFERENCES Rol(idRol)
) ENGINE=InnoDB;

-- TABLA: Vehiculo
CREATE TABLE Vehiculo (
  idVehiculo INT AUTO_INCREMENT PRIMARY KEY,
  matricula VARCHAR(20) NOT NULL,
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
  disponibilidad TINYINT(1) DEFAULT 1,
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
  precioVenta DECIMAL(10,2),
  FOREIGN KEY (idCategoria) REFERENCES Categoria(idCategoria),
  FOREIGN KEY (idEstado) REFERENCES EstadoVehiculo(idEstado)
) ENGINE=InnoDB;

-- TABLA: Reserva
CREATE TABLE Reserva (
  idReserva INT AUTO_INCREMENT PRIMARY KEY,
  idUsuario INT NOT NULL,
  idCategoria VARCHAR(50) NOT NULL,
  marcaSolicitada VARCHAR(50) NOT NULL,
  modeloSolicitado VARCHAR(50) NOT NULL,
  idVehiculo INT,
  matriculaVehiculo VARCHAR(10),
  fechaInicio DATE NOT NULL,
  fechaFin DATE NOT NULL,
  precioDia DECIMAL(10,2) NOT NULL,
  precioTotal DECIMAL(10,2) NOT NULL,
  estado VARCHAR(50) NOT NULL,
  idVehiculoAsignado INT,
  seguro TINYINT(1) DEFAULT 0,
  carnetJoven TINYINT(1) DEFAULT 0,
  FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario),
  FOREIGN KEY (idVehiculo) REFERENCES Vehiculo(idVehiculo)
) ENGINE=InnoDB;

-- TABLA: Compra
CREATE TABLE Compra (
  idCompra INT AUTO_INCREMENT PRIMARY KEY,
  idUsuario INT NOT NULL,
  idVehiculo INT NOT NULL,
  fechaCompra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  precio DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario),
  FOREIGN KEY (idVehiculo) REFERENCES Vehiculo(idVehiculo)
) ENGINE=InnoDB;

-- TABLA: Incidencia
CREATE TABLE Incidencia (
  idIncidencia INT AUTO_INCREMENT PRIMARY KEY,
  idVehiculo INT NOT NULL,
  idUsuario INT,
  tipoIncidencia VARCHAR(50),
  estadoIncidencia VARCHAR(50) DEFAULT 'Pendiente',
  descripcion TEXT,
  fecha DATE NOT NULL,
  FOREIGN KEY (idVehiculo) REFERENCES Vehiculo(idVehiculo),
  FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario)
) ENGINE=InnoDB;

-- TABLA: Penalizacion
CREATE TABLE Penalizacion (
  idPenalizacion INT AUTO_INCREMENT PRIMARY KEY,
  dniCliente VARCHAR(20) NOT NULL,
  matriculaVehiculo VARCHAR(10),
  idReserva INT NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL,
  nota TEXT,
  fechaRegistro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estadoPenalizacion ENUM('PENDIENTE','PAGADO') NOT NULL DEFAULT 'PENDIENTE',
  FOREIGN KEY (idReserva) REFERENCES Reserva(idReserva)
) ENGINE=InnoDB;

-- TABLA: Usuario_Eliminado
CREATE TABLE Usuario_Eliminado (
  idUsuarioOriginal INT NOT NULL,
  dni VARCHAR(20) NOT NULL,
  nombre VARCHAR(100),
  apellidos VARCHAR(100),
  fechaEliminacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
