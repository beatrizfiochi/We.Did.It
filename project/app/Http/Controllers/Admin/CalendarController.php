<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Models\Calendar;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Lista os eventos da agenda.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Calendars/Index', [
            'events' => Calendar::orderBy('date')->get(['id', 'date', 'title']),
        ]);
    }

    /**
     * Mostra o formulário de criação de um novo evento.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Calendars/Create');
    }

    /**
     * Guarda um novo evento da agenda.
     */
    public function store(StoreCalendarRequest $request): RedirectResponse
    {
        Calendar::create($request->validated());

        return redirect()->route('admin.calendars.index')->with('success', 'Evento criado com sucesso.');
    }

    /**
     * Mostra o formulário de edição de um evento.
     */
    public function edit(Calendar $calendar): Response
    {
        return Inertia::render('Admin/Calendars/Edit', [
            'event' => $calendar->only(['id', 'date', 'title']),
        ]);
    }

    /**
     * Atualiza um evento da agenda existente.
     */
    public function update(UpdateCalendarRequest $request, Calendar $calendar): RedirectResponse
    {
        $calendar->update($request->validated());

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
