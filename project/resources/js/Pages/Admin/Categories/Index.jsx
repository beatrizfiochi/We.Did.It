import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

// Ecrã mínimo de leitura (SCRUM-89). O backend já suporta criar, editar e
// remover — falta ligar essas ações aqui quando o ecrã for feito a sério.
export default function Index({ categories }) {
    const columns = [
        { key: 'name', label: 'Nome' },
        {
            key: 'news_count',
            label: 'Notícias',
            render: (row) => row.news_count ?? 0,
        },
        {
            key: 'testimonials_count',
            label: 'Testemunhos',
            render: (row) => row.testimonials_count ?? 0,
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Categorias
                </h2>
            }
        >
            <Head title="Categorias" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <FlashMessage />

                    <DataTable
                        columns={columns}
                        rows={categories}
                        emptyTitle="Ainda não há categorias"
                        emptyDescription="Assim que criares uma categoria, ela aparece aqui."
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
