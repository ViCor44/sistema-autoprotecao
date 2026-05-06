-- ========================================
-- Migração: Numeração Automática de Equipamentos
-- Data: 2026-05-06
-- Garante que os campos de numeração automática existem
-- ========================================

-- Adicionar prefixo_numeracao em tipos_equipamentos (se não existir)
ALTER TABLE tipos_equipamentos
    ADD COLUMN IF NOT EXISTS prefixo_numeracao VARCHAR(20) NULL COMMENT 'Prefixo para numeração dos equipamentos, ex: EXT, HID, LUM',
    ADD COLUMN IF NOT EXISTS proximo_numero INT NOT NULL DEFAULT 1 COMMENT 'Próximo número sequencial do tipo de equipamento';

-- Adicionar numero_registo em equipamentos (se não existir)
ALTER TABLE equipamentos
    ADD COLUMN IF NOT EXISTS numero_registo VARCHAR(100) NULL UNIQUE COMMENT 'Número de registo gerado automaticamente, ex: EXT-0001'
    AFTER tipo_equipamento_id;

-- Inicializar proximo_numero com base nos equipamentos existentes (por tipo)
-- Para cada tipo, o próximo número será o total de equipamentos ativos + 1
UPDATE tipos_equipamentos t
SET t.proximo_numero = (
    SELECT COUNT(*) + 1
    FROM equipamentos e
    WHERE e.tipo_equipamento_id = t.id AND e.ativo = TRUE
)
WHERE t.proximo_numero = 1;
