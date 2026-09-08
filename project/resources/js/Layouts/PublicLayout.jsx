import { Link } from '@inertiajs/react';
import '../../css/public-layout.css';

export default function PublicLayout({ children }) {
    return (
        <div className="public-layout">
            <header className="public-header">
                <nav className="public-nav">
                    <Link href="/" className="public-brand">
                        <img
                            src="/images/cesae-digital-logo.svg"
                            alt="CESAE Digital"
                            className="public-brand__logo"
                        />
                    </Link>

                    <div className="public-links">
                        <Link href="/">
                            Home
                        </Link>

                        <Link href={route('news.create')}>
                            Adicionar notícia
                        </Link>

                        <Link href={route('testimonials.create')}>
                            Adicionar testemunho
                        </Link>

                        <Link href={route('login')}>
                            Login
                        </Link>
                    </div>
                </nav>
            </header>

            <main className="public-main">
                {children}
            </main>

            <footer className="public-footer">
                <div className="public-footer__content">
                    <div className="public-footer__brand">
                        <img
                            src="/images/logo-cesae-claro.svg"
                            alt="CESAE Digital"
                            className="public-footer__logo"
                        />
                    </div>

                    <div className="public-footer__contacts">
                        <span>
                            Telefone{' '}
                            <a href="tel:+351226195200">
                                226 195 200
                            </a>
                        </span>

                        <span>
                            Email{' '}
                            <a href="mailto:geral@cesae.pt">
                                geral@cesae.pt
                            </a>
                        </span>
                    </div>

                    <div className="public-footer__social">
                        <a
                            href="https://www.instagram.com/cesae.digital/"
                            target="_blank"
                            rel="noreferrer"
                            title="Instagram"
                            aria-label="Instagram"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5A4.5 4.5 0 1 1 12 16.5 4.5 4.5 0 0 1 12 7.5Zm0 2A2.5 2.5 0 1 0 12 14.5 2.5 2.5 0 0 0 12 9.5Zm5.25-2.75a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z" />
                            </svg>
                        </a>

                        <a
                            href="https://www.facebook.com/CesaeDigital"
                            target="_blank"
                            rel="noreferrer"
                            title="Facebook"
                            aria-label="Facebook"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M14 8h3V4h-3c-3.1 0-5 1.9-5 5v3H6v4h3v6h4v-6h3.2l.8-4h-4V9c0-.7.3-1 1-1Z" />
                            </svg>
                        </a>

                        <a
                            href="https://www.youtube.com/@cesaedigital"
                            target="_blank"
                            rel="noreferrer"
                            title="Youtube"
                            aria-label="Youtube"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M21.6 7.2a3 3 0 0 0-2.1-2.1C17.7 4.6 12 4.6 12 4.6s-5.7 0-7.5.5a3 3 0 0 0-2.1 2.1A31.6 31.6 0 0 0 2 12a31.6 31.6 0 0 0 .4 4.8 3 3 0 0 0 2.1 2.1c1.8.5 7.5.5 7.5.5s5.7 0 7.5-.5a3 3 0 0 0 2.1-2.1A31.6 31.6 0 0 0 22 12a31.6 31.6 0 0 0-.4-4.8ZM10 15.4V8.6l6 3.4-6 3.4Z" />
                            </svg>
                        </a>

                        <a
                            href="https://pt.linkedin.com/school/cesae-digital/"
                            target="_blank"
                            rel="noreferrer"
                            title="LinkedIn"
                            aria-label="LinkedIn"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6.9 8.8H3.2V21h3.7V8.8ZM5.1 3a2.1 2.1 0 1 0 0 4.2 2.1 2.1 0 0 0 0-4.2ZM21 14.2c0-3.4-1.8-5.7-4.8-5.7a4.1 4.1 0 0 0-3.7 2h-.1V8.8H8.9V21h3.7v-6.1c0-1.6.3-3.2 2.3-3.2s2 1.8 2 3.3v6h3.7v-6.8H21Z" />
                            </svg>
                        </a>
                    </div>
                </div>

                {/* quem construiu isto vive aqui e não no rodapé do documento
                    da newsletter: esse sai no PDF que o CESAE envia a toda a
                    comunidade, e é do cliente, não nosso */}
                <p className="public-footer__credits">
                    <Link href={route('creditos')}>Desenvolvido por</Link>{' '}
                    Beatriz Fiochi, Luana Matos, Leida Dupret e Jéssica Amorim
                </p>
            </footer>
        </div>
    );
}
