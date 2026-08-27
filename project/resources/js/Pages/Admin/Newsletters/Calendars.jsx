import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Calendars({ newsletter, calendars, calendar_ids }) {
    const { data, setData, put, processing, recentlySuccessful } = useForm({
        calendar_ids,
    });

    function toggleCalendar(id) {
        setData(
            'calendar_ids',
            data.calendar_ids.includes(id)
                ? data.calendar_ids.filter((calendarId) => calendarId !== id)
                : [...data.calendar_ids, id],
        );
    }

    function submit(e) {
        e.preventDefault();
        put(route('admin.newsletters.calendars.update', newsletter.id));
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Agenda — {newsletter.title} (edição {newsletter.edition})
                </h2>
            }
        >
            <Head title="Selecionar eventos da agenda" />

            <div className="mx-auto max-w-2xl space-y-4 p-6">
                <Link
                    href={route('admin.newsletters.index')}
                    className="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
                >
                    ← Voltar às newsletters
                </Link>

                <form onSubmit={submit} className="rounded-lg bg-white p-6 shadow">
                    {calendars.length === 0 && (
                        <p className="text-sm text-gray-500">Não existem eventos da agenda disponíveis.</p>
                    )}

                    {calendars.length > 0 && (
                        <p className="mb-3 text-sm text-gray-500">
                            {data.calendar_ids.length} de {calendars.length} selecionados
                        </p>
                    )}

                    <ul className="divide-y divide-gray-200">
                        {calendars.map((event) => (
                            <li key={event.id} className="flex items-center justify-between py-3">
                                <label htmlFor={`calendar-${event.id}`} className="flex flex-1 items-center gap-3">
                                    <input
                                        id={`calendar-${event.id}`}
                                        type="checkbox"
                                        checked={data.calendar_ids.includes(event.id)}
                                        onChange={() => toggleCalendar(event.id)}
                                        className="rounded border-gray-300"
                                    />
                                    <span>
                                        <span className="block font-medium text-gray-900">{event.title}</span>
                                        <span className="block text-sm text-gray-500">
                                            {new Date(event.date).toLocaleDateString('pt-PT')}
                                        </span>
                                    </span>
                                </label>
                            </li>
                        ))}
                    </ul>

                    <div className="mt-6 flex items-center gap-4">
                        <PrimaryButton disabled={processing}>Guardar seleção</PrimaryButton>

                        {recentlySuccessful && <p className="text-sm text-gray-600">Guardado.</p>}
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
