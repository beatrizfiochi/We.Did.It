import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
<<<<<<< HEAD:project/resources/js/Pages/Auth/Register.jsx
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, useForm } from '@inertiajs/react';
=======
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
>>>>>>> origin/main:project/resources/js/Pages/Admin/CreateUser.jsx

export default function CreateUser() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.users.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
<<<<<<< HEAD:project/resources/js/Pages/Auth/Register.jsx
        <PublicLayout>
            <Head title="Criar conta" />

            <section className="mx-auto flex w-full max-w-md flex-col px-6 py-16">
                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-bold text-white">
                        Criar conta
                    </h1>
=======
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Criar administrador
                </h2>
            }
        >
            <Head title="Criar administrador" />

            <form onSubmit={submit} className="mx-auto max-w-xl p-6">
                <div>
                    <InputLabel htmlFor="name" value="Name" />
>>>>>>> origin/main:project/resources/js/Pages/Admin/CreateUser.jsx

                    <p className="mt-2 text-sm text-slate-200">
                        Preenche os dados para criares o teu acesso ao We.Did.It.
                    </p>
                </div>

                <div className="rounded-lg bg-white px-6 py-6 shadow-xl">
                    <form onSubmit={submit}>
                        <div>
                            <InputLabel htmlFor="name" value="Nome" />

                            <TextInput
                                id="name"
                                name="name"
                                value={data.name}
                                className="mt-1 block w-full"
                                autoComplete="name"
                                isFocused={true}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                            />

                            <InputError message={errors.name} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <InputLabel htmlFor="email" value="Email" />

                            <TextInput
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className="mt-1 block w-full"
                                autoComplete="username"
                                onChange={(e) => setData('email', e.target.value)}
                                required
                            />

                            <InputError message={errors.email} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <InputLabel htmlFor="password" value="Palavra-passe" />

                            <TextInput
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className="mt-1 block w-full"
                                autoComplete="new-password"
                                onChange={(e) => setData('password', e.target.value)}
                                required
                            />

                            <InputError message={errors.password} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <InputLabel
                                htmlFor="password_confirmation"
                                value="Confirmar palavra-passe"
                            />

                            <TextInput
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                className="mt-1 block w-full"
                                autoComplete="new-password"
                                onChange={(e) =>
                                    setData('password_confirmation', e.target.value)
                                }
                                required
                            />

                            <InputError
                                message={errors.password_confirmation}
                                className="mt-2"
                            />
                        </div>

                        <div className="mt-4 flex items-center justify-end">
                            <Link
                                href={route('login')}
                                className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Já tens conta?
                            </Link>

                            <PrimaryButton className="ms-4" disabled={processing}>
                                Criar conta
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
<<<<<<< HEAD:project/resources/js/Pages/Auth/Register.jsx
            </section>
        </PublicLayout>
=======

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm Password"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="mt-4 flex items-center justify-end">
                    <PrimaryButton className="ms-4" disabled={processing}>
                        Criar administrador
                    </PrimaryButton>
                </div>
            </form>
        </AuthenticatedLayout>
>>>>>>> origin/main:project/resources/js/Pages/Admin/CreateUser.jsx
    );
}
