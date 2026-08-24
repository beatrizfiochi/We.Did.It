import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ categories = [] }) {
    const [editingCategory, setEditingCategory] = useState(null);
    const [deleteError, setDeleteError] = useState(null);

    const createForm = useForm({
        name: '',
    });

    const editForm = useForm({
        name: '',
    });

    function submitCreate(e) {
        e.preventDefault();

        createForm.post(route('admin.categories.store'), {
            preserveScroll: true,
            onSuccess: () => createForm.reset(),
        });
    }

    function startEdit(category) {
        setEditingCategory(category);
        editForm.setData('name', category.name);
        editForm.clearErrors();
    }

    function cancelEdit() {
        setEditingCategory(null);
        editForm.reset();
        editForm.clearErrors();
    }

    function submitEdit(e) {
        e.preventDefault();

        editForm.put(route('admin.categories.update', editingCategory.id), {
            preserveScroll: true,
            onSuccess: cancelEdit,
        });
    }

    function destroyCategory(category) {
        if (!confirm(`Tens a certeza que queres remover a categoria "${category.name}"?`)) {
            return;
        }

        // O botão fica desativado quando a categoria está em uso, mas as
        // contagens podem estar desatualizadas. A guarda real é no servidor:
        // a foreign key é onDelete('restrict') e o controller devolve um erro
        // de validação na chave 'category' em vez de quebrar com QueryException.
        router.delete(route('admin.categories.destroy', category.id), {
            preserveScroll: true,
            onSuccess: () => {
                setDeleteError(null);
                cancelEdit();
            },
            onError: (errors) => setDeleteError(errors.category),
        });
    }

    const columns = [
        { key: 'name', label: 'Nome' },
        {
            key: 'news_count',
            label: 'Notícias',
            render: (category) => category.news_count ?? 0,
        },
        {
            key: 'testimonials_count',
            label: 'Testemunhos',
            render: (category) => category.testimonials_count ?? 0,
        },
        {
            key: 'actions',
            label: 'Ações',
            render: (category) => {
                const inUse =
                    (category.news_count ?? 0) > 0 ||
                    (category.testimonials_count ?? 0) > 0;

                return (
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => startEdit(category)}
                            className="text-sm font-semibold text-indigo-600 hover:text-indigo-900"
                        >
                            Editar
                        </button>

                        <button
                            type="button"
                            onClick={() => destroyCategory(category)}
                            disabled={inUse}
                            className={
                                'text-sm font-semibold ' +
                                (inUse
                                    ? 'cursor-not-allowed text-gray-400'
                                    : 'text-red-600 hover:text-red-900')
                            }
                            title={
                                inUse
                                    ? 'Não é possível remover uma categoria em uso.'
                                    : undefined
                            }
                        >
                            Remover
                        </button>
                    </div>
                );
            },
        },
    ];

    return (
        <AuthenticatedLayout header="Categorias">
            <Head title="Categorias" />

            <div className="space-y-6">
                <FlashMessage />

                <p className="text-sm text-gray-600">
                    Gere as categorias usadas nas notícias e testemunhos.
                </p>

                <div className="rounded-lg bg-white p-6 shadow-sm">
                    <form onSubmit={submitCreate} className="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <div className="flex-1">
                            <TextInput
                                id="name"
                                name="name"
                                value={createForm.data.name}
                                className="block w-full"
                                placeholder="Nome da categoria"
                                onChange={(e) => createForm.setData('name', e.target.value)}
                            />

                            <InputError message={createForm.errors.name} className="mt-2" />
                        </div>

                        <PrimaryButton disabled={createForm.processing}>
                            Criar categoria
                        </PrimaryButton>
                    </form>
                </div>

                {editingCategory && (
                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <form onSubmit={submitEdit} className="flex flex-col gap-4 sm:flex-row sm:items-start">
                            <div className="flex-1">
                                <p className="mb-2 text-sm font-medium text-gray-700">
                                    Editar categoria
                                </p>

                                <TextInput
                                    id="edit-name"
                                    name="name"
                                    value={editForm.data.name}
                                    className="block w-full"
                                    onChange={(e) => editForm.setData('name', e.target.value)}
                                />

                                <InputError message={editForm.errors.name} className="mt-2" />
                            </div>

                            <div className="flex items-center gap-3">
                                <SecondaryButton type="button" onClick={cancelEdit}>
                                    Cancelar
                                </SecondaryButton>

                                <PrimaryButton disabled={editForm.processing}>
                                    Guardar
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                )}

                <InputError message={deleteError} className="mb-2" />

                <DataTable
                    columns={columns}
                    rows={categories}
                    emptyTitle="Ainda não há categorias"
                    emptyDescription="Assim que criares uma categoria, ela aparece aqui."
                />
            </div>
        </AuthenticatedLayout>
    );
}
