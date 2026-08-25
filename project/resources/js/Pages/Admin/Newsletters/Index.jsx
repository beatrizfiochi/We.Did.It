import DangerButton from '@/Components/DangerButton';
import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('pt-PT');
}

export default function Index({ newsletters }) {
    const [editing, setEditing] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [deleting, setDeleting] = useState(null);

    const {
        data,
        setData,
        post,
        put,
        delete: destroy,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        title: '',
        edition: '',
        date: '',
        period_start: '',
        period_end: '',
    });

    const openCreate = () => {
        reset();
        clearErrors();
        setEditing(null);
        setShowForm(true);
    };

    const openEdit = (newsletter) => {
        clearErrors();
        setEditing(newsletter);
        setData({
            title: newsletter.title ?? '',
            edition: newsletter.edition ?? '',
            date: newsletter.date?.slice(0, 10) ?? '',
            period_start: newsletter.period_start?.slice(0, 10) ?? '',
            period_end: newsletter.period_end?.slice(0, 10) ?? '',
        });
        setShowForm(true);
    };

    const closeForm = () => {
        setShowForm(false);
        reset();
        clearErrors();
    };

    const submit = (e) => {
        e.preventDefault();

        const options = { preserveScroll: true, onSuccess: closeForm };

        if (editing) {
            put(route('admin.newsletters.update', editing.id), options);
        } else {
            post(route('admin.newsletters.store'), options);
        }
    };

    const confirmDelete = () => {
        destroy(route('admin.newsletters.destroy', deleting.id), {
            preserveScroll: true,
            onSuccess: () => setDeleting(null),
        });
    };

    const columns = [
        { key: 'edition', label: 'Edição', render: (row) => `#${row.edition}` },
        { key: 'title', label: 'Título' },
        { key: 'date', label: 'Data', render: (row) => formatDate(row.date) },
        {
            key: 'status',
            label: 'Estado',
            render: (row) => (row.status ? 'Rascunho' : 'Publicada'),
        },
        {
            key: 'actions',
            label: 'Ações',
            render: (row) => (
                <div className="flex flex-wrap items-center gap-2">
                    <Link
                        href={route('admin.newsletters.preview', row.id)}
                        className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Pré-visualizar
                    </Link>

                    <SecondaryButton onClick={() => openEdit(row)}>
                        Editar
                    </SecondaryButton>

                    <button
                        type="button"
                        className="inline-flex items-center rounded-md border border-red-200 bg-red-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 shadow-sm transition duration-150 ease-in-out hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2 active:bg-red-300"
                        onClick={() => setDeleting(row)}
                    >
                        Remover
                    </button>
                </div>
            ),
        },
    ];

    return (
        <AuthenticatedLayout header="Newsletters">
            <Head title="Newsletters" />

            <div className="space-y-6">
                <FlashMessage />

                <div className="flex justify-end">
                    <PrimaryButton onClick={openCreate}>
                        Nova newsletter
                    </PrimaryButton>
                </div>

                <DataTable
                    columns={columns}
                    rows={newsletters}
                    emptyTitle="Ainda não há newsletters"
                    emptyDescription="Cria a primeira newsletter no botão acima."
                />
            </div>

            <Modal show={showForm} onClose={closeForm} maxWidth="md">
                <form onSubmit={submit} className="space-y-4 p-6">
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
                            min="1"
                            value={data.edition}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('edition', e.target.value)}
                        />
                        <InputError message={errors.edition} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="date" value="Data" />
                        <TextInput
                            id="date"
                            type="date"
                            value={data.date}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('date', e.target.value)}
                        />
                        <InputError message={errors.date} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="period_start" value="Início do período" />
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
                        <InputLabel htmlFor="period_end" value="Fim do período" />
                        <TextInput
                            id="period_end"
                            type="date"
                            value={data.period_end}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('period_end', e.target.value)}
                        />
                        <InputError message={errors.period_end} className="mt-2" />
                    </div>

                    <div className="flex flex-wrap justify-end gap-3">
                        <SecondaryButton type="button" onClick={closeForm}>
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton disabled={processing}>
                            {editing ? 'Guardar' : 'Criar'}
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>

            <Modal show={deleting !== null} onClose={() => setDeleting(null)} maxWidth="md">
                <div className="space-y-4 p-6">
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
        </AuthenticatedLayout>
    );
}
