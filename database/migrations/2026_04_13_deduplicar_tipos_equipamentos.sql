-- ========================================
-- Migração: Deduplicar tipos_equipamentos
-- Data: 2026-04-13
-- ========================================

USE sistema_autoprotecao;

-- 1) Criar mapa de duplicados (manter menor id por nome)
DROP TEMPORARY TABLE IF EXISTS tmp_tipos_map;
CREATE TEMPORARY TABLE tmp_tipos_map AS
SELECT nome, MIN(id) AS id_keep
FROM tipos_equipamentos
GROUP BY nome;

-- 2) Atualizar referências em equipamentos
UPDATE equipamentos e
JOIN tipos_equipamentos te ON e.tipo_equipamento_id = te.id
JOIN tmp_tipos_map m ON te.nome = m.nome
SET e.tipo_equipamento_id = m.id_keep;

-- 3) Atualizar referências em calendarios_manutencao (se coluna existir)
UPDATE calendarios_manutencao c
JOIN tipos_equipamentos te ON c.tipo_equipamento_id = te.id
JOIN tmp_tipos_map m ON te.nome = m.nome
SET c.tipo_equipamento_id = m.id_keep;

-- 4) Atualizar referências em tipos_equipamentos_campos
UPDATE tipos_equipamentos_campos tc
JOIN tipos_equipamentos te ON tc.tipo_equipamento_id = te.id
JOIN tmp_tipos_map m ON te.nome = m.nome
SET tc.tipo_equipamento_id = m.id_keep;

-- 5) Remover registos duplicados mantendo apenas id_keep por nome
DELETE te
FROM tipos_equipamentos te
JOIN tmp_tipos_map m ON te.nome = m.nome
WHERE te.id <> m.id_keep;

-- 6) Garantir unicidade para evitar novas duplicações
ALTER TABLE tipos_equipamentos
ADD UNIQUE KEY uk_tipo_nome (nome);
