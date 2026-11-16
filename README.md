# Varanda

Rede social simples: perfis, seguidores, publicações com texto e imagem, curtidas e comentários. O nome e o visual (padrão de **cobogó**) vêm da varanda das casas brasileiras.

Projeto de portfólio full stack: API REST em **Laravel 13** (Sanctum, MySQL) e front-end em **Next.js 16** (React 19, CSS puro).

## Funcionalidades

- Cadastro, login e recuperação de senha
- Perfil com foto, bio e contadores
- Seguir / deixar de seguir
- Publicações com texto e/ou uma imagem, com edição e exclusão
- Feed paginado de quem você segue
- Curtidas, comentários e pesquisa de pessoas
- Interface responsiva

## Como rodar

Requisitos: PHP 8.3+, Composer, MySQL 8 e Node.js 20.9+.

**1. Banco de dados**

```sql
CREATE DATABASE varanda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**2. Back-end** (ajuste as variáveis `DB_*` no `.env`)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

**3. Front-end** (em outro terminal)

```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

Acesse `http://localhost:3000`.

## Conta de demonstração

- E-mail: `demo@varanda.test`
- Senha: `varanda123`

Em desenvolvimento, os e-mails de recuperação de senha vão para `backend/storage/logs/laravel.log`.

## Testes

```bash
cd backend
php artisan test
```

Os testes usam SQLite em memória (extensão `pdo_sqlite`).
