-- ========================================
-- Migração: Registo da execução das inspeções agendadas
-- Data: 2026-04-15
-- ========================================

USE sistema_autoprotecao;

ALTER TABLE calendarios_manutencao
    ADD COLUMN parecer TEXT NULL AFTER descricao,
    ADD COLUMN equipamentos_avariados TEXT NULL AFTER parecer,
    ADD COLUMN observacoes TEXT NULL AFTER equipamentos_avariados,
    ADD COLUMN condicoes_encontradas VARCHAR(30) NULL AFTER observacoes,
    ADD COLUMN proxima_inspecao DATE NULL AFTER condicoes_encontradas,
    ADD COLUMN data_realizacao DATETIME NULL AFTER proxima_inspecao;

UPDATE calendarios_manutencao
SET status = 'concluido'
WHERE status = 'concluida';