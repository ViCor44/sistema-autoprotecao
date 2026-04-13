-- ========================================
-- Migração: Agendamentos por tipo de equipamento
-- Data: 2026-04-13
-- ========================================

USE sistema_autoprotecao;

-- 1) Adicionar tipo_equipamento_id para suportar inspeção por tipo
ALTER TABLE calendarios_manutencao
    ADD COLUMN tipo_equipamento_id INT NULL AFTER id;

-- 2) Popular tipo_equipamento_id para registos já existentes (por equipamento)
UPDATE calendarios_manutencao c
JOIN equipamentos e ON c.equipamento_id = e.id
SET c.tipo_equipamento_id = e.tipo_equipamento_id
WHERE c.tipo_equipamento_id IS NULL;

-- 3) Tornar equipamento opcional (agendamento pode ser do tipo inteiro)
ALTER TABLE calendarios_manutencao
    MODIFY COLUMN equipamento_id INT NULL;

-- 4) Definir tipo_equipamento_id como obrigatório
ALTER TABLE calendarios_manutencao
    MODIFY COLUMN tipo_equipamento_id INT NOT NULL;

-- 5) Chaves e índices
ALTER TABLE calendarios_manutencao
    ADD CONSTRAINT fk_cal_tipo_equipamento FOREIGN KEY (tipo_equipamento_id) REFERENCES tipos_equipamentos(id),
    ADD INDEX idx_tipo_equipamento (tipo_equipamento_id);
