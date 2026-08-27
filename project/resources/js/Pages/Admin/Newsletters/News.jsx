import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function News({ newsletter, news, news_ids }) {
    const { data, setData, put, processing, recentlySuccessful } = useForm({
        news_ids,
    });

    function toggleNews(id) {
        setData(
            'news_ids',
            data.news_ids.includes(id)
                ? data.news_ids.filter((newsId) => newsId !== id)
                : [...data.news_ids, id],
        );
    }

    function submit(e) {
        e.preventDefault();
        put(route('admin.newsletters.news.update', newsletter.id));
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Notícias — {newsletter.title} (edição {newsletter.edition})
                </h2>
            }
        >
            <Head title="Selecionar notícias" />

            <div className="mx-auto max-w-2xl p-6">
                <form onSubmit={submit} className="rounded-lg bg-white p-6 shadow">
                    {news.length === 0 && (
                        <p className="text-sm text-gray-500">Não existem notícias disponíveis.</p>
                    )}

                    <ul className="divide-y divide-gray-200">
                        {news.map((event) => (
                            <li key={event.id} className="flex items-center justify-between py-3">
                                <label htmlFor={`event-${event.id}`} className="flex flex-1 items-center gap-3">
                                    <input
                                        id={`event-${event.id}`}
                                        type="checkbox"
                                        checked={data.news_ids.includes(event.id)}
                                        onChange={() => toggleNews(event.id)}
                                        className="rounded border-gray-300"
                                    />
                                    <span>

                                        <span className="block font-medium text-gray-900">{event.title}</span>
                                        <span className="block text-sm text-gray-500">{new Date(event.created_at).toLocaleDateString('pt-Pt')}</span>
                                        <span className="block text-sm text-gray-500">
                                            {event.category ? `${event.category_id} - ${event.category.name}` : 'Sem categoria'}
                                        </span>
                                    </span>
                                </label>
                                {event.image && (
                                    <img
                                        src={`/storage/${event.image}`}
                                        alt={event.title}
                                        className="h-28 w-48 rounded object-cover"
                                    />
                                )}
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
