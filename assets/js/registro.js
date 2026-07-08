// assets/js/registro.js - Lógica de Registro

document.getElementById('registroForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const prontuario = document.getElementById('prontuario').value;
    const nome = document.getElementById('nome').value;
    const email = document.getElementById('email').value;
    const area = document.getElementById('area').value;
    const senha = document.getElementById('senha').value;
    const confirmarSenha = document.getElementById('confirmar_senha').value;
    
    // Validar senhas
    if (senha !== confirmarSenha) {
        showAlert('As senhas não coincidem', 'error');
        return;
    }
    
    if (senha.length < 6) {
        showAlert('A senha deve ter pelo menos 6 caracteres', 'error');
        return;
    }
    
    try {
        const response = await fetch('api/auth.php?action=register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ prontuario, nome, email, area, senha })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            document.getElementById('registroForm').reset();
            
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            showAlert(result.message || 'Erro ao registrar', 'error');
        }
    } catch (error) {
        showAlert('Erro de conexão. Tente novamente.', 'error');
        console.error('Erro:', error);
    }
});

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    alertContainer.innerHTML = `
        <div class="alert alert-${type}">
            ${message}
        </div>
    `;
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}
