import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, useForm } from '@inertiajs/react';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.store'), {
            onSuccess: () => reset('password', 'password_confirmation'),
            onError: () => reset('password_confirmation') // se nao coincidir apaga o input da confirmacao da password
        });
    };

    return (
        <PublicLayout>
            <Head title="Repor palavra-passe" />

            <section className="mx-auto flex w-full max-w-md flex-col px-6 py-16">
                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-bold text-white">
                        Repor palavra-passe
                    </h1>

                    <p className="mt-2 text-sm text-slate-200">
                        Define uma nova palavra-passe para acederes à tua conta.
                    </p>
                </div>

                <div className="rounded-lg bg-white px-6 py-6 shadow-xl">
                    <form onSubmit={submit}>
                        <div>
                            <InputLabel htmlFor="email" value="Email" />

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                readOnly
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                              text-gray-500 focus:border-gray-300 focus:ring-0"
                                autoComplete="username"
                            />

                            <InputError message={errors.email} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <InputLabel htmlFor="password" value="Nova palavra-passe" />

                            <TextInput
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className="mt-1 block w-full"
                                autoComplete="new-password"
                                isFocused={true}
                                onChange={(e) => setData('password', e.target.value)}
                            />

                            <InputError message={errors.password} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <InputLabel
                                htmlFor="password_confirmation"
                                value="Confirmar palavra-passe"
                            />

                            <TextInput
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                className="mt-1 block w-full"
                                autoComplete="new-password"
                                onChange={(e) =>
                                    setData('password_confirmation', e.target.value)
                                }
                            />

                            <InputError
                                message={errors.password_confirmation}
                                className="mt-2"
                            />
                        </div>

                        <div className="mt-4 flex items-center justify-end">
                            <PrimaryButton className="ms-4" disabled={processing}>
                                Repor palavra-passe
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </section>
        </PublicLayout>
    );
}
