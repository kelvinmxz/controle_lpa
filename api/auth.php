<?php
// api/auth.php - API de Autenticação

header('Content-Type: application/json');
require_once '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $pdo = getDBConnection();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $response = loginUser($pdo);
            break;
        case 'register':
            $response = registerUser($pdo);
            break;
        case 'logout':
            $response = logoutUser();
            break;
        default:
            $response['message'] = 'Ação inválida';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

echo json_encode($response);

function loginUser($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['email']) || empty($input['senha'])) {
        return ['success' => false, 'message' => 'E-mail e senha são obrigatórios'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, prontuario, nome, email, area, senha_hash, ativo FROM usuarios WHERE email = ?");
        $stmt->execute([$input['email']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuário não encontrado'];
        }
        
        if (!$user['ativo']) {
            return ['success' => false, 'message' => 'Usuário desativado. Contate o administrador.'];
        }
        
        if (!password_verify($input['senha'], $user['senha_hash'])) {
            return ['success' => false, 'message' => 'Senha incorreta'];
        }
        
        // Criar sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['prontuario'] = $user['prontuario'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['area'] = $user['area'];
        
        registerLog($pdo, $user['id'], 'LOGIN', 'Usuário realizou login');
        
        return [
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'user' => [
                    'id' => $user['id'],
                    'prontuario' => $user['prontuario'],
                    'nome' => $user['nome'],
                    'area' => $user['area']
                ]
            ]
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao fazer login: ' . $e->getMessage()];
    }
}

function registerUser($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['prontuario']) || empty($input['nome']) || 
        empty($input['email']) || empty($input['senha']) || empty($input['area'])) {
        return ['success' => false, 'message' => 'Todos os campos são obrigatórios'];
    }
    
    // Validar área
    $areasPermitidas = ['smd', 'fa', 'cluster'];
    if (!in_array($input['area'], $areasPermitidas)) {
        return ['success' => false, 'message' => 'Área inválida'];
    }
    
    try {
        // Verificar se prontuario já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE prontuario = ?");
        $stmt->execute([$input['prontuario']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Prontuário já cadastrado'];
        }
        
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$input['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'E-mail já cadastrado'];
        }
        
        // Hash da senha
        $senhaHash = password_hash($input['senha'], PASSWORD_DEFAULT);
        
        // Inserir usuário
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (prontuario, nome, email, senha_hash, area) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['prontuario'],
            $input['nome'],
            $input['email'],
            $senhaHash,
            $input['area']
        ]);
        
        $userId = $pdo->lastInsertId();
        
        registerLog($pdo, $userId, 'REGISTRO', 'Novo usuário registrado');
        
        return [
            'success' => true,
            'message' => 'Usuário registrado com sucesso! Faça login.',
            'data' => ['user_id' => $userId]
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao registrar: ' . $e->getMessage()];
    }
}

function logoutUser() {
    session_destroy();
    return [
        'success' => true,
        'message' => 'Logout realizado com sucesso'
    ];
}

?>
