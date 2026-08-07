import PrimaryButton from '@/Components/PrimaryButton';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <PublicLayout>
            <Head title="Verificar email" />

            <section className="mx-auto flex w-full max-w-md flex-col px-6 py-16">
                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-bold text-white">
                        Verificar email
                    </h1>

                    <p className="mt-2 text-sm text-slate-200">
                        Antes de continuares, confirma o teu endereço de email através da ligação que te enviámos.
                    </p>
                </div>

                <div className="rounded-lg bg-white px-6 py-6 shadow-xl">
                    {status === 'verification-link-sent' && (
                        <div className="mb-4 text-sm font-medium text-green-600">
                            Foi enviada uma nova ligação de verificação para o email indicado no registo.
                        </div>
                    )}

                    <form onSubmit={submit}>
                        <div className="mt-4 flex items-center justify-between">
                            <PrimaryButton disabled={processing}>
                                Reenviar email de verificação
                            </PrimaryButton>

                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Sair
                            </Link>
                        </div>
                    </form>
                </div>
            </section>
        </PublicLayout>
    );
}
