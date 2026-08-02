# We.Did.It

Projeto final — Software Developer, Cesae Digital.

Plataforma de gestão de uma newsletter institucional: recolhe notícias e testemunhos
submetidos pelo público, permite a um administrador aprová-los ou recusá-los, e reúne os
aprovados numa edição de newsletter.

A aplicação Laravel vive na pasta `project/`. A raiz do repositório tem também as atas
(`Atas/`), o modelo de dados (`DataBase/`) e os planos de sprint (`PLANO_SPRINT2/`).

## Requisitos

- PHP 8.3 ou superior, com as extensões `pdo_mysql` e `fileinfo`
- Composer
- MySQL 8
- Node.js 20 e npm

A extensão `intl` é opcional, mas sem ela alguns comandos do artisan que formatam números
falham — o `php artisan db:table` é o caso mais visível. Não afeta a aplicação em si.

## Instalação

```bash
git clone https://github.com/beatrizfiochi/We.Did.It.git
cd We.Did.It/project

composer install
npm install

cp .env.example .env
php artisan key:generate
```

### Configurar o `.env`

O `.env.example` vem com os valores por defeito do Laravel, que **não servem** para este
projeto. Antes de continuar, altera estas quatro coisas:

```dotenv
APP_NAME="We Did It"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

Três avisos sobre este bloco:

- O `.env.example` traz `DB_CONNECTION=sqlite` e as restantes linhas `DB_*` comentadas. Se
  as deixares assim, o Laravel cria uma base SQLite e as migrações correm **sem dar erro** —
  só muito mais tarde percebes que estás a trabalhar contra a base errada. Descomenta-as.
- O `FILESYSTEM_DISK` vem em `local`. Tem de passar a `public`, senão as imagens submetidas
  no formulário de notícias ficam guardadas mas não são acessíveis pelo browser.
- O `MAIL_MAILER=log` fica como está. Os emails são escritos em `storage/logs/laravel.log`
  em vez de enviados — é o que queremos em desenvolvimento.

### Criar a base de dados e arrancar

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS project;"

php artisan storage:link
php artisan migrate:fresh --seed

npm run dev
```

Num segundo terminal:

```bash
php artisan serve
```

A aplicação fica em <http://localhost:8000>.

## Credenciais de acesso

Não existe registo público: a única forma de entrar na área de administração é com a conta
criada pelo seeder.

| Email | Password |
|---|---|
| _(a preencher quando a SCRUM-74 fechar)_ | _(a preencher)_ |

> O `AdminUserSeeder` lê os valores de `ADMIN_EMAIL`, `ADMIN_NAME` e `ADMIN_PASSWORD` do
> `.env`, com `admin@example.com` / `password` por defeito. Enquanto essas credenciais não
> forem fixas e iguais para toda a equipa, cada pessoa entra com um email diferente.

## Comandos úteis

```bash
php artisan migrate:fresh --seed   # recria a base de dados do zero com dados de exemplo
php artisan route:list --except-vendor
php artisan tinker
npm run dev                        # servidor de desenvolvimento do Vite, com hot reload
npm run build                      # build de produção
```

O `migrate:fresh --seed` **apaga todos os dados**. Em desenvolvimento é o que se usa; nunca
o corras contra dados que interesse guardar.

## Stack

Laravel 13 · PHP 8.3 · Inertia 2 · React 18 · Tailwind CSS 3 · MySQL 8 · Vite

O `package.json` tem o `@tailwindcss/vite` versão 4 instalado mas **não usado** — o projeto
corre Tailwind 3 pelo PostCSS. Não migres para o v4 sem falar com a equipa: o build parte.

## Equipa

Beatriz Fiochi · Jéssica Amorim · Leida Dupret · Luana Matos
