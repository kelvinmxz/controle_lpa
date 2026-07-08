// assets/js/facial-recognition.js - Lógica de Reconhecimento Facial

let videoStream = null;
let isRegisterMode = false;

// Verificar se estamos em modo de registro ou reconhecimento
const urlParams = new URLSearchParams(window.location.search);
isRegisterMode = urlParams.get('mode') === 'register';

document.addEventListener('DOMContentLoaded', function() {
    const pageTitle = document.getElementById('pageTitle');
    const btnCapturar = document.getElementById('btnCapturar');
    
    if (isRegisterMode) {
        pageTitle.textContent = 'Cadastre seu rosto para usar o sistema';
        btnCapturar.textContent = '📷 Capturar e Cadastrar Rosto';
        btnCapturar.classList.remove('btn-primary');
        btnCapturar.classList.add('btn-success');
    }
    
    // Iniciar câmera
    startCamera();
    
    // Event listener do botão
    btnCapturar.addEventListener('click', handleCapture);
});

async function startCamera() {
    const video = document.getElementById('videoElement');
    
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            },
            audio: false
        });
        
        video.srcObject = videoStream;
    } catch (error) {
        showAlert('Erro ao acessar câmera: ' + error.message, 'error');
        console.error('Erro câmera:', error);
    }
}

async function handleCapture() {
    const btnCapturar = document.getElementById('btnCapturar');
    btnCapturar.disabled = true;
    btnCapturar.textContent = '⏳ Processando...';
    
    try {
        // Capturar foto do vídeo
        const canvas = document.getElementById('canvas');
        const video = document.getElementById('videoElement');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Obter dados da imagem
        const imageData = canvas.toDataURL('image/jpeg', 0.8);
        
        // Extrair "descritores faciais" simplificados
        // Em produção, use uma biblioteca como face-api.js ou ml5.js
        const facialDescriptor = extractSimpleFacialDescriptor(canvas);
        
        if (isRegisterMode) {
            // Modo de registro
            await registerFace(imageData, facialDescriptor);
        } else {
            // Modo de reconhecimento
            await recognizeFace(imageData, facialDescriptor);
        }
        
        // Mostrar foto capturada
        const resultContainer = document.getElementById('resultContainer');
        const photoElement = document.getElementById('photoElement');
        photoElement.src = imageData;
        resultContainer.style.display = 'block';
        
    } catch (error) {
        showAlert('Erro ao processar imagem: ' + error.message, 'error');
        console.error('Erro:', error);
    } finally {
        btnCapturar.disabled = false;
        btnCapturar.textContent = isRegisterMode ? '📷 Capturar Novamente' : '📷 Capturar e Registrar Ponto';
    }
}

// Função simplificada para extrair "descritores faciais"
// Em produção, use face-api.js ou similar para extração real de características
function extractSimpleFacialDescriptor(canvas) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    
    // Amostrar pixels em uma grade para criar um "descriptor" simplificado
    const gridSize = 20;
    const descriptors = [];
    
    for (let y = 0; y < height; y += gridSize) {
        for (let x = 0; x < width; x += gridSize) {
            const pixel = ctx.getImageData(x, y, 1, 1).data;
            // Normalizar valores de RGB para 0-1
            descriptors.push(pixel[0] / 255); // R
            descriptors.push(pixel[1] / 255); // G
            descriptors.push(pixel[2] / 255); // B
        }
    }
    
    return JSON.stringify(descriptors);
}

async function registerFace(imageData, facialDescriptor) {
    try {
        const response = await fetch('api/facial-recognition.php?action=register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                facialData: imageData,
                facialDescriptor: facialDescriptor
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2000);
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Erro de conexão ao registrar rosto', 'error');
        console.error('Erro:', error);
    }
}

async function recognizeFace(imageData, facialDescriptor) {
    try {
        const response = await fetch('api/facial-recognition.php?action=recognize', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                facialData: imageData,
                facialDescriptor: facialDescriptor
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (result.data && result.data.already_registered) {
                showAlert(result.message, 'info');
            } else {
                showAlert(result.message, 'success');
            }
            
            // Mostrar detalhes se disponível
            if (result.data && result.data.confidence) {
                const confidenceDiv = document.createElement('div');
                confidenceDiv.className = 'alert alert-info';
                confidenceDiv.style.marginTop = '10px';
                confidenceDiv.textContent = `Confiança do reconhecimento: ${result.data.confidence.toFixed(1)}%`;
                document.getElementById('alertContainer').appendChild(confidenceDiv);
            }
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Erro de conexão ao reconhecer rosto', 'error');
        console.error('Erro:', error);
    }
}

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

// Limpar stream de vídeo ao sair da página
window.addEventListener('beforeunload', function() {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }
});
