-- =========================================================
-- Voz School — Migração para as novas funcionalidades
-- Rode este script no banco já existente (não apaga nada).
-- =========================================================

-- 1) CPF no cadastro de usuários (permite localizar denúncias
--    feitas antes de criar conta) + flag do tutorial de 1º acesso
ALTER TABLE usuarios
  ADD COLUMN cpf VARCHAR(14) NULL UNIQUE AFTER email,
  ADD COLUMN tutorial_visto TINYINT(1) NOT NULL DEFAULT 0 AFTER tipo_usuario;

-- 2) Denúncias feitas sem cadastro (só CPF) + protocolo de
--    acompanhamento + anexos de imagem e áudio
ALTER TABLE denuncias
  ADD COLUMN cpf_denunciante VARCHAR(14) NULL AFTER usuario_id,
  ADD COLUMN protocolo VARCHAR(12) NULL UNIQUE AFTER cpf_denunciante,
  ADD COLUMN anexo_imagem VARCHAR(255) NULL AFTER descricao,
  ADD COLUMN anexo_audio VARCHAR(255) NULL AFTER anexo_imagem;

-- usuario_id já é usado com LEFT JOIN no sistema (denúncia de
-- usuário removido), então já aceita NULL. Caso a sua coluna
-- ainda esteja como NOT NULL, rode a linha abaixo:
-- ALTER TABLE denuncias MODIFY usuario_id INT NULL;

CREATE INDEX idx_denuncias_cpf ON denuncias (cpf_denunciante);
CREATE INDEX idx_denuncias_protocolo ON denuncias (protocolo);
