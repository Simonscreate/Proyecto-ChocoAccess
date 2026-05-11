<!DOCTYPE html>
<html lang="es">
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Chocoacceso-Main</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">


    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"> 

    <!-- Iconos de Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Librerias Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- plantilla boostrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- plantilla CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="bg-light">




   


    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top py-lg-0 px-lg-5 wow fadeIn" data-wow-delay="0.1s">
        <a href="index.html" class="navbar-brand ms-4 ms-lg-0">
            <h1 class="text-primary m-0">Chocolates El Rey</h1>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav mx-auto p-4 p-lg-0">
                <a href="index.html" class="nav-item nav-link active">Inicio</a>
                <a href="about.html" class="nav-item nav-link">Sobre Nosotros</a>
                <a href="service.html" class="nav-item nav-link">Servicios</a>
                <a href="product.html" class="nav-item nav-link">Productos</a>
                <a href="contact.html" class="nav-item nav-link">Contacto</a>
            </div>
            <div class=" d-none d-lg-flex">
                <div class="flex-shrink-0 btn-lg-square border border-light rounded-circle">
                    <i class="fa fa-phone text-primary"></i>
                </div>
                <div class="ps-3">
                    <small class="text-primary mb-0">Agenda una Cita</small>
                    <p class="text-light fs-5 mb-0">+58 424 5267187</p>
                </div>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

     <!-- Carousel Start -->
    <div class="container-fluid p-0 pb-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="owl-carousel header-carousel position-relative">
            
            <div class="owl-carousel-item position-relative">
                <img class="img-fluid" src="img/Carrusel2.jpg" alt="">
                <div class="owl-carousel-inner">
                    <div class="container">
                        <div class="row justify-content-start">
                            
                            <!--Login start-->

                            <div class="container-xxl py-6">
                            <div class="container">
                                <div class="row g-5 justify-content-center">
                                    <div class="col-lg-5 col-md-8 wow fadeIn" data-wow-delay="0.1s">
                                        <div class="bg-white rounded p-5 shadow-sm border-top border-primary border-5">
                                            <div class="text-center mb-4">
                                                <h1 class="display-6 text-primary">ChocoAcceso</h1>
                                                <p class="text-dark">Gestión de Acceso | Planta Barquisimeto</p>
                                            </div>
                                            
                                            <form id="accessForm" action="../controllers/AccessController.php" method="POST">
                                                <div class="mb-3">
                                                    
                                                    <input type="text" class="form-control form-control-lg border-2" 
                                                        id="cedula" name="cedula" placeholder="Cedula" required autofocus>
                                                </div>

                                                <div class="mb-3">
                                                    
                                                    <input type="password" class="form-control form-control-lg border-2" 
                                                        id="contrasena" name="contrasena" placeholder="Contraseña" required autofocus>
                                                </div>

                                                <button type="submit" class="btn btn-primary w-100 py-3 fs-5">
                                                    Iniciar Session
                                                </button>
                                            </form>
                                            
                                            <div id="responseMsg" class="mt-4 text-center d-none">
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
    </div>

                            <!--Login End-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->


   



    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>
</html>