-- ========================================
-- Migração: Equipamentos de Reserva
-- Data: 2026-05-13
-- Permite registar equipamentos de reserva (stock) sem numero_registo.
-- ========================================

ALTER TABLE equipamentos
    ADD COLUMN IF NOT EXISTS is_reserva TINYINT(1) NOT NULL DEFAULT 0
        COMMENT 'Equipamento de reserva (stock) sem numeração sequencial' AFTER numero_registo;

-- A coluna numero_registo já é NULLable na migração 2026_05_06.
-- Equipamentos de reserva ficam com numero_registo = NULL.
