<?php require_once '../config/database.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Únete a nosotros - FixItNow</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
    <script src="../services/js/buscador.js" defer></script>
</head>

<style>
    .grid-layout {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        padding: 20px;
        max-width: 1000px;
        margin: 0 auto;
    }

    .option-card {
        background-color: rgba(200, 200, 200, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 15px;
        border-radius: 4px;
        transition: transform 0.2s ease;
        max-width: 300px;
        margin: 0 auto;
    }

    .option-card:hover {
        transform: translateY(-5px);
    }

    .option-image {
        width: 100%;
        max-width: 200px;
        height: auto;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .option-title {
        margin-bottom: 10px;
    }

    .option-title a {
        color: rgb(78, 78, 78);
        text-decoration: none;
        font-size: 1.2rem;
    }

    .document-container3 {
        background-color: transparent !important;
        box-shadow: none;
        margin-top: 40px;
    }

    .document-title {
        font-size: 1.8rem;
        margin-bottom: 15px;
        color: rgb(78, 78, 78);
        text-align: center;
    }

    .option-description {
        font-size: 1rem;
        color: #2c2c2c;
        margin: 0;
    }

    .register-block {
        text-align: center;
        margin-top: 25px;
        margin-bottom: 25px;
    }

    .register-block .option-description {
        font-size: 1rem;
        color: #2c2c2c;
        margin: 0;
    }

    .register-link {
        color: var(--color-primary);
        text-decoration: underline;
        margin-left: 8px;
    }

    @media (max-width: 900px) {
        .grid-layout {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 15px;
        }
    }

    @media (max-width: 600px) {
        .grid-layout {
            grid-template-columns: 1fr;
        }

        .option-card {
            max-width: 250px;
        }

        .document-title {
            font-size: 1.5rem;
            padding: 0 15px;
        }
    }
</style>

<body class="app">
    <header>
        <div class="header-container">
            <div class="logo-container">
                <a href="../index.php">
                    <img src="../media/logo.png" alt="Logo FixItNow" class="logo" style="height: 45px;">
                </a>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar por servicio o localidad..." class="search-input">
                    <img src="../media/lupa.png" alt="Buscar" class="search-icon">
                </div>
            </div>

            <div class="login-profile-box">
                <?php include '../includes/profile_header.php'; ?>
            </div>
        </div>
    </header>

    <main class="app-main">
        <div class="document-container2">
            <h1 class="document-title">Cómo quieres unirte a nosotros?</h1>
        </div>

        <div class="document-container3 grid-layout">
            <div class="option-card">
                <h2 class="option-title"><a href="registro_cliente.php" class="option-link">Cliente</a></h2>
                <br>
                <img src="../media/cliente.jpg" alt="Cliente" class="option-image">
                <br><br>
                <p class="option-description">Busca y contrata servicios</p>
            </div>
            <div class="option-card">
                <h2 class="option-title"><a href="registro_autonomo.php" class="option-link">Autónomo</a></h2>
                <br>
                <img src="../media/autonomo.jpg" alt="Autónomo" class="option-image">
                <br><br>
                <p class="option-description">Ofrece tus servicios profesionales</p>
            </div>
            <div class="option-card">
                <h2 class="option-title"><a href="registro_admin.php" class="option-link">Administrador</a></h2>
                <br>
                <img src="../media/admin.jpg" alt="Administrador" class="option-image">
                <br><br>
                <p class="option-description">Gestiona la plataforma</p>
            </div>
        </div>

        <!-- Bloque de registro debajo de las cards -->
        <div class="register-block">
            <p class="option-description">
                ¿Ya tienes cuenta?
                <a href="../login.php" class="register-link">Inicia Sesión</a>
            </p>
        </div>    </main> 

    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>

</html>