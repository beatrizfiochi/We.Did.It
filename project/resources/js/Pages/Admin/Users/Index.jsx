import DataTable from "@/Components/DataTable";
import FlashMessage from "@/Components/FlashMessage";
import Modal from "@/Components/Modal";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import { Head, useForm, usePage } from "@inertiajs/react";
import PrimaryButton from "@/Components/PrimaryButton";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import SecondaryButton from '@/Components/SecondaryButton';

// tabela comos utilizadores todos e botao para criar --> modal
export default function Index({ users }) {

    // o servidor recusa com 403 quem tente mexer no próprio estado
    // (RegisteredUserController::toggleStatus). Sem isto o botão aparecia na
    // própria linha e o clique atirava o gestor para uma página de erro.
    const { auth } = usePage().props;

    const actionClass =
        'inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-indigo-50 hover:text-indigo-700 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:hover:bg-gray-100 disabled:hover:text-gray-400';
    const dangerActionClass =
        'inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-100 hover:text-red-700 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:hover:bg-gray-100 disabled:hover:text-gray-400';

    // variable that holds the labels to be rendered in <DataTable/> according to keys from the users table
    const columns = [
        { key: 'name', label: 'Nome', render: (row) => row.name },
        { key: 'email', label: 'Email', render: (row) => row.email },
        {
            key: 'status',
            label: 'Estado',
            render: (row) => (
                <span className={row.status ? 'font-semibold text-green-700' : 'text-gray-500'}>
                    {row.status ? 'Ativo' : 'Desativado'}
                </span>
            ),
        },
        // a chave tem de ser 'actions': é o que faz o DataTable pôr os botões
        // sem etiqueta ao lado no telemóvel (ver o comentário no componente)
        {
            key: 'actions',
            label: 'Ações',
            render: (row) => {
                const propriaConta = row.id === auth.user.id;

                return (
                    <button
                        type="button"
                        className={row.status ? dangerActionClass : actionClass}
                        disabled={propriaConta}
                        title={propriaConta ? 'Não podes mudar o estado da tua própria conta.' : undefined}
                        onClick={() => setChangingStatus(row)}
                    >
                        {row.status ? 'Desativar' : 'Ativar'}
                    </button>
                );
            },
        },
    ];

    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({ name: '', email: '', password: '', password_confirmation: '' });


    const [showForm, setShowForm] = useState(false)

    // Abre o modal de criação de utilizador
    const openCreate = () => {
        reset();
        clearErrors();
        setShowForm(true);
    };
    // Fecha o modal de criação de utilizador
    const closeForm = () => {
        setShowForm(false);
        reset();
        clearErrors();
    };

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: closeForm,
        });
    };

    // Desativar tira o acesso a uma pessoa, por isso passa por confirmação —
    // mesma decisão do "Publicar" das newsletters (SCRUM-146).
    //
    // useForm próprio e não o de cima: partilhar o `processing` deixava o
    // botão de criar utilizador desativado enquanto se desativa alguém.
    const [changingStatus, setChangingStatus] = useState(null);

    const statusForm = useForm({});

    const confirmStatusChange = () => {
        statusForm.patch(route('admin.users.status', changingStatus.id), {
            preserveScroll: true,
            onSuccess: () => setChangingStatus(null),
        });
    };

    return (

        <AuthenticatedLayout header="Administradores">

            <Head title="Administradores" />

            <FlashMessage />

            {/* Botao para criar novo admin */}
            <div className="flex justify-end">
                <PrimaryButton onClick={openCreate}>
                    Novo utilizador
                </PrimaryButton>
            </div>

            <div className="mt-5">
                <DataTable
                    columns={columns}
                    rows={users} // shows according to what is set in the filter areas
                    emptyTitle="Ainda não há administradores"
                    emptyDescription="Cria o primeiro administrador no botão acima."
                />
            </div>

            <Modal
                show={showForm}
                onClose={closeForm}
                maxWidth="md"
            >

                <form onSubmit={submit} className="space-y-4 p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Novo utilizador
                    </h2>

                    <div>
                        <InputLabel htmlFor="name" value="Nome" />
                        <TextInput
                            id="name"
                            value={data.name}
                            className="mt-1 block w-full"
                            isFocused
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            value={data.email}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError message={errors.email} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            value={data.password}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        <InputError message={errors.password} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="password_confirmation" value="Confirmação do Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                    </div>


                    <div className="flex flex-wrap justify-end gap-3">
                        <SecondaryButton type="button" onClick={closeForm}>
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton disabled={processing}>
                            Criar
                        </PrimaryButton>
                    </div>

                </form>

            </Modal>

            {/* Confirmação de ativar/desativar. O ?. é preciso porque ao fechar
                o modal o estado volta a null e o React ainda renderiza uma vez
                antes de o esconder. */}
            <Modal
                show={changingStatus !== null}
                onClose={() => setChangingStatus(null)}
                maxWidth="md"
            >
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        {changingStatus?.status ? 'Desativar administrador' : 'Ativar administrador'}
                    </h2>

                    <p className="mt-3 text-sm text-gray-600">
                        {changingStatus?.status
                            ? `${changingStatus?.name} deixa de conseguir entrar e de receber os avisos de novas submissões. O histórico de atividade mantém-se.`
                            : `${changingStatus?.name} volta a poder entrar na área de administração.`}
                    </p>

                    <div className="mt-6 flex flex-wrap justify-end gap-3">
                        <SecondaryButton type="button" onClick={() => setChangingStatus(null)}>
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton onClick={confirmStatusChange} disabled={statusForm.processing}>
                            {changingStatus?.status ? 'Desativar' : 'Ativar'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

        </AuthenticatedLayout>
    )
}
