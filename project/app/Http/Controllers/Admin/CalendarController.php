<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCalendarRequest;
use App\Http\Requests\Admin\UpdateCalendarRequest;
use App\Models\ActivityLog;
use App\Models\Calendar;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * List the calendar events.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Calendar/Index', [
            // sem paginação: são eventos de uma associação, não milhares de linhas
            'events' => Calendar::orderBy('date')->get(['id', 'date', 'title']),
        ]);
    }

    /**
     * Store a new calendar event.
     */
    public function store(StoreCalendarRequest $request): RedirectResponse
    {
        $event = Calendar::create($request->validated());

        ActivityLog::record($event, 'created');

        return back()->with('success', 'Evento criado com sucesso.');
    }

    /**
     * Update an existing calendar event.
     */
    public function update(UpdateCalendarRequest $request, Calendar $calendar): RedirectResponse
    {
        $calendar->update($request->validated());

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
