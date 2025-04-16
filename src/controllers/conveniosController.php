<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';


    if (empty($nome) || !in_array($status, ['ativo', 'inativo'])) {
        header("Location: ../../views/convenios/index.php?erro=dados_invalidos");
        exit;
    }

    if ($acao === 'criar') {
        try {
            $stmt = $pdo->prepare("INSERT INTO convenios (nome, status) VALUES (?, ?)");
            $stmt->execute([$nome, $status]);

            header("Location: ../../views/convenios/index.php?sucesso=1");
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao inserir convênio: " . $e->getMessage());
            header("Location: ../../views/convenios/novo.php?erro=2");
            exit;
        }
    }

    if ($acao === 'atualizar') {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        if (!$id || !is_numeric($id)) {
            header("Location: ../../views/convenios/index.php?erro=id_invalido");
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE convenios SET nome = ?, status = ? WHERE id = ?");
            $stmt->execute([$nome, $status, $id]);

            header("Location: ../../views/convenios/index.php?sucesso=2");
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar convênio: " . $e->getMessage());
            header("Location: ../../views/convenios/editar.php?id=" . $id . "&erro=2");
            exit;
        }
    }
} else {
    header("Location: ../../views/convenios/index.php");
    exit;
}
