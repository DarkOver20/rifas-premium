<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar datos usando filter_input con filtros actuales
    $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $slogan = trim(filter_input(INPUT_POST, 'slogan', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW));
    $precio_boleto = floatval(filter_input(INPUT_POST, 'precio_boleto', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
    $total_boletos = intval(filter_input(INPUT_POST, 'total_boletos', FILTER_SANITIZE_NUMBER_INT));
    $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_UNSAFE_RAW);
    $fecha_fin = filter_input(INPUT_POST, 'fecha_fin', FILTER_UNSAFE_RAW);
    $premio_principal = trim(filter_input(INPUT_POST, 'premio_principal', FILTER_UNSAFE_RAW));
    $estado = filter_input(INPUT_POST, 'estado', FILTER_UNSAFE_RAW);
    
    // Validación de datos
    $errores = [];
    
    if (empty($titulo)) {
        $errores['titulo'] = 'El título es requerido';
    }
    
    if ($precio_boleto <= 0) {
        $errores['precio_boleto'] = 'El precio debe ser mayor a 0';
    }
    
    if ($total_boletos <= 0) {
        $errores['total_boletos'] = 'Debe haber al menos 1 boleto';
    } elseif ($total_boletos > 100000) {
        $errores['total_boletos'] = 'Máximo 100,000 boletos por evento';
    }
    
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        $errores['fechas'] = 'Las fechas son requeridas';
    } elseif (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
        $errores['fechas'] = 'La fecha de fin debe ser posterior a la de inicio';
    }
    
    // Procesar imagen
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png'];
        
        if (in_array($extension, $extensiones_permitidas)) {
            $nombre_archivo = uniqid('evento_') . '.' . $extension;
            $ruta_destino = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombre_archivo;

            // Ensure the upload directory exists
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $imagen = $nombre_archivo;
                
                // Intentar optimizar si GD está instalado
                if (function_exists('imagecreatefromjpeg')) {
                    optimizarImagen($ruta_destino, 1200, 800);
                }
            } else {
                $errores['imagen'] = 'Error al subir la imagen. Verifica los permisos del directorio.';
                error_log("Error al mover archivo: " . print_r(error_get_last(), true));
            }
        } else {
            $errores['imagen'] = 'Formato de imagen no permitido. Solo se aceptan JPG, JPEG, PNG.';
        }
    } else {
        $errores['imagen'] = 'La imagen es requerida';
    }
    
    if (empty($errores)) {
        $pdo = getDBConnection();
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO eventos 
                (titulo, slogan, descripcion, imagen, precio_boleto, total_boletos, boletos_disponibles, 
                 fecha_inicio, fecha_fin, estado, premio_principal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $titulo, $slogan, $descripcion, $imagen, $precio_boleto, $total_boletos, $total_boletos,
                $fecha_inicio, $fecha_fin, $estado, $premio_principal
            ]);
            
            $evento_id = $pdo->lastInsertId();
            generarBoletosLotes($evento_id, $total_boletos);
            
            $pdo->commit();
            
            $_SESSION['mensaje_exito'] = 'Evento creado exitosamente con ' . number_format($total_boletos) . ' boletos';
            header('Location: ../eventos/');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errores['general'] = 'Error al crear el evento: ' . $e->getMessage();
            error_log("Error al crear evento: " . $e->getMessage());
            
            if (!empty($imagen)) {
                @unlink(UPLOAD_DIR . $imagen);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Nuevo Evento - Panel de Administración</title>
  
  <!-- Preconexión y precarga estratégica -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700&display=swap"></noscript>
  
  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  
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
          }
        }
      }
    }
  </script>
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" media="print" onload="this.media='all'"/>
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
  
  <style>
    :root {
      --primary-color: #0066cc;
      --secondary-color: #222222;
      --accent-color: #fcfcfc;
      --background-color: #121212;
      --text-color: #f8f9fa;
    }
    
    html {
      scroll-behavior: smooth;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--background-color);
      color: var(--text-color);
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
    }
    
    /* Efecto de hover para inputs */
    .form-input {
      transition: all 0.3s ease;
      border-color: #374151;
    }
    
    .form-input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.3);
    }
    
    /* Botón con efecto de brillo */
    .btn-glow {
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    
    .btn-glow::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        to bottom right,
        rgba(255, 255, 255, 0.3),
        rgba(255, 255, 255, 0.1),
        transparent
      );
      transform: rotate(30deg);
      z-index: -1;
      transition: all 0.6s ease;
      opacity: 0;
    }
    
    .btn-glow:hover::before {
      opacity: 1;
      animation: shine 1.5s infinite;
    }
    
    @keyframes shine {
      0% { left: -50%; }
      100% { left: 150%; }
    }
    
    /* Estilos para errores */
    .error-message {
      color: #ef4444;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }
    
    /* Efecto de tarjeta */
    .card {
      background-color: rgba(34, 34, 34, 0.7);
      backdrop-filter: blur(10px);
      border: 1px solid #374151;
      transition: all 0.3s ease;
    }
    
    .card:hover {
      border-color: var(--primary-color);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>
<body class="antialiased min-h-screen bg-background">
  <!-- Barra de navegación -->
  <header class="fixed w-full top-0 left-0 z-50 bg-secondary/90 backdrop-blur-md shadow-lg border-b border-gray-800">
    <div class="container mx-auto px-4 py-3">
      <div class="flex justify-between items-center">
        <a href="../" class="flex items-center gap-2 group" aria-label="Bólidos Rifas">
          <img src="https://storage.googleapis.com/a1aa/image/AeamUydK5EmKTfsd6-73yLVqiwQJTSps5dL04l_p_jc.jpg" 
               alt="Logo Bólidos Rifas" 
               class="h-10 w-10 rounded-lg"
               loading="eager">
          <span class="text-2xl font-bold bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">
            Bólidos Rifas
          </span>
        </a>
        
        <div class="flex items-center gap-4">
          <span class="text-white"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
          <a href="../../../logout.php" class="text-gray-300 hover:text-white transition-all">
            <i class="fas fa-sign-out-alt text-xl"></i>
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Contenido principal -->
  <main class="pt-24 pb-12">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto">
        <!-- Encabezado -->
        <div class="text-center mb-10">
          <h1 class="text-3xl md:text-4xl font-bold mb-4">
            <span class="bg-gradient-to-r from-primary to-three bg-clip-text text-transparent">Crear Nuevo</span>
            <span class="text-white">Evento</span>
          </h1>
          <p class="text-xl text-gray-300">
            Completa el formulario para agregar una nueva rifa de autos de lujo.
          </p>
        </div>
        
        <!-- Mensajes de error -->
        <?php if (isset($errores['general'])): ?>
          <div class="bg-danger/20 border-l-4 border-danger text-white p-4 mb-6 rounded-lg">
            <div class="flex items-center gap-3">
              <i class="fas fa-exclamation-circle text-danger"></i>
              <span><?= $errores['general'] ?></span>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Formulario -->
        <form action="" method="POST" enctype="multipart/form-data" class="card rounded-xl p-6 md:p-8 shadow-lg">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Columna izquierda -->
            <div class="space-y-6">
              <!-- Título -->
              <div>
                <label for="titulo" class="block text-gray-300 mb-2 font-medium">
                  Título del Evento <span class="text-danger">*</span>
                </label>
                <input type="text" id="titulo" name="titulo" required
                       class="w-full form-input px-4 py-3 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white placeholder-gray-500"
                       value="<?= isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : '' ?>"
                       placeholder="Ej: Ferrari F8 Tributo 2023">
                <?php if (isset($errores['titulo'])): ?>
                  <p class="error-message"><?= $errores['titulo'] ?></p>
                <?php endif; ?>
              </div>
              
              <!-- Slogan -->
              <div>
                <label for="slogan" class="block text-gray-300 mb-2 font-medium">
                  Slogan (opcional)
                </label>
                <input type="text" id="slogan" name="slogan"
                       class="w-full form-input px-4 py-3 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white placeholder-gray-500"
                       value="<?= isset($_POST['slogan']) ? htmlspecialchars($_POST['slogan']) : '' ?>"
                       placeholder="Ej: El superdeportivo italiano">
              </div>
              
              <!-- Descripción -->
              <div>
                <label for="descripcion" class="block text-gray-300 mb-2 font-medium">
                  Descripción
                </label>
                <textarea id="descripcion" name="descripcion" rows="4"
                          class="w-full form-input px-4 py-3 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white placeholder-gray-500"
                          placeholder="Describe el evento y el auto en detalle"><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
              </div>
              
              <!-- Premio Principal -->
              <div>
                <label for="premio_principal" class="block text-gray-300 mb-2 font-medium">
                  Premio Principal <span class="text-danger">*</span>
                </label>
                <textarea id="premio_principal" name="premio_principal" rows="3" required
                          class="w-full form-input px-4 py-3 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white placeholder-gray-500"
                          placeholder="Detalles del auto que se rifa"><?= isset($_POST['premio_principal']) ? htmlspecialchars($_POST['premio_principal']) : '' ?></textarea>
              </div>
            </div>
            
            <!-- Columna derecha -->
            <div class="space-y-6">
              <!-- Imagen -->
              <div>
                <label for="imagen" class="block text-gray-300 mb-2 font-medium">
                  Imagen del Evento <span class="text-danger">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-700 rounded-lg p-4 text-center">
                  <div id="image-preview" class="mb-4 hidden">
                    <img id="preview" class="max-h-40 mx-auto rounded-lg">
                  </div>
                  <input type="file" id="imagen" name="imagen" accept="image/*" required
                         class="hidden"
                         onchange="previewImage(this)">
                  <label for="imagen" class="cursor-pointer">
                    <div class="flex flex-col items-center justify-center gap-2">
                      <i class="fas fa-cloud-upload-alt text-3xl text-primary"></i>
                      <span class="text-gray-300">Haz clic para subir una imagen</span>
                      <span class="text-sm text-gray-500">Formatos: JPG, PNG. Tamaño recomendado: 1200x800px</span>
                    </div>
                  </label>
                </div>
                <?php if (isset($errores['imagen'])): ?>
                  <p class="error-message"><?= $errores['imagen'] ?></p>
                <?php endif; ?>
              </div>
              
              <!-- Precio y Boletos -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="precio_boleto" class="block text-gray-300 mb-2 font-medium">
                    Precio por boleto <span class="text-danger">*</span>
                  </label>
                  <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-400">$</span>
                    <input type="number" id="precio_boleto" name="precio_boleto" step="0.01" min="0.01" required
                           class="w-full form-input pl-8 pr-4 py-3 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white placeholder-gray-500"
                           value="<?= isset($_POST['precio_boleto']) ? htmlspecialchars($_POST['precio_boleto']) : '' ?>"
                           placeholder="500.00">
                  </div>
                  <?php if (isset($errores['precio_boleto'])): ?>
                    <p class="error-message"><?= $errores['precio_boleto'] ?></p>
                  <?php endif; ?>
                </div>
                
                <div>
                  <label for="total_boletos" class="block text-gray-300 mb-2 font-medium">
                    Total de boletos <span class="text-danger">*</span>
                  </label>
                  <input type="number" id="total_boletos" name="total_boletos" min="1" max="100000" required
                         class="w-full form-input px-4 py-3 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white placeholder-gray-500"
                         value="<?= isset($_POST['total_boletos']) ? htmlspecialchars($_POST['total_boletos']) : '' ?>"
                         placeholder="1000">
                  <?php if (isset($errores['total_boletos'])): ?>
                    <p class="error-message"><?= $errores['total_boletos'] ?></p>
                  <?php endif; ?>
                </div>
              </div>
              
              <!-- Fechas -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div>
                  <label for="fecha_inicio" class="block text-gray-300 mb-2 font-medium">
                    Fecha de inicio <span class="text-danger">*</span>
                  </label>
                  <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required :hover::-webkit-calendar-picker-indicator
                         class="w-full form-input px-4 py-3 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white"
                         value="<?= isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : '' ?>">
                </div>
                
                <div>
                  <label for="fecha_fin" class="block text-gray-300 mb-2 font-medium">
                    Fecha de cierre <span class="text-danger">*</span>
                  </label>
                  <input type="datetime-local" id="fecha_fin" name="fecha_fin" required
                         class="w-full form-input px-4 py-3 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-white"
                         value="<?= isset($_POST['fecha_fin']) ? htmlspecialchars($_POST['fecha_fin']) : '' ?>">
                </div>
              </div>
              <?php if (isset($errores['fechas'])): ?>
                <p class="error-message"><?= $errores['fechas'] ?></p>
              <?php endif; ?>
              
              <!-- Estado -->

            </div>
          </div>
          
          <!-- Botones -->
          <div class="flex flex-col sm:flex-row justify-end gap-4 mt-10">
            <a href="../eventos/" class="px-6 py-3 border border-gray-600 text-gray-300 hover:text-white hover:border-gray-400 rounded-lg transition-all text-center">
              <i class="fas fa-times mr-2"></i> Cancelar
            </a>
            <button type="submit" class="btn-glow bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white px-6 py-3 rounded-lg font-bold shadow-md hover:shadow-lg transition-all duration-300">
              <i class="fas fa-save mr-2"></i> Guardar Evento
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script>
    // Previsualización de imagen
    function previewImage(input) {
      const preview = document.getElementById('preview');
      const imagePreview = document.getElementById('image-preview');
      const file = input.files[0];
      
      if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          preview.src = e.target.result;
          imagePreview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(file);
      }
    }
    
    // Validación de fechas en cliente
    document.addEventListener('DOMContentLoaded', function() {
      const fechaInicio = document.getElementById('fecha_inicio');
      const fechaFin = document.getElementById('fecha_fin');
      
      if (fechaInicio && fechaFin) {
        fechaInicio.addEventListener('change', function() {
          fechaFin.min = this.value;
        });
      }
    });
  </script>
</body>
</html>