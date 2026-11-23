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
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $clave = (string) ($_POST['clave'] ?? '');
    $confirmacion = (string) ($_POST['confirmacion'] ?? '');

    if (!csrf_valido()) {
        $errores[] = 'La sesión del formulario expiró. Recarga e inténtalo nuevamente.';
    }
    if ($nombre === '' || strlen($nombre) > 120) {
        $errores[] = 'Ingresa un nombre de hasta 120 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $errores[] = 'Ingresa un correo electrónico válido.';
    }
    if (strlen($clave) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($clave !== $confirmacion) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
        $consulta->execute(['email' => $email]);
        if ($consulta->fetch()) {
            $errores[] = 'Ya existe una cuenta con ese correo.';
        } else {
            $insertar = $pdo->prepare('INSERT INTO usuarios (nombre, email, clave) VALUES (:nombre, :email, :clave)');
            $insertar->execute([
                'nombre' => $nombre,
                'email' => $email,
                'clave' => password_hash($clave, PASSWORD_DEFAULT),
            ]);
            flash('success', 'Cuenta creada. Ya puedes iniciar sesión.');
            header('Location: index.php');
            exit;
        }
    }
}

$tituloPagina = 'Crear cuenta';
require __DIR__ . '/includes/encabezado.php';
?>
<section class="auth-shell">
    <div class="text-center mb-4">
        <h1 class="page-title h2 fw-bold">Crear cuenta</h1>
        <p class="text-secondary">Registra un usuario para administrar TecnoStock.</p>
    </div>
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <?php foreach ($errores as $error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endforeach; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(token_csrf()) ?>">
                <div class="mb-3">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" name="nombre" id="nombre" maxlength="120" required value="<?= e($_POST['nombre'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input class="form-control" type="email" name="email" id="email" maxlength="190" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="clave">Contraseña</label>
                    <input class="form-control" type="password" name="clave" id="clave" minlength="8" required autocomplete="new-password">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="confirmacion">Repetir contraseña</label>
                    <input class="form-control" type="password" name="confirmacion" id="confirmacion" minlength="8" required autocomplete="new-password">
                </div>
                <button class="btn btn-primary w-100" type="submit">Crear cuenta</button>
            </form>
            <p class="text-center text-secondary small mt-4 mb-0"><a class="link-success" href="index.php">Volver a iniciar sesión</a></p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/pie.php'; ?>
