-- ========================================
-- Migração: Relatório nasce da inspeção
-- Data: 2026-04-13
-- ========================================

USE sistema_autoprotecao;

-- 1) Preparar estrutura de relatorios para ligação com inspeções
ALTER TABLE relatorios
    ADD COLUMN calendario_id INT NULL UNIQUE AFTER id,
    ADD COLUMN tipo_equipamento_id INT NULL AFTER calendario_id,
    MODIFY COLUMN equipamento_id INT NULL;

-- 2) Popular tipo_equipamento_id nos relatórios já existentes
UPDATE relatorios r
JOIN equipamentos e ON r.equipamento_id = e.id
SET r.tipo_equipamento_id = e.tipo_equipamento_id
WHERE r.tipo_equipamento_id IS NULL;

-- 3) Chaves e índices
ALTER TABLE relatorios
    ADD CONSTRAINT fk_relatorio_calendario FOREIGN KEY (calendario_id) REFERENCES calendarios_manutencao(id),
    ADD CONSTRAINT fk_relatorio_tipo_equipamento FOREIGN KEY (tipo_equipamento_id) REFERENCES tipos_equipamentos(id),
    ADD INDEX idx_calendario (calendario_id),
    ADD INDEX idx_tipo_equipamento (tipo_equipamento_id);
