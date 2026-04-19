-- ========================================
-- Migração: Adicionar código de barras aos equipamentos
-- Data: 2026-04-19
-- ========================================

-- Adicionar coluna codigo_barras à tabela equipamentos
ALTER TABLE equipamentos 
ADD COLUMN codigo_barras VARCHAR(100) UNIQUE COMMENT 'Código de barras único do equipamento' AFTER numero_registo,
ADD INDEX idx_codigo_barras (codigo_barras);

-- Inserir código de barras para equipamentos existentes (caso existam)
-- O código será gerado com base na forma: TIPO-ID (ex: EXT-000001)
UPDATE equipamentos e
SET codigo_barras = CONCAT(
    COALESCE((SELECT prefixo_numeracao FROM tipos_equipamentos WHERE id = e.tipo_equipamento_id), 'EQP'),
    '-',
    LPAD(e.id, 6, '0')
)
WHERE codigo_barras IS NULL;
