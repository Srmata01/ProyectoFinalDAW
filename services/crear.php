<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 3) {
    header('Location: ../login.php');
    exit();
}

$id_autonomo = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar primero si el usuario está activo
        $stmt = $pdo->prepare("
            SELECT eu.estado 
            FROM usuarios u
            JOIN estados_usuarios eu ON u.id_estado_usuario = eu.id_estado_usuario
            WHERE u.id_usuario = ?
        ");
        $stmt->execute([$id_autonomo]);
        $usuario = $stmt->fetch();
        
        if (strtolower($usuario['estado']) != 'activo') {
            $error = "No puedes crear servicios porque tu cuenta está inactiva. Contacta con el administrador.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO servicios 
                (id_autonomo, nombre, descripcion, precio, duracion, estado, localidad) 
                VALUES (?, ?, ?, ?, ?, 'activo', ?)
            ");
            
            $stmt->execute([
                $id_autonomo,
                $_POST['nombre'],
                $_POST['descripcion'],
                $_POST['precio'],
                $_POST['duracion'],
                $_POST['localidad']
            ]);
            
            header('Location: ../vistas_usuarios/perfil_autonomo.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al crear el servicio: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Servicio</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../vistas_usuarios/vistas.css">
    <link rel="stylesheet" href="../includes/responsive-header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link rel="icon" type="image/png" href="../media/logo.png">
    <style>
        .app-main {
            position: relative;
            flex: 1;
            background: linear-gradient(-45deg,
                rgba(255, 180, 110, 0.3),
                rgba(255, 220, 150, 0.3),
                rgba(255, 148, 91, 0.3),
                rgba(255, 255, 255, 0.3));
            background-size: 400% 400%;
            animation: moveBackground 8s ease infinite;
        }        .container1 {
            max-width: 600px;
            margin: 15px auto;
            padding: 0 10px;
        }

        .form-container {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--radius-md);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 0 auto;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .document-title {
            color: var(--color-primary);
            font-size: 1.3rem;
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
        }        .form-grid {
            display: grid;
            gap: 8px;
        }

        .form-row {
            display: grid;
            gap: 6px;
            margin-bottom: 0;
        }

        .form-row label {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
        }.form-row label span {
            display: block;
            margin-bottom: 2px;
            font-weight: 500;
            color: var(--color-text);
            font-size: 0.85rem;
        }.form-row input,
        .form-row textarea {
            width: 190%;
            padding: 8px 12px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            background-color: rgba(255, 255, 255, 0.9);
            transition: border-color 0.3s, box-shadow 0.3s;
            font-size: var(--font-size-sm);
        }

        .form-row input:focus,
        .form-row textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(255, 155, 0, 0.2);
        }        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }.form-actions .submit-btn {
            padding: 8px 14px;
            font-size: 0.85rem;
            min-width: 120px;
            background-color: var(--color-primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
        }        .error-message {
            background-color: rgba(248, 215, 218, 0.9);
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: var(--space-sm);
            margin-bottom: var(--space-md);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
        }

        @media (min-width: 768px) {
            .form-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }        @media (max-width: 768px) {
            .container1 {
                padding: 10px;
                margin-top: 10px;
                margin-bottom: 120px;
            }

            .form-container {
                padding: 10px;
            }

            .form-grid {
                gap: 10px;
            }

            .form-actions {
                flex-direction: column;
                gap: 8px;
            }.form-actions .submit-btn {
                width: 100%;
                margin-top: 8px;
                font-weight: 500;
            }
            .form-row input,
        .form-row textarea {
                width: 170%;
            }
        }
    </style>
</head>
<body class="app">
    <?php
    $base_path = '../';
    include '../includes/header_template.php';
    ?><div class="container1">
        <div class="form-container">
            <h2 class="document-title">Crear Nuevo Servicio</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
              <form method="post" class="form-grid">
                <div class="form-row">
                    <label>
                        <span>Nombre del servicio:</span>
                        <input type="text" name="nombre" required placeholder="Ej: Reformas de baños">
                    </label>
                </div>
                <div class="form-row">
                    <label>
                        <span>Descripción:</span>
                        <textarea name="descripcion" required rows="3" placeholder="Describe los detalles del servicio..."></textarea>
                    </label>
                </div>
                <div class="form-row">
                        <label>
                            <span>Precio (€):</span>
                            <input type="number" step="0.01" name="precio" required placeholder="Ej: 50.00">
                        </label>
                </div>
                <div class="form-row">
                        <label>
                            <span>Duración (minutos):</span>
                            <input type="number" name="duracion" required placeholder="Ej: 60">
                        </label>
                </div>
                <div class="form-row">
                    <label>
                        <span>Localidad:</span>
                        <input type="text" name="localidad" required placeholder="Ej: Madrid">
                    </label>
                </div>
                  <div class="form-actions">
                    <button type="submit" class="submit-btn">Crear Servicio</button>
                    <a href="../vistas_usuarios/perfil_autonomo.php" class="submit-btn" style="background-color: #6c757d !important;">Cancelar</a>
                </div>
            </form>
        </div>
    </div>    <?php 
    // Definir la ruta base para el footer
    $base_path = '../';
    include '../includes/footer.php'; 
    ?>
</body>
</html>