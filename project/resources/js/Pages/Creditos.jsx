import PublicLayout from '@/Layouts/PublicLayout';
import { Head } from '@inertiajs/react';
import '../../css/public-layout.css';

/**
 * A equipa que construiu a plataforma (Team B, projeto ACCEPT).
 *
 * A página é pública mas não entra na navegação: chega-se lá pelo rodapé, que
 * é onde se procura quem fez um site. Sem link nenhum a apontar-lhe, ninguém
 * a encontrava — e o objetivo é precisamente o contrário.
 */
const equipa = [
    {
        nome: 'Beatriz Fiochi',
        github: 'https://github.com/beatrizfiochi',
        linkedin: 'https://www.linkedin.com/in/beatriz-fiochi/',
    },
    {
        nome: 'Luana Matos',
        github: 'https://github.com/luamatoss',
        linkedin: 'https://www.linkedin.com/in/luana-val%C3%A9rio-2a4435377/',
    },
    {
        nome: 'Leida Dupret',
        github: 'https://github.com/ItzAdiel',
        linkedin: 'https://www.linkedin.com/in/leida-dupret/',
    },
    {
        nome: 'Jéssica Amorim',
        github: 'https://github.com/jessicakfamorim',
        linkedin: 'https://www.linkedin.com/in/jessica-f-amorim/',
    },
];

function IconeGithub() {
    return (
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 .5C5.7.5.5 5.7.5 12c0 5.1 3.3 9.4 7.9 10.9.6.1.8-.2.8-.6v-2c-3.2.7-3.9-1.5-3.9-1.5-.5-1.3-1.3-1.7-1.3-1.7-1.1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1 1.8 2.7 1.3 3.4 1 .1-.8.4-1.3.8-1.6-2.6-.3-5.3-1.3-5.3-5.8 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.3 1.2a11.4 11.4 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.6 1.6.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.5-2.7 5.5-5.3 5.8.4.4.8 1.1.8 2.2v3.3c0 .4.2.7.8.6 4.6-1.5 7.9-5.8 7.9-10.9C23.5 5.7 18.3.5 12 .5Z" />
        </svg>
    );
}

function IconeLinkedin() {
    return (
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6.9 8.8H3.2V21h3.7V8.8ZM5.1 3a2.1 2.1 0 1 0 0 4.2 2.1 2.1 0 0 0 0-4.2ZM21 14.2c0-3.4-1.8-5.7-4.8-5.7a4.1 4.1 0 0 0-3.7 2h-.1V8.8H8.9V21h3.7v-6.1c0-1.6.3-3.2 2.3-3.2s2 1.8 2 3.3v6h3.7v-6.8H21Z" />
        </svg>
    );
}

/**
 * O cartão de uma pessoa: nome e os dois perfis.
 *
 * Extraído como o NewsletterTemplate faz com o ImageFrame e o
 * TestimonialContent — não é por reutilização, que aqui não há, é para o
 * `map()` lá em baixo caber de uma vez debaixo dos olhos.
 */
function CartaoPessoa({ nome, github, linkedin }) {
    return (
        <li className="credits__card">
            <p className="credits__name">{nome}</p>

            <div className="credits__links">
                <a
                    href={github}
                    target="_blank"
                    rel="noreferrer"
                    aria-label={`GitHub de ${nome}`}
                >
                    <IconeGithub />
                </a>

                <a
                    href={linkedin}
                    target="_blank"
                    rel="noreferrer"
                    aria-label={`LinkedIn de ${nome}`}
                >
                    <IconeLinkedin />
                </a>
            </div>
        </li>
    );
}

export default function Creditos() {
    return (
        <PublicLayout>
            <Head title="Créditos" />

            <section className="credits">
                <p className="welcome-kicker">Projeto ACCEPT &middot; Team B</p>

                <h1>Quem construiu o We.Did.It</h1>

                <p className="credits__intro">
                    Projeto final do curso de Software Developer do CESAE Digital,
                    desenvolvido para o CESAE Digital entre julho e setembro de 2026.
                </p>

                <ul className="credits__list">
                    {equipa.map((pessoa) => (
                        <CartaoPessoa key={pessoa.github} {...pessoa} />
                    ))}
                </ul>
            </section>
        </PublicLayout>
    );
}
