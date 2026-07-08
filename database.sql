-- Banco de Dados para Sistema de Controle LPA
-- Execute este script no MySQL

CREATE DATABASE IF NOT EXISTS lpa_controle;
USE lpa_controle;

-- Tabela de usuários/auditores
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prontuario VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    area ENUM('smd', 'fa', 'cluster') NOT NULL,
    facial_data TEXT, -- Armazena os dados biométricos faciais (encoding em base64)
    facial_descritor TEXT, -- Descritores faciais para comparação
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_prontuario (prontuario),
    INDEX idx_area (area),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de registros de ponto/auditoria
CREATE TABLE IF NOT EXISTS registros_ponto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    prontuario VARCHAR(50) NOT NULL,
    area ENUM('smd', 'fa', 'cluster') NOT NULL,
    data_ponto DATE NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_saida TIME DEFAULT NULL,
    status ENUM('entrada', 'saida', 'completo') DEFAULT 'entrada',
    foto_registro TEXT, -- Foto capturada no momento do registro
    confianca_reconhecimento DECIMAL(5,2), -- Nível de confiança do reconhecimento facial
    ip_origem VARCHAR(45),
    user_agent TEXT,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_data (data_ponto),
    INDEX idx_prontuario_data (prontuario, data_ponto),
    UNIQUE KEY unique_usuario_data (usuario_id, data_ponto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs do sistema
CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT,
    ip_origem VARCHAR(45),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_acao (acao),
    INDEX idx_data (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário administrador padrão (senha: admin123)
-- A senha será hashada via PHP
INSERT INTO usuarios (prontuario, nome, email, senha_hash, area, ativo) 
VALUES ('ADMIN001', 'Administrador', 'admin@lpa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'smd', 1);
