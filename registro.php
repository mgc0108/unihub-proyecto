<?php
session_start();
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Ciframos la clave

    try {
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$nombre, $email, $password])) {
            $mensaje = "<div class='alert alert-success'>¡Cuenta creada! <a href='login.php'>Inicia sesión aquí</a></div>";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Error de duplicado
            $mensaje = "<div class='alert alert-danger'>Este email ya está registrado.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al registrar: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniMa | Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #f8fafc; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        .register-card { 
            background: white; 
            padding: 40px; 
            border-radius: 30px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            width: 100%; 
            max-width: 400px; 
        }
        .fw-800 { font-weight: 800; }
        .btn-primary { background-color: #4338ca; border: none; }
        .btn-primary:hover { background-color: #3730a3; }
    </style>
</head>
<body>

<div class="register-card">
    <h2 class="fw-800 text-center mb-2">Crear Cuenta 🚀</h2>
    <p class="text-center text-muted small mb-4">Únete a UniMa para organizar tus estudios</p>
    
    <?= $mensaje ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">Nombre completo</label>
            <input type="text" name="nombre" class="form-control rounded-pill" placeholder="Lucía P." required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Email UJI / Personal</label>
            <input type="email" name="email" class="form-control rounded-pill" placeholder="nombre@ejemplo.com" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Contraseña</label>
            <input type="password" name="password" class="form-control rounded-pill" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">Registrarme</button>
    </form>
    
    <div class="text-center mt-4">
        <p class="small text-muted">¿Ya tienes cuenta? <a href="login.php" class="text-primary-u fw-bold">Inicia sesión</a></p>
    </div>
</div>

</body>
</html>