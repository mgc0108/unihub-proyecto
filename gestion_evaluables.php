<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

// --- 1. LÓGICA DE ACTUALIZACIÓN (Cuando pulsas Guardar Cambios) ---
if(isset($_POST['update_full'])) {
    $stmt = $db->prepare("UPDATE examenes SET tipo=?, materia=?, fecha=?, nota_estimada=?, nota_sacada=?, anotaciones=? WHERE id=?");
    $stmt->execute([
        $_POST['tipo'], 
        $_POST['materia'], 
        $_POST['fecha'], 
        $_POST['n_est'] ?: NULL, 
        $_POST['n_sac'] ?: NULL, 
        $_POST['anot'], 
        $_POST['id']
    ]);
    header("Location: gestion_evaluables.php"); exit;
}

// --- 2. LÓGICA DE BORRADO ---
if(isset($_GET['del'])) { 
    $db->query("DELETE FROM examenes WHERE id = ".(int)$_GET['del']); 
    header("Location: gestion_evaluables.php"); exit; 
}

// --- 3. CARGAR DATOS PARA EDITAR (Cuando pulsas el botón amarillo) ---
$edit_item = null;
if(isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM examenes WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- 4. CARGAR TODO EL HISTORIAL ---
// Agrupamos manualmente para evitar errores de PDO::FETCH_GROUP
$all = $db->query("SELECT * FROM examenes WHERE tipo IN ('Examen', 'Trabajo') ORDER BY materia ASC, fecha DESC")->fetchAll(PDO::FETCH_ASSOC);
$registros = [];
foreach($all as $row) {
    $registros[$row['materia']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>UniMa | Historial de Notas</title>
    <style>
        body { background: #f1f5f9; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        .subject-header { background: #4338ca; color: white; padding: 12px 20px; border-radius: 12px; margin-top: 30px; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .eval-card { background: white; border-radius: 16px; padding: 20px; margin-top: 12px; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: 0.2s; }
        .anot-box { background: #f8fafc; padding: 12px; border-radius: 10px; font-size: 0.9rem; border-left: 4px solid #4338ca; margin-top: 15px; color: #475569; }
        .btn-edit { color: #d97706; border-color: #fcd34d; }
        .btn-edit:hover { background: #fffbeb; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 850px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">📝 Historial de Notas</h2>
            <a href="index.php" class="btn btn-dark rounded-pill px-4">Volver a UniMa</a>
        </div>

        <?php if($edit_item): ?>
        <div class="card p-4 rounded-4 shadow mb-5 border-0" style="border-top: 5px solid #f59e0b !important;">
            <h5 class="fw-bold mb-4">✏️ Editando: <span class="text-warning"><?= htmlspecialchars($edit_item['materia']) ?></span></h5>
            <form method="POST" class="row g-3">
                <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                
                <div class="col-md-4">
                    <label class="small fw-bold mb-1 text-muted">Tipo de Entrega</label>
                    <select name="tipo" class="form-select rounded-3">
                        <option value="Examen" <?= $edit_item['tipo']=='Examen'?'selected':'' ?>>Examen</option>
                        <option value="Trabajo" <?= $edit_item['tipo']=='Trabajo'?'selected':'' ?>>Trabajo</option>
                    </select>
                </div>
                
                <div class="col-md-8">
                    <label class="small fw-bold mb-1 text-muted">Asignatura / Nombre</label>
                    <input type="text" name="materia" class="form-control rounded-3" value="<?= htmlspecialchars($edit_item['materia']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="small fw-bold mb-1 text-muted">Fecha</label>
                    <input type="date" name="fecha" class="form-control rounded-3" value="<?= $edit_item['fecha'] ?>">
                </div>

                <div class="col-md-4">
                    <label class="small fw-bold mb-1 text-muted">Nota Prevista</label>
                    <input type="number" step="0.01" name="n_est" class="form-control rounded-3" value="<?= $edit_item['nota_estimada'] ?>">
                </div>

                <div class="col-md-4">
                    <label class="small fw-bold mb-1 text-muted">Nota Real</label>
                    <input type="number" step="0.01" name="n_sac" class="form-control rounded-3" value="<?= $edit_item['nota_sacada'] ?>">
                </div>

                <div class="col-12">
                    <label class="small fw-bold mb-1 text-muted">Anotaciones</label>
                    <textarea name="anot" class="form-control rounded-3" rows="3"><?= htmlspecialchars($edit_item['anotaciones']) ?></textarea>
                </div>

                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" name="update_full" class="btn btn-warning w-100 rounded-pill fw-bold py-2">Guardar Cambios</button>
                    <a href="gestion_evaluables.php" class="btn btn-light rounded-pill px-4">Cancelar</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if(!empty($registros)): ?>
            <?php foreach($registros as $nombreMateria => $items): ?>
                <div class="subject-header"><?= htmlspecialchars($nombreMateria) ?></div>
                <?php foreach($items as $it): ?>
                    <div class="eval-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge rounded-pill mb-2 <?= $it['tipo']=='Examen'?'bg-danger':'bg-info text-dark' ?>">
                                    <?= $it['tipo'] ?>
                                </span>
                                <span class="text-muted ms-2 small">📅 <?= date('d/m/Y', strtotime($it['fecha'])) ?></span>
                                <div class="mt-2 h5 mb-0">
                                    <span class="text-muted fw-normal" style="font-size: 0.9rem;">Prevista:</span> <b><?= $it['nota_estimada'] ?? '-' ?></b> 
                                    <span class="text-muted fw-normal ms-2" style="font-size: 0.9rem;">| Real:</span> <b class="text-success"><?= $it['nota_sacada'] ?? '---' ?></b>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="?edit=<?= $it['id'] ?>" class="btn btn-sm btn-edit rounded-pill px-3 fw-bold shadow-sm">Editar</a>
                                <a href="?del=<?= $it['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" onclick="return confirm('¿Borrar este registro?')">✕</a>
                            </div>
                        </div>
                        
                        <?php if(!empty($it['anotaciones'])): ?>
                            <div class="anot-box">
                                <strong class="text-dark small uppercase d-block mb-1">ANOTACIONES:</strong>
                                <?= nl2br(htmlspecialchars($it['anotaciones'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center mt-5 text-muted">
                <h4>No hay exámenes o trabajos registrados.</h4>
                <p>Usa el botón "+" en la página principal para empezar.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>