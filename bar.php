<?php
require_once 'config/database.php';
require_once 'src/Models/Menu.php';

$database = new Database();
$db = $database->getConnection();
$menuModel = new Menu($db);

// Lógica para guardar nuevo menú
if (isset($_POST['btnGuardarMenu'])) {
    $sql = "INSERT INTO menu_bar (fecha, plato_principal, postre, precio) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE plato_principal=VALUES(plato_principal), postre=VALUES(postre), precio=VALUES(precio)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$_POST['fecha'], $_POST['plato'], $_POST['postre'], $_POST['precio']]);
    header("Location: bar.php");
}

// Obtener menús de los próximos 7 días
$sql = "SELECT * FROM menu_bar WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 7";
$proximos_menus = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Bar | UniHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; }
        .menu-card { border: none; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php" class="btn btn-outline-dark rounded-pill">← Volver al Panel</a>
        <h2 class="fw-bold m-0">🍎 Menú de la Universidad</h2>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card menu-card p-4">
                <h5 class="fw-bold mb-3">Añadir Menú</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="small fw-bold">Fecha</label>
                        <input type="date" name="fecha" class="form-control rounded-3" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Plato Principal</label>
                        <input type="text" name="plato" class="form-control rounded-3" placeholder="Ej: Lentejas con chorizo" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Postre</label>
                        <input type="text" name="postre" class="form-control rounded-3" placeholder="Ej: Yogur o Fruta">
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
                                <p class="mb-0 fw-bold"><?= $m['plato_principal'] ?></p>
                                <small class="text-muted">Postre: <?= $m['postre'] ?></small>
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