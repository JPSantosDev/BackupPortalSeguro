-- ============================================================
-- Voz School - Sistema de Denúncia de Bullying
-- SESI IBURA - Projeto 3º Ano C
-- Banco de dados MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS voz_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE voz_school;

-- --------------------------------------------------------
-- Tabela de usuários
-- tipo_usuario: 1 = Aluno/Denunciante | 2 = Atendente | 3 = Administrador
-- --------------------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    turma VARCHAR(40) DEFAULT NULL,
    tipo_usuario TINYINT NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Tipos de denúncia (cadastrados pelo administrador)
-- --------------------------------------------------------
CREATE TABLE tipos_denuncia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Denúncias registradas pelos alunos
-- status: pendente | em_andamento | resolvida | arquivada
-- --------------------------------------------------------
CREATE TABLE denuncias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    tipo_denuncia_id INT NOT NULL,
    anonima TINYINT(1) NOT NULL DEFAULT 0,
    local_ocorrencia VARCHAR(150) DEFAULT NULL,
    envolvidos VARCHAR(255) DEFAULT NULL,
    data_ocorrencia DATE DEFAULT NULL,
    descricao TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    atendente_id INT DEFAULT NULL,
    resposta TEXT DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (tipo_denuncia_id) REFERENCES tipos_denuncia(id),
    FOREIGN KEY (atendente_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE denuncia_tipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    denuncia_id INT NOT NULL,
    tipo_denuncia_id INT NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_denuncia_tipo (denuncia_id, tipo_denuncia_id),
    KEY idx_denuncia_tipo (tipo_denuncia_id),
    CONSTRAINT fk_denuncia_tipos_denuncia FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
    CONSTRAINT fk_denuncia_tipos_tipo FOREIGN KEY (tipo_denuncia_id) REFERENCES tipos_denuncia(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Dados iniciais
-- --------------------------------------------------------

-- Tipos de denúncia padrão
INSERT INTO tipos_denuncia (nome, descricao) VALUES
('Bullying físico', 'Agressões físicas, empurrões, socos ou qualquer violência corporal'),
('Bullying verbal', 'Apelidos ofensivos, xingamentos, ameaças e humilhações verbais'),
('Cyberbullying', 'Ofensas, ameaças ou exposição feitas por redes sociais e aplicativos'),
('Exclusão social', 'Isolamento proposital, exclusão de grupos e atividades'),
('Discriminação', 'Preconceito por raça, gênero, religião, orientação sexual ou aparência'),
('Outro', 'Situações que não se encaixam nas categorias acima');

-- Usuário administrador padrão (login: admin@vozschool.sesi.br | senha: admin123)
INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES
('Administrador Voz School', 'admin@vozschool.sesi.br', '$2b$10$SkDqbMqfNPhHxyEK8o8N0ObsPYbQqpwHM6wXxunteEuh66AQvGalO', 3);

-- Usuário atendente de exemplo (login: atendente@vozschool.sesi.br | senha: atendente123)
INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES
('Equipe de Apoio SESI', 'atendente@vozschool.sesi.br', '$2b$10$V3leKoQg4uwh9U6VEjrvqu7iXKAa3bLzq0XmiLnTj8n61QirsMtzC', 2);

-- IMPORTANTE: troque essas senhas assim que possível após a primeira instalação.
