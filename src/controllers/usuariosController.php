<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';

    if ($acao === 'alterar_senha') {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
        $senha_confirmacao = isset($_POST['senha_confirmacao']) ? $_POST['senha_confirmacao'] : '';

        if (!$id || empty($senha) || empty($senha_confirmacao)) {
            header("Location: ../../views/usuarios/editar_perfil.php?erro=campos");
            exit;
        }

        if ($senha !== $senha_confirmacao) {
            header("Location: ../../views/usuarios/editar_perfil.php?erro=naoconfere");
            exit;
        }

        try {
            $senha_hash = hash('sha256', $senha);
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt->execute([$senha_hash, $id]);

            header("Location: ../../views/dashboard/?sucesso=senha");
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar senha: " . $e->getMessage());
            header("Location: ../../views/usuarios/editar_perfil.php?erro=banco");
            exit;
        }
    }

    // Demais ações (criar/atualizar admin)
    if (isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] === 'admin') {
        $acao = $_POST['acao'];
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $nivel = isset($_POST['nivel']) ? $_POST['nivel'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';

        if (empty($nome) || empty($email) || empty($nivel) || empty($status)) {
            header("Location: ../../views/usuarios/index.php?erro=campos");
            exit;
        }

        if ($acao === 'criar') {
            $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
            if (empty($senha)) {
                header("Location: ../../views/usuarios/novo.php?erro=senha");
                exit;
            }

            try {
                $senha_hash = hash('sha256', $senha);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $email, $senha_hash, $nivel, $status]);
                header("Location: ../../views/usuarios/index.php?sucesso=1");
                exit;
            } catch (PDOException $e) {
                error_log("Erro ao inserir usuário: " . $e->getMessage());
                header("Location: ../../views/usuarios/novo.php?erro=banco");
                exit;
            }
        }

        if ($acao === 'atualizar') {
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            if (!$id || !is_numeric($id)) {
                header("Location: ../../views/usuarios/index.php?erro=id");
                exit;
            }

            try {
                $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, nivel = ?, status = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $nivel, $status, $id]);
                header("Location: ../../views/usuarios/index.php?sucesso=2");
                exit;
            } catch (PDOException $e) {
                error_log("Erro ao atualizar usuário: " . $e->getMessage());
                header("Location: ../../views/usuarios/editar.php?id=" . $id . "&erro=banco");
                exit;
            }
        }
    }
}

header("Location: ../../views/usuarios/index.php");
exit;
