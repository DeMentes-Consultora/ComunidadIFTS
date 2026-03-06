<?php
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query('SHOW COLUMNS FROM institucion');

foreach ($stmt as $col) {
    echo $col['Field'] . PHP_EOL;
}
