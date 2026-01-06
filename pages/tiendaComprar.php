<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin','cliente','limpieza','ventas','mecanico','dropoff']);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comprar Vehiculo Segunda Mano</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet"> <!-- Tipografias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->

    <!-- Estilos CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <!-- Contenedores -->
    <div id="header-container"></div>
    <div id="main-container">
        <div class="container">
            <div class="container-fluid mt-4">
                <div class="row">

                    <div class="col-12 col-md-3 mb-3">
                        <div class="glass p-3" style="min-height: 100%;">

                            <h4 class="mb-3" style="font-family: 'Rajdhani'; letter-spacing: 3px;">
                                FILTROS
                            </h4>

                            <div class="mb-3">
                                <label class="form-label">Ordenar por</label>
                                <select class="form-select" id="ordenamiento">
                                    <option value="">Seleccione</option>
                                    <option value="km">Menos km primero</option>
                                    <option value="precio">Mas barato primero</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Marca</label>
                                <select class="form-select">
                                    <option>Seleccione</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Modelo</label>
                                <select class="form-select">
                                    <option>Seleccione</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-select">
                                    <option>Seleccione</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio</label>
                                <div class="d-flex gap-2">
                                    <input type="number" class="form-control" placeholder="Min">
                                    <input type="number" class="form-control" placeholder="Max">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kilometraje</label>
                                <div class="d-flex gap-2">
                                    <input type="number" class="form-control" placeholder="Min">
                                    <input type="number" class="form-control" placeholder="Max">
                                </div>
                            </div>

                            <button class="btn btn-custom w-100">Aplicar filtros</button>
                        </div>
                    </div>



                    <!-- Vehículos BORRAR MAS ADELANTE -->
                    <div class="col-12 col-md-9">
                        <div class="row g-4">

                            <!-- 10 Tarjetas generadas -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="../images/TestImg/mazda (1).jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Mazda CX-5 Evolution</h6>
                                        <p class="mb-1">SUV</p>
                                        <p class="fw-bold mb-0">27000€</p>
                                        <p class="fw-bold mb-0">61164 km</p>
                                        <br>
                                        <button class="btn btn-custom w-100" onclick="loadPage('pages/articuloCompra.php')">Ver Detalles</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Vehicle 1 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche1.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Audi A4</h6>
                                        <p class="mb-1">Sedán</p>
                                        <p class="fw-bold mb-0">34000€</p>
                                        <p class="fw-bold mb-0">45667 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle 2 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche2.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">BMW X5</h6>
                                        <p class="mb-1">SUV</p>
                                        <p class="fw-bold mb-0">43000€</p>
                                        <p class="fw-bold mb-0">55567 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle 3 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche3.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Mercedes C200</h6>
                                        <p class="mb-1">Sedán</p>
                                        <p class="fw-bold mb-0">33000€</p>
                                        <p class="fw-bold mb-0">2234567 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle 4 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche4.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Honda Civic</h6>
                                        <p class="mb-1">Compacto</p>
                                        <p class="fw-bold mb-0">15000€</p>
                                        <p class="fw-bold mb-0">65467 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle 5 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche5.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Toyota Corolla</h6>
                                        <p class="mb-1">Compacto</p>
                                        <p class="fw-bold mb-0">26000€</p>
                                        <p class="fw-bold mb-0">45567 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle 6 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche6.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Volkswagen Golf</h6>
                                        <p class="mb-1">Compacto</p>
                                        <p class="fw-bold mb-0">12000€</p>
                                        <p class="fw-bold mb-0">453427 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle 7 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche7.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Ford Focus</h6>
                                        <p class="mb-1">Compacto</p>
                                        <p class="fw-bold mb-0">27000€</p>
                                        <p class="fw-bold mb-0">567 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle 8 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche8.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Nissan Qashqai</h6>
                                        <p class="mb-1">SUV</p>
                                        <p class="fw-bold mb-0">22000€</p>
                                        <p class="fw-bold mb-0">34567 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle 9 -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <div class="card h-100 vehicle-card">
                                    <img src="images/TestImg/coche9.jpg" class="card-img-top" alt="">
                                    <div class="card-body">
                                        <h6 class="card-title mb-1">Kia Sportage</h6>
                                        <p class="mb-1">SUV</p>
                                        <p class="fw-bold mb-0">13000€</p>
                                        <p class="fw-bold mb-0">56567 km</p>
                                        <br>
                                        <p class="fw-bold mb-0">NO DISPONIBLE</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="extra-container"></div>
    <div id="footer-container"></div>

    <!-- JS loader -->
    <!-- <script src="js/loader.js"></script> -->
    <!-- <script src="js/registro.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para showcase -->
    <!--  <script src="js/showcase.js"></script>  -->

</body>



</html>