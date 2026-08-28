import { Head, Link, usePage } from '@inertiajs/react';

const MESSAGES = {
    403: {
        title: 'Sem permissão',
        description: 'Não tens autorização para aceder a esta página.',
    },
    404: {
        title: 'Página não encontrada',
        description: 'A página que procuras não existe ou foi movida.',
    },
    419: {
        title: 'Sessão expirada',
        description: 'A tua sessão expirou. Volta a entrar e tenta outra vez.',
    },
    500: {
        title: 'Erro interno',
        description: 'Algo correu mal do nosso lado. Tenta novamente daqui a pouco.',
    },
    503: {
        title: 'Em manutenção',
        description: 'A aplicação está temporariamente indisponível. Volta a tentar em breve.',
    },
};

export default function Error({ status }) {
    const { auth } = usePage().props;
    const { title, description } = MESSAGES[status] ?? {
        title: 'Ocorreu um erro',
        description: 'Algo não correu como esperado.',
    };

    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-slate-100 px-6 text-center">
            <Head title={title} />

            <img src="/images/cesae-digital-logo.svg" alt="CESAE Digital" className="mb-6 h-10" />

            <p className="text-sm font-semibold text-indigo-600">Erro {status}</p>
            <h1 className="mt-2 text-3xl font-bold tracking-tight text-gray-900">{title}</h1>
            <p className="mt-2 max-w-md text-sm text-gray-600">{description}</p>

            <Link
                href={auth?.user ? route('dashboard') : '/'}
                className="mt-6 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {auth?.user ? 'Voltar ao painel' : 'Voltar ao início'}
            </Link>
        </div>
    );
}
