<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

require_once '../../config/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id || !is_numeric($id)) {
    header("Location: index.php?erro=id_invalido");
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE convenios SET status = 'inativo' WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: index.php?sucesso=inativado");
    exit;
} catch (PDOException $e) {
    error_log("Erro ao inativar convênio: " . $e->getMessage());
    header("Location: index.php?erro=bd");
    exit;
}
