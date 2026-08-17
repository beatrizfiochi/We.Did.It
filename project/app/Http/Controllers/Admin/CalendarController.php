<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCalendarRequest;
use App\Http\Requests\Admin\UpdateCalendarRequest;
use App\Models\ActivityLog;
use App\Models\Calendar;
use App\Models\Newsletter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * List the calendar events, with the newsletters each one already enters.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Calendar/Index', [
            // sem paginação: são eventos de uma associação, não milhares de linhas
            'events' => Calendar::with('newsletters:id,title,edition')
                ->orderBy('date')
                ->get(['id', 'date', 'title']),
        ]);
    }

    /**
     * Show the create form for a new calendar event.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Calendar/Create', [
            'newsletters' => Newsletter::orderByDesc('edition')->get(['id', 'title', 'edition']),
        ]);
    }

    /**
     * Store a new calendar event and associate it with the chosen newsletters.
     */
    public function store(StoreCalendarRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $newsletterIds = $data['newsletter_ids'] ?? [];
        unset($data['newsletter_ids']);

        $event = Calendar::create($data);
        $event->newsletters()->sync($newsletterIds);

        ActivityLog::record($event, 'created');

        return back()->with('success', 'Evento criado com sucesso.');
    }

    /**
     * Show the edit form for a calendar event.
     */
    public function edit(Calendar $calendar): Response
    {
        $calendar->load('newsletters:id');

        return Inertia::render('Admin/Calendar/Edit', [
            'event' => [
                ...$calendar->only(['id', 'date', 'title']),
                'newsletter_ids' => $calendar->newsletters->pluck('id'),
            ],
            'newsletters' => Newsletter::orderByDesc('edition')->get(['id', 'title', 'edition']),
        ]);
    }

    /**
     * Update an existing calendar event and the newsletters it belongs to.
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

        ActivityLog::record($calendar, 'updated');

        return back()->with('success', 'Evento atualizado com sucesso.');
    }

    /**
     * Remove a calendar event.
     */
    public function destroy(Calendar $calendar): RedirectResponse
    {
        $calendar->delete();

        // depois do delete(): o Eloquent mantém os atributos na instância e o
        // record_id não é chave estrangeira, por isso a linha do log sobrevive
        ActivityLog::record($calendar, 'removed');

        return back()->with('success', 'Evento removido com sucesso.');
    }
}
