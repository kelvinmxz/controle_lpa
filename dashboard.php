<?php
// dashboard.php - Painel Principal

require_once 'config.php';
requireLogin();

$pdo = getDBConnection();
$userInfo = [
    'nome' => $_SESSION['nome'],
    'prontuario' => $_SESSION['prontuario'],
    'area' => $_SESSION['area']
];

// Buscar estatísticas
$userId = $_SESSION['user_id'];

// Total de registros no mês atual
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registros_ponto WHERE usuario_id = ? AND MONTH(data_ponto) = MONTH(CURDATE()) AND YEAR(data_ponto) = YEAR(CURDATE())");
$stmt->execute([$userId]);
$registrosMes = $stmt->fetch()['total'];

// Último registro
$stmt = $pdo->prepare("SELECT * FROM registros_ponto WHERE usuario_id = ? ORDER BY data_ponto DESC, hora_entrada DESC LIMIT 1");
$stmt->execute([$userId]);
$ultimoRegistro = $stmt->fetch();

// Verificar se já registrou ponto hoje
$stmt = $pdo->prepare("SELECT * FROM registros_ponto WHERE usuario_id = ? AND data_ponto = CURDATE()");
$stmt->execute([$userId]);
$registroHoje = $stmt->fetch();

// Verificar se usuário tem dados faciais cadastrados
$stmt = $pdo->prepare("SELECT facial_data FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();
$hasFacialData = !empty($userData['facial_data']);

// Buscar últimos registros do usuário
$stmt = $pdo->prepare("SELECT * FROM registros_ponto WHERE usuario_id = ? ORDER BY data_ponto DESC, hora_entrada DESC LIMIT 10");
$stmt->execute([$userId]);
$ultimosRegistros = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema LPA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="justify-content: flex-start; padding-top: 40px;">
    <div class="dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <h1>🎯 Sistema de Controle LPA</h1>
                <p style="color: #7f8c8d;">Painel do Auditor</p>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($userInfo['nome'], 0, 1)); ?></div>
                <div>
                    <strong><?php echo htmlspecialchars($userInfo['nome']); ?></strong><br>
                    <small><?php echo htmlspecialchars($userInfo['prontuario']); ?> | 
                        <span class="badge badge-<?php echo $userInfo['area']; ?>">
                            <?php echo strtoupper($userInfo['area']); ?>
                        </span>
                    </small>
                </div>
                <a href="logout.php" class="btn btn-danger" style="padding: 8px 15px; font-size: 12px; width: auto;">Sair</a>
            </div>
        </div>

        <!-- Alerta se não tiver registro facial -->
        <?php if (!$hasFacialData): ?>
        <div class="alert alert-warning" style="margin-bottom: 20px;">
            ⚠️ <strong>Atenção:</strong> Você ainda não cadastrou seus dados faciais. 
            <a href="registro-facial.php?mode=register" style="color: #856404; text-decoration: underline;">Cadastrar agora</a>
        </div>
        <?php endif; ?>

        <!-- Cards de Estatísticas -->
        <div class="cards-grid">
            <div class="card">
                <div class="card-icon">📅</div>
                <div class="card-value"><?php echo $registrosMes; ?></div>
                <div class="card-label">Registros este Mês</div>
            </div>
            
            <div class="card">
                <div class="card-icon">✅</div>
                <div class="card-value"><?php echo $registroHoje ? 'Sim' : 'Não'; ?></div>
                <div class="card-label">Ponto Registrado Hoje</div>
            </div>
            
            <div class="card">
                <div class="card-icon">🕐</div>
                <div class="card-value"><?php echo $registroHoje ? date('H:i', strtotime($registroHoje['hora_entrada'])) : '--:--'; ?></div>
                <div class="card-label">Hora do Registro</div>
            </div>
            
            <div class="card">
                <div class="card-icon">📸</div>
                <div class="card-value"><?php echo $hasFacialData ? '✓' : '✗'; ?></div>
                <div class="card-label">Reconhecimento Facial</div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="table-container" style="margin-bottom: 30px;">
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">⚡ Ações Rápidas</h2>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="reconhecimento.php" class="btn btn-primary" style="flex: 1; min-width: 200px;">
                    📸 Registrar Ponto (Reconhecimento Facial)
                </a>
                <a href="registro-facial.php?mode=register" class="btn btn-success" style="flex: 1; min-width: 200px;">
                    <?php echo $hasFacialData ? '🔄 Atualizar Dados Faciais' : '📷 Cadastrar Reconhecimento Facial'; ?>
                </a>
            </div>
        </div>

        <!-- Últimos Registros -->
        <div class="table-container">
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">📋 Meus Últimos Registros</h2>
            
            <?php if (empty($ultimosRegistros)): ?>
                <p style="text-align: center; color: #7f8c8d; padding: 20px;">
                    Nenhum registro encontrado.
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Hora Entrada</th>
                            <th>Área</th>
                            <th>Prontuário</th>
                            <th>Confiança</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimosRegistros as $registro): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($registro['data_ponto'])); ?></td>
                            <td><?php echo date('H:i:s', strtotime($registro['hora_entrada'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $registro['area']; ?>">
                                    <?php echo strtoupper($registro['area']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($registro['prontuario']); ?></td>
                            <td><?php echo $registro['confianca_reconhecimento'] ? number_format($registro['confianca_reconhecimento'], 1) . '%' : 'N/A'; ?></td>
                            <td>
                                <span class="badge badge-smd">
                                    <?php echo ucfirst($registro['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
