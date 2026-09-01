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

// tabela comos utilizadores todos e botao para criar --> modal
export default function Index({ users }) {


    // variable that holds the labels to be rendered in <DataTable/> according to keys from the users table
    const columns = [
        { key: 'name', label: 'Nome', render: (row) => row.name },
        { key: 'email', label: 'Email', render: (row) => row.email },
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
