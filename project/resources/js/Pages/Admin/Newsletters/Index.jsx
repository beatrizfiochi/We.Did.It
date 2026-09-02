import DangerButton from '@/Components/DangerButton';
import DataTable from '@/Components/DataTable';
import Dropdown from '@/Components/Dropdown';
import FlashMessage from '@/Components/FlashMessage';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import StatusBadge from '@/Components/StatusBadge';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

// Filtro por estado feito no browser sobre a lista que já chega (mesma decisão
// da SCRUM-112). As publicadas continuam na tabela por omissão — é dessa
// listagem que a SCRUM-122 precisa para voltar a gerar o PDF de uma edição
// antiga (ver SCRUM-65).
const STATUS_FILTERS = [
    { key: 'all', label: 'Todas' },
    { key: 'draft', label: 'Rascunhos' },
    { key: 'published', label: 'Publicadas' },
];

export default function Index({ newsletters }) {
    // null = criar, objeto = editar. Um modal só para os dois casos.
    const [editing, setEditing] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [deleting, setDeleting] = useState(null);
    const [publishing, setPublishing] = useState(null);
    const [statusFilter, setStatusFilter] = useState('all');

    // is_draft vem já interpretado na listagem; o status é um boolean cru e não
    // se lê diretamente.
    const visibleNewsletters = newsletters.filter((newsletter) => {
        if (statusFilter === 'draft') return newsletter.is_draft;
        if (statusFilter === 'published') return !newsletter.is_draft;
        return true;
    });
    const actionClass =
        'inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-indigo-50 hover:text-indigo-700';
    const dangerActionClass =
        'inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-100 hover:text-red-700';
    // publicar é uma ação importante e sem volta — não se dilui no cinzento das outras
    const publishActionClass =
        'inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-100';

    // obtem a data atual do utilizador de acordo com o timezone, sem depender do UTC - referencia universal
    const getToday = () => {
        const today = new Date();
        return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    }
    const todayDate = getToday();


    const { data, setData, post, put, delete: destroy, processing, errors, reset, clearErrors } =
        useForm({ title: '', edition: '', date: todayDate, period_start: '', period_end: '' });

    // formulário próprio, sem dados: o processing deste é que sabe se o PATCH
    // de publicar está em curso. Com o router.patch(), o processing acima é o
    // do formulário de edição e não desativava nada — dois cliques rápidos
    // enviavam dois pedidos, e o segundo apanhava o 403 do que o primeiro
    // acabou de publicar.
    const publishForm = useForm({});

    const openCreate = () => {
        reset();
        clearErrors();
        setEditing(null);
        setShowForm(true);
    };

    const openEdit = (event) => {

        //console.log('EVENT:', event);

        clearErrors();
        setEditing(event);

        setData({
            title: event.title,
            edition: event.edition,
            date: event.date.slice(0, 10),
            period_start: event.period_start?.slice(0, 10),
            period_end: event.period_end?.slice(0, 10),
        });

        setShowForm(true);
    };

    const closeForm = () => {
        setShowForm(false);
        clearErrors();
        setEditing(null);
        reset();
    };

    const submit = (e) => {
        e.preventDefault();

        // o status deixou de vir daqui (SCRUM-116): guardar mantém sempre o
        // rascunho, e publicar é uma ação à parte, no confirmPublish()
        const action = editing ? put : post;

        // route de acordo com a ação escolhida
        const url = editing
            ? route('admin.newsletters.update', editing.id)
            : route('admin.newsletters.store');

        action(url, {
            preserveScroll: true,
            onSuccess: closeForm,
        });
    };

    const confirmDelete = () => {
        destroy(route('admin.newsletters.destroy', deleting.id), {
            preserveScroll: true,
            onSuccess: () => setDeleting(null),
        });
    };

    // publicar é irreversível — não há forma de voltar a rascunho — por isso
    // passa por confirmação (SCRUM-116). A ação está na linha, não dentro do
    // modal de edição.
    const confirmPublish = () => {
        publishForm.patch(route('admin.newsletters.publish', publishing.id), {
            preserveScroll: true,
            onSuccess: () => setPublishing(null),
        });
    };

    const columns = [
        {
            key: 'date',
            label: 'Data de criação',
            render: (row) => new Date(row.date).toLocaleDateString('pt-PT'),
        },
        {
            key: 'title',
            label: 'Título',
            render: (row) => row.title,
        },
        {
            key: 'edition',
            label: 'Edição',
            render: (row) => row.edition,
        },
        {
            key: 'status',
            label: 'Estado',
            render: (row) => <StatusBadge status={row.is_draft ? 'Rascunho' : 'Publicada'} />,
        },
        {
            key: 'actions',
            label: 'Ações',
            // As ações dependem do estado. Uma publicada é registo do que foi
            // distribuído: não se edita, não se removem conteúdos, não se apaga
            // (o backend recusa tudo isso com 403 — SCRUM-116). Aqui só se
            // escondem os botões. Fica a pré-visualização, que é o que a
            // SCRUM-122 usa para voltar a gerar o PDF.
            render: (row) => (
                <div className="flex flex-wrap items-center gap-4">
                    <Link
                        href={route('admin.newsletters.preview', row.id)}
                        className={actionClass}
                    >
                        Pré-visualizar
                    </Link>

                    {/* uma publicada só tem "Pré-visualizar": é lá dentro que está
                        o botão de guardar em PDF (SCRUM-120), e é assim que se
                        volta a gerar o PDF de uma edição antiga (SCRUM-122) */}
                    {row.is_draft && (
                        <>
                            <Dropdown>
                                <Dropdown.Trigger>
                                    <span className="inline-flex">
                                        <button
                                            type="button"
                                            className={actionClass}
                                        >
                                            Conteúdos

                                            <svg
                                                className="ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fillRule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </Dropdown.Trigger>

                                <Dropdown.Content align="left">
                                    <Dropdown.Link href={route('admin.newsletters.news.edit', row.id)}>
                                        Notícias
                                    </Dropdown.Link>
                                    <Dropdown.Link href={route('admin.newsletters.testimonials.edit', row.id)}>
                                        Testemunhos
                                    </Dropdown.Link>
                                    <Dropdown.Link href={route('admin.newsletters.courses.edit', row.id)}>
                                        Formações
                                    </Dropdown.Link>
                                    <Dropdown.Link href={route('admin.newsletters.calendars.edit', row.id)}>
                                        Agenda
                                    </Dropdown.Link>
                                </Dropdown.Content>
                            </Dropdown>

                            <button
                                type="button"
                                onClick={() => openEdit(row)}
                                className={actionClass}
                            >
                                Editar
                            </button>

                            <button
                                type="button"
                                onClick={() => setPublishing(row)}
                                className={publishActionClass}
                            >
                                Publicar
                            </button>

                            <button
                                type="button"
                                onClick={() => setDeleting(row)}
                                className={dangerActionClass}
                            >
                                Remover
                            </button>
                        </>
                    )}
                </div>
            ),
        },
    ];


    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Newsletter
                </h2>
            }
        >
            <Head title="Newsletter" />

            <div className="space-y-6">
                <FlashMessage />

                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex flex-wrap gap-2" role="group" aria-label="Filtrar por estado">
                        {STATUS_FILTERS.map((filter) => (
                            <button
                                key={filter.key}
                                type="button"
                                aria-pressed={statusFilter === filter.key}
                                onClick={() => setStatusFilter(filter.key)}
                                className={
                                    'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold shadow-sm transition ' +
                                    (statusFilter === filter.key
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700')
                                }
                            >
                                {filter.label}
                            </button>
                        ))}
                    </div>

                    <PrimaryButton onClick={openCreate}>
                        Nova Newsletter
                    </PrimaryButton>
                </div>

                <DataTable
                    columns={columns}
                    rows={visibleNewsletters}
                    emptyTitle={
                        statusFilter === 'all'
                            ? 'Ainda não há newsletters criadas'
                            : 'Nenhuma newsletter neste estado'
                    }
                    emptyDescription={
                        statusFilter === 'all'
                            ? 'Cria a primeira newsletter no botão acima.'
                            : 'Muda o filtro para veres as outras newsletters.'
                    }
                />
            </div>

            <Modal show={showForm} onClose={closeForm} maxWidth="2xl">
                <form onSubmit={submit} className="space-y-4 p-4 sm:p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        {editing ? 'Editar newsletter' : 'Nova newsletter'}
                    </h2>

                    <div>
                        <InputLabel htmlFor="title" value="Título" />
                        <TextInput
                            id="title"
                            value={data.title}
                            className="mt-1 block w-full"
                            isFocused
                            onChange={(e) => setData('title', e.target.value)}
                        />
                        <InputError message={errors.title} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="edition" value="Edição" />
                        <TextInput
                            id="edition"
                            type="number"
                            value={data.edition}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('edition', e.target.value)}
                        />
                        <InputError message={errors.edition} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="date" value="Data de criação" />
                        <TextInput
                            id="date"
                            type="date"
                            value={data.date}
                            min={editing ? undefined : todayDate} //if user is editing, min and max is undefined(free to choose)
                            max={todayDate}
                            disabled={!editing}
                            className={`mt-1 block w-full
                                ${!editing ? 'cursor-not-allowed bg-gray-100 text-gray-500' : ''
                                }`}
                            onChange={(e) => setData('date', e.target.value)}
                        />
                        <InputError message={errors.date} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="period_start" value="Data de início" />
                        <TextInput
                            id="period_start"
                            type="date"
                            value={data.period_start}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('period_start', e.target.value)}
                        />
                        <InputError message={errors.period_start} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="period_end" value="Data de fim" />
                        <TextInput
                            id="period_end"
                            type="date"
                            value={data.period_end}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('period_end', e.target.value)}
                        />
                        <InputError message={errors.period_end} className="mt-2" />
                    </div>

                    {editing &&
                        <div>
                            <InputLabel htmlFor="status" value="Estado" />

                            {/* lê do registo e não do formulário: o estado
                                deixou de ser um campo editável (SCRUM-116) */}
                            <div className="mt-1">
                                <StatusBadge status={editing.is_draft ? 'Rascunho' : 'Publicada'} />
                            </div>
                        </div>
                    }

                    <div className="flex flex-wrap justify-end gap-3">
                        <SecondaryButton type="button" onClick={closeForm}>
                            Cancelar
                        </SecondaryButton>

                        {/* publicar deixou de estar aqui: é uma ação da linha da
                            listagem, para não ser preciso abrir "Editar" primeiro */}
                        <PrimaryButton
                            type="button"
                            disabled={processing}
                            onClick={submit}>
                            Guardar Rascunho
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>

            {/* publicar não tem volta: não há rota para despublicar, e a partir
                daqui a newsletter deixa de poder ser editada ou removida */}
            <Modal show={publishing !== null} onClose={() => setPublishing(null)} maxWidth="md">
                <div className="space-y-4 p-4 sm:p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Publicar esta newsletter?
                    </h2>

                    <p className="text-sm text-gray-600">
                        {publishing?.title} — edição {publishing?.edition}. Depois de
                        publicada deixa de poder ser editada, ter conteúdos trocados ou
                        ser removida. Esta ação não pode ser anulada.
                    </p>

                    <div className="flex flex-wrap justify-end gap-3">
                        <SecondaryButton onClick={() => setPublishing(null)}>
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton onClick={confirmPublish} disabled={publishForm.processing}>
                            Publicar
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            {/* confirmação em modal, e não window.confirm(), que bloqueia o browser */}
            <Modal show={deleting !== null} onClose={() => setDeleting(null)} maxWidth="md">
                <div className="space-y-4 p-4 sm:p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Remover esta newsletter?
                    </h2>

                    <p className="text-sm text-gray-600">
                        {deleting?.title} — esta ação não pode ser anulada.
                    </p>

                    <div className="flex flex-wrap justify-end gap-3">
                        <SecondaryButton onClick={() => setDeleting(null)}>
                            Cancelar
                        </SecondaryButton>

                        <DangerButton disabled={processing} onClick={confirmDelete}>
                            Remover
                        </DangerButton>
                    </div>
                </div>
            </Modal>

        </AuthenticatedLayout >
    );
}
