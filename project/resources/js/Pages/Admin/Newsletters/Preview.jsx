import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import NewsletterTemplate from '@/Pages/Admin/Newsletters/Partials/NewsletterTemplate';
import { Head, Link } from '@inertiajs/react';
import '../../../../css/print.css';

export default function Preview({ newsletter, publishedAt = null }) {
    // Uma newsletter publicada não tem PDF guardado: volta a gerar-se a partir
    // dos conteúdos atuais (SCRUM-122). O gestor tem de perceber que, se algo
    // for editado depois de publicada, o PDF sai diferente do original.
    const publishedNotice = publishedAt
        ? `Esta newsletter foi publicada a ${new Date(publishedAt).toLocaleDateString('pt-PT')}. `
          + 'O PDF é gerado a partir dos conteúdos atuais, não de uma cópia guardada.'
        : null;

    return (
        <AuthenticatedLayout header="Pré-visualização da newsletter">
            <Head title={`${newsletter.title} — edição ${newsletter.edition}`} />

            <div className="space-y-6">
                {/* print:hidden — nada aqui é conteúdo da newsletter, é só navegação
                    e ações do admin. A barra lateral e o cabeçalho do
                    AuthenticatedLayout também saem na impressão (SCRUM-128), e o
                    print.css trata das margens, cortes de página e cores. */}
                <div className="flex flex-col gap-3 print:hidden sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="text-sm text-gray-600">
                            {publishedNotice ??
                                'Confirma se os conteúdos selecionados estão corretos antes de finalizar.'}
                        </p>
                    </div>

                    <div className="flex flex-col items-start gap-1 sm:items-end">
                        <div className="flex items-center gap-4">
                            <Link
                                href={route('admin.newsletters.index')}
                                className="text-sm font-semibold text-indigo-600 hover:text-indigo-900"
                            >
                                Voltar às newsletters
                            </Link>

                            <PrimaryButton onClick={() => window.print()}>
                                Guardar em PDF
                            </PrimaryButton>
                        </div>

                        <p className="text-xs text-gray-500">
                            No destino escolhe «Guardar como PDF» e desliga «Cabeçalhos e rodapés».
                        </p>
                    </div>
                </div>

                <NewsletterTemplate newsletter={newsletter} />
            </div>
        </AuthenticatedLayout>
    );
}
