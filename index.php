<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    // Usuário já está logado, vai direto para o dashboard
    header("Location: views/dashboard/");
    exit;
} else {
    // Usuário não está logado, redireciona para o login
    header("Location: login.html");
    exit;
}
