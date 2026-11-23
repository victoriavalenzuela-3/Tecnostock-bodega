<?php
declare(strict_types=1);
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
requerir_sesion();

$productosConsulta = $pdo->prepare('SELECT id, nombre, codigo, stock_actual FROM productos ORDER BY nombre');
$productosConsulta->execute();
$productos = $productosConsulta->fetchAll();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productoId = filter_var($_POST['producto_id'] ?? null, FILTER_VALIDATE_INT);
    $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);
    $tipo = (string) ($_POST['tipo'] ?? '');
    $motivo = trim((string) ($_POST['motivo'] ?? ''));

    if (!csrf_valido()) {
        $errores[] = 'La sesión del formulario expiró. Recarga e inténtalo nuevamente.';
    }
    if ($productoId === false || $productoId < 1) {
        $errores[] = 'Selecciona un producto válido.';
    }
    if ($cantidad === false || $cantidad < 1) {
        $errores[] = 'La cantidad debe ser un entero mayor que cero.';
    }
    if (!in_array($tipo, ['ENTRADA', 'SALIDA'], true)) {
        $errores[] = 'Selecciona un tipo de movimiento válido.';
    }
    if ($motivo === '' || strlen($motivo) > 255) {
        $errores[] = 'El motivo es obligatorio y no puede superar 255 caracteres.';
    }

    if (!$errores) {
        $pdo->beginTransaction();
        try {
            $bloqueo = $pdo->prepare('SELECT id, stock_actual FROM productos WHERE id = :id FOR UPDATE');
            $bloqueo->execute(['id' => $productoId]);
            $producto = $bloqueo->fetch();
            if (!$producto) {
                $errores[] = 'El producto no existe.';
                $pdo->rollBack();
            } elseif ($tipo === 'SALIDA' && $cantidad > (int) $producto['stock_actual']) {
                $errores[] = 'La salida no puede superar el stock actual (' . (int) $producto['stock_actual'] . ').';
                $pdo->rollBack();
            } else {
                $nuevoStock = $tipo === 'ENTRADA'
                    ? (int) $producto['stock_actual'] + $cantidad
                    : (int) $producto['stock_actual'] - $cantidad;
                $actualizar = $pdo->prepare('UPDATE productos SET stock_actual = :stock WHERE id = :id');
                $actualizar->execute(['stock' => $nuevoStock, 'id' => $productoId]);
                $insertar = $pdo->prepare(
                    'INSERT INTO movimientos (producto_id, usuario_id, tipo, cantidad, motivo)
                     VALUES (:producto_id, :usuario_id, :tipo, :cantidad, :motivo)'
                );
                $insertar->execute([
                    'producto_id' => $productoId,
                    'usuario_id' => (int) $_SESSION['usuario']['id'],
                    'tipo' => $tipo,
                    'cantidad' => $cantidad,
                    'motivo' => $motivo,
                ]);
                $pdo->commit();
                flash('success', 'Movimiento registrado. Stock actualizado a ' . $nuevoStock . ' unidades.');
                header('Location: historial.php');
                exit;
            }
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('TecnoStock: error al registrar movimiento: ' . $exception->getMessage());
            $errores[] = 'No se pudo registrar el movimiento. Inténtalo nuevamente.';
        }
    }
}

$tituloPagina = 'Registrar movimiento';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="row justify-content-center"><div class="col-lg-7">
    <div class="mb-4"><a class="text-decoration-none text-secondary" href="dashboard.php">← Volver al inventario</a><h1 class="page-title h2 fw-bold mt-3">Registrar movimiento</h1><p class="text-secondary mb-0">Las entradas y salidas actualizan el stock de forma segura.</p></div>
    <div class="card"><div class="card-body p-4 p-lg-5">
        <?php foreach ($errores as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" id="formMovimiento">
            <input type="hidden" name="csrf_token" value="<?= e(token_csrf()) ?>">
            <div class="mb-3"><label class="form-label" for="producto_id">Producto</label><select class="form-select" name="producto_id" id="producto_id" required><option value="">Selecciona un producto…</option><?php foreach ($productos as $item): ?><option value="<?= (int) $item['id'] ?>" data-stock="<?= (int) $item['stock_actual'] ?>" <?= (string) ($_POST['producto_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>><?= e($item['nombre']) ?> (<?= e($item['codigo']) ?>) · stock: <?= (int) $item['stock_actual'] ?></option><?php endforeach; ?></select><div class="form-text" id="stockDisponible"></div></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="tipo">Tipo</label><select class="form-select" name="tipo" id="tipo" required><option value="">Selecciona…</option><option value="ENTRADA" <?= ($_POST['tipo'] ?? '') === 'ENTRADA' ? 'selected' : '' ?>>Entrada</option><option value="SALIDA" <?= ($_POST['tipo'] ?? '') === 'SALIDA' ? 'selected' : '' ?>>Salida</option></select></div>
                <div class="col-md-6"><label class="form-label" for="cantidad">Cantidad</label><input class="form-control" type="number" name="cantidad" id="cantidad" min="1" step="1" required value="<?= e($_POST['cantidad'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label" for="motivo">Motivo</label><input class="form-control" name="motivo" id="motivo" maxlength="255" required placeholder="Compra, venta, ajuste…" value="<?= e($_POST['motivo'] ?? '') ?>"></div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-light" href="dashboard.php">Cancelar</a><button class="btn btn-primary" type="submit">Guardar movimiento</button></div>
        </form>
    </div></div>
</div></div>
<script>
const selectorProducto = document.getElementById('producto_id');
const selectorTipo = document.getElementById('tipo');
const campoCantidad = document.getElementById('cantidad');
const textoStock = document.getElementById('stockDisponible');
function actualizarLimiteStock() {
    const opcion = selectorProducto.options[selectorProducto.selectedIndex];
    const stock = opcion ? Number(opcion.dataset.stock || 0) : 0;
    textoStock.textContent = opcion && opcion.value ? `Stock actual: ${stock} unidades.` : '';
    if (selectorTipo.value === 'SALIDA' && opcion && opcion.value) {
        campoCantidad.max = String(stock);
        campoCantidad.setCustomValidity(Number(campoCantidad.value) > stock ? 'La salida supera el stock disponible.' : '');
    } else {
        campoCantidad.removeAttribute('max');
        campoCantidad.setCustomValidity('');
    }
}
selectorProducto.addEventListener('change', actualizarLimiteStock);
selectorTipo.addEventListener('change', actualizarLimiteStock);
campoCantidad.addEventListener('input', actualizarLimiteStock);
document.getElementById('formMovimiento').addEventListener('submit', function (event) {
    if (!Number.isInteger(Number(campoCantidad.value)) || Number(campoCantidad.value) < 1 || !this.checkValidity()) {
        event.preventDefault();
        this.reportValidity();
    }
});
actualizarLimiteStock();
</script>
<?php require __DIR__ . '/includes/pie.php'; ?>
