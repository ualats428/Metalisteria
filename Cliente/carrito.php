<?php
session_start();
include '../conexion.php'; 

// --- 1. LÓGICA DEL CARRITO (Backend) ---

// Inicializar si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// AÑADIR PRODUCTO (Viene del formulario de infoProducto.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $id = $_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];

    // Buscamos datos frescos en la BD
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        // Si ya existe, sumamos
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
        } else {
            // Si no, añadimos
            $_SESSION['carrito'][$id] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'imagen' => $producto['imagen_url'],
                'referencia' => $producto['referencia'],
                'color' => $producto['color'],
                'medidas' => $producto['medidas'],
                'cantidad' => $cantidad
            ];
        }
    }
    // Recargar para limpiar POST
    header("Location: carrito.php");
    exit;
}

// ELIMINAR PRODUCTO
if (isset($_GET['remove'])) {
    $id_borrar = $_GET['remove'];
    unset($_SESSION['carrito'][$id_borrar]);
    header("Location: carrito.php");
    exit;
}

// CALCULAR TOTAL
$total_carrito = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total_carrito += $item['precio'] * $item['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Compra - Metalistería Fulsan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Source+Sans+Pro:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/carrito.css">
    <script src="../js/auth.js"></script>
</head>
<body>
    <div class="visitante-carrito">
        
        <header class="cabecera">
            <div class="container">
                <div class="logo-main">
                    <a href="index.php" class="logo-link">
                        <img src="../imagenes/logo.png" alt="Logo Metalful">
                        <div class="logo-text">
                            <span>Metalistería</span>
                            <strong>Fulsan</strong>
                        </div>
                    </a>
                </div>

                <nav class="nav-bar">
                    <a href="conocenos.php">Conócenos</a>
                    <a href="productos.php">Productos</a>
                    <a href="carrito.php" class="activo">Carrito</a>
                    <a href="IniciarSesion.php" id="link-login">Iniciar Sesión</a>
                </nav>

                <div class="sign-in" id="box-registro">
                    <a href="registro.php" id="link-registro">Registrarse</a>
                </div>
            </div>
        </header>

        <main class="carrito-section">
            <h1 class="carrito-title">Mi Compra</h1>

            <div class="carrito-container">
                
                <!-- CASO 1: CARRITO VACÍO -->
                <?php if (empty($_SESSION['carrito'])): ?>
                    <div style="text-align: center; padding: 40px; width: 100%;">
                        <p style="font-size: 18px; color: #666;">Tu carrito está vacío.</p>
                        <a href="productos.php" class="btn-comprar" style="display:inline-block; margin-top:20px; text-decoration:none;">Ver Productos</a>
                    </div>
                
                <!-- CASO 2: HAY PRODUCTOS -->
                <?php else: ?>
                    <div class="carrito-items">
                        
                        <!-- BUCLE PHP: Repite este bloque por cada producto -->
                        <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                            <article class="item-card">
                                <!-- Imagen real del producto -->
                                <img src="../<?php echo htmlspecialchars($item['imagen']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['nombre']); ?>" 
                                     class="item-image-placeholder" 
                                     style="object-fit: cover; height: 200px;"
                                     onerror="this.src='https://via.placeholder.com/200?text=Sin+Imagen'">
                                
                                <div class="item-details">
                                    <div class="item-info">
                                        <p class="item-label">Nombre Producto:</p>
                                        <p class="item-value"><?php echo htmlspecialchars($item['nombre']); ?></p>
                                        
                                        <!-- Detalles extra útiles -->
                                        <p class="item-label" style="font-size: 0.85em; margin-top: 5px;">Detalles:</p>
                                        <p class="item-value" style="font-size: 0.85em;">
                                            <?php echo $item['color']; ?> | <?php echo $item['medidas']; ?>
                                        </p>
                                        
                                        <p class="item-label">Precio Unidad:</p>
                                        <p class="item-value"><?php echo number_format($item['precio'], 2); ?>€</p>
                                    </div>

                                    <div class="item-actions">
                                        <!-- Selector de cantidad (Visual por ahora, muestra la cantidad real) -->
                                        <div class="qty-selector">
                                            <!-- Podrías hacer estos botones funcionales más adelante con enlaces PHP -->
                                            <button class="qty-btn btn-menos" disabled>-</button>
                                            <span class="qty-text"><?php echo $item['cantidad']; ?></span>
                                            <button class="qty-btn btn-mas" disabled>+</button>
                                        </div>

                                        <!-- Botón Eliminar (Funcional) -->
                                        <a href="carrito.php?remove=<?php echo $id; ?>" class="btn-eliminar" aria-label="Eliminar producto" style="display:flex; align-items:center; justify-content:center; text-decoration:none; color:inherit;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                                <path d="M17 6h5v2h-2v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8H2V6h5V3a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v3zm1 2H6v12h12V8zm-9 3h2v6H9v-6zm4 0h2v6h-2v-6zM9 4v2h6V4H9z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>

                    </div>

                    <div class="carrito-summary">
                        <!-- Total Calculado -->
                        <h2 class="total-text">Precio Total: <span class="total-amount"><?php echo number_format($total_carrito, 2); ?>€</span></h2>
                        
                        <!-- BOTÓN DE COMPRA INTELIGENTE -->
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <!-- Si está logueado -> TRAMITAR PEDIDO (crea venta en BD) -->
                            <a href="tramitar_pedido.php" class="btn-comprar" style="text-align:center; text-decoration:none; display:block;">
                                Tramitar Pedido
                            </a>
                        <?php else: ?>
                            <!-- Si NO está logueado -> INICIAR SESIÓN (y luego volverá aquí) -->
                            <a href="IniciarSesion.php" class="btn-comprar" style="text-align:center; text-decoration:none; display:block; background-color: #666;">
                                Iniciar Sesión para Comprar
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </main>

        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-logo-section">
                        <div class="logo-footer">
                            <img src="../imagenes/footer.png" alt="Logo Metalful">
                        </div>
                        <div class="redes">
                            <a href="https://www.instagram.com/metalfulsansl/" target="_blank" class="instagram-link">
                                <svg viewBox="0 0 24 24" fill="white">
                                    <path d="M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="footer-links">
                        <div class="enlaces-rapidos">
                            <h3>Enlaces rápidos</h3>
                            <ul>
                                <li><a href="conocenos.php">Conócenos</a></li>
                                <li><a href="productos.php">Productos</a></li>
                                <li><a href="IniciarSesion.php">Iniciar Sesión</a></li>
                            </ul>
                        </div>
                        <div class="contacto-footer">
                            <h3>Contacto</h3>
                            <ul>
                                <li>
                                    <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" /></svg>
                                    <a href="https://www.google.com/maps/place//data=!4m2!3m1!1s0xd71fd00684554b1:0xef4e70ab821a7762?sa=X&ved=1t:8290&ictx=111" target="_blank">Extrarradio Cortijo la Purisima, 2P, 18004 Granada</a>
                                </li>
                                <li>
                                    <svg viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" /></svg>
                                    <a href="tel:652921960">652 921 960</a>
                                </li>
                                <li>
                                    <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" /></svg>
                                    <a href="mailto:metalfulsan@gmail.com">metalfulsan@gmail.com</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <div class="politica-legal">
                        <a href="#aviso-legal">Aviso Legal</a>
                        <span>•</span>
                        <a href="#privacidad">Política de Privacidad</a>
                        <span>•</span>
                        <a href="#cookies">Política de Cookies</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>