import AppLogoIcon from '@/components/app-logo-icon';
import { type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Check, FileCheck2, FileText, Languages, LockKeyhole, Search, ShieldCheck } from 'lucide-react';

const featureIcons = [Search, FileCheck2, ShieldCheck];

export default function Welcome() {
    const { auth, locale, name, supportedLocales, translations } = usePage<SharedData>().props;
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
        <>
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

            <div className="min-h-screen bg-[#f6f8f5] text-slate-950">
                <header className="border-b border-slate-200/90 bg-white/95">
                    <div className="mx-auto flex min-h-20 max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <Link href={route('home')} className="focus-ring flex min-h-11 items-center gap-3 rounded-xl" aria-label={`${name} home`}>
                            <AppLogoIcon className="size-10 text-[#14594f]" />
                            <span>
                                <span className="block text-base font-bold tracking-[-0.02em] text-slate-950">{name}</span>
                                <span className="block text-xs font-medium text-slate-500">Barangay Haraya</span>
                            </span>
                        </Link>

                        <div className="flex flex-wrap items-center justify-end gap-2">
                            <Link
                                href={route('services.index')}
                                className="focus-ring inline-flex min-h-11 items-center rounded-xl px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#14594f]"
                            >
                                {common.services}
                            </Link>
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
                                        className="focus-ring min-h-9 rounded-lg px-3 text-xs font-semibold text-slate-600 hover:bg-white hover:text-slate-950 aria-pressed:bg-white aria-pressed:text-[#14594f] aria-pressed:shadow-sm"
                                    >
                                        {label}
                                    </button>
                                ))}
                            </div>
                            <Link
                                href={staffDestination}
                                className="focus-ring inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-800 hover:border-[#14594f] hover:text-[#14594f]"
                            >
                                {staffLabel}
                            </Link>
                        </div>
                    </div>
                </header>

                <main id="main-content">
                    <section className="relative isolate overflow-hidden border-b border-slate-200 bg-white">
                        <div className="pointer-events-none absolute -top-32 right-[-12rem] -z-10 size-[30rem] rounded-full bg-[#dcece6] blur-3xl" />
                        <div className="pointer-events-none absolute -bottom-56 left-[-14rem] -z-10 size-[32rem] rounded-full bg-[#fae8c7] opacity-70 blur-3xl" />

                        <div className="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:px-8 lg:py-28">
                            <div className="max-w-3xl">
                                <p className="mb-5 inline-flex min-h-8 items-center rounded-full border border-[#b9d7ce] bg-[#edf7f3] px-4 text-xs font-bold tracking-[0.12em] text-[#14594f] uppercase">
                                    {home.eyebrow}
                                </p>
                                <h1 className="max-w-3xl text-4xl leading-[1.08] font-bold tracking-[-0.045em] text-balance text-slate-950 sm:text-5xl lg:text-6xl">
                                    {home.title}
                                </h1>
                                <p className="mt-6 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">{home.description}</p>

                                <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                    <Link
                                        href={route('services.index')}
                                        className="focus-ring inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#14594f] px-5 text-sm font-bold text-white shadow-sm hover:bg-[#0e463e]"
                                    >
                                        {home.primary_action}
                                        <ArrowRight className="size-4" aria-hidden="true" />
                                    </Link>
                                    <Link
                                        href={staffDestination}
                                        className="focus-ring inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-800 hover:border-slate-400 hover:bg-slate-50"
                                    >
                                        {home.secondary_action}
                                    </Link>
                                </div>

                                <p className="mt-7 flex max-w-xl items-start gap-2 text-sm leading-6 text-slate-500">
                                    <LockKeyhole className="mt-1 size-4 shrink-0 text-[#14594f]" aria-hidden="true" />
                                    {home.trust_note}
                                </p>
                            </div>

                            <aside
                                className="relative rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-[0_24px_70px_-32px_rgba(15,23,42,0.35)] sm:p-7"
                                aria-labelledby="journey-title"
                            >
                                <div className="mb-6 flex items-center justify-between gap-4 border-b border-slate-100 pb-5">
                                    <div>
                                        <p className="text-xs font-bold tracking-[0.12em] text-[#9a5d12] uppercase">{home.preview_label}</p>
                                        <h2 id="journey-title" className="mt-2 text-xl font-bold tracking-[-0.025em] text-slate-950">
                                            {home.preview_title}
                                        </h2>
                                    </div>
                                    <div className="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-[#fdf0d7] text-[#9a5d12]">
                                        <CalendarDays className="size-6" aria-hidden="true" />
                                    </div>
                                </div>
                                <ol className="space-y-4">
                                    {home.preview_items.map((item, index) => (
                                        <li key={item} className="flex items-start gap-3 rounded-xl bg-slate-50 px-4 py-3.5">
                                            <span className="flex size-7 shrink-0 items-center justify-center rounded-full bg-[#14594f] text-xs font-bold text-white">
                                                {index + 1}
                                            </span>
                                            <span className="pt-0.5 text-sm leading-6 font-medium text-slate-700">{item}</span>
                                        </li>
                                    ))}
                                </ol>
                            </aside>
                        </div>
                    </section>

                    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8" aria-labelledby="features-title">
                        <div className="max-w-3xl">
                            <p className="text-xs font-bold tracking-[0.14em] text-[#14594f] uppercase">{home.features_label}</p>
                            <h2 id="features-title" className="mt-3 text-3xl font-bold tracking-[-0.035em] text-balance text-slate-950 sm:text-4xl">
                                {home.features_title}
                            </h2>
                            <p className="mt-4 text-base leading-7 text-slate-600">{home.features_intro}</p>
                        </div>

                        <div className="mt-10 grid gap-5 md:grid-cols-3">
                            {home.features.map((feature, index) => {
                                const FeatureIcon = featureIcons[index] ?? FileText;

                                return (
                                    <article
                                        key={feature.title}
                                        className="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.45)]"
                                    >
                                        <div className="flex size-11 items-center justify-center rounded-xl bg-[#e4f1ed] text-[#14594f]">
                                            <FeatureIcon className="size-5" aria-hidden="true" />
                                        </div>
                                        <h3 className="mt-5 text-lg font-bold tracking-[-0.02em] text-slate-950">{feature.title}</h3>
                                        <p className="mt-3 text-sm leading-6 text-slate-600">{feature.body}</p>
                                    </article>
                                );
                            })}
                        </div>
                    </section>

                    <section id="how-it-works" className="scroll-mt-6 border-y border-slate-200 bg-[#e9f2ef]" aria-labelledby="steps-title">
                        <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                            <p className="text-xs font-bold tracking-[0.14em] text-[#14594f] uppercase">{home.steps_label}</p>
                            <h2 id="steps-title" className="mt-3 max-w-2xl text-3xl font-bold tracking-[-0.035em] text-slate-950 sm:text-4xl">
                                {home.steps_title}
                            </h2>

                            <ol className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                                {home.steps.map((step, index) => (
                                    <li key={step.title} className="rounded-2xl border border-[#c7ddd6] bg-white/85 p-5">
                                        <span className="flex size-9 items-center justify-center rounded-full bg-[#14594f] text-sm font-bold text-white">
                                            {index + 1}
                                        </span>
                                        <h3 className="mt-5 font-bold text-slate-950">{step.title}</h3>
                                        <p className="mt-2 text-sm leading-6 text-slate-600">{step.body}</p>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    </section>

                    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8" aria-labelledby="foundation-title">
                        <div className="grid overflow-hidden rounded-[1.75rem] bg-[#163f3a] text-white lg:grid-cols-[0.9fr_1.1fr]">
                            <div className="p-7 sm:p-10 lg:p-12">
                                <p className="text-xs font-bold tracking-[0.14em] text-[#f4c776] uppercase">{home.foundation_label}</p>
                                <h2 id="foundation-title" className="mt-3 text-3xl font-bold tracking-[-0.035em] text-balance sm:text-4xl">
                                    {home.foundation_title}
                                </h2>
                                <p className="mt-5 text-sm leading-7 text-[#d7e8e4] sm:text-base">{home.foundation_body}</p>
                            </div>
                            <ul className="grid gap-px bg-white/10 lg:grid-rows-3">
                                {home.foundation_points.map((point) => (
                                    <li key={point} className="flex items-center gap-4 bg-[#1b4b45] px-7 py-6 sm:px-10">
                                        <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#f4c776] text-[#163f3a]">
                                            <Check className="size-4 stroke-[3]" aria-hidden="true" />
                                        </span>
                                        <span className="text-sm leading-6 font-semibold sm:text-base">{point}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </section>
                </main>

                <footer className="border-t border-slate-200 bg-white">
                    <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-slate-600 sm:px-6 md:flex-row md:items-end md:justify-between lg:px-8">
                        <div>
                            <div className="flex items-center gap-2 font-bold text-slate-950">
                                <AppLogoIcon className="size-7 text-[#14594f]" />
                                {name}
                            </div>
                            <p className="mt-3">{home.footer_office}</p>
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
        </>
    );
}
