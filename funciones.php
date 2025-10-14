<?php
// Factores de costo
define('FACTORES_DE_COSTO', [
    "RELY" => [0.75,0.88,1.00,1.15,1.40,null],
    "DATA" => [null,0.94,1.00,1.08,1.16,null],
    "CPLX" => [0.70,0.85,1.00,1.15,1.30,1.65],
    "TIME" => [null,null,1.00,1.11,1.30,1.66],
    "STOR" => [null,null,1.00,1.06,1.21,1.56],
    "VIRT" => [0.87,0.94,1.00,1.10,1.15,null],
    "TURN" => [null,0.87,1.00,1.07,1.15,null],
    "ACAP" => [1.46,1.19,1.00,0.86,0.71,null],
    "AEXP" => [1.29,1.13,1.00,0.91,0.82,null],
    "PCAP" => [1.42,1.17,1.00,0.86,0.70,null],
    "VEXP" => [1.19,1.10,1.00,0.90,0.85,null],
    "LTEX" => [1.14,1.07,1.00,0.95,0.84,null],
    "MODP" => [1.24,1.10,1.00,0.91,0.82,null],
    "TOOL" => [1.24,1.10,1.00,0.91,0.83,null],
    "SCED" => [1.23,1.08,1.00,1.04,1.10,null],
]);

define('VALORACIONES', ["Muy bajo","Bajo","Nominal","Alto","Muy alto","Extra alto"]);

// Modos de desarrollo
define('MODOS', [
    "organico" => [2.4, 1.05, 2.5, 0.38],
    "semiacoplado" => [3.0, 1.12, 2.5, 0.35],
    "empotrado" => [3.6, 1.20, 2.5, 0.32],
]);

function obtener_multiplicador($factor, $valoracion) {
    $indice = array_search($valoracion, VALORACIONES);
    $valor = FACTORES_DE_COSTO[$factor][$indice];

    // Si el valor es null, tomar el valor nominal (índice 2)
    if ($valor === null) {
        $valor = FACTORES_DE_COSTO[$factor][2]; // Nominal
    }

    return $valor;
}

function estimar_costo_proyecto($datos) {
    $KLOC = floatval($datos['KLOC']);
    $salario = floatval($datos['salario']);
    $modo = $datos['modo'];
    $factores = $datos['factores'];

    $EAF = 1.0;
    foreach ($factores as $f => $v) {
        $EAF *= obtener_multiplicador($f, $v);
    }

    list($a,$b,$c,$d) = MODOS[$modo];
    $PM = $a * pow($KLOC, $b) * $EAF;
    $duracion = $c * pow($PM, $d);
    $P = $PM / $duracion;
    $costo_total = $P * $salario;

    return [
        "KLOC"=>$KLOC,
        "Modo"=>$modo,
        "EAF"=>$EAF,
        "PM"=>$PM,
        "Duracion"=>$duracion,
        "Personal"=>$P,
        "Costo"=>$costo_total
    ];
}
