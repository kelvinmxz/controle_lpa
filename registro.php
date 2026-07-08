<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema LPA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Criar Nova Conta</h1>
            <p>Preencha os dados para se cadastrar no sistema</p>
        </div>

        <div id="alertContainer"></div>

        <form id="registroForm">
            <div class="form-group">
                <label for="prontuario">Prontuário</label>
                <input type="text" id="prontuario" name="prontuario" required placeholder="Ex: 123456">
            </div>

            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" required placeholder="Seu nome completo">
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="seu@email.com">
            </div>

            <div class="form-group">
                <label for="area">Área</label>
                <select id="area" name="area" required>
                    <option value="">Selecione a área</option>
                    <option value="smd">SMD</option>
                    <option value="fa">FA</option>
                    <option value="cluster">Cluster</option>
                </select>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required placeholder="Mínimo 6 caracteres" minlength="6">
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Repita a senha">
            </div>

            <button type="submit" class="btn btn-success">Cadastrar</button>
        </form>

        <div class="link">
            <p>Já tem conta? <a href="login.php">Fazer login</a></p>
        </div>
    </div>

    <script src="assets/js/registro.js"></script>
</body>
</html>
