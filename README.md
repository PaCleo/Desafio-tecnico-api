# Desafio Técnico Sicredi API

API RESTful desenvolvida em PHP com Slim Framework 4 para o desafio técnico do Sicredi.

## 🚀 Tecnologias

- **PHP 8+**
- **Slim Framework 4** (Micro-framework)
- **Eloquent ORM** (Banco de dados)
- **SQLite** (Banco de dados file-based)
- **PHP-JWT** (Autenticação)
- **Swagger/OpenAPI** (Documentação)

## 📋 Pré-requisitos

- PHP 8.0 ou superior instalado.
- Composer instalado.

## 📦 Instalação

1.  Clone o repositório.
2.  Instale as dependências:
    ```bash
    composer install
    ```
3.  Copie o arquivo de exemplo de ambiente:
    ```bash
    cp .env.example .env
    # Ou no Windows: copy .env.example .env
    ```
4.  Crie o arquivo do banco de dados SQLite (se não existir):
    ```bash
    # Windows PowerShell
    New-Item -ItemType File -Path database.sqlite -Force
    ```
5.  Rode as migrações para criar as tabelas:
    ```bash
    php scripts/migrate.php
    ```

## 🛠️ Como Rodar

Para iniciar o servidor embutido do PHP:

```bash
php -S localhost:8000 -t public
```

A API estará acessível em: `http://localhost:8000`

## 📖 Documentação da API (Swagger)

A documentação interativa está disponível em:

**[http://localhost:8000/docs](http://localhost:8000/docs)**

Lá você pode testar todos os endpoints diretamente pelo navegador.

## 🧪 Testes

### Postman
Importe o arquivo `postman_collection.json` (na raiz do projeto) para o Postman.

### Endpoints Principais

**Autenticação:**
- `POST /register`: Criar novo usuário.
- `POST /login`: Receber Token JWT.

**Associados (Requer Token Bearer):**
- `GET /api/associados`: Listar todos.
- `POST /api/associados`: Criar associado.
- `GET /api/associados/{id}`: Detalhes.
- `PUT /api/associados/{id}`: Atualizar.
- `DELETE /api/associados/{id}`: Remover.

## 🔒 Autenticação

Esta API usa **JWT (JSON Web Tokens)**.
1.  Faça login em `/login`.
2.  Copie o `token` retornado.
3.  Nas requisições protegidas, use o Header:
    `Authorization: Bearer <seu_token>`
