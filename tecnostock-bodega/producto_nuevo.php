<?php
declare(strict_types=1);
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
requerir_sesion();

$categoriasConsulta = $pdo->prepare('SELECT id, nombre FROM categorias ORDER BY nombre');
$categoriasConsulta->execute();
$categorias = $categoriasConsulta->fetchAll();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre' => trim((string) ($_POST['nombre'] ?? '')),
        'codigo' => trim((string) ($_POST['codigo'] ?? '')),
        'categoria_id' => $_POST['categoria_id'] ?? '',
        'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
        'precio' => $_POST['precio'] ?? '',
        'stock_actual' => $_POST['stock_actual'] ?? '',
        'stock_minimo' => $_POST['stock_minimo'] ?? '',
    ];
    if (!csrf_valido()) {
        $errores[] = 'La sesión del formulario expiró. Recarga e inténtalo nuevamente.';
    }
    $errores = array_merge($errores, validar_producto($datos));
    if (strlen($datos['descripcion']) > 1000) {
        $errores[] = 'La descripción no puede superar los 1.000 caracteres.';
    }
    if (!$errores) {
        $categoriaCheck = $pdo->prepare('SELECT id FROM categorias WHERE id = :id');
        $categoriaCheck->execute(['id' => (int) $datos['categoria_id']]);
        if (!$categoriaCheck->fetch()) {
            $errores[] = 'La categoría seleccionada no existe.';
        }
        $codigoCheck = $pdo->prepare('SELECT id FROM productos WHERE codigo = :codigo LIMIT 1');
        $codigoCheck->execute(['codigo' => $datos['codigo']]);
        if ($codigoCheck->fetch()) {
            $errores[] = 'Ya existe un producto con ese código.';
        }
    }
    if (!$errores) {
        $insertar = $pdo->prepare(
            'INSERT INTO productos (categoria_id, nombre, codigo, descripcion, precio, stock_actual, stock_minimo)
             VALUES (:categoria_id, :nombre, :codigo, :descripcion, :precio, :stock_actual, :stock_minimo)'
        );
        $insertar->execute([
            'categoria_id' => (int) $datos['categoria_id'],
            'nombre' => $datos['nombre'],
            'codigo' => $datos['codigo'],
            'descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
            'precio' => $datos['precio'],
            'stock_actual' => (int) $datos['stock_actual'],
            'stock_minimo' => (int) $datos['stock_minimo'],
        ]);
        flash('success', 'Producto creado correctamente.');
        header('Location: dashboard.php');
        exit;
    }
}

$tituloPagina = 'Nuevo producto';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="mb-4"><a class="text-decoration-none text-secondary" href="dashboard.php">← Volver al inventario</a><h1 class="page-title h2 fw-bold mt-3">Nuevo producto</h1></div>
    <div class="card"><div class="card-body p-4 p-lg-5">
        <?php foreach ($errores as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" id="formProducto" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(token_csrf()) ?>">
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" maxlength="150" required value="<?= e($_POST['nombre'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label" for="codigo">Código único</label><input class="form-control" id="codigo" name="codigo" maxlength="50" required value="<?= e($_POST['codigo'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label" for="categoria_id">Categoría</label><select class="form-select" id="categoria_id" name="categoria_id" required><option value="">Selecciona…</option><?php foreach ($categorias as $categoria): ?><option value="<?= (int) $categoria['id'] ?>" <?= (string) ($_POST['categoria_id'] ?? '') === (string) $categoria['id'] ? 'selected' : '' ?>><?= e($categoria['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label" for="precio">Precio (CLP)</label><input class="form-control" type="number" id="precio" name="precio" min="0" step="1" required value="<?= e($_POST['precio'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label" for="stock_actual">Stock actual</label><input class="form-control" type="number" id="stock_actual" name="stock_actual" min="0" step="1" required value="<?= e($_POST['stock_actual'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label" for="stock_minimo">Stock mínimo</label><input class="form-control" type="number" id="stock_minimo" name="stock_minimo" min="0" step="1" required value="<?= e($_POST['stock_minimo'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label" for="descripcion">Descripción (opcional)</label><textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="1000"><?= e($_POST['descripcion'] ?? '') ?></textarea></div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-light" href="dashboard.php">Cancelar</a><button class="btn btn-primary" type="submit">Guardar producto</button></div>
        </form>
    </div></div>
</div></div>
<script>
document.getElementById('formProducto').addEventListener('submit', function (event) {
    const form = event.currentTarget;
    const precio = Number(form.precio.value);
    const actual = Number(form.stock_actual.value);
    const minimo = Number(form.stock_minimo.value);
    if (!form.checkValidity() || precio < 0 || actual < 0 || minimo < 0 || !Number.isInteger(actual) || !Number.isInteger(minimo)) {
        event.preventDefault();
        form.classList.add('was-validated');
    }
});
</script>
<?php require __DIR__ . '/includes/pie.php'; ?>
