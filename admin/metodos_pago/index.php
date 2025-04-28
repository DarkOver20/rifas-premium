



<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
require_admin();

// Manejar activación/desactivación AJAX
if (isset($_GET['toggle_activo']) && is_numeric($_GET['toggle_activo'])) {
    header('Content-Type: application/json');
    
    try {
        $id = $_GET['toggle_activo'];
        $stmt = $pdo->prepare("UPDATE metodos_pago SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$id]);
        
        // Obtener el nuevo estado
        $stmt = $pdo->prepare("SELECT activo FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        $activo = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true, 
            'message' => $activo ? 'Método activado correctamente' : 'Método desactivado correctamente',
            'activo' => (bool)$activo
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()]);
    }
    exit();
}

// Manejar el envío del formulario (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $tipo_pago_id = isset($_POST['tipo_pago_id']) && $_POST['tipo_pago_id'] ? (int)$_POST['tipo_pago_id'] : null;
    
    // Procesar detalles dinámicos
    $detalles_array = [];
    if (isset($_POST['detalle_nombre']) && is_array($_POST['detalle_nombre'])) {
        for ($i = 0; $i < count($_POST['detalle_nombre']); $i++) {
            $nombre_detalle = trim($_POST['detalle_nombre'][$i]);
            $valor_detalle = trim($_POST['detalle_valor'][$i]);
            if (!empty($nombre_detalle) && !empty($valor_detalle)) {
                $detalles_array[$nombre_detalle] = $valor_detalle;
            }
        }
    }
    $detalles_json = json_encode($detalles_array);

    // Validaciones
    $errores = [];
    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre del método de pago es requerido.';
    }

    // Procesar icono
    $icono = isset($_POST['icono_actual']) ? $_POST['icono_actual'] : '';
    if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = $_FILES['icono']['name'];
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $nombre_base = uniqid('icono_') . '.' . $extension;
        $ruta_destino = UPLOAD_DIR . $nombre_base;
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (move_uploaded_file($_FILES['icono']['tmp_name'], $ruta_destino)) {
                $icono = 'admin/uploads/' . $nombre_base;
                // Eliminar icono anterior si existe
                if (!empty($_POST['icono_actual']) && file_exists(dirname(__DIR__) . '/' . $_POST['icono_actual'])) {
                    unlink(dirname(__DIR__) . '/' . $_POST['icono_actual']);
                }
            } else {
                $errores['icono'] = 'Error al subir el icono.';
            }
        } else {
            $errores['icono'] = 'Formato de icono no válido (solo JPG, JPEG, PNG, GIF, WEBP).';
        }
    }

    if (empty($errores)) {
        try {
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Editar método existente
                $stmt = $pdo->prepare("UPDATE metodos_pago SET nombre = ?, detalles = ?, icono = ?, tipo_pago_id = ? WHERE id = ?");
                $stmt->execute([$nombre, $detalles_json, $icono, $tipo_pago_id, $_POST['id']]);
                $id = $_POST['id'];
            } else {
                // Crear nuevo método
                $stmt = $pdo->prepare("INSERT INTO metodos_pago (nombre, detalles, icono, tipo_pago_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $detalles_json, $icono, $tipo_pago_id]);
                $id = $pdo->lastInsertId();
            }
            
            // Obtener el método completo para devolverlo
            $stmt = $pdo->prepare("SELECT * FROM metodos_pago WHERE id = ?");
            $stmt->execute([$id]);
            $metodo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$metodo) {
                throw new Exception('No se pudo recuperar el método después de guardar');
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => isset($_POST['id']) ? 'Método actualizado con éxito' : 'Método añadido con éxito',
                'data' => $metodo
            ]);
            exit();
            
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
            exit();
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Errores de validación',
            'errors' => $errores
        ]);
        exit();
    }
}

// Obtener métodos de pago
$metodos_pago = obtener_metodos_pago();
$tipos_pago = obtener_tipos_pago();

// Si estamos editando desde URL, cargar los datos
$metodo_actual = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM metodos_pago WHERE id = ?");
    $stmt->execute([$id]);
    $metodo_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$metodo_actual) {
        header("Location: ./metodos");
        exit();
    }
    
    // Convertir detalles JSON a array
    if (!empty($metodo_actual['detalles'])) {
        $detalles_array = json_decode($metodo_actual['detalles'], true);
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métodos de Pago - A&M Recreciones</title>
    
    <!-- Preconexión y precarga estratégica -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap"></noscript>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" media="print" onload="this.media='all'"/>
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#0066cc',
                            dark: '#004999',
                            light: '#3385d6'
                        },
                        secondary: '#222222',
                        accent: '#ffd700',
                        three: '#fcfcfc',
                        background: '#121212',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444'
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        heading: ['Montserrat', 'sans-serif']
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'bounce-slow': 'bounce 2s infinite'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        :root {
            --primary-color: #0066cc;
            --secondary-color: #222222;
            --accent-color: #fcfcfc;
            --background-color: #121212;
            --text-color: #f8f9fa;
            --highlight-color: hsl(0, 0%, 100%);
        }
        
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            transition: all 0.3s ease;
            transform: translateX(-100%);
            z-index: 30;
        }
        
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: none !important;
            }
            main {
                padding-left: 280px !important;
            }
        }
        
        .sidebar-open .sidebar {
            transform: translateX(0);
        }
        
        .sidebar-link {
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .sidebar-link.active {
            background-color: rgba(0, 102, 204, 0.2);
            border-left: 4px solid var(--primary-color);
        }
        
        /* Modal */
        .modal {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* Tablas */
        .data-table {
            min-width: 100%;
        }
        
        .data-table th {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .data-table tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .data-table tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
        
        /* Iconos */
        .method-icon {
            width: 30px;
            height: 30px;
            object-fit: contain;
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
            padding: 2px;
        }
        
        /* Estados */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .status-inactive {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        /* Animaciones */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .notification {
    animation: slideIn 0.3s ease-out, fadeOut 0.5s ease 2.5s forwards;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes fadeOut {
    to { opacity: 0; }
}
    </style>
</head>
<body class="antialiased bg-background text-white">
<!-- Navbar -->
<header class="fixed w-full top-0 left-0 z-40 bg-secondary/90 backdrop-blur-md shadow-lg border-b border-gray-800">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="lg:hidden text-white mr-4">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <a href="/rifas-premium/admin/" class="flex items-center gap-2 group" aria-label="A&M Recreciones">
                    <img src="../uploads/logocolor.webp" 
                         alt="Logo A&M Recreciones" 
                         class="h-10 w-10 rounded-lg"
                         loading="eager">
                    <span class="text-xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
                        A&M Recreciones
                    </span>
                </a>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-sm text-gray-300">Bienvenido,</span>
                    <span class="font-medium"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                </div>
                <a href="/rifas-premium/logout.php" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 transition-all">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar-overlay fixed inset-0 lg:hidden"></div>
    <aside class="sidebar fixed top-0 left-0 h-full bg-secondary shadow-xl overflow-y-auto pt-16">
        <div class="p-4">
            <div class="mb-8 px-4 py-3 bg-primary/10 rounded-lg border border-primary/20">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Panel de Administración</h4>
                        <p class="text-xs text-gray-400">Acceso completo</p>
                    </div>
                </div>
            </div>
            
            <nav class="space-y-1">
                <a href="/rifas-premium/dashboard" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-tachometer-alt w-5 text-center text-gray-400"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="/rifas-premium/eventos/" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-trophy w-5 text-center text-gray-400"></i>
                    <span>Eventos</span>
                </a>
                
                <a href="/rifas-premium/metodos" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-dollar-sign w-5 text-center text-primary"></i>
                    <span>Métodos de Pago</span>
                </a>
                <a href="/rifas-premium/metodos/tasas" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg">
                    <i class="fa-solid fa-money-bill-transfer w-5 text-center text-gray-400"></i>
                    <span>Tasas</span>
                </a>
            </nav>
        </div>
    </aside>
    <!-- Main Content -->
    <main class="relative min-h-screen pt-16 pl-0 lg:pl-[280px] transition-all duration-300">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8 fade-in">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <h1 class="text-2xl md:text-3xl font-bold">
                    <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Metodos de Pago</span>
                    </h1>
                    
                    <button id="abrir-modal" 
                            class="btn-glow bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-md transition-all inline-flex items-center gap-2">
                        <i class="fas fa-plus"></i> Añadir Nuevo
                    </button>
                </div>
                
                <!-- Eliminamos la sección de $_SESSION['success'] ya que usaremos notificaciones AJAX -->
            </div>
            
            <!-- Tabla de métodos -->
            <div class="bg-secondary rounded-xl shadow-lg overflow-hidden fade-in" style="transition-delay: 0.1s">
                <?php if (!empty($metodos_pago)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full data-table">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Icono</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800" id="tabla-metodos">
                                <?php foreach ($metodos_pago as $metodo): ?>
                                    <tr data-id="<?= $metodo['id'] ?>" class="<?= $metodo['activo'] ? '' : 'opacity-50' ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= $metodo['id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                            <?= htmlspecialchars($metodo['nombre']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($metodo['icono'])): ?>
                                                <img src="/rifas-premium/<?= htmlspecialchars($metodo['icono']) ?>" 
                                                     alt="<?= htmlspecialchars($metodo['nombre']) ?>" 
                                                     class="method-icon">
                                            <?php else: ?>
                                                <div class="text-gray-400 text-sm">Sin icono</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge <?= $metodo['activo'] ? 'status-active' : 'status-inactive' ?>">
                                                <?= $metodo['activo'] ? 'Activo' : 'Desactivado' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="editarMetodo(<?= $metodo['id'] ?>)" 
                                                    class="btn-glow bg-primary/10 hover:bg-primary/20 text-primary px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1">
                                                <i class="fas fa-edit text-xs"></i> Editar
                                            </button>
                                            <button onclick="toggleActivoMetodo(<?= $metodo['id'] ?>, this)" 
                                                    class="btn-glow <?= $metodo['activo'] ? 'bg-warning/10 hover:bg-warning/20 text-warning' : 'bg-success/10 hover:bg-success/20 text-success' ?> px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1">
                                                <i class="fas <?= $metodo['activo'] ? 'fa-eye-slash' : 'fa-eye' ?> text-xs"></i> 
                                                <?= $metodo['activo'] ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </div>
                                    </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-400">
                        No hay métodos de pago registrados. 
                        <button id="abrir-modal-vacio" class="text-primary hover:underline">Crear un nuevo método</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

     <!-- Modal para añadir/editar método -->
     <div id="metodo-modal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75">
        <div class="bg-secondary rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white" id="modal-titulo">
                        <?= isset($metodo_actual) ? 'Editar Método de Pago' : 'Añadir Método de Pago' ?>
                    </h3>
                    <button id="cerrar-modal" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="errores-formulario" class="mb-4 hidden p-4 bg-danger/10 border border-danger/20 text-danger rounded-lg">
                    <ul class="list-disc pl-5" id="lista-errores"></ul>
                </div>
                
                <form id="metodo-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= isset($metodo_actual['id']) ? $metodo_actual['id'] : '' ?>">
                    <input type="hidden" name="icono_actual" value="<?= isset($metodo_actual['icono']) ? $metodo_actual['icono'] : '' ?>">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-300 mb-1">Nombre del Método</label>
                            <input type="text" id="nombre" name="nombre" required
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                   value="<?= isset($metodo_actual['nombre']) ? htmlspecialchars($metodo_actual['nombre']) : '' ?>">
                        </div>
                        
                        <div>
                            <label for="tipo_pago_id" class="block text-sm font-medium text-gray-300 mb-1">Tipo de Pago</label>
                            <select id="tipo_pago_id" name="tipo_pago_id"
                                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Dólares (USD) - Sin conversión</option>
                                <?php foreach ($tipos_pago as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>"
                                        <?= (isset($metodo_actual['tipo_pago_id']) && $metodo_actual['tipo_pago_id'] == $tipo['id'] ? 'selected' : '' )?>>
                                        <?= htmlspecialchars($tipo['nombre']) ?> (<?= $tipo['codigo'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Selecciona un tipo de pago si requiere conversión de moneda</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Detalles Adicionales</label>
                            <div id="detalles-container" class="space-y-2 mb-2">
                                <?php if (isset($detalles_array) && !empty($detalles_array)): ?>
                                    <?php foreach ($detalles_array as $nombre => $valor): ?>
                                        <div class="flex space-x-2">
                                            <input type="text" name="detalle_nombre[]" placeholder="Nombre (Ej: Teléfono)"
                                                   class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                                   value="<?= htmlspecialchars($nombre) ?>">
                                            <input type="text" name="detalle_valor[]" placeholder="Valor"
                                                   class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                                   value="<?= htmlspecialchars($valor) ?>">
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="flex space-x-2">
                                        <input type="text" name="detalle_nombre[]" placeholder="Nombre (Ej: Teléfono)"
                                               class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                        <input type="text" name="detalle_valor[]" placeholder="Valor"
                                               class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" id="btn-agregar-detalle" 
                                    class="px-3 py-1 bg-primary/10 hover:bg-primary/20 text-primary text-sm rounded-md transition-all inline-flex items-center">
                                <i class="fas fa-plus mr-1"></i> Añadir otro detalle
                            </button>
                        </div>
                        
                        <div>
                            <label for="icono" class="block text-sm font-medium text-gray-300 mb-1">Icono</label>
                            <input type="file" id="icono" name="icono"
                            
                                   class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                   <div id="icono-preview">
    <?php if (isset($metodo_actual['icono']) && !empty($metodo_actual['icono'])): ?>
        <div class="mt-2 flex items-center">
            <img src="/rifas-premium/<?= htmlspecialchars($metodo_actual['icono']) ?>" 
                 class="h-10 w-10 object-contain bg-gray-800 rounded-md">
            <span class="ml-2 text-sm text-gray-300">Icono actual</span>
        </div>
    <?php endif; ?>
</div>
                                   <p class="mt-1 text-xs text-gray-400">Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP</p>
                            <?php if (isset($metodo_actual['icono']) && !empty($metodo_actual['icono'])): ?>
                                <div class="mt-2 flex items-center">
                                    <img src="/rifas-premium/<?= htmlspecialchars($metodo_actual['icono']) ?>" 
                                         class="h-10 w-10 object-contain bg-gray-800 rounded-md">
                                    <span class="ml-2 text-sm text-gray-300">Icono actual</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" id="cancelar-form" 
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-md transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-md transition-colors">
                            <?= isset($metodo_actual) ? 'Actualizar' : 'Guardar' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle Sidebar
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        const html = document.documentElement;
        
        sidebarToggle.addEventListener('click', () => {
            html.classList.toggle('sidebar-open');
        });
        
        sidebarOverlay.addEventListener('click', () => {
            html.classList.remove('sidebar-open');
        });
        
        // Animación de aparición
        const fadeElements = document.querySelectorAll('.fade-in');
        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        
        fadeElements.forEach(element => {
            fadeInObserver.observe(element);
        });
        
        // Manejo del modal
        const modal = document.getElementById('metodo-modal');
        const abrirModalBtn = document.getElementById('abrir-modal');
        const abrirModalVacioBtn = document.getElementById('abrir-modal-vacio');
        const cerrarModalBtn = document.getElementById('cerrar-modal');
        const cancelarFormBtn = document.getElementById('cancelar-form');
        const detallesContainer = document.getElementById('detalles-container');
        const btnAgregarDetalle = document.getElementById('btn-agregar-detalle');
        const metodoForm = document.getElementById('metodo-form');
        const erroresFormulario = document.getElementById('errores-formulario');
        const listaErrores = document.getElementById('lista-errores');
        
        function toggleModal(abrir = true) {
            if (abrir) {
                modal.classList.add('active');
                document.body.classList.add('overflow-hidden');
                document.getElementById('nombre')?.focus();
            } else {
                modal.classList.remove('active');
                document.body.classList.remove('overflow-hidden');
                limpiarFormulario();
                history.replaceState(null, '', window.location.pathname);
            }
        }
        
        function limpiarFormulario() {
            
            metodoForm.reset();
            metodoForm.querySelector('input[name="id"]').value = '';
            metodoForm.querySelector('input[name="icono_actual"]').value = '';
            document.getElementById('modal-titulo').textContent = 'Añadir Método de Pago';
            detallesContainer.innerHTML = `
                <div class="flex space-x-2">
                    <input type="text" name="detalle_nombre[]" placeholder="Nombre (Ej: Teléfono)"
                           class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                    <input type="text" name="detalle_valor[]" placeholder="Valor"
                           class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            `;
            
            limpiarErrores();
           
            
        }
        
        function limpiarErrores() {
            erroresFormulario.classList.add('hidden');
            listaErrores.innerHTML = '';
            // Limpiar clases de error de los campos
            document.querySelectorAll('.border-danger').forEach(el => {
                el.classList.remove('border-danger');
            });
        }
        
        function mostrarErrores(errors) {
            limpiarErrores();
            listaErrores.innerHTML = Object.values(errors).map(error => 
                `<li>${escapeHtml(error)}</li>`
            ).join('');
            erroresFormulario.classList.remove('hidden');
            
            // Marcar campos con error
            Object.keys(errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('border-danger');
                }
            });
        }
        
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        // Event listeners
        if (abrirModalBtn) abrirModalBtn.addEventListener('click', () => toggleModal(true));
        if (abrirModalVacioBtn) abrirModalVacioBtn.addEventListener('click', () => toggleModal(true));
        cerrarModalBtn.addEventListener('click', () => toggleModal(false));
        cancelarFormBtn.addEventListener('click', () => toggleModal(false));
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                toggleModal(false);
            }
        });
        
        // Añadir campos de detalle dinámicos
        btnAgregarDetalle.addEventListener('click', () => {
            const nuevoDetalle = document.createElement('div');
            nuevoDetalle.className = 'flex space-x-2';
            nuevoDetalle.innerHTML = `
                <input type="text" name="detalle_nombre[]" placeholder="Nombre (Ej: Teléfono)"
                       class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                <input type="text" name="detalle_valor[]" placeholder="Valor"
                       class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
            `;
            detallesContainer.appendChild(nuevoDetalle);
        });
        
       async function editarMetodo(id) {
    try {
        // Mostrar loader o estado de carga
        document.getElementById('modal-titulo').textContent = 'Cargando...';         toggleModal(true); 
        // Obtener los datos del método
        const response = await fetch(`./metodos?editar=${id}`); 
        const result = await response.text(); 

        // Crear un DOM temporal para parsear la respuesta
        const parser = new DOMParser();
        const htmlDoc = parser.parseFromString(result, 'text/html');
        const metodoActual = htmlDoc.querySelector('input[name="id"]')?.value;

        if (!metodoActual) { 
            throw new Error('No se pudo cargar el método'); 
        }

        // Actualizar la URL sin recargar
        history.pushState(null, '', `?editar=${id}`); 
        // Cargar los datos en el formulario
        document.getElementById('modal-titulo').textContent = 'Editar Método de Pago'; 
        document.querySelector('input[name="id"]').value = metodoActual; 
        document.querySelector('input[name="nombre"]').value = htmlDoc.querySelector('input[name="nombre"]')?.value || ''; 

        // Cargar tipo de pago
        const tipoPagoSelect = document.getElementById('tipo_pago_id'); 
        const tipoPagoValue = htmlDoc.querySelector('select[name="tipo_pago_id"]')?.value || ''; 
        if (tipoPagoSelect && tipoPagoValue) { 
            tipoPagoSelect.value = tipoPagoValue; 
        }

        // Cargar icono actual
        const iconoActual = htmlDoc.querySelector('input[name="icono_actual"]')?.value || ''; 
        document.querySelector('input[name="icono_actual"]').value = iconoActual; 

        // Cargar detalles dinámicos
        const detallesContainer = document.getElementById('detalles-container'); 
        detallesContainer.innerHTML = ''; 

        const detallesInputs = htmlDoc.querySelectorAll('[name^="detalle_nombre"]'); 
        if (detallesInputs.length > 0) { 
            detallesInputs.forEach((input, index) => { 
                const nombre = input.value; 
                const valor = htmlDoc.querySelectorAll('[name^="detalle_valor"]')[index]?.value || '';

                if (nombre || valor) { 
                    const div = document.createElement('div'); 
                    div.className = 'flex space-x-2'; 
                    div.innerHTML = `
                        <input type="text" name="detalle_nombre[]" placeholder="Nombre (Ej: Teléfono)"
                               class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary" 
                               value="${escapeHtml(nombre)}"> 
                        <input type="text" name="detalle_valor[]" placeholder="Valor"
                               class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary" 
                               value="${escapeHtml(valor)}"> 
                    `; 
                    detallesContainer.appendChild(div); 
                }
            }); 
        } else {
            // Si no hay detalles, agregar un campo vacío
            detallesContainer.innerHTML = `
                <div class="flex space-x-2">
                    <input type="text" name="detalle_nombre[]" placeholder="Nombre (Ej: Teléfono)"
                           class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary"> 
                    <input type="text" name="detalle_valor[]" placeholder="Valor"
                           class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary"> 
                </div>
            `; 
        }

        // Mostrar imagen actual si existe
        const iconoPreview = document.querySelector('#icono-preview'); 
        if (iconoActual && iconoPreview) { 
            iconoPreview.innerHTML = `
                <div class="mt-2 flex items-center">
                    <img src="/rifas-premium/${escapeHtml(iconoActual)}"
                         class="h-10 w-10 object-contain bg-gray-800 rounded-md"> 
                    <span class="ml-2 text-sm text-gray-300">Icono actual</span> 
                </div>
            `; 
        }

    } catch (error) {
        console.error('Error al cargar método:', error); 
        mostrarNotificacion('error', 'Error al cargar el método para editar'); 
        toggleModal(false); 
    }
}
async function toggleActivoMetodo(id, boton) {
    if (!confirm('¿Estás seguro de que deseas cambiar el estado de este método de pago?')) {
        return;
    }

    try {
        const response = await fetch(`./metodos?toggle_activo=${id}`);
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacion('success', result.message);
            
            // Actualizar la fila
            const fila = document.querySelector(`tr[data-id="${id}"]`);
            if (fila) {
                // Actualizar badge de estado
                const statusBadge = fila.querySelector('.status-badge');
                if (result.activo) {
                    statusBadge.className = 'status-badge status-active';
                    statusBadge.textContent = 'Activo';
                } else {
                    statusBadge.className = 'status-badge status-inactive';
                    statusBadge.textContent = 'Inactivo';
                }
                
                // Actualizar botón
                if (boton) {
                    if (result.activo) {
                        boton.className = 'btn-glow bg-warning/10 hover:bg-warning/20 text-warning px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1';
                        boton.innerHTML = '<i class="fas fa-eye-slash text-xs"></i> Desactivar';
                    } else {
                        boton.className = 'btn-glow bg-success/10 hover:bg-success/20 text-success px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1';
                        boton.innerHTML = '<i class="fas fa-eye text-xs"></i> Activar';
                    }
                }
            }
        } else {
            mostrarNotificacion('error', result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    }
}

// En la función editarMetodo, actualizar el botón de toggle al cargar
async function editarMetodo(id) {
    try {
 // Mostrar loader o estado de carga
        document.getElementById('modal-titulo').textContent = 'Cargando...';
        toggleModal(true);
        
        // Obtener los datos del método
        const response = await fetch(`./metodos?editar=${id}`);
        const result = await response.text();
        
        // Crear un DOM temporal para parsear la respuesta
        const parser = new DOMParser();
        const htmlDoc = parser.parseFromString(result, 'text/html');
        const metodoActual = htmlDoc.querySelector('input[name="id"]')?.value;
        
        if (!metodoActual) {
            throw new Error('No se pudo cargar el método');
        }
        
        // Actualizar la URL sin recargar
        history.pushState(null, '', `?editar=${id}`);
        
        // Cargar los datos en el formulario
        document.getElementById('modal-titulo').textContent = 'Editar Método de Pago';
        document.querySelector('input[name="id"]').value = metodoActual;
        document.querySelector('input[name="nombre"]').value = htmlDoc.querySelector('input[name="nombre"]')?.value || '';
        
        // Cargar tipo de pago
        const tipoPagoSelect = document.getElementById('tipo_pago_id');
        const tipoPagoValue = htmlDoc.querySelector('select[name="tipo_pago_id"]')?.value || '';
        if (tipoPagoSelect && tipoPagoValue) {
            tipoPagoSelect.value = tipoPagoValue;
        }
        
        // Cargar icono actual
        const iconoActual = htmlDoc.querySelector('input[name="icono_actual"]')?.value || '';
        document.querySelector('input[name="icono_actual"]').value = iconoActual;
        
        // Cargar detalles dinámicos
        const detallesContainer = document.getElementById('detalles-container');
        detallesContainer.innerHTML = '';
        
        const detallesInputs = htmlDoc.querySelectorAll('[name^="detalle_nombre"]');
        if (detallesInputs.length > 0) {
            detallesInputs.forEach((input, index) => {
                const nombre = input.value;
                const valor = htmlDoc.querySelectorAll('[name^="detalle_valor"]')[index]?.value || '';
                
                if (nombre || valor) {
                    const div = document.createElement('div');
                    div.className = 'flex space-x-2';
                    div.innerHTML = `
                        <input type="text" name="detalle_nombre[]" placeholder="Nombre (Ej: Teléfono)"
                               class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary"
                               value="${escapeHtml(nombre)}">
                        <input type="text" name="detalle_valor[]" placeholder="Valor"
                               class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary"
                               value="${escapeHtml(valor)}">
                    `;
                    detallesContainer.appendChild(div);
                }
            });
        } else {
            // Si no hay detalles, agregar un campo vacío
            detallesContainer.innerHTML = `
                <div class="flex space-x-2">
                    <input type="text" name="detalle_nombre[]" placeholder="Nombre (Ej: Teléfono)"
                           class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                    <input type="text" name="detalle_valor[]" placeholder="Valor"
                           class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            `;
        }
        
        // Mostrar imagen actual si existe
        const iconoPreview = document.querySelector('#icono-preview');
        if (iconoActual && iconoPreview) {
            iconoPreview.innerHTML = `
                <div class="mt-2 flex items-center">
                    <img src="/rifas-premium/${escapeHtml(iconoActual)}" 
                         class="h-10 w-10 object-contain bg-gray-800 rounded-md">
                    <span class="ml-2 text-sm text-gray-300">Icono actual</span>
                </div>
            `;
        }
        

        // Después de cargar los datos, actualizar el botón de toggle si existe
        const fila = document.querySelector(`tr[data-id="${id}"]`);
        if (fila) {
            const toggleBtn = fila.querySelector('button[onclick^="toggleActivoMetodo"]');
            if (toggleBtn) {
                const activo = htmlDoc.querySelector('.status-badge')?.textContent?.trim() === 'Activo';
                if (activo) {
                    toggleBtn.className = 'btn-glow bg-warning/10 hover:bg-warning/20 text-warning px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1';
                    toggleBtn.innerHTML = '<i class="fas fa-eye-slash text-xs"></i> Desactivar';
                } else {
                    toggleBtn.className = 'btn-glow bg-success/10 hover:bg-success/20 text-success px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1';
                    toggleBtn.innerHTML = '<i class="fas fa-eye text-xs"></i> Activar';
                }
            }
        }
    } catch (error) {
        console.error('Error al cargar método:', error);
        mostrarNotificacion('error', 'Error al cargar el método para editar');
        toggleModal(false);
    }
}

        
        // Función para mostrar notificaciones
        function mostrarNotificacion(tipo, mensaje) {
            const notificacion = document.createElement('div');
            notificacion.className = `fixed top-4 right-4 z-50`;
            
            const contenido = document.createElement('div');
            contenido.className = `p-4 rounded-md shadow-lg text-white ${
                tipo === 'success' ? 'bg-green-500' : 'bg-red-500'
            } animate-fade-in-up`;
            contenido.textContent = mensaje;
            
            notificacion.appendChild(contenido);
            document.body.appendChild(notificacion);
        
            // Eliminar después de 3 segundos
            setTimeout(() => {
                contenido.classList.add('animate-fade-out');
                setTimeout(() => {
                    notificacion.remove();
                }, 300);
            }, 3000);
        }
        
        // Enviar formulario con AJAX
        metodoForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validación básica del lado del cliente
            if (!document.getElementById('nombre').value.trim()) {
            mostrarErrores({ nombre: 'El nombre es requerido' });
            return;
            }
            
            const formData = new FormData(this);
            const isEdit = formData.get('id') !== '';
            
            try {
            const response = await fetch('./metodos' + (isEdit ? `?editar=${formData.get('id')}` : ''), {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                mostrarNotificacion('success', result.message);
                limpiarErrores();
                
                setTimeout(() => {
                toggleModal(false);
                
                if (isEdit) {
            // Actualizar fila existente
            const fila = document.querySelector(`tr[data-id="${result.data.id}"]`);
            if (fila) {
                fila.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200">${result.data.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                    ${escapeHtml(result.data.nombre)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                    ${result.data.icono ? 
                        `<img src="/rifas-premium/${escapeHtml(result.data.icono)}" 
                          class="method-icon">` : 
                        '<div class="text-gray-400 text-sm">Sin icono</div>'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-badge ${result.data.activo ? 'status-active' : 'status-inactive'}">
                        ${result.data.activo ? 'Activo' : 'Inactivo'}
                    </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="editarMetodo(${result.data.id})" 
                            class="btn-glow bg-primary/10 hover:bg-primary/20 text-primary px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1">
                        <i class="fas fa-edit text-xs"></i> Editar
                        </button>
                        <button onclick="toggleActivoMetodo(${result.data.id}, this)" 
                            class="btn-glow ${result.data.activo ? 'bg-warning/10 hover:bg-warning/20 text-warning' : 'bg-success/10 hover:bg-success/20 text-success'} px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1">
                        <i class="fas ${result.data.activo ? 'fa-eye-slash' : 'fa-eye'} text-xs"></i> 
                        ${result.data.activo ? 'Desactivar' : 'Activar'}
                        </button>
                    </div>
                    </td>
                `;
            }
        } else {
            // Añadir nueva fila
            const tbody = document.querySelector('#tabla-metodos');
            if (tbody) {
                // Si estaba vacío, limpiar el mensaje
                if (tbody.querySelector('td[colspan="5"]')) {
                    tbody.innerHTML = '';
                }
                
                const fila = document.createElement('tr');
                fila.dataset.id = result.data.id;
                fila.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200">${result.data.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                    ${escapeHtml(result.data.nombre)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                    ${result.data.icono ? 
                        `<img src="/rifas-premium/${escapeHtml(result.data.icono)}" 
                          class="method-icon">` : 
                        '<div class="text-gray-400 text-sm">Sin icono</div>'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-badge ${result.data.activo ? 'status-active' : 'status-inactive'}">
                        ${result.data.activo ? 'Activo' : 'Inactivo'}
                    </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="editarMetodo(${result.data.id})" 
                            class="btn-glow bg-primary/10 hover:bg-primary/20 text-primary px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1">
                        <i class="fas fa-edit text-xs"></i> Editar
                        </button>
                        <button onclick="toggleActivoMetodo(${result.data.id}, this)" 
                            class="btn-glow ${result.data.activo ? 'bg-warning/10 hover:bg-warning/20 text-warning' : 'bg-success/10 hover:bg-success/20 text-success'} px-3 py-1 rounded-md text-xs transition-all inline-flex items-center gap-1">
                        <i class="fas ${result.data.activo ? 'fa-eye-slash' : 'fa-eye'} text-xs"></i> 
                        ${result.data.activo ? 'Desactivar' : 'Activar'}
                        </button>
                    </div>
                    </td>
                `;
                tbody.prepend(fila);
            }
        }
    }, 500);
} else {
                if (result.errors) {
                mostrarErrores(result.errors);
                } else {
                mostrarNotificacion('error', result.message);
                }
            }
            } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión');
            }
        });
        // Al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('editar')) {
                toggleModal(true);
            }
        });
        
        // Estilos para animaciones
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            .animate-fade-in-up {
                animation: fadeInUp 0.3s ease-out forwards;
            }
            .animate-fade-out {
                animation: fadeOut 0.3s ease-out forwards;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>