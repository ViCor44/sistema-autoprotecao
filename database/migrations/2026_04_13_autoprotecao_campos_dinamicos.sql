-- ========================================
-- Migração: Estrutura extensível para autoproteção
-- Data: 2026-04-13
-- ========================================

USE sistema_autoprotecao;

-- 1) Tipos de equipamentos: numeração por tipo
ALTER TABLE tipos_equipamentos
    ADD COLUMN prefixo_numeracao VARCHAR(20) NULL COMMENT 'Prefixo para numeração dos equipamentos, ex: EXT, HID, LUM',
    ADD COLUMN proximo_numero INT NOT NULL DEFAULT 1 COMMENT 'Próximo número sequencial do tipo de equipamento';

-- 2) Equipamentos: registo interno e vistoria
ALTER TABLE equipamentos
    ADD COLUMN numero_registo VARCHAR(100) NULL UNIQUE COMMENT 'Número interno de registo/património do equipamento',
    ADD COLUMN data_ultima_vistoria DATE NULL;

-- 3) Campos dinâmicos por tipo de equipamento
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

-- 4) Valores dos campos dinâmicos por equipamento
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

-- 5) Prefixos padrão para tipos já existentes
UPDATE tipos_equipamentos SET prefixo_numeracao = 'EXT' WHERE nome LIKE 'Extintores%';
UPDATE tipos_equipamentos SET prefixo_numeracao = 'HID' WHERE nome LIKE 'Hidrantes%';
UPDATE tipos_equipamentos SET prefixo_numeracao = 'LUM' WHERE nome LIKE 'Luminárias%';
UPDATE tipos_equipamentos SET prefixo_numeracao = 'PCF' WHERE nome LIKE 'Portas%';
UPDATE tipos_equipamentos SET prefixo_numeracao = 'ESC' WHERE nome LIKE 'Escadas%';
UPDATE tipos_equipamentos SET prefixo_numeracao = 'SDF' WHERE nome LIKE 'Sistemas de Deteção%';
UPDATE tipos_equipamentos SET prefixo_numeracao = 'BOM' WHERE nome LIKE 'Bombas%';
UPDATE tipos_equipamentos SET prefixo_numeracao = 'QEL' WHERE nome LIKE 'Quadros%';

-- 6) Campos dinâmicos exemplo para extintores
INSERT INTO tipos_equipamentos_campos (tipo_equipamento_id, nome_campo, slug, tipo_dado, unidade, obrigatorio, ordem)
SELECT te.id, 'Capacidade', 'capacidade', 'numero', 'kg', TRUE, 1
FROM tipos_equipamentos te
WHERE te.nome LIKE 'Extintores%' AND NOT EXISTS (
    SELECT 1 FROM tipos_equipamentos_campos c WHERE c.tipo_equipamento_id = te.id AND c.slug = 'capacidade'
);

INSERT INTO tipos_equipamentos_campos (tipo_equipamento_id, nome_campo, slug, tipo_dado, unidade, obrigatorio, ordem)
SELECT te.id, 'Classe de Fogo', 'classe_fogo', 'texto', NULL, TRUE, 2
FROM tipos_equipamentos te
WHERE te.nome LIKE 'Extintores%' AND NOT EXISTS (
    SELECT 1 FROM tipos_equipamentos_campos c WHERE c.tipo_equipamento_id = te.id AND c.slug = 'classe_fogo'
);

INSERT INTO tipos_equipamentos_campos (tipo_equipamento_id, nome_campo, slug, tipo_dado, unidade, obrigatorio, ordem)
SELECT te.id, 'Agente Extintor', 'agente_extintor', 'texto', NULL, TRUE, 3
FROM tipos_equipamentos te
WHERE te.nome LIKE 'Extintores%' AND NOT EXISTS (
    SELECT 1 FROM tipos_equipamentos_campos c WHERE c.tipo_equipamento_id = te.id AND c.slug = 'agente_extintor'
);
