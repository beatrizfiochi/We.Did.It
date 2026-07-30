# We.Did.It
Projeto final - Software Developer Cesae Digital - Newsletter

## Setup inicial

```bash
git clone <url-do-repo>
cd we-did-it

composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate

npm install
npm run build
```

> Configura as credenciais da base de dados no `.env` antes de correr as migrações.
