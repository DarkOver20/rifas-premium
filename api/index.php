<?php
require_once __DIR__ . '/admin/includes/config.php';
require_once __DIR__ . '/admin/includes/functions.php';

// Obtener y limpiar la URL solicitada
$request = isset($_GET['url']) ? strtolower(trim($_GET['url'], '/')) : '';


// Sistema de enrutamiento
switch ($request) {
    case '':
        // Página principal (landing)
        require __DIR__ . '/landing.php';
        break;
        
    case 'sys-396/access':
        // Página de login
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        require __DIR__ . '/admin/usuario/login.php';
        break;
        
    case 'dashboard':
        // Panel de administración
        require_admin();
        require __DIR__ . '/admin/dashboard.php';
        break;
    
    case 'eventos':
        // Página de eventos activos
        require_admin();
        require __DIR__ . '/admin/eventosact.php';
        break;
    
    case 'eventos/crear':
         // Página de crear eventos
        require_admin();
        require __DIR__ . '/admin/eventos/nuevo.php';
        break;

    case 'metodos':
        // Página de metodos de pago
        require_admin();
        require __DIR__ . '/admin/metodos_pago/index.php';
        break;

    // case 'metodos/crear':
    //     // Página de crear metodos de pago
    //     require_admin();
    //     require __DIR__ . '/admin/metodos_pago/nuevo.php';
    //     break; 

    
    case 'metodos/tasas':
            // Página de crear metodos de pago
            require_admin();
            require __DIR__ . '/admin/metodos_pago/tasas.php';
            break; 

    // case 'admin/perfil':
    //     // Página de crear metodos de pago
    //     require_admin();
    //     require __DIR__ . '/admin/usuario/perfil.php';
    //     break; 
            
    case 'logout':
        // Cerrar sesión
        require __DIR__ . '/logout.php';
        break;
        
    default:
        // Rutas dinámicas
        if (preg_match('/^evento\/(\d+)\/[a-z0-9-]+$/i', $request, $matches)) {
            // Vista de evento público
            $_GET['id'] = (int)$matches[1];
            require __DIR__ . '/evento2.php';
        }
            
        elseif (preg_match('/^admin\/solicitud\/comprobante-([a-z0-9-]+)$/i', $request, $matches)) {
            // Servir comprobante de pago
            require_admin();
            $nombre_archivo = $matches[1];
            $ruta_comprobante = dirname(__DIR__) . '/comprobante_' . $nombre_archivo;
            
            if (file_exists($ruta_comprobante)) {
                $mime_type = mime_content_type($ruta_comprobante);
                header('Content-Type: ' . $mime_type);
                readfile($ruta_comprobante);
                exit;
            } else {
                header("HTTP/1.0 404 Not Found");
                echo "Comprobante no encontrado";
                exit;
            }}
        elseif (preg_match('/^admin\/solicitud\/(\d+)\/\d+$/i', $request, $matches)) {
            // Detalle de solicitud (admin)
            require_admin();
            $_GET['id'] = (int)$matches[1];
            require __DIR__ . '/admin/solicitudes/detalle2.php';
        }
        
        else {
            // Página no encontrada
            header("HTTP/1.0 404 Not Found");
            require __DIR__ . '/404.php';
        }

        
}

?>