-- ============================================================================
-- STUDAY — Atualização do banco (Simulado do ENEM)
--
-- QUANDO USAR: só se você JÁ tinha importado o banco.sql antes desta versão.
-- Se você vai importar o banco.sql agora, NÃO precisa deste arquivo.
--
-- COMO USAR: phpMyAdmin -> escolha o seu banco -> aba "SQL" -> cole este conteúdo -> Executar.
-- Pode rodar mais de uma vez sem problema (só cria o que ainda não existe).
-- Sem este arquivo o site continua funcionando, mas o simulado não guarda
-- preferências, histórico, dúvidas nem erros.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- Simulado do ENEM
-- ---------------------------------------------------------------------------

-- As opções que a pessoa escolheu no simulado (tempo, gabarito na hora etc.)
CREATE TABLE IF NOT EXISTS preferencias_simulado (
    usuario_id    INT UNSIGNED NOT NULL,
    dados         TEXT         NOT NULL,               -- opções em JSON
    atualizado_em DATETIME     NOT NULL,
    PRIMARY KEY (usuario_id),
    CONSTRAINT fk_pref_sim_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cada simulado que a pessoa terminou
CREATE TABLE IF NOT EXISTS resultados_simulado (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id   INT UNSIGNED NOT NULL,
    total        INT          NOT NULL,                -- questões do simulado
    respondidas  INT          NOT NULL,
    acertos      INT          NOT NULL,
    duracao_seg  INT          NOT NULL DEFAULT 0,
    por_area     TEXT         NULL,                    -- acertos por área, em JSON
    criado_em    DATETIME     NOT NULL,
    PRIMARY KEY (id),
    KEY idx_res_sim_usuario (usuario_id),
    CONSTRAINT fk_res_sim_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Questões marcadas como "dúvida" ou que a pessoa errou (caderno de erros)
CREATE TABLE IF NOT EXISTS questoes_salvas (
    usuario_id  INT UNSIGNED NOT NULL,
    questao_id  VARCHAR(30)  NOT NULL,                 -- ex.: 2023-96
    tipo        VARCHAR(10)  NOT NULL,                 -- 'duvida' ou 'erro'
    criado_em   DATETIME     NOT NULL,
    PRIMARY KEY (usuario_id, questao_id, tipo),
    CONSTRAINT fk_questoes_salvas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
