# Voz School — Sistema de Denúncia de Bullying

Projeto desenvolvido pela turma **3º Ano C** do **SESI IBURA**.

Sistema web para registro e acompanhamento de denúncias de bullying escolar, com três tipos de usuário (denunciante, atendente e administrador), feito em **PHP + MySQL**, HTML e CSS puro, 100% responsivo.

---

## 1. O que o sistema faz

- **Tela de login/cadastro** — alunos podem se cadastrar sozinhos para denunciar; contas de atendente e administrador são criadas pelo próprio administrador.
- **Home** — painel diferente para cada tipo de usuário, com estatísticas e atalhos.
- **Denúncias** — aluno registra uma denúncia (podendo ser anônima), escolhendo entre os tipos cadastrados (bullying físico, verbal, cyberbullying, exclusão social, discriminação, etc.).
- **Atendimento** — o atendente vê a fila de denúncias, atualiza o status (pendente → em andamento → resolvida/arquivada) e escreve uma resposta para o aluno.
- **Administração** — o administrador cadastra/edita/remove os **tipos de denúncia**, cria contas de atendentes/admins e ativa/desativa usuários, além de ver o painel geral do sistema.

### Tipos de usuário
| Tipo | Como é criado | O que faz |
|---|---|---|
| 1 — Denunciante (aluno) | Cadastro público na tela de login | Envia e acompanha suas denúncias |
| 2 — Atendente | Criado pelo administrador | Atende, atualiza status e responde denúncias |
| 3 — Administrador | Criado via banco de dados (conta inicial) ou por outro admin | Gerencia tipos de denúncia e usuários |

---

## 2. Requisitos

- PHP **8.0 ou superior** com extensão **PDO MySQL** habilitada
- MySQL ou MariaDB
- Um servidor local como **SERVIDOR WEB**, **WAMP**, **Laragon** ou similar

---

## 3. Como instalar (passo a passo)

1. **Copie a pasta** `voz_school` inteira para dentro da pasta pública do seu servidor:
   - XAMPP (Windows): `C:\xampp\htdocs\voz_school`
   - XAMPP (Linux): `/opt/lampp/htdocs/voz_school`
   - Laragon: `C:\laragon\www\voz_school`

2. **Inicie o Apache e o MySQL** pelo painel do XAMPP/WAMP/Laragon.

3. **Crie o banco de dados**:
   - Abra o **phpMyAdmin** (geralmente em `http://localhost/phpmyadmin`)
   - Vá em **Importar** → selecione o arquivo `database.sql` desta pasta → clique em **Executar**
   - Isso cria o banco `voz_school`, as tabelas e os dados iniciais (tipos de denúncia + contas de exemplo)

4. **Confira as credenciais do banco** no arquivo `config/database.php`. Por padrão já está configurado para o XAMPP:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'voz_school');
   define('DB_USER', 'root');
   define('DB_PASS', 'root');
   ```
   Se o seu MySQL tiver senha, ajuste `DB_PASS` aqui.

5. **Acesse o sistema** no navegador:
   ```
   http://localhost/voz_school/
   ```

---

## 4. Contas de acesso iniciais

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | `admin@vozschool.sesi.br` | `admin123` |
| Atendente | `atendente@vozschool.sesi.br` | `atendente123` |

> ⚠️ **Troque essas senhas assim que possível.** Elas existem apenas para você testar o sistema logo após instalar. Alunos criam sua própria conta pela tela de cadastro (aba "Criar conta").

---

## 5. Estrutura de pastas

```
voz_school/
├── config/
│   └── database.php        → conexão com o banco (ajuste usuário/senha aqui)
├── includes/
│   ├── auth.php             → sessão, permissões e funções auxiliares
│   ├── header.php           → menu lateral (varia conforme o tipo de usuário)
│   └── footer.php           → rodapé + script do menu mobile
├── css/
│   └── style.css            → todo o visual do sistema (cores do SESI)
├── database.sql             → script para criar o banco e as tabelas
├── index.php                → login e cadastro
├── home.php                 → painel inicial (varia por tipo de usuário)
├── denuncia_nova.php        → formulário de nova denúncia (aluno)
├── minhas_denuncias.php     → histórico de denúncias do aluno
├── atendente_denuncias.php  → fila de denúncias (atendente)
├── atendente_denuncia_detalhe.php → detalhe + atualização de status (atendente)
├── admin_dashboard.php      → painel geral (administrador)
├── admin_tipos_denuncia.php → cadastro de tipos de denúncia (administrador)
├── admin_usuarios.php       → gestão de usuários (administrador)
└── process_*.php            → arquivos que processam os formulários (back-end)
```

---

## 6. Segurança já implementada

- Senhas armazenadas com `password_hash()` (bcrypt) — nunca em texto puro
- Proteção contra CSRF em todos os formulários
- Consultas ao banco sempre com **prepared statements** (proteção contra SQL Injection)
- Controle de acesso por tipo de usuário em todas as páginas internas
- Opção de denúncia anônima: o nome do aluno não é exibido ao atendente quando marcado

---

## 7. Personalização

- **Cores**: edite as variáveis no topo do arquivo `css/style.css` (`--color-primary`, `--color-accent`, etc.)
- **Tipos de denúncia**: não precisa mexer no código — use a tela **Tipos de denúncia** como administrador
- **Logo**: você pode substituir o ícone SVG usado no cabeçalho por uma imagem do SESI, se preferir, dentro de `includes/header.php` e `index.php`

---

Desenvolvido com 💚 pela turma **3º Ano C** — **SESI Ibura**.
