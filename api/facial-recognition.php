<?php
// api/facial-recognition.php - API de Reconhecimento Facial

header('Content-Type: application/json');
require_once '../config.php';

// Configurar CORS se necessário
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $pdo = getDBConnection();
    
    // Roteamento simples baseado na ação
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'register':
            $response = registerFacialData($pdo);
            break;
        case 'recognize':
            $response = recognizeFace($pdo);
            break;
        case 'check_existing':
            $response = checkExistingFace($pdo);
            break;
        default:
            $response['message'] = 'Ação inválida';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

echo json_encode($response);

// Função para registrar dados faciais
function registerFacialData($pdo) {
    if (!isLoggedIn()) {
        return ['success' => false, 'message' => 'Usuário não autenticado'];
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['facialData']) || empty($input['facialDescriptor'])) {
        return ['success' => false, 'message' => 'Dados faciais não fornecidos'];
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET facial_data = ?, facial_descritor = ?, atualizado_em = NOW() WHERE id = ?");
        $stmt->execute([
            $input['facialData'],
            $input['facialDescriptor'],
            $_SESSION['user_id']
        ]);
        
        registerLog($pdo, $_SESSION['user_id'], 'REGISTRO_FACIAL', 'Dados faciais registrados com sucesso');
        
        return [
            'success' => true,
            'message' => 'Dados faciais registrados com sucesso',
            'data' => ['user_id' => $_SESSION['user_id']]
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao registrar: ' . $e->getMessage()];
    }
}

// Função para reconhecer face e registrar ponto
function recognizeFace($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['facialData']) || empty($input['facialDescriptor'])) {
        return ['success' => false, 'message' => 'Dados faciais não fornecidos'];
    }
    
    $inputDescriptor = $input['facialDescriptor'];
    $inputImage = $input['facialData'];
    
    // Buscar todos os usuários com dados faciais cadastrados
    $stmt = $pdo->prepare("SELECT id, prontuario, nome, area, facial_descritor FROM usuarios WHERE facial_descritor IS NOT NULL AND ativo = 1");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        return ['success' => false, 'message' => 'Nenhum usuário com reconhecimento facial cadastrado'];
    }
    
    // Comparação simplificada de descritores faciais
    // Em produção, use uma biblioteca especializada como OpenCV com PHP ou serviço externo
    $bestMatch = null;
    $bestScore = 0;
    
    foreach ($users as $user) {
        $score = compareFaceDescriptors($inputDescriptor, $user['facial_descritor']);
        
        if ($score > $bestScore && $score >= 0.85) { // Threshold de 85% de confiança
            $bestScore = $score;
            $bestMatch = $user;
        }
    }
    
    if (!$bestMatch) {
        return ['success' => false, 'message' => 'Rosto não reconhecido. Por favor, cadastre-se primeiro.'];
    }
    
    // Verificar se já registrou ponto hoje
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM registros_ponto WHERE usuario_id = ? AND data_ponto = ?");
    $stmt->execute([$bestMatch['id'], $today]);
    $existingRecord = $stmt->fetch();
    
    if ($existingRecord) {
        return [
            'success' => true,
            'message' => 'Ponto já registrado hoje às ' . $existingRecord['hora_entrada'],
            'data' => [
                'user' => $bestMatch,
                'already_registered' => true,
                'registro' => $existingRecord,
                'confidence' => $bestScore * 100
            ]
        ];
    }
    
    // Registrar novo ponto
    try {
        $stmt = $pdo->prepare("
            INSERT INTO registros_ponto 
            (usuario_id, prontuario, area, data_ponto, hora_entrada, foto_registro, confianca_reconhecimento, ip_origem, user_agent) 
            VALUES (?, ?, ?, CURDATE(), CURTIME(), ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $bestMatch['id'],
            $bestMatch['prontuario'],
            $bestMatch['area'],
            $inputImage,
            number_format($bestScore * 100, 2),
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        $registroId = $pdo->lastInsertId();
        
        registerLog($pdo, $bestMatch['id'], 'REGISTRO_PONTO', 'Ponto registrado via reconhecimento facial');
        
        return [
            'success' => true,
            'message' => 'Ponto registrado com sucesso! Bem-vindo, ' . $bestMatch['nome'],
            'data' => [
                'user' => $bestMatch,
                'already_registered' => false,
                'registro_id' => $registroId,
                'hora_entrada' => date('H:i:s'),
                'confidence' => $bestScore * 100
            ]
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao registrar ponto: ' . $e->getMessage()];
    }
}

// Função para verificar se usuário já tem face cadastrada
function checkExistingFace($pdo) {
    if (!isLoggedIn()) {
        return ['success' => false, 'message' => 'Usuário não autenticado'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT facial_data, facial_descritor FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        $hasFace = !empty($user['facial_data']) && !empty($user['facial_descritor']);
        
        return [
            'success' => true,
            'message' => $hasFace ? 'Dados faciais já cadastrados' : 'Dados faciais não cadastrados',
            'data' => [
                'has_facial_data' => $hasFace
            ]
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao verificar: ' . $e->getMessage()];
    }
}

// Função simplificada de comparação de descritores faciais
// Em produção, implemente um algoritmo real de comparação biométrica
function compareFaceDescriptors($descriptor1, $descriptor2) {
    // Decodificar descritores (espera-se que sejam JSON ou base64)
    $desc1 = json_decode($descriptor1, true) ?? explode(',', $descriptor1);
    $desc2 = json_decode($descriptor2, true) ?? explode(',', $descriptor2);
    
    // Se forem arrays numéricos, calcular similaridade de cosseno
    if (is_array($desc1) && is_array($desc2) && count($desc1) > 0 && count($desc2) > 0) {
        // Garantir que tenham o mesmo tamanho
        $minLength = min(count($desc1), count($desc2));
        
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;
        
        for ($i = 0; $i < $minLength; $i++) {
            $val1 = floatval($desc1[$i]);
            $val2 = floatval($desc2[$i]);
            $dotProduct += $val1 * $val2;
            $norm1 += $val1 * $val1;
            $norm2 += $val2 * $val2;
        }
        
        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);
        
        if ($norm1 > 0 && $norm2 > 0) {
            return $dotProduct / ($norm1 * $norm2);
        }
    }
    
    // Fallback: comparação de string simples (hash)
    if ($descriptor1 === $descriptor2) {
        return 1.0;
    }
    
    // Similaridade de Levenshtein para strings
    $maxLen = max(strlen($descriptor1), strlen($descriptor2));
    if ($maxLen === 0) return 1.0;
    
    $distance = levenshtein($descriptor1, $descriptor2);
    return 1 - ($distance / $maxLen);
}

?>
