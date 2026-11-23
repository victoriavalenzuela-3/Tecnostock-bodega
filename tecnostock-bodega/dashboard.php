<?php
declare(strict_types=1);
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
requerir_sesion();

$busqueda = trim((string) ($_GET['q'] ?? ''));
$categoriaId = trim((string) ($_GET['categoria'] ?? ''));
$categoriasConsulta = $pdo->prepare('SELECT id, nombre FROM categorias ORDER BY nombre');
$categoriasConsulta->execute();
$categorias = $categoriasConsulta->fetchAll();

$totalConsulta = $pdo->prepare('SELECT COUNT(*) FROM productos');
$totalConsulta->execute();
$totalProductos = (int) $totalConsulta->fetchColumn();
$alertasConsulta = $pdo->prepare('SELECT COUNT(*) FROM productos WHERE stock_actual <= stock_minimo');
$alertasConsulta->execute();
$totalAlertas = (int) $alertasConsulta->fetchColumn();

$sql = 'SELECT p.id, p.nombre, p.codigo, p.precio, p.stock_actual, p.stock_minimo, c.nombre AS categoria
        FROM productos p INNER JOIN categorias c ON c.id = p.categoria_id WHERE 1 = 1';
$parametros = [];
if ($busqueda !== '') {
    $sql .= ' AND (p.nombre LIKE :nombre OR p.codigo LIKE :codigo)';
    $parametros['nombre'] = '%' . $busqueda . '%';
    $parametros['codigo'] = '%' . $busqueda . '%';
}
if ($categoriaId !== '' && filter_var($categoriaId, FILTER_VALIDATE_INT) !== false && (int) $categoriaId > 0) {
    $sql .= ' AND p.categoria_id = :categoria_id';
    $parametros['categoria_id'] = (int) $categoriaId;
}
$sql .= ' ORDER BY p.nombre';
$productosConsulta = $pdo->prepare($sql);
$productosConsulta->execute($parametros);
$productos = $productosConsulta->fetchAll();

$tituloPagina = 'Panel de inventario';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
        <p class="text-teal fw-semibold mb-1">RESUMEN DE LA TIENDA</p>
        <h1 class="page-title h2 fw-bold mb-1">Inventario</h1>
        <p class="text-secondary mb-0">Consulta productos y mantén el stock bajo control.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="historial.php">Ver historial</a>
        <a class="btn btn-primary" href="producto_nuevo.php">+ Nuevo producto</a>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card stat-card h-100"><div class="card-body p-4 d-flex align-items-center gap-3">
            <div class="stat-icon">P</div><div><div class="text-secondary small">Productos</div><div class="fs-3 fw-bold"><?= $totalProductos ?></div></div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card h-100"><div class="card-body p-4 d-flex align-items-center gap-3">
            <div class="stat-icon">!</div><div><div class="text-secondary small">Alertas de stock bajo</div><div class="fs-3 fw-bold"><?= $totalAlertas ?></div></div>
        </div></div>
    </div>
</div>
<section class="card">
    <div class="card-body p-3 p-lg-4">
        <form class="row g-2 align-items-end mb-3" method="get">
            <div class="col-md-6">
                <label class="form-label small fw-semibold" for="q">Buscar producto</label>
                <input class="form-control" id="q" name="q" placeholder="Nombre o código" value="<?= e($busqueda) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold" for="categoria">Categoría</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria['id'] ?>" <?= $categoriaId === (string) $categoria['id'] ? 'selected' : '' ?>><?= e($categoria['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit">Filtrar</button></div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Disponibilidad</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><div class="fw-semibold"><?= e($producto['nombre']) ?></div><small class="text-secondary"><?= e($producto['codigo']) ?></small></td>
                        <td><?= e($producto['categoria']) ?></td>
                        <td><?= e(formato_precio($producto['precio'])) ?></td>
                        <td><span class="fw-semibold"><?= (int) $producto['stock_actual'] ?></span><small class="text-secondary"> / mín. <?= (int) $producto['stock_minimo'] ?></small></td>
                        <td>
                            <?php if ((int) $producto['stock_actual'] <= (int) $producto['stock_minimo']): ?>
                                <span class="badge rounded-pill text-bg-warning">Stock bajo</span>
                            <?php else: ?>
                                <span class="badge rounded-pill text-bg-success">Disponible</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap">
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="producto_editar.php?id=<?= (int) $producto['id'] ?>">Editar</a>
                                <a class="btn btn-sm btn-outline-primary" href="historial.php?producto_id=<?= (int) $producto['id'] ?>">Historial</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$productos): ?>
                    <tr><td class="empty-state text-center py-5" colspan="6">No se encontraron productos con estos filtros.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/pie.php'; ?>
