import PublicLayout from '@/Layouts/PublicLayout';
import { Head } from '@inertiajs/react';

export default function Homepage() {
    return (
        <PublicLayout>
            <Head title="We.Did.It" />

            <section className="mx-auto max-w-7xl px-6 py-20">
                <h1 className="text-4xl font-bold">We.Did.It</h1>
                <p className="mt-4 text-lg">
                    Newsletter interna do CESAE Digital
                </p>
            </section>
        </PublicLayout>
    );
}
