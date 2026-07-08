<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconhecimento Facial - Sistema LPA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <div class="header">
            <h1>📸 Reconhecimento Facial</h1>
            <p id="pageTitle">Posicione seu rosto para registrar o ponto</p>
        </div>

        <div id="alertContainer"></div>

        <div class="camera-container">
            <video id="videoElement" autoplay playsinline></video>
            <canvas id="canvas"></canvas>
        </div>

        <div class="camera-controls">
            <button id="btnCapturar" class="btn btn-primary">
                📷 Capturar e Registrar Ponto
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
