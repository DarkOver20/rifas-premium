<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rifasam');

// Configuración de la aplicación
define('SITE_URL', 'http://localhost/rifas-premium');
define('SITE_NAME', 'Rifas Premium');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
// Iniciar sesión
session_start();

// Manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Funciones de ayuda
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_logged_in() {
    return isset($_SESSION['usuario_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('/login.php');
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['usuario_rol'] !== 'admin') {
        $_SESSION['error'] = 'Acceso denegado. Se requieren privilegios de administrador.';
        redirect('/');
    }
}

// Incluir funciones principales
require_once __DIR__ . '/functions.php';