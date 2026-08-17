<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Models\Calendar;
use App\Models\Newsletter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Lista os eventos da agenda, com as newsletters onde cada um já entra.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Calendars/Index', [
            'events' => Calendar::with('newsletters:id,title,edition')
                ->orderBy('date')
                ->get(['id', 'date', 'title']),
        ]);
    }

    /**
     * Mostra o formulário de criação de um novo evento.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Calendars/Create', [
            'newsletters' => Newsletter::orderByDesc('edition')->get(['id', 'title', 'edition']),
        ]);
    }

    /**
     * Guarda um novo evento da agenda e associa-o às newsletters escolhidas.
     */
    public function store(StoreCalendarRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $newsletterIds = $data['newsletter_ids'] ?? [];
        unset($data['newsletter_ids']);

        $calendar = Calendar::create($data);
        $calendar->newsletters()->sync($newsletterIds);

        return redirect()->route('admin.calendars.index')->with('success', 'Evento criado com sucesso.');
    }

    /**
     * Mostra o formulário de edição de um evento.
     */
    public function edit(Calendar $calendar): Response
    {
        $calendar->load('newsletters:id');

        return Inertia::render('Admin/Calendars/Edit', [
            'event' => [
                ...$calendar->only(['id', 'date', 'title']),
                'newsletter_ids' => $calendar->newsletters->pluck('id'),
            ],
            'newsletters' => Newsletter::orderByDesc('edition')->get(['id', 'title', 'edition']),
        ]);
    }

    /**
     * Atualiza um evento da agenda existente e as newsletters associadas.
     */
    public function update(UpdateCalendarRequest $request, Calendar $calendar): RedirectResponse
    {
        $data = $request->validated();

        // só mexe nas newsletters associadas se a chave vier no pedido;
        // uma atualização parcial (ex.: só o date) não deve desassociar tudo
        if (array_key_exists('newsletter_ids', $data)) {
            $calendar->newsletters()->sync($data['newsletter_ids']);
        }
        unset($data['newsletter_ids']);

        $calendar->update($data);

        return redirect()->route('admin.calendars.index')->with('success', 'Evento atualizado com sucesso.');
    }

    /**
     * Remove um evento da agenda.
     */
    public function destroy(Calendar $calendar): RedirectResponse
    {
        $calendar->delete();

        return redirect()->route('admin.calendars.index')->with('success', 'Evento removido com sucesso.');
    }
}
