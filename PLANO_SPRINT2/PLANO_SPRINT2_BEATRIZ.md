# Plano de implementação — Sprint 2 (28/07 a 03/08/2026)

Objetivo da semana: **base de dados criada, layouts base, login a funcionar e o formulário
público de notícias a gravar na base de dados.**

Baseado no export `wedidit_2026-07-28_07.51pm.csv`: **15 tarefas** (SCRUM-70 a SCRUM-85,
exceto a SCRUM-76 que é de outro sprint), sob as histórias SCRUM-10, SCRUM-11 e SCRUM-18.

⚠️ **O trabalho arranca na quarta, 29/07, e o sábado 01/08 conta como dia de trabalho.**
São 5 dias úteis (quarta, quinta, sexta, sábado e segunda) para as mesmas 15 tarefas —
o domingo 02/08 é a única folga que sobra.

Estado real do projeto (verificado no código): Laravel 13.8, PHP 8.3, Inertia 2, React 18,
Tailwind 3 via PostCSS, MySQL (base `project`). O Breeze está instalado e só tem as páginas
por defeito — não existe nenhuma migration nossa ainda.

| Pessoa | Tarefas | Chaves |
|---|---|---|
| Luana Matos | 5 | SCRUM-70, 71, 72, 73, 74 |
| Beatriz Fiochi | 3 | SCRUM-75, 77, 78 |
| Jéssica Amorim | 3 | SCRUM-79, 80, 81 |
| Leida Dupret | 4 | SCRUM-82, 83, 84, 85 |

---

## Antes de começar — 8 avisos técnicos

Isto poupa-vos horas. Leiam antes de escrever código.

1. **O Laravel 13 usa atributos PHP, não propriedades.** Vejam o `app/Models/User.php`:
   tem `#[Fillable(['name','email','password'])]` em vez de `protected $fillable`.
   Os models novos devem seguir o mesmo estilo, para o código ficar coerente.
2. **Não criem um model chamado `Log`.** Choca com a facade `Log` do Laravel e vão ter
   erros de import difíceis de perceber. Usem `ActivityLog` com `protected $table = 'logs';`.
3. **O model das notícias chama-se `News` e a tabela `news`.** "News" é invariável em inglês,
   por isso escrevam `protected $table = 'news';` explicitamente para não haver surpresas.
4. **Nomes das tabelas de ligação.** O diagrama diz `Newsletter_Course`, mas o Laravel espera
   os dois nomes no singular e por ordem alfabética. Se usarem os nomes do Laravel, o
   `belongsToMany()` funciona sozinho; se usarem os do diagrama, têm de passar o nome da
   tabela à mão em cada relação. Recomendo os do Laravel:

   | Diagrama | Usar |
   |---|---|
   | New_Newsletter | `news_newsletter` |
   | Testimorial_Newsletter | `newsletter_testimonial` |
   | Newsletter_Course | `course_newsletter` |
   | Newsletter_Calendar | `calendar_newsletter` |

5. **`FILESYSTEM_DISK` está em `local`.** Tem de passar a `public`, senão as imagens
   carregadas não ficam acessíveis pelo browser.
6. **A rota `/dashboard` tem o middleware `verified`, que não serve para nada aqui** — o
   `User` não implementa `MustVerifyEmail`. Tirem-no, fica só `auth`.
7. **`MAIL_MAILER=log`.** Nesta semana está bem: os emails são escritos em
   `storage/logs/laravel.log` em vez de enviados. O Mailtrap só entra no Sprint 3.
8. **Não mexam no Tailwind.** O `package.json` tem o `@tailwindcss/vite` versão 4 instalado
   mas *não usado* — o projeto corre Tailwind 3 pelo PostCSS. Se alguém "arrumar" isto e
   passar para o v4, o build parte e perdem meio dia.

---

## Dependências entre pessoas

Há três pontos onde alguém fica parado. São estes que ditam a ordem do plano:

```
Luana (migrations + models)  ──►  Beatriz (controllers)  ──►  Leida (ecrãs ligados)
                             └─►  Luana (seeders)        ──►  todos (dados para testar)
Jéssica (componentes)        ──►  Leida (formulário de notícia)
```

**Marco crítico: os models da Luana têm de estar no `main` até sexta-feira, 31/07, ao almoço.**
Se escorregarem, a Beatriz e a Leida param. Como o sábado passou a ser dia de trabalho,
não há folga a seguir — só o domingo.

---

## Calendário da semana

| | Luana | Beatriz | Jéssica | Leida |
|---|---|---|---|---|
| **Qua 29** | SCRUM-70 migrations base | SCRUM-75 ambiente | SCRUM-79 layout público | SCRUM-85 Git+README → SCRUM-82 login |
| **Qui 30** | SCRUM-71 + SCRUM-72 migrations | SCRUM-78 tirar registo público | SCRUM-80 layout admin | SCRUM-82 login + dashboard |
| **Sex 31** | **SCRUM-73 models ⚑ (até ao almoço)** | SCRUM-77 controller notícia (tarde) | SCRUM-81 componentes | SCRUM-83 logout + menu |
| **Sáb 01** | SCRUM-74 seeders | fechar SCRUM-77 | SCRUM-81 componentes (fim) | SCRUM-84 formulário (início) |
| **Seg 03** | rever PRs | rever PRs | rever PRs | SCRUM-84 fim + teste final |

Só o domingo 02/08 fica de folga. Duas notas sobre esta distribuição:

- A **SCRUM-78** da Beatriz subiu para quinta porque não depende da base de dados — assim
  ela fica livre na sexta à tarde para pegar na SCRUM-77 mal os models entrem.
- A **SCRUM-84** da Leida ficou repartida entre sábado e segunda, em vez de só segunda.
  É a última da corrente de dependências e não pode ficar toda no último dia.

---

# BEATRIZ — backend

### SCRUM-75 · Ambiente: .env, base de dados, uploads — *quarta*

```bash
cp .env.example .env
php artisan key:generate
```

No `.env`, muda três coisas:

- `APP_NAME="We Did It"`
- `FILESYSTEM_DISK=public` ← **está em `local`, tem de mudar**
- confirma que `DB_DATABASE=project` bate certo com a base que criaste no MySQL

Depois:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS project;"
php artisan storage:link
php artisan migrate
npm install && npm run dev
```

**Ainda nesta tarefa**, faz uma coisa pequena que a Jéssica vai precisar: partilhar as
mensagens flash com o Inertia. Em `app/Http/Middleware/HandleInertiaRequests.php`, no
método `share()`, acrescenta ao array:

```php
'flash' => [
    'success' => fn () => $request->session()->get('success'),
    'error'   => fn () => $request->session()->get('error'),
],
```

Sem isto, o componente `FlashMessage` da Jéssica (SCRUM-81) não recebe nada.

Fecha a tarefa escrevendo os passos no README, em conjunto com a Leida (SCRUM-85).

### SCRUM-77 · Rota e controller públicos da notícia — *sexta à tarde e sábado* (depende da SCRUM-73 da Luana)

```bash
php artisan make:controller Public/NewsSubmissionController
php artisan make:request StoreNewsRequest
```

Em `routes/web.php`, fora de qualquer middleware de auth:

```php
Route::get('/noticias/nova',  [NewsSubmissionController::class, 'create'])->name('news.create');
Route::post('/noticias',      [NewsSubmissionController::class, 'store'])->name('news.store');
```

- O `create()` devolve `Inertia::render('Public/NewsForm', ['categories' => Category::all()])`.
- No `StoreNewsRequest`: `title` obrigatório até 255, `description` obrigatório,
  `category_id` tem de existir na tabela categories, `image` opcional, tipo imagem, máx. 2 MB.
- No `store()`, guarda a imagem com `$request->file('image')->store('news', 'public')` e
  grava o caminho devolvido na coluna `image`. O `status` fica `pending` por defeito.
- Termina com `return back()->with('success', 'A tua notícia foi enviada para aprovação.');`

Combina com a Leida os nomes exatos dos campos antes de ela começar a SCRUM-84.

### SCRUM-78 · Tirar o registo público e proteger a área de administração — *quinta*

Não depende da base de dados, por isso podes fazê-la enquanto esperas pelos models da Luana.

Em `routes/auth.php`, apaga as três linhas do registo (estão nas linhas 15 a 18):
a rota `GET register` e a `POST register`.

**Não apagues** o `RegisteredUserController` nem a página `Pages/Auth/Register.jsx` — no
Sprint 5 são reaproveitados para o admin criar outros admins (tarefas 48 e 54).

Em `routes/web.php`:

- tira o `'verified'` da rota `/dashboard`, fica só `->middleware('auth')`
- agrupa a futura área de gestão para os outros não terem de repetir isto:

```php
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // as rotas de gestão dos próximos sprints entram aqui
});
```

Por fim, em `Pages/Welcome.jsx` tira a prop `canRegister` e o link de registo, e na rota `/`
do `web.php` tira `'canRegister' => Route::has('register')` — senão rebenta, porque a rota
deixou de existir.

---

## Como saber se o sprint está fechado

Na segunda-feira, dia 03, façam este teste em conjunto, de uma ponta à outra:

1. `php artisan migrate:fresh --seed` corre sem erros.
2. A página inicial abre com o layout público e os dois cartões.
3. `/register` dá 404.
4. O login com as credenciais do seeder entra no dashboard com o menu lateral.
5. O logout devolve à página inicial.
6. O formulário de notícia aceita uma submissão **com imagem**, e a notícia aparece na
   base de dados com `status = pending` e a imagem visível em `storage/app/public/news`.

Se os seis passarem, o Sprint 2 está pronto e o Sprint 3 arranca sem dívidas.
