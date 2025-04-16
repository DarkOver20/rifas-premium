<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();

$usuario = obtenerUsuario($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Rifas Premium</title>
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body>
    
    <main class="user-container">
        <div class="user-sidebar">
            <div class="user-avatar">
                <img src="../../uploads/avatars/<?= $usuario['avatar'] ?? 'default.jpg' ?>" alt="Avatar">
                <h3><?= htmlspecialchars($usuario['nombre']) ?></h3>
            </div>
            
            <nav class="user-menu">
                <a href="perfil.php" class="active"><i class="fas fa-user"></i> Mi Perfil</a>
                <a href="mis-rifas.php"><i class="fas fa-ticket-alt"></i> Mis Rifas</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>
        
        <div class="user-main">
            <h1>Mi Perfil</h1>
            
            <div class="user-content">
                <form class="user-form" method="POST" action="actualizar-perfil.php" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="avatar">Foto de Perfil</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                        <input type="password" id="password" name="password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nueva Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
                
            </div>
        </div>
    </main>
    </body>
</html>