<?php
// registro-facial.php - Página de Registro Facial

require_once 'config.php';
requireLogin();

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Verificar se usuário já tem dados faciais cadastrados
$stmt = $pdo->prepare("SELECT facial_data FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();
$hasFacialData = !empty($userData['facial_data']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Facial - Sistema LPA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <div class="header">
            <h1>📸 Cadastro de Reconhecimento Facial</h1>
            <p><?php echo $hasFacialData ? 'Atualize seus dados faciais' : 'Cadastre seu rosto para usar o sistema de ponto'; ?></p>
        </div>

        <div id="alertContainer"></div>

        <?php if ($hasFacialData): ?>
        <div class="alert alert-info" style="margin-bottom: 20px;">
            ℹ️ Você já possui dados faciais cadastrados. Pode atualizá-los se necessário.
        </div>
        <?php endif; ?>

        <div class="camera-container">
            <video id="videoElement" autoplay playsinline></video>
            <canvas id="canvas"></canvas>
        </div>

        <div class="camera-controls">
            <button id="btnCapturar" class="btn btn-success">
                📷 Capturar e Cadastrar Rosto
            </button>
        </div>

        <div id="resultContainer" style="margin-top: 20px; display: none;">
            <img id="photoElement" alt="Foto capturada" style="width: 100%; border-radius: 10px;">
        </div>

        <div class="link" style="margin-top: 20px;">
            <a href="dashboard.php">← Voltar ao Dashboard</a>
        </div>
    </div>

    <script src="assets/js/facial-recognition.js"></script>
</body>
</html>
