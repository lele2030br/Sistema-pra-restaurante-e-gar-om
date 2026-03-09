# RestoPRO — Sistema simples para restaurante / garçons

Um sistema leve em PHP + MySQL/MariaDB para gestão de mesas, pedidos, produtos e equipe, projetado para uso local em pequenos restaurantes, bares e similares. Interface minimalista com TailwindCSS e autenticação por PIN numérico (4 dígitos).

Visite os arquivos principais:
- `config.php` — conexão ao banco, criação automática das tabelas e usuário admin padrão.
- `login.php` — tela de login por PIN.
- `index.php` — painel do salão (mesas).
- `pedido.php` — painel de atendimento por mesa (lançar itens, fechar conta).
- `admin.php` — painel administrativo (gerenciar mesas, produtos, equipe, caixa).

## Recursos
- Gerenciamento de mesas (criar/excluir).
- Cadastro e remoção de produtos.
- Cadastro e remoção de usuários (nível "garcom"; existe um admin padrão).
- Lançamento de itens em pedidos por mesa e fechamento (apenas admin).
- Relatórios rápidos: Caixa do dia, ranking de melhores vendedores e fluxo recente.
- Criação automática das tabelas ao iniciar (quando o banco estiver configurado).

## Requisitos
- PHP 7.4+ (a dump indica 7.4; funciona também em 8.x).
- Extensão PDO e pdo_mysql habilitadas.
- MySQL ou MariaDB.
- Servidor web (Apache/Nginx) configurado para servir o diretório do projeto.
- Composer não é necessário (sem dependências externas).

## Instalação rápida (local)
1. Clone o repositório para sua máquina/servidor:
   git clone https://github.com/lele2030br/Sistema-pra-restaurante-e-gar-om.git

2. Crie o banco de dados (ex.: `geilirmj_R`) e importe o dump SQL (`geilirmj_R.sql`) usando phpMyAdmin, MySQL CLI ou ferramenta preferida:
   - Usando mysql CLI:
     mysql -u usuario -p nome_do_banco < geilirmj_R.sql

3. Atualize as credenciais do banco em `config.php`:
   ```php
   $host = 'localhost';
   $dbname = 'nome_do_banco';
   $user = 'seu_usuario';
   $pass = 'sua_senha';
   ```

4. Garanta permissões ao servidor web e que a extensão PDO esteja ativa.

5. Acesse via navegador:
   - Página de login: `http://seu-host/login.php`
   - Após login, você será direcionado ao salão (`index.php`).
   - Usuário admin padrão: PIN `9999` (criado automaticamente por `config.php`).

## Uso básico
- Login: digite o PIN numérico (teclado virtual) e pressione OK.
- Salão (`index.php`): clique na mesa para abrir `pedido.php`.
- Pedido (`pedido.php`): selecione produto, informe quantidade e clique em "Lançar Item".
- Fechamento de conta: apenas usuário de nível `admin` pode encerrar (botão "Encerrar Conta").
- Admin (`admin.php`): gerencie mesas, produtos e equipe; veja caixa do dia e ranking.

## Banco de dados (estrutura)
O repositório já contém `geilirmj_R.sql` com a estrutura e alguns dados de exemplo:
- tabelas: `usuarios`, `mesas`, `produtos`, `pedidos`, `itens_pedido`
- admin padrão com PIN `9999`

Se quiser recriar o admin manualmente:
- SQL para criar/resetar admin:
  ```sql
  INSERT INTO usuarios (nome, pin, nivel) VALUES ('Gerente', '9999', 'admin')
  -- ou para resetar o PIN do admin existente:
  UPDATE usuarios SET pin = '9999' WHERE nivel = 'admin' LIMIT 1;
  ```

## Segurança — notas importantes (leia antes de usar em produção)
O projeto é pensado para uso local/ambientes controlados. Se for expor à internet, aplique as seguintes melhorias:
- NÃO armazene PINs em texto plano. Use hashing (ex.: password_hash / password_verify) ou outro método seguro.
- Adicione CSRF tokens em formulários para evitar requisições forjadas.
- Valide estritamente todas as entradas (tipos, tamanhos, valores) do lado servidor.
- Restrinja exibição de erros em produção (em `config.php` ajuste error_reporting e display_errors).
- Use HTTPS, cookies de sessão configurados como Secure, HttpOnly e SameSite.
- Considere controle de acesso mais robusto (roles/permissions) e logging de ações sensíveis.

Observação: o código já usa PDO e prepared statements na maior parte das operações, o que reduz injeção SQL, mas mantenha validações adicionais conforme necessário.

## Sugestões de melhorias
- Hash de PINs e autenticação mais robusta (e.g., e-mail/senha ou 2FA).
- Telas para edição de produtos/usuários (atualmente só inclusão e exclusão).
- Geração de relatórios (filtrar por datas, exportar CSV).
- Histórico de ações e auditoria.
- Internacionalização (i18n) se necessário.
- Testes automatizados e CI.

## Contribuições
Pull requests são bem-vindos. Abra uma issue para discutir mudanças maiores antes de começar a trabalhar. Siga o padrão do código existente e mantenha as alterações pequenas e testáveis.

## Licença
Este projeto está licenciado sob a MIT License — veja o arquivo `LICENSE` para detalhes.

---

Se algo no README estiver incompleto ou você quiser que eu gere um modelo de CHANGELOG, exemplos de queries SQL úteis ou um script de inicialização (shell/php) que automatize a criação do banco e configuração, posso criar isso agora.