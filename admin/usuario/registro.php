<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $telefono = sanitize($_POST['telefono'] ?? '');
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Todos los campos son requeridos';
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 8) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres';
    } else {
        // Registrar usuario
        $usuario_id = registrarUsuario($nombre, $email, $password, $telefono);
        
        if ($usuario_id) {
            // Autologin
            login_admin($email, $password);
            $_SESSION['exito'] = '¡Registro exitoso! Bienvenido a Rifas Premium';
            redirect('index.php');
        } else {
            $_SESSION['error'] = 'El email ya está registrado';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Rifas Premium</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Crear Cuenta</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono (Opcional)</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="telefono" name="telefono">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </button>
                </div>
                
                <div class="auth-links">
                    <a href="login.php">¿Ya tienes cuenta? Inicia Sesión</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>