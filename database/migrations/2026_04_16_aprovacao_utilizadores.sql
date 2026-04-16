-- ========================================
-- Migração: Registo e aprovação de utilizadores
-- Data: 2026-04-16
-- ========================================

USE sistema_autoprotecao;

ALTER TABLE utilizadores
    ADD COLUMN aprovado BOOLEAN NOT NULL DEFAULT FALSE AFTER ativo,
    ADD COLUMN aprovado_por INT NULL AFTER aprovado,
    ADD COLUMN data_aprovacao TIMESTAMP NULL AFTER aprovado_por,
    ADD INDEX idx_aprovado (aprovado),
    ADD CONSTRAINT fk_utilizadores_aprovado_por FOREIGN KEY (aprovado_por) REFERENCES utilizadores(id);

UPDATE utilizadores
SET aprovado = TRUE,
    data_aprovacao = COALESCE(data_aprovacao, data_criacao)
WHERE ativo = TRUE;