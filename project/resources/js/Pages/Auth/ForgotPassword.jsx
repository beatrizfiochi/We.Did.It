import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, useForm } from '@inertiajs/react';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <PublicLayout>
            <Head title="Recuperar palavra-passe" />

            <section className="mx-auto flex w-full max-w-md flex-col px-6 py-16">
                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-bold text-white">
                        Não te lembras da tua palavra-passe?
                    </h1>

                    <p className="mt-2 text-sm text-slate-200">
                        Introduz abaixo o email associado à tua conta We.Did.It.
                    </p>
                </div>

                <div className="rounded-lg bg-white px-6 py-6 shadow-xl">
                    {status && (
                        <div className="mb-4 text-sm font-medium text-green-600">
                            {status}
                        </div>
                    )}

                    <form onSubmit={submit}>
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block w-full"
                            isFocused={true}
                            onChange={(e) => setData('email', e.target.value)}
                        />

                        <InputError message={errors.email} className="mt-2" />

                        <div className="mt-4 flex items-center justify-end">
                            <PrimaryButton className="ms-4" disabled={processing}>
                                Recuperar Palavra-Passe
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </section>
        </PublicLayout>
    );
}
