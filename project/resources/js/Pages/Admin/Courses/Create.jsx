import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CourseForm from '@/Pages/Admin/Courses/Partials/CourseForm';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        url: '',
        imageUrl: '',
        location: '',
        schedule: '',
        start_date: '',
        price: '',
        status: 'received',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.courses.store'));
    };

    return (
        <AuthenticatedLayout header="Nova oferta formativa">
            <Head title="Nova oferta formativa" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">
                        Nova oferta formativa
                    </h1>

                    <p className="mt-1 text-sm text-gray-600">
                        Preenche os dados para criar uma nova oferta formativa.
                    </p>
                </div>

                <div className="rounded-lg bg-white p-6 shadow-sm">
                    <form onSubmit={submit} className="space-y-6">
                        <CourseForm
                            data={data}
                            setData={setData}
                            errors={errors}
                        />

                        <div className="flex items-center justify-end gap-3">
                            <Link
                                href={route('admin.courses.index')}
                                className="rounded-md px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                            >
                                Cancelar
                            </Link>

                            <PrimaryButton disabled={processing}>
                                Guardar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
