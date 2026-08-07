import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

export default function Welcome() {
    return (
        <PublicLayout>
            <Head title="Início" />

            <section className="welcome-hero">
                <div className="welcome-hero__content">
                    <p className="welcome-kicker">Newsletter interna</p>

                    <h1>WE DID IT</h1>

                    <p className="welcome-hero__text">
                        Um instrumento de comunicação para registar e divulgar o que
                        de bom fazemos. Uma visão dos presentes projetos e uma
                        perspetiva dos futuros.
                    </p>

                    <div className="welcome-actions">
                        <Link href="/noticias/create" className="welcome-action">
                            Contribua com a sua notícia
                        </Link>

                        <Link href="/testemunhos/create" className="welcome-action">
                            Contribua com o seu testemunho
                        </Link>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
