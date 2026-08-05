import AppLogoIcon from '@/components/app-logo-icon';
import { type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Languages, ShieldCheck } from 'lucide-react';
import { type PropsWithChildren } from 'react';

export default function PublicLayout({ children }: PropsWithChildren) {
    const page = usePage<SharedData>();
    const { auth, locale, name, office, supportedLocales, translations } = page.props;
    const { common, home } = translations;
    const staffDestination = auth.user ? route('dashboard') : route('login');
    const staffLabel = auth.user ? common.dashboard : common.staff_portal;

    const updateLocale = (nextLocale: string) => {
        if (nextLocale !== locale) {
            router.post(route('locale.update'), { locale: nextLocale, redirect_to: page.url }, { preserveScroll: true });
        }
    };

    const publicNav = [
        { label: common.home, href: route('home') },
        { label: common.services, href: route('services.index') },
        { label: common.track, href: route('tracking.index') },
        { label: common.help, href: route('help') },
    ];

    return (
        <div className="public-shell">
            <a href="#main-content" className="skip-link">
                {common.skip_to_content}
            </a>

            <div className="bg-primary text-primary-foreground" data-print-hidden="true">
                <div className="mx-auto flex max-w-7xl items-start gap-3 px-4 py-3 text-sm leading-5 sm:px-6 lg:px-8">
                    <ShieldCheck className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <p>
                        <strong className="font-semibold">{home.disclaimer_label}:</strong> {home.disclaimer}
                    </p>
                </div>
            </div>

            <div className="min-h-screen bg-[#f6f8f5] text-slate-950">
                <header className="border-b border-slate-200 bg-white" data-print-hidden="true">
                    <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <div className="flex min-h-14 flex-wrap items-center justify-between gap-3">
                            <Link href={route('home')} className="focus-ring flex min-h-11 items-center gap-3 rounded-xl" aria-label={`${name} home`}>
                                <AppLogoIcon className="size-10 text-[#14594f]" />
                                <span>
                                    <span className="block text-base font-bold tracking-[-0.02em]">{name}</span>
                                    <span className="block text-xs font-medium text-slate-500">Barangay Haraya</span>
                                </span>
                            </Link>

                            <div className="flex flex-wrap items-center justify-end gap-2">
                                <div
                                    className="flex min-h-11 items-center rounded-xl border border-slate-200 bg-slate-50 p-1"
                                    aria-label={common.language_label}
                                    role="group"
                                >
                                    <Languages className="mx-2 hidden size-4 text-slate-500 sm:block" aria-hidden="true" />
                                    {Object.entries(supportedLocales).map(([code, label]) => (
                                        <button
                                            key={code}
                                            type="button"
                                            onClick={() => updateLocale(code)}
                                            aria-pressed={locale === code}
                                            className="focus-ring min-h-9 rounded-lg px-3 text-xs font-semibold text-slate-600 hover:bg-white aria-pressed:bg-white aria-pressed:text-[#14594f] aria-pressed:shadow-sm"
                                        >
                                            {label}
                                        </button>
                                    ))}
                                </div>
                                <Link
                                    href={staffDestination}
                                    className="focus-ring inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold hover:border-[#14594f] hover:text-[#14594f]"
                                >
                                    {staffLabel}
                                </Link>
                            </div>
                        </div>

                        <nav aria-label="Public navigation" className="flex flex-wrap gap-1 border-t border-slate-100 pt-2">
                            {publicNav.map((item) => {
                                const active = page.url === item.href || (item.href !== '/' && page.url.startsWith(item.href));

                                return (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        aria-current={active ? 'page' : undefined}
                                        className="focus-ring inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-[#14594f] aria-[current=page]:bg-[#e4f1ed] aria-[current=page]:text-[#14594f]"
                                    >
                                        {item.label}
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>
                </header>

                <main id="main-content">{children}</main>

                <footer className="border-t border-slate-200 bg-white" data-print-hidden="true">
                    <div className="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 text-sm text-slate-600 sm:px-6 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                        <div>
                            <div className="flex items-center gap-2 font-bold text-slate-950">
                                <AppLogoIcon className="size-7 text-[#14594f]" />
                                {name}
                            </div>
                            <p className="mt-3 font-semibold">{office.name}</p>
                            <p className="mt-1">{office.address}</p>
                            <p className="mt-1">
                                <a className="hover:text-[#14594f] hover:underline" href={`mailto:${office.email}`}>
                                    {office.email}
                                </a>
                                {' · '}
                                <a className="hover:text-[#14594f] hover:underline" href={`tel:${office.phone}`}>
                                    {office.phone}
                                </a>
                            </p>
                            <p className="mt-1 max-w-2xl text-xs leading-5">{home.disclaimer}</p>
                        </div>
                        <div>
                            <nav aria-label="Legal and help" className="flex flex-wrap gap-x-4 gap-y-2 font-semibold">
                                <Link className="focus-ring rounded hover:text-[#14594f]" href={route('privacy')}>
                                    {common.privacy}
                                </Link>
                                <Link className="focus-ring rounded hover:text-[#14594f]" href={route('accessibility')}>
                                    {common.accessibility}
                                </Link>
                                <Link className="focus-ring rounded hover:text-[#14594f]" href={route('help')}>
                                    {common.help}
                                </Link>
                            </nav>
                            <p className="mt-3 text-xs font-medium text-slate-500">{home.footer_phase}</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    );
}
