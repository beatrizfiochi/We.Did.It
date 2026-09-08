/**
 * Só os AAAA-MM-DD do valor, como três números.
 *
 * O servidor manda as datas em ISO com hora zero em UTC — "2026-05-12T00:00:00Z".
 * Passar isso a new Date() e depois formatar dá o dia no fuso do browser, e a
 * oeste de Greenwich a meia-noite UTC ainda é o dia anterior: em São Paulo o
 * dia 12 aparecia como 11. Confirmado a correr o formatador em vários fusos.
 *
 * Trabalhar sobre a string evita o problema todo: não há instante nenhum a
 * converter, só o dia do calendário que o servidor escreveu.
 */
function dateParts(value) {
    const [ano, mes, dia] = value.slice(0, 10).split('-').map(Number);

    return { ano, mes, dia };
}

function formatDate(value) {
    if (!value) {
        return 'Data por definir';
    }

    const { ano, mes, dia } = dateParts(value);

    return new Intl.DateTimeFormat('pt-PT', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(ano, mes - 1, dia));
}

/**
 * O período em que o evento aconteceu (SCRUM-145).
 *
 * Sem data de fim é um dia único. Com data de fim, e se o mês e o ano forem
 * os mesmos, escreve-se "12 a 15 de maio de 2026" em vez de repetir o mês
 * duas vezes — é como se escreve em português e poupa espaço no cartão.
 */
function formatEventPeriod(start, end) {
    if (!start) {
        return null;
    }

    if (!end) {
        return formatDate(start);
    }

    // comparação sobre os números da string, pela mesma razão do dateParts:
    // um new Date() aqui trazia o fuso do browser para dentro da decisão
    const inicio = dateParts(start);
    const fim = dateParts(end);

    if (inicio.mes === fim.mes && inicio.ano === fim.ano) {
        return `${inicio.dia} a ${formatDate(end)}`;
    }

    return `${formatDate(start)} a ${formatDate(end)}`;
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

/**
 * URLs das imagens que este item traz nesta edição, já pela ordem escolhida.
 * O controller (preview) resolve a relação `images` para a escolha da pivot
 * (SCRUM-143); aqui é só mapear para /storage. Fallback ao `image` espelho
 * para quem chamar o template fora do preview.
 */
function editionImages(item) {
    const images = item.images ?? [];

    if (images.length > 0) {
        return images.map((image) => imageUrl(image.path)).filter(Boolean);
    }

    const mirror = imageUrl(item.image);

    return mirror ? [mirror] : [];
}

function ImageFrame({ src, alt, ratio }) {
    return (
        <div className={`${ratio} w-full overflow-hidden rounded-md border border-gray-200 bg-gray-100`}>
            <img src={src} alt={alt} className="h-full w-full object-cover" />
        </div>
    );
}

function ImageLayout({ images, alt }) {
    const visibleImages = images.slice(0, 3);

    if (visibleImages.length === 0) {
        return null;
    }

    if (visibleImages.length === 1) {
        return (
            <div className="bg-gray-50 p-3">
                <ImageFrame
                    src={visibleImages[0]}
                    alt={alt}
                    ratio="aspect-[16/9]"
                />
            </div>
        );
    }

    if (visibleImages.length === 2) {
        return (
            <div className="flex gap-3 bg-gray-50 p-3">
                {visibleImages.map((src, index) => (
                    <div key={src} className="min-w-0 flex-1">
                        <ImageFrame
                            src={src}
                            alt={`${alt} - imagem ${index + 1}`}
                            ratio="aspect-[4/3]"
                        />
                    </div>
                ))}
            </div>
        );
    }

    return (
        <div className="space-y-3 bg-gray-50 p-3">
            <ImageFrame
                src={visibleImages[0]}
                alt={`${alt} - imagem 1`}
                ratio="aspect-[16/9]"
            />

            <div className="flex gap-3">
                {visibleImages.slice(1).map((src, index) => (
                    <div key={src} className="min-w-0 flex-1">
                        <ImageFrame
                            src={src}
                            alt={`${alt} - imagem ${index + 2}`}
                            ratio="aspect-[4/3]"
                        />
                    </div>
                ))}
            </div>
        </div>
    );
}

function TestimonialContent({ item }) {
    return (
        <>
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
        </>
    );
}

export default function NewsletterTemplate({ newsletter }) {
    const news = newsletter.news ?? [];
    const testimonials = newsletter.testimonials ?? [];
    const courses = newsletter.courses ?? [];
    const calendars = newsletter.calendars ?? [];

    const periodStart = formatDate(newsletter.period_start);
    const periodEnd = formatDate(newsletter.period_end);
    const newsletterDate = formatDate(newsletter.date);

    /*
     * print:!px-8 no cabeçalho, no corpo e no rodapé — não px-0.
     *
     * A margem do @page é a da folha; este padding é o ar dentro da faixa
     * escura do cabeçalho. Com px-0 o título ficava colado ao bordo do azul,
     * que era o que parecia cortado na impressão.
     *
     * O "!" é preciso porque em impressão a largura útil da folha (~700px)
     * ainda é maior do que o breakpoint sm, portanto o sm:px-10 continua a
     * aplicar-se e colide com esta regra. Sem o !important, quem ganha
     * depende da ordem em que o Tailwind emite as duas — e essa ordem não é
     * a mesma no npm run dev e no build, ou seja, imprimia diferente
     * conforme o sítio.
     *
     * print:!mt-0 no <article>: o space-y-6 do Preview.jsx põe margem no
     * segundo filho, e o primeiro (a barra de ações) é print:hidden mas
     * continua a contar para o seletor. Sem isto, a primeira página começava
     * 6 mm mais abaixo do que todas as outras.
     */
    return (
        <article className="newsletter-document overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200 print:!mt-0 print:rounded-none print:shadow-none print:ring-0">
            <header className="bg-gradient-to-br from-[#0d2740] to-[#243b73] px-6 py-8 text-white sm:px-10 print:!px-8 print:!py-5">
                <div className="max-w-3xl">
                    <p className="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-200">
                        Newsletter interna
                    </p>

                    <h1 className="mt-4 text-3xl font-bold leading-tight sm:text-5xl print:!text-3xl">
                        {newsletter.title}
                    </h1>

                    <div className="mt-6 grid gap-3 text-sm text-gray-200 sm:grid-cols-3 print:!mt-4">
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

            <div className="space-y-10 px-6 py-8 sm:px-10 sm:py-10 print:!px-8 print:!py-6">
                <Section title="Notícias" eyebrow="Atualizações">
                    {news.length === 0 ? (
                        <EmptyMessage>Sem notícias selecionadas.</EmptyMessage>
                    ) : (
                        <div className="grid gap-6 lg:grid-cols-2">
                            {news.map((item) => {
                                const images = editionImages(item);

                                return (
                                    <article
                                        key={item.id}
                                        className="overflow-hidden rounded-lg border border-gray-200 bg-white"
                                    >
                                        <ImageLayout images={images} alt={item.title} />

                                        <div className="p-5">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                                                {item.category?.name ?? 'Sem categoria'}
                                            </p>

                                            {formatEventPeriod(item.event_start_date, item.event_end_date) && (
                                                <p className="mt-1 text-xs text-gray-500">
                                                    {formatEventPeriod(item.event_start_date, item.event_end_date)}
                                                </p>
                                            )}

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
                                const images = editionImages(item);

                                if (images.length > 1) {
                                    return (
                                        <article
                                            key={item.id}
                                            className="overflow-hidden rounded-lg border border-gray-200 bg-gray-50"
                                        >
                                            <ImageLayout images={images} alt={item.title} />

                                            <div className="p-5">
                                                <TestimonialContent item={item} />
                                            </div>
                                        </article>
                                    );
                                }

                                return (
                                    <article
                                        key={item.id}
                                        className="rounded-lg border border-gray-200 bg-gray-50 p-5"
                                    >
                                        <div className="flex gap-4">
                                            {images[0] && (
                                                <img
                                                    src={images[0]}
                                                    alt={item.title}
                                                    className="h-16 w-16 shrink-0 rounded-full object-cover"
                                                />
                                            )}

                                            <div>
                                                <TestimonialContent item={item} />
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

            <footer className="border-t border-gray-200 bg-gray-50 px-6 py-5 text-sm text-gray-500 sm:px-10 print:!px-8">
                We.Did.It · {newsletter.title} — edição {newsletter.edition}
            </footer>
        </article>
    );
}
