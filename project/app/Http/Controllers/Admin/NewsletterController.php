<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsletterRequest;
use App\Http\Requests\Admin\UpdateNewsletterCalendarsRequest;
use App\Http\Requests\Admin\UpdateNewsletterCoursesRequest;
use App\Http\Requests\Admin\UpdateNewsletterNewsRequest;
use App\Http\Requests\Admin\UpdateNewsletterRequest;
use App\Http\Requests\Admin\UpdateNewsletterTestimonialsRequest;
use App\Models\ActivityLog;
use App\Models\Calendar;
use App\Models\Category;
use App\Models\Course;
use App\Models\News;
use App\Models\Newsletter;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterController extends Controller
{
    /**
     * Lista as newsletters, da edição mais recente para a mais antiga.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Newsletters/Index', [
            'newsletters' => Newsletter::orderByDesc('edition')
                ->get([
                    'id',
                    'title',
                    'edition',
                    'date',
                    'period_start',
                    'period_end',
                    'status',
                ]),
        ]);
    }

    /**
     * Guarda uma nova newsletter.
     */
    public function store(StoreNewsletterRequest $request): RedirectResponse
    {
        $newsletter = Newsletter::create([
            ...$request->validated(),
            'status' => Newsletter::RASCUNHO,
        ]);

        ActivityLog::record($newsletter, 'created');

        return back()->with('success', 'Newsletter criada como rascunho.');
    }

    /**
     * Atualiza uma newsletter existente.
     *
     * O status deixou de vir daqui (SCRUM-116): publicar é uma ação própria,
     * no publish(). Ter dois caminhos para mudar de estado significava que um
     * deles não aplicava as regras de bloqueio.
     */
    public function update(UpdateNewsletterRequest $request, Newsletter $newsletter): RedirectResponse
    {
        $newsletter->update($request->validated());

        ActivityLog::record($newsletter, 'updated');

        // só um rascunho chega aqui, por isso a mensagem deixou de ser condicional
        return back()->with('success', 'Rascunho guardado com sucesso.');
    }

    /**
     * Finaliza a newsletter: passa de rascunho a publicada (SCRUM-116).
     *
     * A partir daqui a edição fica bloqueada — o conteúdo tem de continuar a
     * corresponder ao que foi distribuído. A pré-visualização mantém-se
     * acessível, que é o que permite voltar a gerar o PDF (SCRUM-122).
     *
     * Não leva FormRequest: não recebe dados nenhuns, só o modelo pela rota.
     *
     * O ActivityLog regista 'updated' — o enum só tem created|updated|removed,
     * e publicar é uma alteração de estado do mesmo registo.
     */
    public function publish(Newsletter $newsletter): RedirectResponse
    {
        $this->ensureEditable($newsletter, 'Esta newsletter já está publicada.');

        $newsletter->update(['status' => Newsletter::PUBLICADA]);

        ActivityLog::record($newsletter, 'updated');

        return back()->with('success', 'Newsletter publicada com sucesso.');
    }

    /**
     * Remove uma newsletter.
     *
     * Bloqueado depois de publicada: uma edição distribuída é registo
     * histórico, e a SCRUM-21 quer poder consultar todas as que já saíram.
     */
    public function destroy(Newsletter $newsletter): RedirectResponse
    {
        $this->ensureEditable($newsletter);

        $newsletter->delete();

        ActivityLog::record($newsletter, 'removed');

        return back()->with('success', 'Newsletter removida com sucesso.');
    }

    /**
     * Mostra a newsletter com todos os conteúdos selecionados, para o gestor
     * poder decidir se está bem antes de finalizar (SCRUM-15, ecrã da SCRUM-114).
     *
     * Continua acessível depois de publicada: é assim que se volta a gerar o
     * PDF de uma edição antiga (SCRUM-122). Não há ficheiro guardado — o PDF
     * sai dos conteúdos atuais no momento em que se imprime.
     */
    public function preview(Newsletter $newsletter): Response
    {
        $newsletter->load([
            'news' => fn ($q) => $q->with('category')->orderByPivot('order'),
            'testimonials' => fn ($q) => $q->with('category')->orderByPivot('order'),
            'calendars' => fn ($q) => $q->orderBy('date'),
            'courses',
        ]);

        // start_date é string livre vinda da API externa, não dá para ordenar em SQL
        $newsletter->setRelation('courses', $newsletter->courses
            ->sortBy(fn (Course $course) => $course->start_date_for_sorting)
            ->values());

        return Inertia::render('Admin/Newsletters/Preview', [
            'newsletter' => $newsletter,
            // Uma newsletter publicada deixa de poder ser editada (SCRUM-116),
            // por isso o updated_at fica congelado no instante da publicação e
            // serve de data de publicação sem ser preciso uma coluna nova.
            //
            // Isto depende do bloqueio da 116 e de mais nada. Se um dia deixarmos
            // editar uma publicada — corrigir uma gralha antes de reenviar, por
            // exemplo — o updated_at passa a ser a data da correção e esta linha
            // começa a mentir sem dar erro. Nessa altura tem de passar a coluna
            // própria (published_at), preenchida no publish().
            'publishedAt' => $newsletter->is_draft ? null : $newsletter->updated_at,
        ]);
    }

    /**
     * Mostra o ecrã de seleção das ofertas formativas para esta newsletter.
     */
    public function editCourses(Newsletter $newsletter): Response
    {
        $this->ensureEditable($newsletter);

        $newsletter->load('courses:id');

        // start_date é uma string vinda da API externa, não uma coluna de data,
        // por isso ordenamos em PHP (start_date_for_sorting) em vez de orderBy() no banco.
        $courses = Course::get(['id', 'title', 'start_date'])
            ->sortBy(fn (Course $course) => $course->start_date_for_sorting)
            ->values();

        return Inertia::render('Admin/Newsletters/Courses', [
            'newsletter' => $newsletter->only(['id', 'title', 'edition']),
            'courses' => $courses,
            'course_ids' => $newsletter->courses->pluck('id'),
        ]);
    }

    /**
     * Guarda as ofertas formativas selecionadas para esta newsletter.
     */
    public function updateCourses(UpdateNewsletterCoursesRequest $request, Newsletter $newsletter): RedirectResponse
    {
        $newsletter->courses()->sync($request->validated()['course_ids'] ?? []);

        // o log é sobre a newsletter, não sobre os conteúdos associados: o enum
        // da coluna operation só tem created|updated|removed, e o record_id
        // guarda uma chave só. Fica registado que a newsletter foi alterada e
        // por quem, não que conteúdos entraram ou saíram. Vale para os quatro
        // update* de conteúdos (SCRUM-105).
        ActivityLog::record($newsletter, 'updated');

        return redirect()->route('admin.newsletters.courses.edit', $newsletter)
            ->with('success', 'Ofertas formativas da newsletter atualizadas com sucesso.');
    }

    /**
     * Mostra o ecrã de seleção das notícias para esta newsletter.
     *
     * Não confundir com o Admin/News/Index, que é a moderação: lá aprovam-se e
     * editam-se notícias; aqui escolhem-se, de entre as já aprovadas, as que
     * entram nesta edição. São dois ecrãs distintos e ambos ficam.
     *
     * A image vai como caminho relativo ("news/abc.jpg"): no .jsx é
     * /storage/{image}, como o Admin/News/Index.jsx já faz.
     */
    public function editNews(Newsletter $newsletter): Response
    {
        $this->ensureEditable($newsletter);

        $newsletter->load('news:id');

        // só as notícias aprovadas podem entrar na newsletter
        $news = News::where('status', 'accepted')
            ->with('category:id,name')
            ->latest()
            ->get(['id', 'category_id', 'title', 'image', 'created_at']);

        return Inertia::render('Admin/Newsletters/News', [
            'newsletter' => $newsletter->only(['id', 'title', 'edition']),
            'news' => $news,
            'news_ids' => $newsletter->news->pluck('id'),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Guarda as notícias selecionadas para esta newsletter.
     */
    public function updateNews(UpdateNewsletterNewsRequest $request, Newsletter $newsletter): RedirectResponse
    {
        $ids = $request->validated()['news_ids'] ?? [];

        // a ordem de chegada é a ordem em que saem na newsletter; começa em 1
        // para acompanhar o NewsletterSeeder
        $newsletter->news()->sync(
            collect($ids)->mapWithKeys(fn ($id, $i) => [$id => ['order' => $i + 1]])
        );

        // regista a newsletter, não os conteúdos: o enum só tem
        // created|updated|removed e o record_id é uma chave só
        ActivityLog::record($newsletter, 'updated');

        return back()->with('success', 'Notícias da newsletter atualizadas com sucesso.');
    }

    /**
     * Mostra o ecrã de seleção dos testemunhos para esta newsletter.
     *
     * Como no editNews: isto não substitui o Admin/Testimonials/Index, que é a
     * moderação. A description fica de fora de propósito — são até 1050
     * caracteres por testemunho e o conteúdo vê-se na pré-visualização.
     */
    public function editTestimonials(Newsletter $newsletter): Response
    {
        $this->ensureEditable($newsletter);

        $newsletter->load('testimonials:id');

        // só os testemunhos aprovados podem entrar na newsletter
        $testimonials = Testimonial::where('status', 'accepted')
            ->with('category:id,name')
            ->latest()
            ->get(['id', 'category_id', 'title', 'name', 'image', 'created_at']);

        return Inertia::render('Admin/Newsletters/Testimonials', [
            'newsletter' => $newsletter->only(['id', 'title', 'edition']),
            'testimonials' => $testimonials,
            'testimonial_ids' => $newsletter->testimonials->pluck('id'),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Guarda os testemunhos selecionados para esta newsletter.
     */
    public function updateTestimonials(UpdateNewsletterTestimonialsRequest $request, Newsletter $newsletter): RedirectResponse
    {
        $ids = $request->validated()['testimonial_ids'] ?? [];

        // a ordem de chegada é a ordem em que saem na newsletter; começa em 1
        // para acompanhar o NewsletterSeeder
        $newsletter->testimonials()->sync(
            collect($ids)->mapWithKeys(fn ($id, $i) => [$id => ['order' => $i + 1]])
        );

        // regista a newsletter, não os conteúdos: o enum só tem
        // created|updated|removed e o record_id é uma chave só
        ActivityLog::record($newsletter, 'updated');

        return back()->with('success', 'Testemunhos da newsletter atualizados com sucesso.');
    }

    /**
     * Mostra o ecrã de seleção dos eventos da agenda para esta newsletter.
     */
    public function editCalendars(Newsletter $newsletter): Response
    {
        $this->ensureEditable($newsletter);

        $newsletter->load('calendars:id');

        $calendars = Calendar::orderBy('date')->get(['id', 'date', 'title']);

        return Inertia::render('Admin/Newsletters/Calendars', [
            'newsletter' => $newsletter->only(['id', 'title', 'edition']),
            'calendars' => $calendars,
            'calendar_ids' => $newsletter->calendars->pluck('id'),
        ]);
    }

    /**
     * Guarda os eventos da agenda selecionados para esta newsletter.
     */
    public function updateCalendars(UpdateNewsletterCalendarsRequest $request, Newsletter $newsletter): RedirectResponse
    {
        $newsletter->calendars()->sync($request->validated()['calendar_ids'] ?? []);

        // regista a newsletter, não os conteúdos: o enum só tem
        // created|updated|removed e o record_id é uma chave só
        ActivityLog::record($newsletter, 'updated');

        return redirect()->route('admin.newsletters.calendars.edit', $newsletter)
            ->with('success', 'Eventos da agenda da newsletter atualizados com sucesso.');
    }

    /**
     * Recusa qualquer alteração a uma newsletter já publicada (SCRUM-116).
     *
     * Num sítio só, para a regra não divergir entre os onze métodos que a
     * aplicam. Devolve 403, que a página de erro da SCRUM-129 já apresenta.
     */
    private function ensureEditable(Newsletter $newsletter, string $message = 'Uma newsletter publicada não pode ser alterada.'): void
    {
        abort_if(! $newsletter->isEditable(), 403, $message);
    }
}
