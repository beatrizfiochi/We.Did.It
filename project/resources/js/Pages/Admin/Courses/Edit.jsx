import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CourseForm from '@/Pages/Admin/Courses/Partials/CourseForm';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ course, newsletters = [] }) {
    const { data, setData, put, processing, errors } = useForm({
        title: course.title ?? '',
        url: course.url ?? '',
        imageUrl: course.imageUrl ?? '',
        location: course.location ?? '',
        schedule: course.schedule ?? '',
        start_date: course.start_date ?? '',
        price: course.price ?? '',
        status: course.status ?? 'received',
        newsletter_ids: course.newsletter_ids ?? [],
    });

    const submit = (e) => {
        e.preventDefault();

        put(route('admin.courses.update', course.id));
    };

    return (
        <AuthenticatedLayout header="Editar oferta formativa">
            <Head title="Editar oferta formativa" />

            <div className="space-y-6">
                <div>

                    <p className="mt-1 text-sm text-gray-600">
                        Atualiza os dados da oferta formativa.
                    </p>
                </div>

                <div className="rounded-lg bg-white p-4 shadow-sm sm:p-6">
                    <form onSubmit={submit} className="space-y-6">
                        <CourseForm
                            data={data}
                            setData={setData}
                            errors={errors}
                            newsletters={newsletters}
                        />

                        <div className="flex flex-wrap items-center justify-end gap-3">
                            <Link
                                href={route('admin.courses.index')}
                                className="rounded-md px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                            >
                                Cancelar
                            </Link>

                            <PrimaryButton disabled={processing}>
                                Atualizar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
