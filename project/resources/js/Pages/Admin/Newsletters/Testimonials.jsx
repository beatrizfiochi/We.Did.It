import ContentFilters from '@/Components/ContentFilters';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Testimonials({ newsletter, testimonials, testimonial_ids, categories }) {
    const { data, setData, put, processing, recentlySuccessful } = useForm({
        testimonial_ids,
    });

    const [selectedCategories, setSelectedCategories] = useState([]);
    const [periodStart, setPeriodStart] = useState('');
    const [periodEnd, setPeriodEnd] = useState('');

    function toggleTestimonials(id) {
        setData(
            'testimonial_ids',
            data.testimonial_ids.includes(id)
                ? data.testimonial_ids.filter((testimonialsId) => testimonialsId !== id)
                : [...data.testimonial_ids, id],
        );
    }

    function toggleCategory(categoryId) {
        setSelectedCategories((prev) =>
            prev.includes(categoryId) ? prev.filter((id) => id !== categoryId) : [...prev, categoryId],
        );
    }

    function submit(e) {
        e.preventDefault();
        put(route('admin.newsletters.testimonials.update', newsletter.id));
    }

    const filteredTestimonials = testimonials.filter((item) => {
        const matchesCategory = selectedCategories.length === 0 || selectedCategories.includes(item.category_id);

        const publishedOn = item.created_at.slice(0, 10);
        const matchesPeriod = (!periodStart || publishedOn >= periodStart) && (!periodEnd || publishedOn <= periodEnd);

        return matchesCategory && matchesPeriod;
    });

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Testemunhos — {newsletter.title} (edição {newsletter.edition})
                </h2>
            }
        >
            <Head title="Selecionar testemunhos" />

            <div className="mx-auto max-w-5xl space-y-4 p-6">
                <Link
                    href={route('admin.newsletters.index')}
                    className="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
                >
                    ← Voltar às newsletters
                </Link>

                <div className="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
                    <ContentFilters
                        categories={categories}
                        selectedCategories={selectedCategories}
                        onToggleCategory={toggleCategory}
                        onClearCategories={() => setSelectedCategories([])}
                        periodStart={periodStart}
                        periodEnd={periodEnd}
                        onPeriodStartChange={setPeriodStart}
                        onPeriodEndChange={setPeriodEnd}
                    />

                    <form onSubmit={submit} className="min-w-0 rounded-lg bg-white p-6 shadow">
                        {testimonials.length === 0 && (
                            <p className="text-sm text-gray-500">Não existem testemunhos disponíveis.</p>
                        )}

                        {testimonials.length > 0 && filteredTestimonials.length === 0 && (
                            <p className="text-sm text-gray-500">Nenhum testemunho corresponde aos filtros selecionados.</p>
                        )}

                        {testimonials.length > 0 && (
                            <p className="mb-3 text-sm text-gray-500">
                                {data.testimonial_ids.length} de {testimonials.length} selecionados
                            </p>
                        )}

                        <ul className="divide-y divide-gray-200">
                            {filteredTestimonials.map((event) => (
                                <li key={event.id} className="flex items-center justify-between py-3">
                                    <label htmlFor={`event-${event.id}`} className="flex flex-1 items-center gap-3">
                                        <input
                                            id={`event-${event.id}`}
                                            type="checkbox"
                                            checked={data.testimonial_ids.includes(event.id)}
                                            onChange={() => toggleTestimonials(event.id)}
                                            className="rounded border-gray-300"
                                        />
                                        <span>
                                            <span className="block font-medium text-gray-900">{event.title}</span>
                                            <span className="block text-sm text-gray-500">
                                                {event.name} · {new Date(event.created_at).toLocaleDateString('pt-PT')}
                                            </span>
                                            <span className="block text-sm text-gray-500">
                                                {event.category ? event.category.name : 'Sem categoria'}
                                            </span>
                                        </span>
                                    </label>
                                    {
                                        event.image && (
                                            <img
                                                src={`/storage/${event.image}`}
                                                alt={event.title}
                                                className="h-28 w-48 rounded object-cover"
                                            />
                                        )
                                    }
                                </li>
                            ))}
                        </ul>

                        <div className="mt-6 flex items-center gap-4">
                            <PrimaryButton disabled={processing}>Guardar seleção</PrimaryButton>

                            {recentlySuccessful && <p className="text-sm text-gray-600">Guardado.</p>}
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
