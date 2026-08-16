import DataTable from '@/Components/DataTable';
import DangerButton from '@/Components/DangerButton';
import FlashMessage from '@/Components/FlashMessage';
import PrimaryButton from '@/Components/PrimaryButton';
import StatusBadge from '@/Components/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

/*
 * ⚠️ ECRÃ PROVISÓRIO — não serve para moderar a sério.
 *
 * O SCRUM-86 eram os endpoints de aprovar, recusar e editar. Este ficheiro
 * existe porque sem ele a rota admin/noticias devolve 500: o app.blade.php
 * procura a página no manifest do Vite.
 *
 * Já funciona: listar, aprovar e recusar.
 * Falta:       ⛔ ler o conteúdo submetido antes de decidir.
 *
 * Esta lacuna é a mais importante: hoje pede-se para aprovar uma notícia sem
 * a poder ler. A descrição, a imagem e a data já vêm todas nas props (o
 * controller faz paginate() sobre o model completo) — falta só renderizá-las,
 * num modal de detalhe ou numa página própria. O email de aviso também não
 * leva o conteúdo, de propósito, por isso neste momento não há sítio nenhum
 * na aplicação onde se possa ler antes de aprovar.
 *
 * Falta ainda: o formulário de edição (PUT admin.news.update, já testado) e
 * o filtro por estado, que o backend suporta em ?status=received|accepted|refused
 * e chega ao frontend na prop `filters`.
 *
 * ✅ Backend pronto e testado — ver tests/Feature/Admin/NewsModerationTest.php.
 */
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
