<?php
session_start();
include 'funciones.php';

// Inicializar array de proyectos
if (!isset($_SESSION['proyectos'])) {
    $_SESSION['proyectos'] = [];
}

// Descripciones de los factores
$DESCRIPCIONES = [
    "RELY" => "Fiabilidad requerida",
    "DATA" => "Tamaño de la base de datos",
    "CPLX" => "Complejidad del producto",
    "TIME" => "Restricciones de tiempo",
    "STOR" => "Restricciones de almacenamiento",
    "VIRT" => "Capacidades virtuales del equipo",
    "TURN" => "Tiempo de respuesta requerido",
    "ACAP" => "Capacidad del analista",
    "PCAP" => "Capacidad del programador",
    "AEXP" => "Experiencia en aplicación",
    "LTEX" => "Experiencia en herramientas",
    "MODP" => "Prácticas de desarrollo",
    "TOOL" => "Herramientas de software",
    "SCED" => "Restricciones de calendario",
    "VEXP" => "Experiencia con la plataforma"
];

// Procesar formulario
$mensaje_error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $KLOC = $_POST['KLOC'];
    $salario = $_POST['salario'];

    // Validaciones
    if ($KLOC <= 0) {
        $mensaje_error = "Error: El tamaño del proyecto (KLOC) debe ser mayor a 0.";
    } elseif ($salario < 0) {
        $mensaje_error = "Error: El salario no puede ser negativo.";
    } else {
        $resultado = estimar_costo_proyecto($_POST);
        $_SESSION['proyectos'][] = $resultado;
    }
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

    <?php if ($mensaje_error): ?>
        <p style="color: red; font-weight: bold;"><?= $mensaje_error ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Tamaño del proyecto (KLOC):</label>
        <input type="number" step="0.01" name="KLOC" required min="0" value="<?= $_POST['KLOC'] ?? '' ?>">

        <label>Salario mensual del equipo:</label>
        <input type="number" step="0.01" name="salario" required min="0" value="<?= $_POST['salario'] ?? '' ?>">

        <label>Modo de desarrollo:</label>
        <select name="modo" required>
            <option value="organico" <?= (($_POST['modo'] ?? '') == 'organico') ? 'selected' : '' ?>>Orgánico</option>
            <option value="semiacoplado" <?= (($_POST['modo'] ?? '') == 'semiacoplado') ? 'selected' : '' ?>>Semiacoplado</option>
            <option value="empotrado" <?= (($_POST['modo'] ?? '') == 'empotrado') ? 'selected' : '' ?>>Empotrado</option>
        </select>

        <h3>Factores de costo</h3>
        <?php
        // Factores que no permiten "Muy bajo"
        $excluir_muy_bajo = ["DATA", "TIME", "STOR", "TURN"];

        foreach (FACTORES_DE_COSTO as $factor => $valores): ?>
            <label><?= $factor ?> <?= $DESCRIPCIONES[$factor] ?? '' ?>:</label>
            <select name="factores[<?= $factor ?>]" required>
                <?php foreach (VALORACIONES as $v):
                    if (in_array($factor, $excluir_muy_bajo) && $v === "Muy bajo") continue;

                    $indice = array_search($v, VALORACIONES);
                    $valor = $valores[$indice] ?? $valores[2];
                    $selected = (($_POST['factores'][$factor] ?? '') == $v) ? 'selected' : (($v === "Nominal") ? 'selected' : '');
                ?>
                    <option value="<?= $v ?>" <?= $selected ?>><?= $v ?> (<?= $valor ?>)</option>
                <?php endforeach; ?>
            </select>
        <?php endforeach; ?>

        <button type="submit" name="agregar">Agregar Proyecto</button>
    </form>

    <!-- Tabla de factores seleccionados -->
    <?php if (!empty($_POST['factores']) && !$mensaje_error): ?>
        <h3>Factores Seleccionados para este Proyecto</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Factor</th>
                    <th>Descripción</th>
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
                        <td><?= $DESCRIPCIONES[$factor] ?? '' ?></td>
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

