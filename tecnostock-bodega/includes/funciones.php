<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function usuario_autenticado(): bool
{
    return isset($_SESSION['usuario']['id']);
}

function requerir_sesion(): void
{
    if (!usuario_autenticado()) {
        header('Location: index.php');
        exit;
    }
}

function token_csrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_valido(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtener_flash(): ?array
{
    $mensaje = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $mensaje;
}

function validar_producto(array $datos): array
{
    $errores = [];
    if (trim((string) ($datos['nombre'] ?? '')) === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if (trim((string) ($datos['codigo'] ?? '')) === '') {
        $errores[] = 'El código es obligatorio.';
    }
    if (!isset($datos['categoria_id']) || filter_var($datos['categoria_id'], FILTER_VALIDATE_INT) === false
        || (int) $datos['categoria_id'] < 1) {
        $errores[] = 'Selecciona una categoría válida.';
    }
    if (!isset($datos['precio']) || !is_numeric($datos['precio']) || (float) $datos['precio'] < 0) {
        $errores[] = 'El precio debe ser un número igual o mayor que cero.';
    }
    foreach (['stock_actual' => 'actual', 'stock_minimo' => 'mínimo'] as $campo => $etiqueta) {
        if (!isset($datos[$campo]) || filter_var($datos[$campo], FILTER_VALIDATE_INT) === false
            || (int) $datos[$campo] < 0) {
            $errores[] = "El stock {$etiqueta} debe ser un entero igual o mayor que cero.";
        }
    }
    if (strlen(trim((string) ($datos['nombre'] ?? ''))) > 150) {
        $errores[] = 'El nombre no puede superar los 150 caracteres.';
    }
    if (strlen(trim((string) ($datos['codigo'] ?? ''))) > 50) {
        $errores[] = 'El código no puede superar los 50 caracteres.';
    }
    return $errores;
}

function formato_precio(mixed $precio): string
{
    return '$' . number_format((float) $precio, 0, ',', '.');
}
