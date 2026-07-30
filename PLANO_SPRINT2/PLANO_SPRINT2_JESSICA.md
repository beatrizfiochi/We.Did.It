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

# JÉSSICA — frontend

Podes começar já na quarta: nenhuma das tuas tarefas depende da base de dados.

### SCRUM-79 · Layout público — *quarta*

Cria `resources/js/Layouts/PublicLayout.jsx`: header com o nome do projeto e links para
"Enviar notícia" e "Enviar testemunho", `<main>` para o conteúdo, e footer.

Reescreve `resources/js/Pages/Welcome.jsx` para o usar: página de entrada com dois cartões
grandes, um para cada formulário, e um link discreto de login no canto.

A Welcome atual recebe `canLogin`, `canRegister`, `laravelVersion` e `phpVersion` — deita
fora essas props, não servem para nada no nosso projeto. Combina com a Beatriz (SCRUM-78), que
está a mexer na mesma rota.

### SCRUM-80 · Layout de administração — *quinta*

Adapta o `resources/js/Layouts/AuthenticatedLayout.jsx` que já existe, trocando a barra de
topo por um menu lateral. Mete já todas as entradas que o projeto vai ter, mesmo que
apontem para `#` por agora:

Dashboard · Notícias · Testemunhos · Categorias · Formações · Agenda · Newsletters · Administradores

⚠️ O menu de utilizador com o logout vive dentro deste ficheiro. A Leida está a mexer nele
na SCRUM-83 — falem antes de começar, para não estarem as duas a editar o mesmo ficheiro.

### SCRUM-81 · Componentes reutilizáveis — *sexta e sábado*

O Breeze já traz `TextInput`, `InputLabel`, `InputError`, `PrimaryButton`, `SecondaryButton`,
`DangerButton`, `Checkbox`, `Modal` e `Dropdown`. **Não os refaças.**

Faltam estes, e são os que o resto da equipa vai usar até ao fim do projeto:

| Componente | Para quê |
|---|---|
| `TextArea.jsx` | descrição das notícias e testemunhos |
| `SelectInput.jsx` | escolher categoria |
| `FileInput.jsx` | upload de imagem, com pré-visualização |
| `StatusBadge.jsx` | pendente / aprovado / recusado, com cores |
| `DataTable.jsx` | tabela simples com cabeçalho e linhas |
| `FlashMessage.jsx` | lê `usePage().props.flash` e mostra sucesso ou erro |
| `EmptyState.jsx` | "ainda não há nada aqui" |

O `FlashMessage` depende da alteração que a Beatriz faz na SCRUM-75 — se ainda não estiver no
`main`, `props.flash` vem `undefined`, por isso protege com `?.`.

Prioriza `TextArea`, `SelectInput` e `FileInput`: a Leida precisa deles **no sábado de manhã**
para a SCRUM-84. Deixa os restantes para o fim de sábado.

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
