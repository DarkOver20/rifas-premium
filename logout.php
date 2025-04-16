<?php
require_once './admin/includes/config.php';


// Limpia todas las variables de sesión
$_SESSION = [];

// Destruye la sesión
session_destroy();

// Redirige al usuario a la página de inicio de sesión
header('Location: /rifas-premium/sys-396/access');
exit;