<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

// --- LÓGICA DE EDICIÓN ---
$edit_item = null;
if(isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM examenes WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- GUARDAR CAMBIOS ---
if(isset($_POST['update_full'])) {
    $stmt = $db->prepare("UPDATE examenes SET tipo=?, materia=?, fecha=?, nota_estimada=?, nota_sacada=?, anotaciones=? WHERE id=?");
    $stmt->execute([$_POST['tipo'], $_POST['materia'], $_POST['fecha'], $_POST['n_est'], $_POST['n_sac'], $_POST['anot'], $_POST['id']]);
    header("Location: gestion_evaluables.php"); exit;
}

if(isset($_GET['del'])) { $db->query("DELETE FROM examenes WHERE id = ".(int)$_GET['del']); header("Location: gestion_evaluables.php"); exit; }

// CARGA POR ASIGNATURA
$registros = $db->query("SELECT * FROM examenes WHERE tipo IN ('Examen', 'Trabajo') ORDER BY materia ASC, fecha DESC")->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>UniMa | Gestión Evaluables</title>
    <style>
        body { background: #f1f5f9; padding: 20px; font-family: sans-serif; }
        .subject-header { background: #4338ca; color: white; padding: 10px 20px; border-radius: 15px; margin-top: 30px; font-weight: bold; }
        .eval-card { background: white; border-radius: 15px; padding: 15px; margin-top: 10px; border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .anot-box { background: #f8fafc; padding: 10px; border-radius: 10px; font-size: 0.9rem; border-left: 4px solid #cbd5e1; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">📝 Historial de Notas</h2>
            <a href="index.php" class="btn btn-dark rounded-pill">Volver a UniMa</a>
        </div>

        <?php if($edit_item): ?>
        <div class="card p-4 rounded-4 shadow-sm mb-5 border-0">
            <h5 class="fw-bold mb-3 text-primary">✏️ Editando: <?= $edit_item['materia'] ?></h5>
            <form method="POST" class="row g-3">
                <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                <div class="col-md-3"><label class="small fw-bold">Tipo</label><select name="tipo" class="form-select"><option <?= $edit_item['tipo']=='Examen'?'selected':'' ?>>Examen</option><option <?= $edit_item['tipo']=='Trabajo'?'selected':'' ?>>Trabajo</option></select></div>
                <div class="col-md-5"><label class="small fw-bold">Materia</label><input type="text" name="materia" class="form-control" value="<?= $edit_item['materia'] ?>"></div>
                <div class="col-md-4"><label class="small fw-bold">Fecha</label><input type="date" name="fecha" class="form-control" value="<?= $edit_item['fecha'] ?>"></div>
                <div class="col-md-6"><label class="small fw-bold">Nota Prevista</label><input type="number" step="0.1" name="n_est" class="form-control" value="<?= $edit_item['nota_estimada'] ?>"></div>
                <div class="col-md-6"><label class="small fw-bold">Nota Real</label><input type="number" step="0.1" name="n_sac" class="form-control" value="<?= $edit_item['nota_sacada'] ?>"></div>
                <div class="col-12"><label class="small fw-bold">Anotaciones</label><textarea name="anot" class="form-control" rows="3"><?= $edit_item['anotaciones'] ?></textarea></div>
                <div class="col-12"><button type="submit" name="update_full" class="btn btn-primary w-100 rounded-pill">Guardar Cambios</button></div>
            </form>
        </div>
        <?php endif; ?>

        <?php foreach($registros as $materia => $items): ?>
            <div class="subject-header"><?= $materia ?></div>
            <?php foreach($items as $it): ?>
                <div class="eval-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge <?= $it['tipo']=='Examen'?'bg-danger':'bg-info text-dark' ?> mb-2"><?= $it['tipo'] ?></span>
                            <span class="text-muted ms-2 small">📅 <?= $it['fecha'] ?></span>
                            <div class="mt-1">
                                <b>Prevista:</b> <?= $it['nota_estimada'] ?? '-' ?> | 
                                <b class="text-success">Real:</b> <?= $it['nota_sacada'] ?? 'Pendiente' ?>
                            </div>
                        </div>
                        <div class="btn-group">
                            <a href="?edit=<?= $it['id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                            <a href="?del=<?= $it['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar?')">✕</a>
                        </div>
                    </div>
                    <?php if(!empty($it['anotaciones'])): ?>
                        <div class="anot-box">
                            <b>Anotaciones:</b><br><?= nl2br($it['anotaciones']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>