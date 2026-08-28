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


export default function Index({ newsletters }) {
    // null = criar, objeto = editar. Um modal só para os dois casos.
    const [editing, setEditing] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [deleting, setDeleting] = useState(null);

    // obtem a data atual do utilizador de acordo com o timezone, sem depender do UTC - referencia universal
    const getToday = () => {
        const today = new Date();
        return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    }
    const todayDate = getToday();


    const { data, setData, post, put, delete: destroy, processing, errors, reset, clearErrors, transform } =
        useForm({ title: '', edition: '', date: todayDate, period_start: '', period_end: '', status: false });

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
            status: event.status,
        });

        setShowForm(true);
    };

    const closeForm = () => {
        setShowForm(false);
        clearErrors();
        setEditing(null);
        reset();
    };

    const submit = (e, status = false) => {
        e.preventDefault();

        // status === true  → draft
        // status === false → published
        // adiciona o status no formulario a partir do respetivo botão que chama esta função
        transform((formData) => ({ ...formData, status }));

        // ações possiveis: atualizar e publicar
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
            render: (row) => <StatusBadge status={row.status ? 'Rascunho' : 'Publicada'}></StatusBadge>,
        },
        {
            key: 'actions',
            label: 'Ações',
            render: (row) => (
                <div className="flex flex-wrap items-center justify-end gap-2">
                    <Link
                        href={route('admin.newsletters.preview', row.id)}
                        className="inline-flex min-w-32 justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-center text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Pré-visualizar
                    </Link>

                    <Dropdown>
                        <Dropdown.Trigger>
                            <span className="inline-flex rounded-md">
                                <button
                                    type="button"
                                    className="inline-flex min-w-32 items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
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

                    <SecondaryButton className="min-w-24 justify-center px-3" onClick={() => openEdit(row)}>
                        Editar
                    </SecondaryButton>

                    <DangerButton className="min-w-24 justify-center px-3" onClick={() => setDeleting(row)}>
                        Remover
                    </DangerButton>
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

                <div className="flex justify-end">
                    <PrimaryButton onClick={openCreate}>
                        Nova Newsletter
                    </PrimaryButton>
                </div>

                <DataTable
                    columns={columns}
                    rows={newsletters}
                    emptyTitle="Ainda não há newsletters criadas"
                    emptyDescription="Cria a primeira newsletter no botão acima."
                />
            </div>

            <Modal show={showForm} onClose={closeForm} maxWidth="md">
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

                            {/* "publicada" será eliminada mais tarde nos proximo sprint(nao sera possivel editar uma publicada) */}
                            <div className="mt-1">
                                <StatusBadge status={data.status ? 'Rascunho' : 'Publicada'} />
                            </div>

                            <InputError message={errors.status} className="mt-2" />
                        </div>
                    }

                    <div className="flex flex-wrap justify-end gap-3">
                        <SecondaryButton type="button" onClick={closeForm}>
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton
                            type="button"
                            disabled={processing}
                            //status=true guarda como rascunho
                            onClick={(event) => submit(event, true)}>
                            Guardar Rascunho
                        </PrimaryButton>

                        {editing && // só pode publicar depois de guardar rascunho
                            <PrimaryButton disabled={processing}>
                                {/* entra no default do submit status=false -> publica a newsletter */}
                                {/* {editing ? 'Guardar' : 'Criar'} */}
                                Publicar
                            </PrimaryButton>}
                    </div>
                </form>
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
