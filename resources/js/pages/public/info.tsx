import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, Mail } from 'lucide-react';

type PageKey = 'privacy' | 'accessibility' | 'help';

export default function InfoPage({ pageKey }: { pageKey: PageKey }) {
    const { translations } = usePage<SharedData>().props;
    const page = translations.info[pageKey];

    return (
        <PublicLayout>
            <Head title={page.meta_title} />

            <section className="border-border bg-card border-b">
                <div className="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                    <p className="text-primary text-xs font-bold tracking-[0.14em] uppercase">{page.eyebrow}</p>
                    <h1 className="mt-3 text-4xl font-bold tracking-[-0.04em] text-balance sm:text-5xl">{page.title}</h1>
                    <p className="text-muted-foreground mt-5 max-w-3xl text-base leading-7 sm:text-lg">{page.intro}</p>
                </div>
            </section>

            <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                <div className="grid gap-5 md:grid-cols-2">
                    {page.sections.map((section) => (
                        <section key={section.title} className="border-border bg-card rounded-2xl border p-6">
                            <h2 className="text-xl font-bold tracking-[-0.02em]">{section.title}</h2>
                            <p className="text-muted-foreground mt-3 text-sm leading-7">{section.body}</p>
                        </section>
                    ))}
                </div>

                <div className="mt-8 flex flex-col gap-3 rounded-2xl bg-[#163f3a] p-6 text-white sm:flex-row sm:items-center sm:justify-between">
                    <p className="max-w-xl text-sm leading-6 text-[#d7e8e4]">
                        Barangay Haraya and its contact details are fictional and are used only for this portfolio demonstration.
                    </p>
                    <div className="flex flex-wrap gap-2">
                        <Link
                            href={route('services.index')}
                            className="focus-ring inline-flex min-h-11 items-center gap-2 rounded-xl bg-white px-4 text-sm font-bold text-[#14594f]"
                        >
                            {translations.common.services}
                            <ArrowRight className="size-4" aria-hidden="true" />
                        </Link>
                        <a
                            href="mailto:help@barangayharaya.test"
                            className="focus-ring inline-flex min-h-11 items-center gap-2 rounded-xl border border-white/40 px-4 text-sm font-bold text-white hover:bg-white/10"
                        >
                            <Mail className="size-4" aria-hidden="true" />
                            {translations.common.help}
                        </a>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
