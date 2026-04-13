-- ========================================
-- Script de Criação da Base de Dados
-- Sistema de Autoproteção
-- ========================================

-- Criar base de dados se não existir
CREATE DATABASE IF NOT EXISTS sistema_autoprotecao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_autoprotecao;

-- ========================================
-- Tabela: utilizadores
-- ========================================
CREATE TABLE IF NOT EXISTS utilizadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    funcao VARCHAR(50),
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela: tipos_equipamentos
-- ========================================
CREATE TABLE IF NOT EXISTS tipos_equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    icone VARCHAR(50),
    prefixo_numeracao VARCHAR(20) COMMENT 'Prefixo para numeração dos equipamentos, ex: EXT, HID, LUM',
    proximo_numero INT DEFAULT 1 COMMENT 'Próximo número sequencial do tipo de equipamento',
    frequencia_inspecao INT COMMENT 'Frequência em dias',
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela: equipamentos
-- ========================================
CREATE TABLE IF NOT EXISTS equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_equipamento_id INT NOT NULL,
    numero_registo VARCHAR(100) UNIQUE COMMENT 'Número interno de registo/património do equipamento',
    numero_serie VARCHAR(100) UNIQUE,
    localizacao VARCHAR(255) NOT NULL,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    data_aquisicao DATE,
    data_instalacao DATE,
    data_ultima_vistoria DATE,
    data_proxima_manutencao DATE,
    estado VARCHAR(30) DEFAULT 'operacional' COMMENT 'operacional, inservivel, aguardando_reparacao',
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_equipamento_id) REFERENCES tipos_equipamentos(id),
    INDEX idx_tipo (tipo_equipamento_id),
    INDEX idx_localizacao (localizacao),
    INDEX idx_proxima_manutencao (data_proxima_manutencao),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela: tipos_equipamentos_campos
-- Campos dinâmicos por tipo de equipamento
-- ========================================
CREATE TABLE IF NOT EXISTS tipos_equipamentos_campos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_equipamento_id INT NOT NULL,
    nome_campo VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    tipo_dado VARCHAR(30) NOT NULL DEFAULT 'texto' COMMENT 'texto, numero, data, opcao, booleano',
    unidade VARCHAR(20),
    obrigatorio BOOLEAN DEFAULT FALSE,
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_equipamento_id) REFERENCES tipos_equipamentos(id),
    UNIQUE KEY uk_tipo_slug (tipo_equipamento_id, slug),
    INDEX idx_tipo (tipo_equipamento_id),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela: equipamentos_campos_valores
-- Valores dos campos dinâmicos por equipamento
-- ========================================
CREATE TABLE IF NOT EXISTS equipamentos_campos_valores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipamento_id INT NOT NULL,
    campo_id INT NOT NULL,
    valor TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (campo_id) REFERENCES tipos_equipamentos_campos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_equipamento_campo (equipamento_id, campo_id),
    INDEX idx_equipamento (equipamento_id),
    INDEX idx_campo (campo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela: calendarios_manutencao
-- ========================================
CREATE TABLE IF NOT EXISTS calendarios_manutencao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_equipamento_id INT NOT NULL,
    equipamento_id INT NULL,
    data_inspecao DATE NOT NULL,
    tipo_inspecao VARCHAR(50) COMMENT 'inspeção, manutenção, reparação',
    descricao TEXT,
    responsavel_id INT,
    status VARCHAR(30) DEFAULT 'agendado' COMMENT 'agendado, em_progresso, concluido, cancelado',
    prioridade VARCHAR(20) DEFAULT 'normal' COMMENT 'baixa, normal, alta, urgente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_equipamento_id) REFERENCES tipos_equipamentos(id),
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id),
    FOREIGN KEY (responsavel_id) REFERENCES utilizadores(id),
    INDEX idx_tipo_equipamento (tipo_equipamento_id),
    INDEX idx_equipamento (equipamento_id),
    INDEX idx_data (data_inspecao),
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela: relatorios
-- ========================================
CREATE TABLE IF NOT EXISTS relatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipamento_id INT NOT NULL,
    data_relatorio DATE NOT NULL,
    responsavel_id INT NOT NULL,
    tipo_relatorio VARCHAR(50) NOT NULL COMMENT 'inspecao, manutencao, reparacao',
    descricao TEXT,
    observacoes TEXT,
    condicoes_encontradas VARCHAR(30) COMMENT 'bom, aceitavel, deficiente, inservivel',
    proxima_inspecao DATE,
    assinado BOOLEAN DEFAULT FALSE,
    data_assinatura TIMESTAMP,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id),
    FOREIGN KEY (responsavel_id) REFERENCES utilizadores(id),
    INDEX idx_equipamento (equipamento_id),
    INDEX idx_data (data_relatorio),
    INDEX idx_tipo (tipo_relatorio),
    INDEX idx_responsavel (responsavel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Tabela: itens_relatorio
-- ========================================
CREATE TABLE IF NOT EXISTS itens_relatorio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relatorio_id INT NOT NULL,
    descricao_verificacao VARCHAR(255) NOT NULL,
    resultado VARCHAR(20) COMMENT 'ok, nao_ok, n_aplicavel',
    observacao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relatorio_id) REFERENCES relatorios(id) ON DELETE CASCADE,
    INDEX idx_relatorio (relatorio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Dados de exemplo - Tipos de Equipamentos
-- ========================================
INSERT INTO tipos_equipamentos (nome, descricao, prefixo_numeracao, frequencia_inspecao) VALUES
    ('Extintores de Incêndio', 'Extintores portáteis para combate a incêndios', 'EXT', 365),
    ('Hidrantes', 'Hidrantes interiores e exteriores', 'HID', 180),
    ('Luminárias de Emergência', 'Luminárias de emergência e sinalização', 'LUM', 365),
    ('Portas Corta-Fogo', 'Portas e sistemas corta-fogo', 'PCF', 365),
    ('Escadas de Emergência', 'Escadas e caminhos de evacuação', 'ESC', 180),
    ('Sistemas de Deteção de Fumo', 'Detetores de fumo e alarmes', 'SDF', 180),
    ('Bombas de Incêndio', 'Sistemas de bombagem de água', 'BOM', 90),
    ('Quadros Elétricos', 'Quadros de controlo e distribuição', 'QEL', 365);

-- ========================================
-- Campos dinâmicos exemplo - Extintores
-- ========================================
INSERT INTO tipos_equipamentos_campos (tipo_equipamento_id, nome_campo, slug, tipo_dado, unidade, obrigatorio, ordem) VALUES
    (1, 'Capacidade', 'capacidade', 'numero', 'kg', TRUE, 1),
    (1, 'Classe de Fogo', 'classe_fogo', 'texto', NULL, TRUE, 2),
    (1, 'Agente Extintor', 'agente_extintor', 'texto', NULL, TRUE, 3),
    (1, 'Pressão de Serviço', 'pressao_servico', 'numero', 'bar', FALSE, 4);

-- ========================================
-- Dados de exemplo - Utilizador Administrador
-- ========================================
INSERT INTO utilizadores (nome, email, senha, funcao) VALUES
    ('Administrador', 'admin@autoprotecao.com', '$2y$10$SomeHashedPasswordHere', 'administrador');

-- Commit das alterações
COMMIT;
