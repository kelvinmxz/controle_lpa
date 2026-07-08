<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema LPA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Sistema de Controle LPA</h1>
            <p>Faça login para acessar o sistema</p>
        </div>

        <div id="alertContainer"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="seu@email.com">
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required placeholder="Sua senha">
            </div>

            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>

        <div class="link">
            <p>Não tem conta? <a href="registro.php">Criar nova conta</a></p>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>
