-- Correção da numeração de extintores de incêndio
-- Contexto: os registos EXT-002 e EXT-003 foram saltados devido a erros de INSERT
-- (duplicate entry em numero_serie) que incrementavam o contador antes de falhar.
-- Este problema foi corrigido no modelo Equipamento.php (commit 22e6f00).

-- 1. Corrigir o numero_registo do equipamento registado como EXT-004 para EXT-002
UPDATE equipamentos
SET numero_registo = 'EXT-002'
WHERE numero_registo = 'EXT-004';

-- 2. Repor o contador para o próximo número correto
UPDATE tipos_equipamentos
SET proximo_numero = 3
WHERE prefixo_numeracao = 'EXT';
