import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import StatusBadge from '@/Components/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function Index({ courses = [] }) {
    const { post, processing } = useForm();
    const actionClass =
        'inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-indigo-50 hover:text-indigo-700';
    const dangerActionClass =
        'inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-100 hover:text-red-700';

    function importCourses() {
        post(route('admin.courses.import'), { preserveScroll: true });
    }

    const columns = [
        { key: 'title', label: 'Título' },
        { key: 'location', label: 'Local' },
        { key: 'schedule', label: 'Horário' },
        { key: 'start_date', label: 'Data' },
        { key: 'price', label: 'Preço' },
        {
            key: 'status',
            label: 'Estado',
            render: (course) => <StatusBadge status={course.status} />,
        },
        {
            key: 'actions',
            label: 'Ações',
            render: (course) => (
                <div className="flex flex-wrap items-center gap-3">
                    <Link
                        href={route('admin.courses.edit', course.id)}
                        className={actionClass}
                    >
                        Editar
                    </Link>

                    <button
                        type="button"
                        onClick={() => {
                            if (confirm('Tens a certeza que queres remover esta oferta?')) {
                                router.delete(route('admin.courses.destroy', course.id));
                            }
                        }}
                        className={dangerActionClass}
                    >
                        Remover
                    </button>
                </div>
            ),
        },
    ];

    return (
        <AuthenticatedLayout header="Ofertas formativas">
            <Head title="Ofertas formativas" />

            <div className="space-y-6">
                <FlashMessage />

                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>

                        <p className="mt-1 text-sm text-gray-600">
                            Gere as ofertas formativas disponíveis para divulgação.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={importCourses}
                            disabled={processing}
                            className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {processing ? 'A importar…' : 'Importar cursos'}
                        </button>

                        <Link
                            href={route('admin.courses.create')}
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Nova oferta
                        </Link>
                    </div>
                </div>

                <DataTable
                    columns={columns}
                    rows={courses}
                    emptyTitle="Ainda não há ofertas formativas"
                    emptyDescription="Quando forem criadas, as ofertas aparecem nesta lista."
                />
            </div>
        </AuthenticatedLayout>
    );
}
