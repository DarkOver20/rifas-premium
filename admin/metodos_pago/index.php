<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_login();

$metodos_pago = obtener_metodos_pago(); // Necesitarás crear esta función en functions.php

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    // Realiza la lógica para eliminar el método de pago (con confirmación)
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM metodos_pago WHERE id = ?");
    $stmt->execute([$id_eliminar]);
    header("Location: index.php"); // Redirigir para actualizar la lista
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métodos de Pago - Panel de Administración</title>
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <div class="container">
        <h1>Métodos de Pago</h1>

        <a href="nuevo.php" class="button">Añadir Nuevo Método de Pago</a>

        <?php if (!empty($metodos_pago)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Icono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($metodos_pago as $metodo): ?>
                        <tr>
                            <td><?php echo $metodo['id']; ?></td>
                            <td><?php echo htmlspecialchars($metodo['nombre']); ?></td>
                            <td><?php if (!empty($metodo['icono'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($metodo['icono']); ?>" alt="<?php echo htmlspecialchars($metodo['nombre']); ?>" width="30">
                                <?php else: ?>
                                    Sin icono
                                <?php endif; ?>
                            </td>
                            <td><?php echo $metodo['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                            <td>
                                <a href="editar.php?id=<?php echo $metodo['id']; ?>">Editar</a> |
                                <a href="index.php?eliminar=<?php echo $metodo['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar este método de pago?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay métodos de pago registrados.</p>
        <?php endif; ?>
    </div>
</body>
</html>