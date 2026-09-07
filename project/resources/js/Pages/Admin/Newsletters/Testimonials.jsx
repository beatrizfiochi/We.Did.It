import ContentFilters from '@/Components/ContentFilters';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

// Ids das imagens que um testemunho traz, na ordem em que devem sair.
function allImageIds(item) {
    return (item.images ?? []).map((image) => image.id);
}

export default function Testimonials({ newsletter, testimonials, testimonial_ids, selected_images, categories }) {
    // image_ids: { [testimonialId]: [imageId, …] } — quais imagens de cada
    // testemunho saem nesta edição. selected_images vem do servidor; null
    // (nunca escolhido) conta como "todas".
    const { data, setData, put, processing, recentlySuccessful } = useForm({
        testimonial_ids,
        image_ids: Object.fromEntries(
            testimonial_ids.map((id) => {
                const saved = selected_images?.[id];
                const item = testimonials.find((t) => t.id === id);

                return [id, saved ?? allImageIds(item)];
            }),
        ),
    });

    const [selectedCategories, setSelectedCategories] = useState([]);
    const [periodStart, setPeriodStart] = useState('');
    const [periodEnd, setPeriodEnd] = useState('');

    function toggleTestimonials(id) {
        const isSelected = data.testimonial_ids.includes(id);

        if (isSelected) {
            const { [id]: _removed, ...rest } = data.image_ids;
            setData({
                ...data,
                testimonial_ids: data.testimonial_ids.filter((t) => t !== id),
                image_ids: rest,
            });
            return;
        }

        const item = testimonials.find((t) => t.id === id);
        setData({
            ...data,
            testimonial_ids: [...data.testimonial_ids, id],
            image_ids: { ...data.image_ids, [id]: allImageIds(item) }, // todas por defeito
        });
    }

    function toggleImage(testimonialId, imageId) {
        const current = data.image_ids[testimonialId] ?? [];
        const next = current.includes(imageId)
            ? current.filter((i) => i !== imageId)
            : [...current, imageId];

        setData('image_ids', { ...data.image_ids, [testimonialId]: next });
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
                            {filteredTestimonials.map((item) => {
                                const isSelected = data.testimonial_ids.includes(item.id);
                                const chosen = data.image_ids[item.id] ?? [];

                                return (
                                    <li key={item.id} className="py-3">
                                        <label htmlFor={`testimonial-${item.id}`} className="flex items-start gap-3">
                                            <input
                                                id={`testimonial-${item.id}`}
                                                type="checkbox"
                                                checked={isSelected}
                                                onChange={() => toggleTestimonials(item.id)}
                                                className="mt-1 rounded border-gray-300"
                                            />
                                            <span className="min-w-0 flex-1">
                                                <span className="block font-medium text-gray-900">{item.title}</span>
                                                <span className="block text-sm text-gray-500">
                                                    {item.name} · {new Date(item.created_at).toLocaleDateString('pt-PT')}
                                                </span>
                                                <span className="block text-sm text-gray-500">
                                                    {item.category ? item.category.name : 'Sem categoria'}
                                                </span>
                                            </span>
                                        </label>

                                        {isSelected && (item.images ?? []).length > 0 && (
                                            <div className="ml-8 mt-3">
                                                <p className="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">
                                                    {chosen.length === 0
                                                        ? 'Sai sem imagem'
                                                        : `Imagens que saem (${chosen.length})`}
                                                </p>
                                                <div className="flex flex-wrap gap-2">
                                                    {item.images.map((image) => {
                                                        const on = chosen.includes(image.id);

                                                        return (
                                                            <button
                                                                type="button"
                                                                key={image.id}
                                                                onClick={() => toggleImage(item.id, image.id)}
                                                                aria-pressed={on}
                                                                className={`relative h-20 w-32 overflow-hidden rounded border-2 transition ${
                                                                    on ? 'border-indigo-600' : 'border-transparent opacity-50 hover:opacity-100'
                                                                }`}
                                                            >
                                                                <img
                                                                    src={`/storage/${image.path}`}
                                                                    alt=""
                                                                    className="h-full w-full object-cover"
                                                                />
                                                                {on && (
                                                                    <span className="absolute right-1 top-1 rounded-full bg-indigo-600 px-1.5 text-xs font-semibold text-white">
                                                                        {chosen.indexOf(image.id) + 1}
                                                                    </span>
                                                                )}
                                                            </button>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        )}
                                    </li>
                                );
                            })}
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
