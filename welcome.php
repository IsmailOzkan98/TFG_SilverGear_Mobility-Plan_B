<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SilverGear Mobility</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet"> <!-- Tipografias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body> 
    <!-- Contenedores -->
    <div id="header-container"></div>
    <div id="main-container">
        <div id="main-container">

    <section class="bgblock" style="background-image: url('images/backgroundIndex.jpg');">
        <div class="bgblock-content text-center">
            <h1 class="display-5 fw-bold mb-3">Bienvenido a SilverGear Mobility</h1>
            <p class="lead mb-4">
                Tu plataforma confiable para alquilar o comprar el vehículo que necesitas.
            </p>
            <a href="pages/login.php" class="btn btn-custom mt-3">Iniciar</a>
        </div>
    </section>

    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <p class="mb-4 fs-5">
                    En <strong>SilverGear Mobility</strong> queremos que moverte sea más fácil que nunca.
                    Aquí podrás encontrar vehículos disponibles para <strong>alquiler</strong> cuando los necesites,
                    ya sea para un viaje puntual, trabajo o una escapada espontánea.
                </p>

                <p class="mb-4 fs-5">
                    Si estás pensando en adquirir tu próximo vehículo, también contamos con una selección de modelos
                    disponibles para <strong>comprar</strong>, con información clara y un proceso sencillo para ayudarte
                    a tomar la mejor decisión.
                </p>

                <p class="fs-5">
                    Explora nuestras opciones, compara y elige cómo quieres moverte.
                    En <strong>SilverGear Mobility</strong>, tu camino comienza aquí.
                </p>

            </div>
        </div>
    </section>


</div>

    </div>
    <div id="extra-container">
        <div class="divider"></div>

    <div id="multiCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">

            <div class="carousel-item active">
                <div class="d-flex justify-content-center gap-3">
                    <div class="d-flex flex-column align-items-center">
                        <img src="images/0A-Economy.png" class="slider-img" alt="A-Economy">
                        <label>A-Economy</label>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <img src="images/0B-Compact.png" class="slider-img" alt="B-Compact">
                        <label>B-Compact</label>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="d-flex justify-content-center gap-3">
                    <div class="d-flex flex-column align-items-center">
                        <img src="images/0C-Intermediate.png" class="slider-img" alt="C-Intermediate">
                        <label>C-Intermediate</label>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <img src="images/0D-SUV.png" class="slider-img" alt="D-SUV">
                        <label>D-SUV</label>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="d-flex justify-content-center gap-3">
                    <div class="d-flex flex-column align-items-center">
                        <img src="images/0E-Premium.png" class="slider-img" alt="E-Premium">
                        <label>E-Premium</label>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <img src="images/0F-Van.png" class="slider-img" alt="F-Van">
                        <label>F-Van</label>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="d-flex justify-content-center gap-3">
                    <div class="d-flex flex-column align-items-center">
                        <img src="images/0G-Cargo.png" class="slider-img" alt="G-Cargo">
                        <label>G-Cargo</label>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <img src="images/0H-Classic.png" class="slider-img" alt="H-Classic">
                        <label>H-Classic</label>
                    </div>
                </div>
            </div>

        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#multiCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#multiCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    </div>
    <div id="footer-container"></div>

    <!-- JS loader -->
    <!-- <script src="js/loader.js"></script> -->
    <!-- <script src="js/registro.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para showcase -->
    <!--  <script src="js/showcase.js"></script>  -->
    
</body>



</html>