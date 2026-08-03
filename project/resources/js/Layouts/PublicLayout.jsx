import { Link } from '@inertiajs/react';
import '../../css/public-layout.css';

export default function PublicLayout({ children }) {
    return (
        <div className="public-layout">
            <header className="public-header">
                <nav className="public-nav">
                    <Link href="/" className="public-brand">
                        We.Did.It
                    </Link>

                    <div className="public-links">
                        <Link href="/noticias/create">
                            Enviar notícia
                        </Link>

                        <Link href="/testemunhos/create">
                            Enviar testemunho
                        </Link>

                        <Link href="/login">
                            Login
                        </Link>
                    </div>
                </nav>
            </header>

            <main className="public-main">
                {children}
            </main>

            <footer className="public-footer">
                CESAE Digital · We.Did.It
            </footer>
        </div>
    );
}
