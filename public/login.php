<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['password']);

    if (empty($email) || empty($senha)) {
        header("Location: ../../public/login.php?erro=1");
        exit;
    }

    $senha_hash = hash('sha256', $senha);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND senha = ? AND status = 'ativo' AND deleted_at IS NULL");
    $stmt->execute([$email, $senha_hash]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_nivel'] = $usuario['nivel'];

        header("Location: ../../views/dashboard.php");
        exit;
    } else {
        header("Location: ../../public/login.php?erro=2");
        exit;
    }
} else {
    header("Location: ../../public/login.php");
    exit;
}
