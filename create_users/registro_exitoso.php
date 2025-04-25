<?php
$tipo = isset($_GET['tipo']) ? htmlspecialchars($_GET['tipo']) : 'usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f5f5f5;
        }
        
        .success-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .success-icon {
            color: orange;
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .success-message {
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .success-button {
            display: inline-block;
            background-color: orange;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .success-button:hover {
            background-color:rgb(140, 140, 140);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>¡Registro completado con éxito!</h1>
        <br>
        <div class="success-message">
            <p>Tu cuenta como <strong><?= ucfirst($tipo) ?></strong> ha sido creada y está <strong>activa</strong>.</p>
            <p>Ahora puedes iniciar sesión con tus credenciales.</p>
        </div>
        <br>
        <a href="../login.php" class="success-button">Volver al inicio de sesión</a>
    </div>
</body>
</html>