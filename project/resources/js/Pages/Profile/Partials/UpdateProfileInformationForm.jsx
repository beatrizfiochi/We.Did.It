import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Transition } from '@headlessui/react';
import { Link, useForm, usePage } from '@inertiajs/react';

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    className = '',
}) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
        });

    const submit = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">
                    Dados da conta
                </h2>

                <p className="mt-1 text-sm text-gray-600">
                    Atualiza o teu nome e o endereço de email da conta.
                </p>
            </header>

            {/* noValidate como no GeneralForm dos formulários públicos: sem
                isto o browser mostra a sua própria validação ("Please fill out
                this field"), em inglês, porque essa mensagem segue o idioma do
                browser e não o da página. Assim quem valida é o servidor, e a
                mensagem que aparece é a nossa, em português.

                O `required` fica nos inputs de propósito — deixa de mostrar a
                janela do browser, mas continua a ser anunciado por leitores de
                ecrã como campo obrigatório. */}
            <form onSubmit={submit} className="mt-6 space-y-6" noValidate>
                <div>
                    <InputLabel htmlFor="name" value="Nome" />

                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        isFocused
                        autoComplete="name"
                    />

                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        className="mt-1 block w-full"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoComplete="username"
                    />

                    <InputError className="mt-2" message={errors.email} />
                </div>

                {/* O User não implementa MustVerifyEmail — está comentado no
                    modelo — por isso este bloco não aparece hoje. Fica
                    traduzido para o dia em que a confirmação for ligada. */}
                {mustVerifyEmail && user.email_verified_at === null && (
                    <div>
                        <p className="mt-2 text-sm text-gray-800">
                            O teu endereço de email ainda não foi confirmado.
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Clica aqui para reenviar o email de confirmação.
                            </Link>
                        </p>

                        {status === 'verification-link-sent' && (
                            <div className="mt-2 text-sm font-medium text-green-600">
                                Enviámos uma nova ligação de confirmação para
                                o teu email.
                            </div>
                        )}
                    </div>
                )}

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Guardar</PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm text-gray-600">
                            Guardado.
                        </p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
