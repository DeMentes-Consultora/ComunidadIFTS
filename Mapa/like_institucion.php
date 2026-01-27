<?php
header('Content-Type: application/json');
require 'conexion.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

try {
    // Incrementamos el contador de likes. COALESCE asegura que si es NULL lo trate como 0.
    $stmt = $pdo->prepare("UPDATE instituciones SET likes = COALESCE(likes, 0) + 1 WHERE id = ?");
    $stmt->execute([$id]);

    // Obtenemos el nuevo valor para actualizar la pantalla
    $stmt = $pdo->prepare("SELECT likes FROM instituciones WHERE id = ?");
    $stmt->execute([$id]);
    $likes = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'likes' => $likes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
}
?>