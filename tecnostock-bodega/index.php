<?php
declare(strict_types=1);
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

if (usuario_autenticado()) {
    header('Location: dashboard.php');
    exit;
}

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $errores[] = 'La sesión del formulario expiró. Recarga e inténtalo nuevamente.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $clave = (string) ($_POST['clave'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $clave === '') {
            $errores[] = 'Ingresa un correo y una contraseña válidos.';
        } else {
            $consulta = $pdo->prepare('SELECT id, nombre, email, clave FROM usuarios WHERE email = :email LIMIT 1');
            $consulta->execute(['email' => $email]);
            $usuario = $consulta->fetch();
            if ($usuario && password_verify($clave, $usuario['clave'])) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = [
                    'id' => (int) $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email'],
                ];
                header('Location: dashboard.php');
                exit;
            }
            $errores[] = 'Correo o contraseña incorrectos.';
        }
    }
}

$tituloPagina = 'Iniciar sesión';
require __DIR__ . '/includes/encabezado.php';
?>
<section class="auth-shell">
    <div class="text-center mb-4">
        <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis px-3 py-2 mb-3">Inventario inteligente</span>
        <h1 class="page-title h2 fw-bold">Bienvenido a TecnoStock</h1>
        <p class="text-secondary">Ingresa para administrar el inventario de tu tienda.</p>
    </div>
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <h2 class="h5 fw-bold mb-4">Iniciar sesión</h2>
            <?php foreach ($errores as $error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endforeach; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(token_csrf()) ?>">
                <div class="mb-3">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input class="form-control" type="email" name="email" id="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="clave">Contraseña</label>
                    <input class="form-control" type="password" name="clave" id="clave" required autocomplete="current-password">
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Entrar al sistema</button>
            </form>
            <p class="text-center text-secondary small mt-4 mb-0">¿Aún no tienes cuenta? <a class="link-success" href="registro.php">Crear usuario</a></p>
        </div>
    </div>
    <p class="small text-center text-secondary mt-3">Demo: admin@tecnostock.cl · tecno123</p>
</section>
<?php require __DIR__ . '/includes/pie.php'; ?>
