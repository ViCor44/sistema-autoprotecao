# Sistema de Autoproteção

Sistema completo de gestão de relatórios de medidas de autoproteção, desenvolvido em **PHP com padrão MVC** e **MySQL**.

## Características Principais

- ✅ **Inventário de Equipamentos**: Registe e gerencie todos os equipamentos de autoproteção (extintores, hidrantes, luminárias de emergência, etc.)
- ✅ **Relatórios de Inspeção**: Crie relatórios detalhados de inspeção e manutenção
- ✅ **Calendário de Manutenção**: Agende e controle datas de manutenção preventiva
- ✅ **Dashboard Intuitivo**: Visualize rapidamente o estado geral do sistema
- ✅ **Autenticação de Utilizadores**: Sistema seguro com gestão de utilizadores
- ✅ **Interface Responsiva**: Design moderno com Bootstrap 5

## Requisitos do Sistema

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- XAMPP (para desenvolvimento local)
- Modern web browser

## Instalação

### 1. Clonar o Repositório

```bash
cd c:\xampp\htdocs
git clone <url-do-repositorio> sistema-autoprotecao
cd sistema-autoprotecao
```

### 2. Configurar a Base de Dados

1. Abra o phpMyAdmin (http://localhost/phpmyadmin)
2. Execute o script SQL:
   ```sql
   -- Coloque o conteúdo de database/schema.sql aqui
   ```
3. Ou importe o ficheiro `database/schema.sql` diretamente

### 3. Configurar as Variáveis de Ambiente

A configuração padrão já está em `config/Config.php`:
- Host: localhost
- User: root
- Password: (vazio)
- Database: sistema_autoprotecao

Se precisar alterar, edite o ficheiro `config/Config.php`.

### 4. Aceder à Aplicação

Abra no seu browser:
```
http://localhost/sistema-autoprotecao/public/
```

## Dados de Teste

**Login padrão:**
- Email: `admin@autoprotecao.com`
- Senha: Altere após o primeiro acesso

## Estrutura do Projeto

```
sistema-autoprotecao/
├── app/
│   ├── controllers/          # Controllers da aplicação
│   ├── models/              # Modelos de dados
│   ├── views/               # Templates HTML
│   │   ├── equipamentos/    # Views de equipamentos
│   │   ├── relatorios/      # Views de relatórios
│   │   ├── calendario/      # Views de calendário
│   │   ├── home/            # Views da homepage
│   │   └── layouts/         # Template principal
│   └── helpers/             # Funções auxiliares
├── config/                  # Ficheiros de configuração
├── public/                  # Ficheiros públicos
│   ├── index.php           # Ponto de entrada
│   ├── css/                # Estilos CSS
│   └── js/                 # Scripts JavaScript
├── database/               # Scripts de base de dados
└── README.md              # Este ficheiro
```

## Funcionalidades Principais

### 1. Gestão de Equipamentos
- Registar novos equipamentos
- Categorizar por tipo (extintores, hidrantes, etc.)
- Rastrear localização e estado operacional
- Agendar manutenções

### 2. Relatórios
- Criar relatórios de inspeção
- Adicionar itens de verificação
- Registar condições encontradas
- Assinar digitalmente

### 3. Calendário
- Visualizar calendário de manutenções
- Agendar inspeções periódicas
- Marcar prioridades
- Acompanhar histórico

### 4. Dashboard
- Resumo do estado geral
- Alertas de manutenções vencidas
- Próximas manutenções
- Estatísticas rápidas

## Fluxo de Utilização

1. **Login** → Autentifique-se no sistema
2. **Dashboard** → Visualize o estado geral
3. **Equipamentos** → Registar equipamentos (se necessário)
4. **Calendário** → Agendar manutenções
5. **Relatórios** → Criar e assinar relatórios
6. **Acompanhamento** → Monitorar histórico

## Contribuição

Para contribuir com melhorias:
1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Suporte

Em caso de dúvidas ou problemas, contacte o administrador do sistema.

## Licença

Este projeto está licenciado sob a MIT License - veja o ficheiro LICENSE para detalhes.

---

**Desenvolvido com ❤️ para garantir a segurança e autoproteção.**