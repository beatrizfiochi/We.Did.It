# We.Did.It

Projeto final — Software Developer, Cesae Digital.

Plataforma de gestão de uma newsletter institucional do CESAE Digital: recolhe notícias e
testemunhos submetidos por formandos e colaboradores, permite a um administrador aprová-los
ou recusá-los, e reúne os aprovados numa edição de newsletter.

A aplicação Laravel vive na pasta `project/`. A raiz do repositório tem também as atas
(`Atas/`), o modelo de dados (`DataBase/`) e os planos de sprint (`PLANO_SPRINT2/`).

## Requisitos

- PHP 8.3 ou superior, com as extensões `pdo_mysql` e `fileinfo`
- Composer
- MySQL 8
- Node.js 20 e npm

A extensão `gd` é precisa **apenas para correr os testes**: o `UploadedFile::fake()->image()`
gera as imagens de teste com ela e sem a extensão atira `GD extension is not installed`.
A aplicação não usa a `gd` — quem faz upload de imagens precisa da `fileinfo`, que valida
o tipo do ficheiro.

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
DB_USERNAME=<o teu utilizador>
DB_PASSWORD=<a tua password>

ADMIN_EMAIL=admin@cesae.pt
ADMIN_PASSWORD=<uma password à tua escolha>
```

> O `AdminUserSeeder` lê `ADMIN_EMAIL`, `ADMIN_NAME` e `ADMIN_PASSWORD` do `.env`.
> O `.env.example` já traz `admin@cesae.pt`, para toda a equipa entrar com o mesmo
> email; a password é de cada pessoa. Se o administrador já existir, o seeder não
> faz nada e avisa.

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

## Deploy

A aplicação corre em qualquer alojamento com PHP, MySQL e acesso a linha de comandos.

### Requisitos do servidor

- PHP 8.3 ou superior, com a extensão **`fileinfo`** — sem ela a validação das imagens
  enviadas nos formulários falha. A `gd` não é precisa em produção, só para os testes
- MySQL 8
- Acesso a linha de comandos, para correr o Composer e o artisan
- **O document root tem de apontar para `project/public`**, nunca para a raiz do
  projeto: só a pasta `public` deve ser acessível pela web. Se apontar para a raiz,
  o ficheiro `.env` — com as credenciais da base de dados — fica exposto na internet

O Node é preciso apenas para construir os assets, o que se faz **antes** de enviar
para o servidor: a pasta `node_modules` ronda os 200 MB e não tem de lá estar.

### Primeira instalação

No computador de quem faz o deploy:

```bash
npm ci && npm run build     # gera project/public/build
```

No servidor, depois de lá pôr o código e a pasta `public/build`:

```bash
cd project
composer install --no-dev --optimize-autoloader

cp .env.example .env             # e preencher — ver abaixo
php artisan key:generate
php artisan migrate --force      # --force: em produção o Laravel recusa-se sem ele
php artisan storage:link         # sem o link, as imagens dão todas 404
php artisan db:seed --class=AdminUserSeeder
php artisan config:cache         # obrigatório sempre que o .env muda
php artisan route:cache
```

### O `.env` em produção

Além da base de dados, mudar pelo menos:

```dotenv
APP_NAME="We Did It"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://o-dominio-real
```

O `APP_DEBUG=false` é crítico: com `true`, qualquer erro mostra o código-fonte e o
conteúdo do `.env` a quem abrir a página.

O `APP_NAME` e o `APP_URL` saem nos emails: o nome aparece no remetente, no rodapé e
na assinatura, e o URL é a base do logo do cabeçalho (`asset()`). Com o `APP_NAME` por
acertar, os emails saem assinados com o valor por defeito do Laravel; com o `APP_URL`
errado, o logo dá 404 e o cabeçalho passa a ser o texto alternativo. Depois de os
mudar, correr `php artisan config:cache`.

### Atualizar

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache         # sem isto, a configuração antiga fica em cache
php artisan route:cache
```

Mais o `npm run build` e o envio da pasta `public/build` atualizada. **Apagar a pasta
`public/build` do servidor antes de a copiar** — ver a última alínea da secção seguinte.

### Dados iniciais

Correr apenas o `AdminUserSeeder`, que cria a conta de administrador a partir do
`ADMIN_EMAIL` e `ADMIN_PASSWORD` do `.env`. Não há registo público: sem essa conta
não há como entrar na aplicação.

**Não correr o `php artisan db:seed` completo em produção** — ele cria notícias e
testemunhos de exemplo com texto gerado, que podem ser confundidos com conteúdo real.

### Se alguma coisa correr mal

Os casos que encontrámos a pôr a aplicação a correr num alojamento partilhado.

**`Invalid URI` ao correr um comando artisan**
O `APP_URL` está mal formado. Tem de incluir o esquema, sem espaços à volta do `=` e
sem barra no fim: `APP_URL=https://o-dominio-real`.

**As alterações não aparecem no site**
Falta o `php artisan config:cache` e o `route:cache`. A configuração e as rotas ficam
em cache, e sem estes comandos a aplicação continua a servir as antigas. É o passo
mais esquecido depois de mexer no `.env`.

**Erro de base de dados logo no arranque**
Confirmar `DB_CONNECTION=mysql` no `.env`. O valor por defeito do Laravel é `sqlite`,
e a aplicação arranca sem dar erro visível até tentar ler dados.

**As imagens dão todas 404**
Falta o link simbólico: `php artisan storage:link`. As notícias e os testemunhos
guardam as imagens em `storage/app/public` e servem-nas de `/storage/...`.

**O site continua com o aspeto antigo depois de copiar o `public/build`**
Se a pasta `build` já existir no servidor, o `scp -r` **não a substitui** — copia para
dentro dela e cria `public/build/build`. O site passa a servir os assets antigos e
**não dá erro nenhum**, o que faz perder muito tempo. Apagar a pasta antes de copiar:

```bash
ssh utilizador@servidor 'rm -rf caminho/do/projeto/public/build'
scp -r public/build utilizador@servidor:caminho/do/projeto/public/
```

Para confirmar, o nome do ficheiro no servidor tem de ser igual ao local:

```bash
ls public/build/assets | grep '^app-'
ssh utilizador@servidor 'ls caminho/do/projeto/public/build/assets | grep "^app-"'
```

## Stack

Laravel 13 · PHP 8.3 · Inertia 2 · React 18 · Tailwind CSS 3 · MySQL 8 · Vite

O `package.json` tem o `@tailwindcss/vite` versão 4 instalado mas **não usado** — o projeto
corre Tailwind 3 pelo PostCSS. Não migres para o v4 sem falar com a equipa: o build parte.

## Equipa e método de trabalho

| Elemento | Responsabilidade |
|---|---|
| Beatriz Fiochi (Team Lead) | Rotas de backend |
| Jéssica Amorim | Layout do frontend e identidade visual |
| Leida Dupret | Configuração de backend, rotas protegidas, lógica de UX |
| Luana Matos | Configuração da base de dados e migrações |

O projeto seguiu um workflow por sprints, com cada elemento a levar um conjunto de tarefas
até ao fim numa sprint de uma semana. Integrar qualquer tarefa na `main` obriga a abrir um
pull request, que despoleta a revisão pelos restantes elementos: aprovam o PR ou deixam
comentários para afinar a implementação. Como as componentes são interdependentes, é assim
que toda a gente acompanha o progresso das outras partes.