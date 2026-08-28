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
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ events }) {
    // null = criar, objeto = editar. Um modal só para os dois casos.
    const [editing, setEditing] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [deleting, setDeleting] = useState(null);
    const actionClass =
        'inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-indigo-50 hover:text-indigo-700';
    const dangerActionClass =
        'inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-100 hover:text-red-700';

    const { data, setData, post, put, delete: destroy, processing, errors, reset, clearErrors } =
        useForm({ title: '', date: '' });

    const openCreate = () => {
        reset();
        clearErrors();
        setEditing(null);
        setShowForm(true);
    };

    const openEdit = (event) => {
        clearErrors();
        setEditing(event);
        // a coluna é DATE, mas pode vir com hora; o input type="date" quer YYYY-MM-DD
        setData({ title: event.title, date: event.date.slice(0, 10) });
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
            put(route('admin.calendar.update', editing.id), options);
        } else {
            post(route('admin.calendar.store'), options);
        }
    };

    const confirmDelete = () => {
        destroy(route('admin.calendar.destroy', deleting.id), {
            preserveScroll: true,
            onSuccess: () => setDeleting(null),
        });
    };

    const columns = [
        {
            key: 'date',
            label: 'Data',
            render: (row) => new Date(row.date).toLocaleDateString('pt-PT'),
        },
        { key: 'title', label: 'Evento' },
        {
            key: 'actions',
            label: 'Ações',
            render: (row) => (
                <div className="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        onClick={() => openEdit(row)}
                        className={actionClass}
                    >
                        Editar
                    </button>

                    <button
                        type="button"
                        onClick={() => setDeleting(row)}
                        className={dangerActionClass}
                    >
                        Remover
                    </button>
                </div>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Agenda
                </h2>
            }
        >
            <Head title="Agenda" />

            <div className="space-y-6">
                <FlashMessage />

                <div className="flex justify-end">
                    <PrimaryButton onClick={openCreate}>
                        Novo evento
                    </PrimaryButton>
                </div>

                <DataTable
                    columns={columns}
                    rows={events}
                    emptyTitle="Ainda não há eventos na agenda"
                    emptyDescription="Cria o primeiro evento no botão acima."
                />
            </div>

            <Modal show={showForm} onClose={closeForm} maxWidth="md">
                <form onSubmit={submit} className="space-y-4 p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        {editing ? 'Editar evento' : 'Novo evento'}
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

            {/* confirmação em modal, e não window.confirm(), que bloqueia o browser */}
            <Modal show={deleting !== null} onClose={() => setDeleting(null)} maxWidth="md">
                <div className="space-y-4 p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Remover este evento?
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
