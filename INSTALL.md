# Guia de Instalação - Sistema de Autoproteção

## Pré-requisitos

Antes de começar, certifique-se de que tem:
- XAMPP instalado (ou Apache + PHP 7.4+ + MySQL 5.7+)
- Git instalado
- Um browser web moderno

## Passo 1: Preparar o Ambiente

### Windows com XAMPP:

1. **Iniciar XAMPP**
   - Abra o painel de controle do XAMPP
   - Clique em "Start" ao lado de "Apache"
   - Clique em "Start" ao lado de "MySQL"

2. **Verificar se está funcionando**
   - Abra http://localhost/dashboard no seu browser
   - Você deve ver o painel do XAMPP

## Passo 2: Clonar ou Extrair o Projeto

### Opção A: Clonar com Git

```bash
cd C:\xampp\htdocs
git clone <url-do-repositorio> sistema-autoprotecao
cd sistema-autoprotecao
```

### Opção B: Extrair arquivo ZIP

1. Descarregue o arquivo ZIP do projeto
2. Extraia para `C:\xampp\htdocs\sistema-autoprotecao`

## Passo 3: Criar a Base de Dados

### Opção A: Via phpMyAdmin (GUI)

1. Abra http://localhost/phpmyadmin no seu browser
2. Faça login (normalmente sem senha)
3. Clique em "Importar"
4. Selecione o ficheiro `database/schema.sql`
5. Clique em "Execute"

### Opção B: Via Terminal MySQL

```bash
mysql -u root -p < database\schema.sql
```

(Se tiver password, adicione `-p` e será pedida)

## Passo 4: Verificar Configurações

Abra o ficheiro `config/Config.php` e verifique:

```php
define('DB_HOST', 'localhost');      // Host do MySQL
define('DB_USER', 'root');           // Utilizador MySQL
define('DB_PASS', '');               // Password (vazio por padrão no XAMPP)
define('DB_NAME', 'sistema_autoprotecao');
```

Se tiver alterado as credenciais do MySQL, atualize aqui.

## Passo 5: Acessar a Aplicação

Abra no seu browser:

```
http://localhost/sistema-autoprotecao/public/
```

## Passo 6: Inicial Sessão

Use as credenciais padrão:

**Email:** `admin@autoprotecao.com`
**Senha:** (será pedida para alterar no primeiro acesso)

> ⚠️ **IMPORTANTE**: Altere a senha padrão imediatamente após o primeiro acesso!

## Solução de Problemas

### "Erro na conexão com a base de dados"

1. Verifique se MySQL está em execução
2. Verifique as credenciais em `config/Config.php`
3. Certifique-se de que a base de dados `sistema_autoprotecao` foi criada

### "Página em branco"

1. Verifique se PHP está habilitado em Apache
2. Verifique o ficheiro `php.ini` para erros de configuração
3. Verifique os logs do Apache em `c:\xampp\apache\logs\`

### "Erros de permissão"

Certifique-se de que os ficheiros têm permissões adequadas:
- Pasta `app/` legível
- Pasta `public/` acessível

### "Esqueci a senha"

Execute o seguinte comando MySQL:

```sql
UPDATE utilizadores SET senha = '$2y$10$...' WHERE email = 'admin@autoprotecao.com';
```

Ou crie um novo utilizador via SQL:

```sql
INSERT INTO utilizadores (nome, email, senha, funcao) 
VALUES ('Novo Admin', 'novo@admin.com', '$2y$10$SomeHashedPassword', 'administrador');
```

## Configuração Adicional (Opcional)

### Alterar Fuso Horário

Edite `config/Config.php`:

```php
date_default_timezone_set('America/New_York'); // ou outro fuso
```

### Ativar/Desativar Debug

Edite `config/Config.php`:

```php
define('APP_DEBUG', false); // Desabilitar em produção
```

## Próximos Passos

1. ✅ [Guia de Utilização](./USAGE.md)
2. ✅ [Documentação da API](./API.md) (em desenvolvimento)
3. ✅ [Perguntas Frequentes](./FAQ.md) (em desenvolvimento)

## Suporte

Se encontrar problemas na instalação, contacte [email de suporte].

---

**Parabéns! O seu Sistema de Autoproteção está pronto para usar! 🎉**
