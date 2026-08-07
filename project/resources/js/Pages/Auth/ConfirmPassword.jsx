import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, useForm } from '@inertiajs/react';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <PublicLayout>
            <Head title="Confirmar palavra-passe" />

            <section className="mx-auto flex w-full max-w-md flex-col px-6 py-16">
                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-bold text-white">
                        Confirmar palavra-passe
                    </h1>

                    <p className="mt-2 text-sm text-slate-200">
                        Esta é uma área segura. Confirma a tua palavra-passe para continuares.
                    </p>
                </div>

                <div className="rounded-lg bg-white px-6 py-6 shadow-xl">
                    <form onSubmit={submit}>
                        <div>
                            <InputLabel htmlFor="password" value="Palavra-passe" />

                            <TextInput
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className="mt-1 block w-full"
                                isFocused={true}
                                onChange={(e) => setData('password', e.target.value)}
                            />

                            <InputError message={errors.password} className="mt-2" />
                        </div>

                        <div className="mt-4 flex items-center justify-end">
                            <PrimaryButton className="ms-4" disabled={processing}>
                                Confirmar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </section>
        </PublicLayout>
    );
}
