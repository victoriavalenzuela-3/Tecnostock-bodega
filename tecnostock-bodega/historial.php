<?php
declare(strict_types=1);
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
requerir_sesion();

$productoId = trim((string) ($_GET['producto_id'] ?? ''));
$sql = 'SELECT m.id, m.tipo, m.cantidad, m.motivo, m.fecha, p.nombre AS producto, p.codigo,
               u.nombre AS usuario
        FROM movimientos m
        INNER JOIN productos p ON p.id = m.producto_id
        INNER JOIN usuarios u ON u.id = m.usuario_id';
$parametros = [];
if ($productoId !== '' && filter_var($productoId, FILTER_VALIDATE_INT) !== false && (int) $productoId > 0) {
    $sql .= ' WHERE m.producto_id = :producto_id';
    $parametros['producto_id'] = (int) $productoId;
}
$sql .= ' ORDER BY m.fecha DESC, m.id DESC';
$consulta = $pdo->prepare($sql);
$consulta->execute($parametros);
$movimientos = $consulta->fetchAll();

$tituloPagina = 'Historial de movimientos';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div><p class="text-teal fw-semibold mb-1">TRAZABILIDAD</p><h1 class="page-title h2 fw-bold mb-1">Historial de movimientos</h1><p class="text-secondary mb-0">Entradas y salidas registradas por el equipo.</p></div>
    <a class="btn btn-primary" href="movimiento_nuevo.php">+ Registrar movimiento</a>
</div>
<section class="card"><div class="card-body p-3 p-lg-4">
    <?php if ($productoId !== ''): ?><div class="mb-3"><a class="link-success" href="historial.php">Limpiar filtro de producto</a></div><?php endif; ?>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Motivo</th><th>Usuario</th></tr></thead>
        <tbody>
        <?php foreach ($movimientos as $movimiento): ?>
            <tr>
                <td class="text-nowrap"><?= e(date('d/m/Y H:i', strtotime($movimiento['fecha']))) ?></td>
                <td><div class="fw-semibold"><?= e($movimiento['producto']) ?></div><small class="text-secondary"><?= e($movimiento['codigo']) ?></small></td>
                <td><span class="badge rounded-pill <?= $movimiento['tipo'] === 'ENTRADA' ? 'text-bg-success' : 'text-bg-primary' ?>"><?= e($movimiento['tipo']) ?></span></td>
                <td class="fw-semibold"><?= (int) $movimiento['cantidad'] ?></td>
                <td><?= e($movimiento['motivo']) ?></td>
                <td><?= e($movimiento['usuario']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$movimientos): ?><tr><td colspan="6" class="empty-state text-center py-5">Aún no hay movimientos para mostrar.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div></section>
<?php require __DIR__ . '/includes/pie.php'; ?>
