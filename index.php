<?php
session_start();
include 'funciones.php';

// Inicializar array de proyectos
if (!isset($_SESSION['proyectos'])) {
    $_SESSION['proyectos'] = [];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $resultado = estimar_costo_proyecto($_POST);
    $_SESSION['proyectos'][] = $resultado;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>COCOMO I - Estimación de Proyectos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Estimación de Proyectos COCOMO I</h1>

    <form method="post">
        <label>Tamaño del proyecto (KLOC):</label>
        <input type="number" step="0.01" name="KLOC" required>

        <label>Salario mensual del equipo:</label>
        <input type="number" step="0.01" name="salario" required>

        <label>Modo de desarrollo:</label>
        <select name="modo" required>
            <option value="organico">Orgánico</option>
            <option value="semiacoplado">Semiacoplado</option>
            <option value="empotrado">Empotrado</option>
        </select>

        <h3>Factores de costo</h3>
        <?php
        // Factores que no permiten "Muy bajo"
        $excluir_muy_bajo = ["DATA", "TIME", "STOR", "TURN"];

        foreach (FACTORES_DE_COSTO as $factor => $valores): ?>
            <label><?= $factor ?>:</label>
            <select name="factores[<?= $factor ?>]" required>
                <?php foreach (VALORACIONES as $v):
                    if (in_array($factor, $excluir_muy_bajo) && $v === "Muy bajo") continue;

                    $indice = array_search($v, VALORACIONES);
                    $valor = $valores[$indice] ?? $valores[2];
                    $selected = ($v === "Nominal") ? 'selected' : '';
                ?>
                    <option value="<?= $v ?>" <?= $selected ?>><?= $v ?> (<?= $valor ?>)</option>
                <?php endforeach; ?>
            </select>
        <?php endforeach; ?>

        <button type="submit" name="agregar">Agregar Proyecto</button>
    </form>

    <!-- Tabla de factores seleccionados -->
    <?php if (!empty($_POST['factores'])): ?>
        <h3>Factores Seleccionados para este Proyecto</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Factor</th>
                    <th>Valoración</th>
                    <th>Multiplicador</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_POST['factores'] as $factor => $valoracion):
                    $indice = array_search($valoracion, VALORACIONES);
                    $multiplicador = FACTORES_DE_COSTO[$factor][$indice] ?? FACTORES_DE_COSTO[$factor][2];
                ?>
                    <tr>
                        <td><?= $factor ?></td>
                        <td><?= $valoracion ?></td>
                        <td><?= $multiplicador ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Comparativa de Proyectos</h2>
    <?php if(!empty($_SESSION['proyectos'])): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>KLOC</th>
                    <th>Modo</th>
                    <th>EAF</th>
                    <th>Esfuerzo (PM)</th>
                    <th>Duración (meses)</th>
                    <th>Personal promedio</th>
                    <th>Costo total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($_SESSION['proyectos'] as $i => $p): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= $p['KLOC'] ?></td>
                        <td><?= ucfirst($p['Modo']) ?></td>
                        <td><?= number_format($p['EAF'], 3) ?></td>
                        <td><?= number_format($p['PM'], 2) ?></td>
                        <td><?= number_format($p['Duracion'], 2) ?></td>
                        <td><?= number_format($p['Personal'], 2) ?></td>
                        <td><?= number_format($p['Costo'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="reset.php" class="btn-reset">Limpiar Proyectos</a>
    <?php endif; ?>
</div>
</body>
</html>
