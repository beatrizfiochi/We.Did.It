function formatDate(value) {
    if (!value) {
        return 'Data por definir';
    }

    return new Intl.DateTimeFormat('pt-PT', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}

/**
 * A data de início das formações não é uma coluna de data: vem do site do
 * CESAE como texto e tanto pode ser "2026-08-28" como "A anunciar". O
 * formatDate acima parte nesse segundo caso — devolve "Invalid Date".
 *
 * Mesma função que o Courses.jsx já usa no ecrã de seleção.
 */
function formatStartDate(rawDate) {
    const date = new Date(rawDate);

    return Number.isNaN(date.getTime()) ? rawDate : date.toLocaleDateString('pt-PT');
}

function Section({ title, eyebrow, children }) {
    return (
        <section className="border-t border-gray-200 pt-8">
            {eyebrow && (
                <p className="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                    {eyebrow}
                </p>
            )}

            <h2 className="mt-1 text-2xl font-bold text-gray-950">
                {title}
            </h2>

            <div className="mt-5">{children}</div>
        </section>
    );
}

function EmptyMessage({ children }) {
    return (
        <p className="rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500">
            {children}
        </p>
    );
}

function imageUrl(path) {
    if (!path) {
        return null;
    }

    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }

    return `/storage/${path}`;
}

export default function NewsletterTemplate({ newsletter }) {
    const news = newsletter.news ?? [];
    const testimonials = newsletter.testimonials ?? [];
    const courses = newsletter.courses ?? [];
    const calendars = newsletter.calendars ?? [];

    const periodStart = formatDate(newsletter.period_start);
    const periodEnd = formatDate(newsletter.period_end);
    const newsletterDate = formatDate(newsletter.date);

    return (
        <article className="newsletter-document overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200 print:rounded-none print:shadow-none print:ring-0">
            <header className="bg-gradient-to-br from-[#0d2740] to-[#243b73] px-6 py-8 text-white sm:px-10 print:px-0">
                <div className="max-w-3xl">
                    <p className="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-200">
                        Newsletter interna
                    </p>

                    <h1 className="mt-4 text-3xl font-bold leading-tight sm:text-5xl">
                        {newsletter.title}
                    </h1>

                    <div className="mt-6 grid gap-3 text-sm text-gray-200 sm:grid-cols-3">
                        <div>
                            <p className="text-xs uppercase tracking-wide text-gray-400">
                                Edição
                            </p>
                            <p className="mt-1 font-semibold text-white">
                                #{newsletter.edition}
                            </p>
                        </div>

                        <div>
                            <p className="text-xs uppercase tracking-wide text-gray-400">
                                Data
                            </p>
                            <p className="mt-1 font-semibold text-white">
                                {newsletterDate}
                            </p>
                        </div>

                        <div>
                            <p className="text-xs uppercase tracking-wide text-gray-400">
                                Período
                            </p>
                            <p className="mt-1 font-semibold text-white">
                                {periodStart} - {periodEnd}
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            <div className="space-y-10 px-6 py-8 sm:px-10 sm:py-10 print:px-0 print:py-6">
                <Section title="Notícias" eyebrow="Atualizações">
                    {news.length === 0 ? (
                        <EmptyMessage>Sem notícias selecionadas.</EmptyMessage>
                    ) : (
                        <div className="grid gap-6 lg:grid-cols-2">
                            {news.map((item) => {
                                const src = imageUrl(item.image);

                                return (
                                    <article
                                        key={item.id}
                                        className="overflow-hidden rounded-lg border border-gray-200 bg-white"
                                    >
                                        {src && (
                                            <img
                                                src={src}
                                                alt={item.title}
                                                className="h-48 w-full object-cover print:h-32"
                                            />
                                        )}

                                        <div className="p-5">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                                                {item.category?.name ?? 'Sem categoria'}
                                            </p>

                                            <h3 className="mt-2 text-xl font-bold text-gray-950">
                                                {item.title}
                                            </h3>

                                            <p className="mt-3 text-sm leading-6 text-gray-700">
                                                {item.description}
                                            </p>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    )}
                </Section>

                <Section title="Testemunhos" eyebrow="Vozes da comunidade">
                    {testimonials.length === 0 ? (
                        <EmptyMessage>Sem testemunhos selecionados.</EmptyMessage>
                    ) : (
                        <div className="grid gap-5 md:grid-cols-2">
                            {testimonials.map((item) => {
                                const src = imageUrl(item.image);

                                return (
                                    <article
                                        key={item.id}
                                        className="rounded-lg border border-gray-200 bg-gray-50 p-5"
                                    >
                                        <div className="flex gap-4">
                                            {src && (
                                                <img
                                                    src={src}
                                                    alt={item.name}
                                                    className="h-16 w-16 shrink-0 rounded-full object-cover"
                                                />
                                            )}

                                            <div>
                                                <p className="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                                                    {item.category?.name ?? 'Testemunho'}
                                                </p>

                                                <h3 className="mt-1 text-lg font-bold text-gray-950">
                                                    {item.title}
                                                </h3>

                                                <p className="mt-2 text-sm leading-6 text-gray-700">
                                                    {item.description}
                                                </p>

                                                <p className="mt-3 text-sm font-semibold text-gray-900">
                                                    {item.name}
                                                </p>
                                            </div>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    )}
                </Section>

                <Section title="Formações" eyebrow="Aprendizagem">
                    {courses.length === 0 ? (
                        <EmptyMessage>Sem formações selecionadas.</EmptyMessage>
                    ) : (
                        <div className="space-y-3">
                            {courses.map((item) => (
                                <article
                                    key={item.id}
                                    className="rounded-lg border border-gray-200 bg-white p-4"
                                >
                                    <h3 className="font-bold text-gray-950">{item.title}</h3>

                                    <dl className="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600">
                                        {item.start_date && (
                                            <div className="flex gap-1">
                                                <dt className="font-semibold text-gray-900">Início:</dt>
                                                <dd>{formatStartDate(item.start_date)}</dd>
                                            </div>
                                        )}
                                        {item.schedule && (
                                            <div className="flex gap-1">
                                                <dt className="font-semibold text-gray-900">Horário:</dt>
                                                <dd>{item.schedule}</dd>
                                            </div>
                                        )}
                                        {item.location && (
                                            <div className="flex gap-1">
                                                <dt className="font-semibold text-gray-900">Local:</dt>
                                                <dd>{item.location}</dd>
                                            </div>
                                        )}
                                        {item.price && (
                                            <div className="flex gap-1">
                                                <dt className="font-semibold text-gray-900">Preço:</dt>
                                                <dd>{item.price}</dd>
                                            </div>
                                        )}
                                    </dl>
                                </article>
                            ))}
                        </div>
                    )}
                </Section>

                <Section title="Agenda" eyebrow="Próximos eventos">
                    {calendars.length === 0 ? (
                        <EmptyMessage>Sem eventos selecionados.</EmptyMessage>
                    ) : (
                        <div className="space-y-3">
                            {calendars.map((item) => (
                                <article
                                    key={item.id}
                                    className="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <h3 className="font-semibold text-gray-950">
                                        {item.title}
                                    </h3>

                                    <p className="text-sm font-medium text-gray-600">
                                        {formatDate(item.date)}
                                    </p>
                                </article>
                            ))}
                        </div>
                    )}
                </Section>
            </div>

            <footer className="border-t border-gray-200 bg-gray-50 px-6 py-5 text-sm text-gray-500 sm:px-10 print:px-0">
                We.Did.It · {newsletter.title} — edição {newsletter.edition}
            </footer>
        </article>
    );
}
