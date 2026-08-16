import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Courses({ newsletter, courses, course_ids }) {
    const { data, setData, put, processing, recentlySuccessful } = useForm({
        course_ids,
    });

    function toggleCourse(id) {
        setData(
            'course_ids',
            data.course_ids.includes(id)
                ? data.course_ids.filter((courseId) => courseId !== id)
                : [...data.course_ids, id],
        );
    }

    function submit(e) {
        e.preventDefault();
        put(route('admin.newsletters.courses.update', newsletter.id));
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ofertas formativas — {newsletter.title} (edição {newsletter.edition})
                </h2>
            }
        >
            <Head title="Selecionar ofertas formativas" />

            <div className="mx-auto max-w-2xl p-6">
                <form onSubmit={submit} className="rounded-lg bg-white p-6 shadow">
                    {courses.length === 0 && (
                        <p className="text-sm text-gray-500">Não existem ofertas formativas disponíveis.</p>
                    )}

                    <ul className="divide-y divide-gray-200">
                        {courses.map((course) => (
                            <li key={course.id} className="flex items-center justify-between py-3">
                                <label htmlFor={`course-${course.id}`} className="flex flex-1 items-center gap-3">
                                    <input
                                        id={`course-${course.id}`}
                                        type="checkbox"
                                        checked={data.course_ids.includes(course.id)}
                                        onChange={() => toggleCourse(course.id)}
                                        className="rounded border-gray-300"
                                    />
                                    <span>
                                        <span className="block font-medium text-gray-900">{course.title}</span>
                                        <span className="block text-sm text-gray-500">{course.start_date}</span>
                                    </span>
                                </label>
                            </li>
                        ))}
                    </ul>

                    <div className="mt-6 flex items-center gap-4">
                        <PrimaryButton disabled={processing}>Guardar seleção</PrimaryButton>

                        {recentlySuccessful && <p className="text-sm text-gray-600">Guardado.</p>}
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
