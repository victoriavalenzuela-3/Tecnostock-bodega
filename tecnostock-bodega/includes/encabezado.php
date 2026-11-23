<?php
$tituloPagina = $tituloPagina ?? 'TecnoStock';
$mensajeFlash = obtener_flash();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina) ?> | TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-tecnostock">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= usuario_autenticado() ? 'dashboard.php' : 'index.php' ?>">
            <span class="brand-mark">T</span> TecnoStock
        </a>
        <?php if (usuario_autenticado()): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <a class="nav-link" href="dashboard.php">Inventario</a>
                    <a class="nav-link" href="producto_nuevo.php">Nuevo producto</a>
                    <a class="nav-link" href="movimiento_nuevo.php">Registrar movimiento</a>
                    <a class="nav-link" href="historial.php">Historial</a>
                    <span class="navbar-text px-lg-2"><?= e($_SESSION['usuario']['nombre']) ?></span>
                    <a class="btn btn-outline-light btn-sm" href="logout.php">Cerrar sesión</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</nav>
<main class="container py-4 py-lg-5">
    <?php if ($mensajeFlash): ?>
        <div class="alert alert-<?= e($mensajeFlash['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= e($mensajeFlash['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>
