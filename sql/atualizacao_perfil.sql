-- ============================================================================
-- STUDAY — Atualização do banco (perfil com avatar e apresentação do Faísca)
--
-- QUANDO USAR: só se você JÁ tinha importado o banco.sql antes desta versão.
-- Se você vai importar o banco.sql agora, NÃO precisa deste arquivo (ele já vem com tudo).
--
-- COMO USAR: phpMyAdmin -> escolha o seu banco -> aba "SQL" -> cole este conteúdo -> Executar.
-- Rode UMA vez só. Se der erro "Duplicate column name", é porque já foi feito: pode ignorar.
-- ============================================================================

ALTER TABLE usuarios ADD COLUMN avatar VARCHAR(40) NOT NULL DEFAULT 'faisca-classico' AFTER papel;
ALTER TABLE usuarios ADD COLUMN apresentacao_vista TINYINT(1) NOT NULL DEFAULT 0 AFTER avatar;

-- Quem já tinha conta não precisa ver a apresentação de novo (só as contas novas veem).
UPDATE usuarios SET apresentacao_vista = 1;
