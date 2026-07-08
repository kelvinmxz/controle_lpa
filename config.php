<?php
// config.php - Configuração do banco de dados e sessões

session_start();

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'lpa_controle');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações da aplicação
define('APP_NAME', 'Sistema de Controle LPA');
define('APP_URL', 'http://localhost/lpa_controle');

// Função para conectar ao banco de dados
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

// Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['prontuario']);
}

// Função para redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Função para registrar logs
function registerLog($pdo, $userId, $action, $description) {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs_sistema (usuario_id, acao, descricao, ip_origem) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $description, $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

// Função para obter IP do cliente
function getClientIP() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Função para formatar data
function formatDate($date, $format = 'd/m/Y H:i:s') {
    if (empty($date)) return '';
    $dateTime = new DateTime($date);
    return $dateTime->format($format);
}

?>
