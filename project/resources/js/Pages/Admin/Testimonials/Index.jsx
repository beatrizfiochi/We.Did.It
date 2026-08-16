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
 * existe porque sem ele a rota admin/testemunhos devolve 500: o app.blade.php
 * procura a página no manifest do Vite.
 *
 * Já funciona: listar, aprovar e recusar.
 * Falta:       ⛔ ler o testemunho submetido antes de decidir.
 *
 * Esta lacuna é a mais importante: hoje pede-se para aprovar um testemunho sem
 * o poder ler. A descrição, o email de quem submeteu e a imagem já vêm todos
 * nas props (o controller faz paginate() sobre o model completo) — falta só
 * renderizá-los, num modal de detalhe ou numa página própria. O email de aviso
 * também não leva o conteúdo, de propósito, por isso neste momento não há
 * sítio nenhum na aplicação onde se possa ler antes de aprovar.
 *
 * Falta ainda: o formulário de edição (PUT admin.testimonials.update, já
 * testado) e o filtro por estado, que o backend suporta em
 * ?status=received|accepted|refused e chega ao frontend na prop `filters`.
 *
 * ✅ Backend pronto e testado — ver
 *    tests/Feature/Admin/TestimonialModerationTest.php.
 */
export default function Index({ testimonials }) {
    const columns = [
        { key: 'title', label: 'Título' },
        { key: 'name', label: 'Autor' },
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
                        onClick={() =>
                            router.patch(route('admin.testimonials.approve', row.id))
                        }
                    >
                        Aprovar
                    </PrimaryButton>

                    <DangerButton
                        disabled={row.status === 'refused'}
                        onClick={() =>
                            router.patch(route('admin.testimonials.refuse', row.id))
                        }
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
                    Testemunhos
                </h2>
            }
        >
            <Head title="Testemunhos" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <FlashMessage />

                    <DataTable
                        columns={columns}
                        rows={testimonials.data}
                        emptyTitle="Ainda não há testemunhos submetidos"
                        emptyDescription="Os testemunhos enviados pelo formulário público aparecem aqui."
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
