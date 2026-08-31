import DataTable from "@/Components/DataTable";
import FlashMessage from "@/Components/FlashMessage";
import Modal from "@/Components/Modal";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import PrimaryButton from "@/Components/PrimaryButton";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import SecondaryButton from '@/Components/SecondaryButton';

// tabela com as noticias todas e botao para ver --> modal
export default function Index({ users }) {

    const actionClass =
        'inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-indigo-50 hover:text-indigo-700';

    // variable that holds the labels to be rendered in <DataTable/> according to keys from the users table
    const columns = [
        { key: 'name', label: 'Nome', render: (row) => row.name },
        { key: 'email', label: 'Email', render: (row) => row.email },
    ];

    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({ name: '', email: '', password: '', password_confirmed: '' });


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


    // for the VIEW Modal
    const [selectedUsers, setSelectedUsers] = useState(null)
    const [showViewModal, setShowViewModal] = useState(false)

    const handleViewUsers = (row) => {
        setSelectedUsers(row);
        setShowViewModal(true); // shows the row selected inside the modal
    };

    const closeViewModal = () => {
        setShowViewModal(false);
        setSelectedUsers(null); // turns selected view as null, closes modal
    }



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

            <div className="flex justify-center mt-5">

                <section className="w-full max-w-2xl">


                    <DataTable
                        columns={columns}
                        rows={users} // shows according to what is set in the filter areas
                        emptyTitle="Ainda não há administradores"
                        emptyDescription="Cria o primeiro administrador no botão acima."
                    />
                </section>

            </div>


            {/* Modal for viewing each individual users */}
            <Modal show={showViewModal} onClose={closeViewModal} maxWidth="lg">
                {selectedUsers && (
                    <div className="p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">
                            {selectedUsers.name}
                        </h2>
                        <p className="text-md text-gray-500 ">
                            <strong>Nome:</strong> {selectedUsers.name}
                        </p>
                        <p className="text-md text-gray-500 mb-4">
                            <strong>Email:</strong> {selectedUsers.email}
                        </p>


                        <div className="mt-6 flex flex-wrap justify-end gap-3">

                            <button
                                type="button"
                                className="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50"
                                onClick={closeViewModal}
                            >
                                Fechar
                            </button>
                        </div>
                    </div>
                )}
            </Modal>

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




        </AuthenticatedLayout>
    )
}
