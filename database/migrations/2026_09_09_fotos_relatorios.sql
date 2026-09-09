-- ========================================
-- Migração: Fotografias nos relatórios
-- Data: 2026-09-09
-- ========================================

CREATE TABLE IF NOT EXISTS relatorios_fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relatorio_id INT NOT NULL,
    caminho VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relatorio_id) REFERENCES relatorios(id) ON DELETE CASCADE,
    INDEX idx_relatorio (relatorio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;