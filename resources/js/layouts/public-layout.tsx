import AppLogoIcon from '@/components/app-logo-icon';
import AppearanceToggleDropdown from '@/components/appearance-dropdown';
import { PageAnnouncer } from '@/components/page-announcer';
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
            <PageAnnouncer />
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

            <div className="bg-background text-foreground min-h-screen">
                <header className="border-border bg-card border-b" data-print-hidden="true">
                    <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <div className="flex min-h-14 flex-wrap items-center justify-between gap-3">
                            <Link href={route('home')} className="focus-ring flex min-h-11 items-center gap-3 rounded-xl" aria-label={`${name} home`}>
                                <AppLogoIcon className="text-primary size-10" />
                                <span>
                                    <span className="block text-base font-bold tracking-[-0.02em]">{name}</span>
                                    <span className="text-muted-foreground block text-xs font-medium">Barangay Haraya</span>
                                </span>
                            </Link>

                            <div className="flex flex-wrap items-center justify-end gap-2">
                                <div
                                    className="border-border bg-muted flex min-h-11 items-center rounded-xl border p-1"
                                    aria-label={common.language_label}
                                    role="group"
                                >
                                    <Languages className="text-muted-foreground mx-2 hidden size-4 sm:block" aria-hidden="true" />
                                    {Object.entries(supportedLocales).map(([code, label]) => (
                                        <button
                                            key={code}
                                            type="button"
                                            onClick={() => updateLocale(code)}
                                            aria-pressed={locale === code}
                                            className="focus-ring text-muted-foreground hover:bg-card hover:text-foreground aria-pressed:bg-card aria-pressed:text-primary min-h-9 rounded-lg px-3 text-xs font-semibold aria-pressed:shadow-sm"
                                        >
                                            {label}
                                        </button>
                                    ))}
                                </div>
                                <AppearanceToggleDropdown />
                                <Link
                                    href={staffDestination}
                                    className="focus-ring border-border bg-card hover:border-primary hover:text-primary inline-flex min-h-11 items-center justify-center rounded-xl border px-4 text-sm font-semibold"
                                >
                                    {staffLabel}
                                </Link>
                            </div>
                        </div>

                        <nav aria-label="Public navigation" className="border-border/60 flex flex-wrap gap-1 border-t pt-2">
                            {publicNav.map((item) => {
                                const active = page.url === item.href || (item.href !== '/' && page.url.startsWith(item.href));

                                return (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        aria-current={active ? 'page' : undefined}
                                        className="focus-ring text-muted-foreground hover:bg-muted hover:text-primary aria-[current=page]:bg-secondary aria-[current=page]:text-primary inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-semibold"
                                    >
                                        {item.label}
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>
                </header>

                <main id="main-content" tabIndex={-1}>
                    {children}
                </main>

                <footer className="border-border bg-card border-t" data-print-hidden="true">
                    <div className="text-muted-foreground mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 text-sm sm:px-6 lg:flex-row lg:items-end lg:justify-between lg:px-8">
                        <div>
                            <div className="text-foreground flex items-center gap-2 font-bold">
                                <AppLogoIcon className="text-primary size-7" />
                                {name}
                            </div>
                            <p className="mt-3 font-semibold">{office.name}</p>
                            <p className="mt-1">{office.address}</p>
                            <p className="mt-1">
                                <a className="hover:text-primary hover:underline" href={`mailto:${office.email}`}>
                                    {office.email}
                                </a>
                                {' · '}
                                <a className="hover:text-primary hover:underline" href={`tel:${office.phone}`}>
                                    {office.phone}
                                </a>
                            </p>
                            <p className="mt-1 max-w-2xl text-xs leading-5">{home.disclaimer}</p>
                        </div>
                        <div>
                            <nav aria-label="Legal and help" className="flex flex-wrap gap-x-4 gap-y-2 font-semibold">
                                <Link className="focus-ring hover:text-primary rounded" href={route('privacy')}>
                                    {common.privacy}
                                </Link>
                                <Link className="focus-ring hover:text-primary rounded" href={route('accessibility')}>
                                    {common.accessibility}
                                </Link>
                                <Link className="focus-ring hover:text-primary rounded" href={route('help')}>
                                    {common.help}
                                </Link>
                            </nav>
                            <p className="text-muted-foreground mt-3 text-xs font-medium">{home.footer_phase}</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    );
}
