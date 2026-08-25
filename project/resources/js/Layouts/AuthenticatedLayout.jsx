import Dropdown from '@/Components/Dropdown';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    const adminLinks = [
        {
            label: 'Painel Administrativo',
            href: route('dashboard'),
            active: route().current('dashboard'),
        },
        {
            label: 'Notícias',
            href: route('admin.news.index'),
            active: route().current('admin.news.*'),
        },
        {
            label: 'Testemunhos',
            href: route('admin.testimonials.index'),
            active: route().current('admin.testimonials.*'),
        },
        {
            label: 'Categorias',
            href: route('admin.categories.index'),
            active: route().current('admin.categories.*'),
        },
        {
            label: 'Formações',
            href: route('admin.courses.index'),
            active: route().current('admin.courses.*'),
        },
        {
            label: 'Agenda',
            href: route('admin.calendar.index'),
            active: route().current('admin.calendar.*'),
        },
        {
            label: 'Newsletters',
            href: route('admin.newsletters.index'),
            active: route().current('admin.newsletters.*'),
        },
        { label: 'Administradores', disabled: true },
    ];

    return (

        <div className="min-h-screen bg-slate-100">
            <div className="flex min-h-screen">
                <aside className="hidden w-72 shrink-0 border-r border-gray-200 bg-white lg:block">
                    <div className="flex h-16 items-center border-b border-gray-200 px-6">
                        <Link href={route('dashboard')} className="text-lg font-bold text-gray-900">
                            We.Did.It
                        </Link>
                    </div>

                    {adminLinks.map((item) => (
                        item.disabled ? (
                            <span
                                key={item.label}
                                className="block cursor-not-allowed rounded-md px-4 py-3 text-sm font-medium text-gray-400"
                            >
                                {item.label}
                                <span className="ms-2 text-xs text-gray-400">
                                    Em breve
                                </span>
                            </span>
                        ) : (
                            <Link
                                key={item.label}
                                href={item.href}
                                className={
                                    'block rounded-md px-4 py-3 text-sm font-medium transition ' +
                                    (item.active
                                        ? 'bg-indigo-50 text-indigo-700'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900')
                                }
                            >
                                {item.label}
                            </Link>
                        )
                    ))}
                </aside>

                <div className="flex min-w-0 flex-1 flex-col">
                    <header className="border-b border-gray-200 bg-white">
                        <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                            <div className="flex items-center gap-4">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowingNavigationDropdown(
                                            (previousState) => !previousState,
                                        )
                                    }
                                    className="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 lg:hidden"
                                >
                                    <span className="sr-only">Abrir menu</span>

                                    <svg
                                        className="h-6 w-6"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d={
                                                showingNavigationDropdown
                                                    ? 'M6 18L18 6M6 6l12 12'
                                                    : 'M4 6h16M4 12h16M4 18h16'
                                            }
                                        />
                                    </svg>
                                </button>

                                <div>
                                    <p className="text-sm font-medium text-gray-500">
                                        Área de administração
                                    </p>

                                    <div className="text-lg font-semibold text-gray-900">
                                        {header ?? 'Painel Administrativo'}
                                    </div>
                                </div>
                            </div>

                            <div className="relative">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 focus:outline-none"
                                            >
                                                <span className="text-left">
                                                    <span className="block text-sm font-semibold text-gray-900">
                                                        {user.name}
                                                    </span>
                                                    <span className="block text-xs text-gray-500">
                                                        {user.email}
                                                    </span>
                                                </span>

                                                <svg
                                                    className="ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>
                                            Perfil
                                        </Dropdown.Link>

                                        <Dropdown.Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                        >
                                            Sair
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div
                            className={
                                (showingNavigationDropdown ? 'block' : 'hidden') +
                                ' border-t border-gray-200 bg-white lg:hidden'
                            }
                        >
                            {adminLinks.map((item) => (
                                item.disabled ? (
                                    <span
                                        key={item.label}
                                        className="block cursor-not-allowed rounded-md px-4 py-3 text-sm font-medium text-gray-400"
                                    >
                                        {item.label}
                                        <span className="ms-2 text-xs text-gray-400">
                                            Em breve
                                        </span>
                                    </span>
                                ) : (
                                    <Link
                                        key={item.label}
                                        href={item.href}
                                        className={
                                            'block rounded-md px-4 py-3 text-sm font-medium transition ' +
                                            (item.active
                                                ? 'bg-indigo-50 text-indigo-700'
                                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900')
                                        }
                                    >
                                        {item.label}
                                    </Link>
                                )
                            ))}
                        </div>
                    </header>

                    <main className="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                        <div className="mx-auto max-w-7xl">
                            {children}
                        </div>
                    </main>
                </div>
            </div>
        </div>
    );
}
