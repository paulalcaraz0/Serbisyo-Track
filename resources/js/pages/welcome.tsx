import AppLogoIcon from '@/components/app-logo-icon';
import AppearanceToggleDropdown from '@/components/appearance-dropdown';
import { PageAnnouncer } from '@/components/page-announcer';
import { type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Check, FileCheck2, FileText, Languages, LockKeyhole, Search, ShieldCheck } from 'lucide-react';

const featureIcons = [Search, FileCheck2, ShieldCheck];

export default function Welcome() {
    const { auth, locale, name, office, supportedLocales, translations } = usePage<SharedData>().props;
    const { common, home } = translations;
    const staffDestination = auth.user ? route('dashboard') : route('login');
    const staffLabel = auth.user ? common.dashboard : common.staff_portal;

    const updateLocale = (nextLocale: string) => {
        if (nextLocale === locale) {
            return;
        }

        router.post(route('locale.update'), { locale: nextLocale, redirect_to: '/' }, { preserveScroll: true });
    };

    return (
        <div className="public-shell">
            <PageAnnouncer />
            <Head title={home.meta_title}>
                <meta name="description" content={home.meta_description} />
            </Head>

            <a href="#main-content" className="skip-link">
                {common.skip_to_content}
            </a>

            <div className="bg-primary text-primary-foreground">
                <div className="mx-auto flex max-w-7xl items-start gap-3 px-4 py-3 text-sm leading-5 sm:px-6 lg:px-8">
                    <ShieldCheck className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <p>
                        <strong className="font-semibold">{home.disclaimer_label}:</strong> {home.disclaimer}
                    </p>
                </div>
            </div>

            <div className="bg-background text-foreground min-h-screen">
                <header className="border-border/90 bg-card/95 border-b">
                    <div className="mx-auto flex min-h-20 max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <Link href={route('home')} className="focus-ring flex min-h-11 items-center gap-3 rounded-xl" aria-label={`${name} home`}>
                            <AppLogoIcon className="text-primary size-10" />
                            <span>
                                <span className="text-foreground block text-base font-bold tracking-[-0.02em]">{name}</span>
                                <span className="text-muted-foreground block text-xs font-medium">Barangay Haraya</span>
                            </span>
                        </Link>

                        <div className="flex flex-wrap items-center justify-end gap-2">
                            <Link
                                href={route('services.index')}
                                className="focus-ring text-foreground hover:bg-muted hover:text-primary inline-flex min-h-11 items-center rounded-xl px-3 text-sm font-semibold"
                            >
                                {common.services}
                            </Link>
                            <Link
                                href={route('tracking.index')}
                                className="focus-ring text-foreground hover:bg-muted hover:text-primary inline-flex min-h-11 items-center rounded-xl px-3 text-sm font-semibold"
                            >
                                {common.track}
                            </Link>
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
                                className="focus-ring border-border bg-card text-foreground hover:border-primary hover:text-primary inline-flex min-h-11 items-center justify-center rounded-xl border px-4 text-sm font-semibold"
                            >
                                {staffLabel}
                            </Link>
                        </div>
                    </div>
                </header>

                <main id="main-content" tabIndex={-1}>
                    <section className="border-border bg-card relative isolate overflow-hidden border-b">
                        <div className="dot-grid pointer-events-none absolute inset-0 -z-10 opacity-60 [mask-image:radial-gradient(ellipse_at_top_left,black,transparent_65%)]" />
                        <div className="pointer-events-none absolute -top-40 right-[-14rem] -z-10 size-[34rem] rounded-full bg-[radial-gradient(circle_at_center,hsl(213_55%_60%/0.35),transparent_70%)] blur-3xl" />
                        <div className="pointer-events-none absolute -bottom-64 left-[-16rem] -z-10 size-[38rem] rounded-full bg-[radial-gradient(circle_at_center,hsl(39_80%_70%/0.4),transparent_70%)] blur-3xl" />
                        <div className="via-primary/40 pointer-events-none absolute inset-x-0 top-0 -z-10 h-px bg-gradient-to-r from-transparent to-transparent" />

                        <div className="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:px-8 lg:py-28">
                            <div className="max-w-3xl">
                                <p
                                    className="border-primary/30 bg-secondary/80 text-primary animate-fade-up mb-5 inline-flex min-h-8 items-center rounded-full border px-4 text-xs font-bold tracking-[0.12em] uppercase backdrop-blur"
                                    style={{ animationDelay: '0ms' }}
                                >
                                    {home.eyebrow}
                                </p>
                                <h1 className="text-foreground relative max-w-3xl text-4xl leading-[1.08] font-bold tracking-[-0.045em] text-balance sm:text-5xl lg:text-6xl">
                                    <span className="animate-fade-up block" style={{ animationDelay: '60ms' }}>
                                        {home.title}
                                    </span>
                                    <span
                                        aria-hidden="true"
                                        className="from-primary via-primary/60 to-accent absolute -bottom-2 left-1 h-1.5 w-24 rounded-full bg-gradient-to-r"
                                    />
                                </h1>
                                <p
                                    className="text-muted-foreground animate-fade-up mt-8 max-w-2xl text-base leading-7 sm:text-lg sm:leading-8"
                                    style={{ animationDelay: '120ms' }}
                                >
                                    {home.description}
                                </p>

                                <div className="animate-fade-up mt-9 flex flex-col gap-3 sm:flex-row" style={{ animationDelay: '180ms' }}>
                                    <Link
                                        href={route('services.index')}
                                        className="focus-ring bg-primary text-primary-foreground hover:bg-primary/90 shadow-soft hover:shadow-lift inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-6 text-sm font-bold transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                                    >
                                        {home.primary_action}
                                        <ArrowRight className="size-4" aria-hidden="true" />
                                    </Link>
                                    <Link
                                        href={staffDestination}
                                        className="focus-ring border-border bg-card/80 text-foreground hover:border-primary/60 hover:bg-muted inline-flex min-h-12 items-center justify-center rounded-xl border px-6 text-sm font-bold backdrop-blur transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                                    >
                                        {home.secondary_action}
                                    </Link>
                                </div>

                                <p
                                    className="border-border/70 bg-card/60 animate-fade-up mt-8 flex max-w-xl items-start gap-3 rounded-2xl border p-4 text-sm leading-6 backdrop-blur"
                                    style={{ animationDelay: '240ms' }}
                                >
                                    <LockKeyhole className="text-primary mt-0.5 size-4 shrink-0" aria-hidden="true" />
                                    <span className="text-muted-foreground">{home.trust_note}</span>
                                </p>
                            </div>

                            <aside
                                className="animate-fade-up border-border bg-card/90 shadow-glow relative rounded-[1.75rem] border p-5 ring-1 ring-white/40 backdrop-blur-sm transition-transform duration-300 hover:-translate-y-1 sm:p-7 dark:ring-white/5"
                                style={{ animationDelay: '160ms' }}
                                aria-labelledby="journey-title"
                            >
                                <div className="border-border/60 mb-6 flex items-center justify-between gap-4 border-b pb-5">
                                    <div>
                                        <p className="text-accent-foreground text-xs font-bold tracking-[0.12em] uppercase">{home.preview_label}</p>
                                        <h2 id="journey-title" className="text-foreground mt-2 text-xl font-bold tracking-[-0.025em]">
                                            {home.preview_title}
                                        </h2>
                                    </div>
                                    <div className="bg-accent text-accent-foreground flex size-12 shrink-0 items-center justify-center rounded-2xl">
                                        <CalendarDays className="size-6" aria-hidden="true" />
                                    </div>
                                </div>
                                <ol className="space-y-4">
                                    {home.preview_items.map((item, index) => (
                                        <li key={item} className="bg-muted flex items-start gap-3 rounded-xl px-4 py-3.5">
                                            <span className="bg-primary text-primary-foreground flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold">
                                                {index + 1}
                                            </span>
                                            <span className="text-foreground pt-0.5 text-sm leading-6 font-medium">{item}</span>
                                        </li>
                                    ))}
                                </ol>
                            </aside>
                        </div>
                    </section>

                    <section className="mx-auto max-w-7xl px-4 pt-10 sm:px-6 sm:pt-14 lg:px-8">
                        <p className="sr-only">{home.stats_label}</p>
                        <div className="border-border bg-card/80 shadow-soft focus-within:shadow-glow hover:shadow-glow sm:divide-border/70 grid gap-y-6 rounded-2xl border px-6 py-8 backdrop-blur transition-shadow duration-300 sm:grid-cols-3 sm:gap-x-6 sm:divide-x">
                            {home.stats.map((stat) => (
                                <div key={stat.label} className="flex flex-col items-center gap-1.5 px-4 text-center sm:px-6">
                                    <span className="from-primary to-primary/55 bg-gradient-to-br bg-clip-text text-4xl font-bold tracking-[-0.03em] text-transparent">
                                        {stat.value}
                                    </span>
                                    <span className="text-muted-foreground max-w-56 text-sm leading-6 font-semibold">{stat.label}</span>
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8" aria-labelledby="features-title">
                        <div className="max-w-3xl">
                            <p className="text-primary text-xs font-bold tracking-[0.14em] uppercase">{home.features_label}</p>
                            <h2 id="features-title" className="text-foreground mt-3 text-3xl font-bold tracking-[-0.035em] text-balance sm:text-4xl">
                                {home.features_title}
                            </h2>
                            <p className="text-muted-foreground mt-4 text-base leading-7">{home.features_intro}</p>
                        </div>

                        <div className="mt-10 grid gap-5 md:grid-cols-3">
                            {home.features.map((feature, index) => {
                                const FeatureIcon = featureIcons[index] ?? FileText;

                                return (
                                    <article
                                        key={feature.title}
                                        className="group border-border bg-card shadow-soft hover:shadow-lift relative rounded-2xl border p-6 transition-all duration-300 hover:-translate-y-1"
                                    >
                                        <div className="via-primary/50 absolute inset-x-6 top-0 h-px bg-gradient-to-r from-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                                        <div className="bg-secondary text-primary flex size-11 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-105">
                                            <FeatureIcon className="size-5" aria-hidden="true" />
                                        </div>
                                        <h3 className="text-foreground mt-5 text-lg font-bold tracking-[-0.02em]">{feature.title}</h3>
                                        <p className="text-muted-foreground mt-3 text-sm leading-6">{feature.body}</p>
                                    </article>
                                );
                            })}
                        </div>
                    </section>

                    <section id="how-it-works" className="border-border bg-secondary scroll-mt-6 border-y" aria-labelledby="steps-title">
                        <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                            <p className="text-primary text-xs font-bold tracking-[0.14em] uppercase">{home.steps_label}</p>
                            <h2 id="steps-title" className="text-foreground mt-3 max-w-2xl text-3xl font-bold tracking-[-0.035em] sm:text-4xl">
                                {home.steps_title}
                            </h2>

                            <ol className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                                {home.steps.map((step, index) => (
                                    <li
                                        key={step.title}
                                        className="border-primary/25 bg-card/85 hover:border-primary/50 hover:shadow-lift rounded-2xl border p-5 transition-all duration-300 hover:-translate-y-1"
                                    >
                                        <span className="bg-primary text-primary-foreground shadow-soft flex size-9 items-center justify-center rounded-full text-sm font-bold">
                                            {index + 1}
                                        </span>
                                        <h3 className="text-foreground mt-5 font-bold">{step.title}</h3>
                                        <p className="text-muted-foreground mt-2 text-sm leading-6">{step.body}</p>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    </section>

                    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8" aria-labelledby="foundation-title">
                        <div className="shadow-lift relative isolate grid overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-[hsl(213_48%_28%)] to-[hsl(216_42%_15%)] text-white lg:grid-cols-[0.9fr_1.1fr]">
                            <div
                                aria-hidden="true"
                                className="pointer-events-none absolute -top-24 right-0 -z-10 size-[26rem] rounded-full bg-[radial-gradient(circle_at_center,hsl(213_60%_55%/0.35),transparent_70%)] blur-3xl"
                            />
                            <div className="p-7 sm:p-10 lg:p-12">
                                <p className="text-accent text-xs font-bold tracking-[0.14em] uppercase">{home.foundation_label}</p>
                                <h2 id="foundation-title" className="mt-3 text-3xl font-bold tracking-[-0.035em] text-balance sm:text-4xl">
                                    {home.foundation_title}
                                </h2>
                                <p className="mt-5 text-sm leading-7 text-white/70 sm:text-base">{home.foundation_body}</p>
                            </div>
                            <ul className="grid gap-px bg-white/10 lg:grid-rows-3">
                                {home.foundation_points.map((point) => (
                                    <li
                                        key={point}
                                        className="flex items-center gap-4 px-7 py-6 transition-colors duration-200 hover:bg-white/5 sm:px-10"
                                    >
                                        <span className="bg-accent text-accent-foreground shadow-soft flex size-8 shrink-0 items-center justify-center rounded-full">
                                            <Check className="size-4 stroke-[3]" aria-hidden="true" />
                                        </span>
                                        <span className="text-sm leading-6 font-semibold sm:text-base">{point}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </section>
                </main>

                <footer className="border-border bg-card border-t">
                    <div className="text-muted-foreground mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm sm:px-6 md:flex-row md:items-end md:justify-between lg:px-8">
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
