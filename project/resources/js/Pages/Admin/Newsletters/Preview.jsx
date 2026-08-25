import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import NewsletterTemplate from '@/Pages/Admin/Newsletters/Partials/NewsletterTemplate';
import { Head, Link } from '@inertiajs/react';

export default function Preview({ newsletter }) {
    return (
        <AuthenticatedLayout header="Pré-visualização da newsletter">
            <Head title="Pré-visualização da newsletter" />

            <div className="space-y-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="text-sm text-gray-600">
                            Confirma se os conteúdos selecionados estão corretos antes de finalizar.
                        </p>
                    </div>

                    <Link
                        href={route('admin.newsletters.index')}
                        className="text-sm font-semibold text-indigo-600 hover:text-indigo-900"
                    >
                        Voltar às newsletters
                    </Link>
                </div>

                <NewsletterTemplate newsletter={newsletter} />
            </div>
        </AuthenticatedLayout>
    );
}
