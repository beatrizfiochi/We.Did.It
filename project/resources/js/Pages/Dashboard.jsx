import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Bem-vindo(a) ao painel administrativo
                </h2>
            }
        >
            <Head title="Painel Administrativo" />

            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div className="p-6 text-gray-900">
                    Aqui vais poder gerir notícias, testemunhos, categorias, formações, agenda, newsletters e administradores.
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
