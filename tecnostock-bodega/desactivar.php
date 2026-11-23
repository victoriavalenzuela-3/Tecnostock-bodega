<?php
declare(strict_types=1);
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
requerir_sesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
    http_response_code(400);
    exit('Solicitud inválida o formulario expirado.');
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    flash('danger', 'Producto no válido.');
    header('Location: dashboard.php');
    exit;
}
$consulta = $pdo->prepare('UPDATE productos SET estado = 0 WHERE id = :id AND estado = 1');
$consulta->execute(['id' => $id]);
flash($consulta->rowCount() ? 'success' : 'warning', $consulta->rowCount() ? 'Producto desactivado correctamente.' : 'El producto no existe o ya estaba desactivado.');
header('Location: dashboard.php');
exit;
