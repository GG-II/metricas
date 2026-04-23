<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Metrica;

$metricaModel = new Metrica();

// ID del área Software Factory
$area_id = 1;

echo "<h1>Debug Métricas - Área $area_id</h1>";

// Obtener métricas usando el método
$metricas = $metricaModel->getByArea($area_id, true);

echo "<h2>Total métricas obtenidas: " . count($metricas) . "</h2>";

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Índice</th><th>ID</th><th>Nombre</th><th>Orden</th><th>Slug</th></tr>";
foreach ($metricas as $idx => $m) {
    echo "<tr>";
    echo "<td>$idx</td>";
    echo "<td>{$m['id']}</td>";
    echo "<td>{$m['nombre']}</td>";
    echo "<td>{$m['orden']}</td>";
    echo "<td>{$m['slug']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2>Query directa a la base de datos</h2>";

$config = require __DIR__ . '/config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

$stmt = $db->prepare("SELECT * FROM metricas WHERE area_id = ? AND activo = 1 ORDER BY orden ASC, id ASC");
$stmt->execute([$area_id]);
$results = $stmt->fetchAll();

echo "<h3>Total filas devueltas por SQL: " . count($results) . "</h3>";

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Orden</th><th>Área ID</th><th>Activo</th></tr>";
foreach ($results as $r) {
    echo "<tr>";
    echo "<td>{$r['id']}</td>";
    echo "<td>{$r['nombre']}</td>";
    echo "<td>{$r['orden']}</td>";
    echo "<td>{$r['area_id']}</td>";
    echo "<td>{$r['activo']}</td>";
    echo "</tr>";
}
echo "</table>";
