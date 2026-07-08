<?php
// logout.php - Logout do Sistema

require_once 'config.php';

// Registrar log de logout se usuário estiver logado
if (isLoggedIn()) {
    try {
        $pdo = getDBConnection();
        registerLog($pdo, $_SESSION['user_id'], 'LOGOUT', 'Usuário realizou logout');
    } catch (Exception $e) {
        // Ignorar erros de log
    }
}

// Destruir sessão
session_destroy();

// Redirecionar para login
header('Location: login.php');
exit;
?>
