import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

/*
 * ⚠️ ECRÃ PROVISÓRIO — só leitura.
 *
 * O SCRUM-89 era o CRUD de categorias no backend (rotas, controller e
 * validação). Este ficheiro existe porque sem ele a rota admin/categorias
 * devolve 500: o app.blade.php procura a página no manifest do Vite.
 *
 * Já funciona: listar, com a contagem de notícias e testemunhos por categoria
 *              (vem do withCount no controller) e as mensagens de sucesso.
 * Falta:       criar, editar e remover na interface.
 *
 * ✅ O backend está pronto e testado — ver tests/Feature/Admin/CategoryTest.php.
 *    Quem fizer o ecrã só precisa de ligar os botões a estas rotas:
 *      POST   admin.categories.store    { name }
 *      PUT    admin.categories.update   { name }
 *      DELETE admin.categories.destroy
 *
 * Nota: remover uma categoria em uso devolve um erro de validação na chave
 * 'category' — a foreign key é onDelete('restrict'). O botão de remover deve
 * ficar desativado quando news_count ou testimonials_count forem maiores que 0.
 */
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
