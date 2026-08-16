import DataTable from '@/Components/DataTable';
import DangerButton from '@/Components/DangerButton';
import FlashMessage from '@/Components/FlashMessage';
import PrimaryButton from '@/Components/PrimaryButton';
import StatusBadge from '@/Components/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

// Ecrã mínimo de moderação (SCRUM-86): listar, aprovar e recusar.
// A edição do conteúdo já existe no backend (PUT) e falta ligar aqui.
export default function Index({ news }) {
    const columns = [
        { key: 'title', label: 'Título' },
        {
            key: 'category',
            label: 'Categoria',
            render: (row) => row.category?.name ?? 'Nenhuma',
        },
        {
            key: 'status',
            label: 'Estado',
            render: (row) => <StatusBadge status={row.status} />,
        },
        {
            key: 'actions',
            label: 'Ações',
            render: (row) => (
                <div className="flex gap-2">
                    <PrimaryButton
                        disabled={row.status === 'accepted'}
                        onClick={() => router.patch(route('admin.news.approve', row.id))}
                    >
                        Aprovar
                    </PrimaryButton>

                    <DangerButton
                        disabled={row.status === 'refused'}
                        onClick={() => router.patch(route('admin.news.refuse', row.id))}
                    >
                        Recusar
                    </DangerButton>
                </div>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Notícias
                </h2>
            }
        >
            <Head title="Notícias" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <FlashMessage />

                    <DataTable
                        columns={columns}
                        rows={news.data}
                        emptyTitle="Ainda não há notícias submetidas"
                        emptyDescription="As notícias enviadas pelo formulário público aparecem aqui."
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
