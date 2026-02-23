<?php
// 1. SEGURIDAD
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); 
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['usuario_id'];
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Lógica para guardar nuevo menú (Asociado al usuario)
if (isset($_POST['btnGuardarMenu'])) {
    $sql = "INSERT INTO menu_bar (fecha, plato_principal, postre, precio, usuario_id) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE plato_principal=VALUES(plato_principal), postre=VALUES(postre), precio=VALUES(precio)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$_POST['fecha'], $_POST['plato'], $_POST['postre'], $_POST['precio'], $user_id]);
    header("Location: bar.php");
    exit();
}

// Obtener menús de los próximos 7 días
$stmt_menus = $db->prepare("SELECT * FROM menu_bar WHERE fecha >= CURDATE() AND (usuario_id = ? OR usuario_id IS NULL) ORDER BY fecha ASC LIMIT 7");
$stmt_menus->execute([$user_id]);
$proximos_menus = $stmt_menus->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Bar | UniMa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .menu-card { border: none; border-radius: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="py-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Comedor UJI 🍏</h2>
        <a href="index.php" class="btn btn-outline-dark rounded-pill">Volver</a>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card menu-card p-4">
                <h5 class="fw-bold mb-3">Registrar Menú</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="small fw-bold">Fecha</label>
                        <input type="date" name="fecha" class="form-control rounded-3" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Plato Principal</label>
                        <input type="text" name="plato" class="form-control rounded-3" placeholder="Ej: Lentejas" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Postre</label>
                        <input type="text" name="postre" class="form-control rounded-3" placeholder="Ej: Fruta de temporada">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Precio (€)</label>
                        <input type="number" step="0.01" name="precio" class="form-control rounded-3" value="5.50">
                    </div>
                    <button type="submit" name="btnGuardarMenu" class="btn btn-success w-100 fw-bold rounded-3">Guardar Menú</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card menu-card p-4 text-center">
                <h5 class="fw-bold mb-4 text-start">Próximos 7 días</h5>
                <?php if(empty($proximos_menus)): ?>
                    <p class="text-muted py-5">No hay menús programados todavía.</p>
                <?php else: ?>
                    <div class="row g-3 text-start">
                        <?php foreach($proximos_menus as $m): ?>
                            <div class="col-12 border-bottom pb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-primary mb-1"><?= $m['fecha'] ?></span>
                                    <span class="fw-bold text-success"><?= $m['precio'] ?>€</span>
                                </div>
                                <p class="mb-0 fw-bold"><?= htmlspecialchars($m['plato_principal']) ?></p>
                                <small class="text-muted">Postre: <?= htmlspecialchars($m['postre']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>