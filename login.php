<?php
session_start();
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Añadimos "nombre" a la consulta SQL
    $stmt = $db->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?"); // <--- CAMBIO AQUÍ
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre']; // <--- GUARDAMOS EL NOMBRE EN LA SESIÓN
        header("Location: index.php");
        exit; // Siempre es bueno poner exit después de un header
    } else {
        $error = "Email o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>UniMa | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; }
        .login-card { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 class="fw-bold text-center mb-4">UniMa 🚀</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <input type="email" name="email" class="form-control mb-3 rounded-pill" placeholder="Tu email" required>
            <input type="password" name="password" class="form-control mb-4 rounded-pill" placeholder="Contraseña" required>
            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Entrar</button>
        </form>
        <p class="text-center mt-3 small text-muted">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
    </div>
</body>
</html>