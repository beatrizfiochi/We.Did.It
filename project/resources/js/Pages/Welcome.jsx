import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

export default function Welcome() {
    return (
        <PublicLayout>
            <Head title="We.Did.It" />

            <section className="welcome-hero">
                <div className="welcome-intro">
                    <p className="welcome-kicker">Newsletter interna</p>

                    <h1>We.Did.It</h1>

                    <p>
                        Um espaço para partilhar notícias, conquistas,
                        testemunhos e momentos da comunidade CESAE Digital.
                    </p>
                </div>

                <div className="welcome-cards">
                    <Link href="/noticias/create" className="welcome-card">
                        <span className="welcome-card__label">
                            Enviar notícia
                        </span>

                        <strong>
                            Partilha uma novidade com a equipa
                        </strong>

                        <p>
                            Usa este formulário para sugerir notícias,
                            eventos, formações ou atualizações relevantes.
                        </p>
                    </Link>

                    <Link href="/testemunhos/create" className="welcome-card">
                        <span className="welcome-card__label">
                            Enviar testemunho
                        </span>

                        <strong>
                            Conta uma experiência ou conquista
                        </strong>

                        <p>
                            Envia um testemunho para destacar histórias,
                            percursos e resultados da comunidade.
                        </p>
                    </Link>
                </div>
            </section>
        </PublicLayout>
    );
}
