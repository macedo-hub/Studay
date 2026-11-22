-- ============================================================================
-- STUDAY — Banco de dados (MySQL / MariaDB)
--
-- COMO USAR NO INFINITYFREE:
--   1) No painel, abra "MySQL Databases" e crie um banco.
--   2) Clique em "phpMyAdmin" ao lado do banco criado.
--   3) Aba "Importar" -> escolha este arquivo (banco.sql) -> Executar.
--   4) Preencha os dados do banco em config/config.php.
--
-- Pode rodar de novo sem medo: só cria o que ainda não existe.
-- ============================================================================

-- Pessoas que criaram conta no Studay
CREATE TABLE IF NOT EXISTS usuarios (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome              VARCHAR(60)  NOT NULL,
    apelido           VARCHAR(20)  NOT NULL,            -- é o nome que aparece no ranking
    email             VARCHAR(120) NOT NULL,
    senha_hash        VARCHAR(255) NOT NULL,            -- senha criptografada (nunca a senha de verdade)
    papel             VARCHAR(10)  NOT NULL DEFAULT 'aluno',   -- 'aluno' ou 'admin'
    avatar            VARCHAR(40)  NOT NULL DEFAULT 'faisca-classico',  -- foto de perfil escolhida (veja includes/avatares.php)
    apresentacao_vista TINYINT(1)  NOT NULL DEFAULT 0,         -- 1 = já viu a apresentação do Faísca
    xp_total          INT          NOT NULL DEFAULT 0,
    sequencia_dias    INT          NOT NULL DEFAULT 0,  -- dias seguidos estudando
    maior_sequencia   INT          NOT NULL DEFAULT 0,
    ultimo_dia_ativo  DATE         NULL,
    criado_em         DATETIME     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_apelido (apelido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sites das outras equipes da turma (o portal)
CREATE TABLE IF NOT EXISTS sites_turma (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_site      VARCHAR(80)  NOT NULL,
    equipe         VARCHAR(120) NOT NULL,              -- nome da equipe ou dos integrantes
    disciplina     VARCHAR(40)  NOT NULL,              -- ex.: História, Matemática...
    descricao      VARCHAR(300) NOT NULL,              -- o que tem no site
    perspectiva    VARCHAR(400) NOT NULL DEFAULT '',   -- como a equipe enxergou o ENEM
    link           VARCHAR(255) NOT NULL,
    imagem_capa    VARCHAR(80)  NOT NULL DEFAULT '',   -- nome do arquivo dentro de img/sites/
    validado_por   VARCHAR(80)  NOT NULL DEFAULT '',   -- professor(a) que validou o conteúdo
    testado_por    VARCHAR(80)  NOT NULL DEFAULT '',   -- equipe que testou o site
    fontes         VARCHAR(400) NOT NULL DEFAULT '',   -- fontes e créditos
    ativo          TINYINT(1)   NOT NULL DEFAULT 1,
    ordem          INT          NOT NULL DEFAULT 0,
    criado_em      DATETIME     NOT NULL,
    atualizado_em  DATETIME     NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sites_ativo_ordem (ativo, ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cada vez que alguém abre um site da turma pelo Studay
CREATE TABLE IF NOT EXISTS cliques_sites (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    site_id     INT UNSIGNED NOT NULL,
    usuario_id  INT UNSIGNED NULL,                      -- vazio se a pessoa não estava logada
    criado_em   DATETIME     NOT NULL,
    PRIMARY KEY (id),
    KEY idx_cliques_site (site_id),
    CONSTRAINT fk_cliques_site FOREIGN KEY (site_id) REFERENCES sites_turma (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tudo que deu XP para alguém (serve para o ranking da semana)
CREATE TABLE IF NOT EXISTS eventos_xp (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL,
    tipo        VARCHAR(30)  NOT NULL,                  -- pomodoro, faisca, quiz...
    xp          INT          NOT NULL,
    referencia  VARCHAR(40)  NULL,                      -- ex.: id do site visitado
    dia         DATE         NOT NULL,
    criado_em   DATETIME     NOT NULL,
    PRIMARY KEY (id),
    KEY idx_eventos_usuario_dia (usuario_id, dia),
    KEY idx_eventos_dia (dia),
    CONSTRAINT fk_eventos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conquistas (medalhas) que cada pessoa já ganhou
CREATE TABLE IF NOT EXISTS conquistas_usuarios (
    usuario_id     INT UNSIGNED NOT NULL,
    codigo         VARCHAR(30)  NOT NULL,
    conquistada_em DATETIME     NOT NULL,
    PRIMARY KEY (usuario_id, codigo),
    CONSTRAINT fk_conquistas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resultado de cada vez que a pessoa fez o quiz do Modo ENEM
CREATE TABLE IF NOT EXISTS resultados_quiz (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL,
    quiz        VARCHAR(30)  NOT NULL,                  -- ex.: era_vargas
    acertos     INT          NOT NULL,
    total       INT          NOT NULL,
    criado_em   DATETIME     NOT NULL,
    PRIMARY KEY (id),
    KEY idx_quiz_usuario (usuario_id),
    CONSTRAINT fk_quiz_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
