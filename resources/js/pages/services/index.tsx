import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, CalendarClock, Clock3, PhilippinePeso } from 'lucide-react';

interface PublicService {
    slug: string;
    name: string;
    description: string;
    fee_centavos: number;
    processing_time: string;
    appointment_required: boolean;
}

interface Props {
    services: { data: PublicService[] };
}

export default function ServiceDirectory({ services }: Props) {
    const { locale, translations } = usePage<SharedData>().props;
    const copy = translations.services;
    const formatFee = (centavos: number) =>
        centavos === 0
            ? copy.free
            : new Intl.NumberFormat(locale === 'fil' ? 'fil-PH' : 'en-PH', { style: 'currency', currency: 'PHP' }).format(centavos / 100);

    return (
        <PublicLayout>
            <Head title={copy.meta_title} />

            <section className="border-b border-slate-200 bg-white">
                <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                    <p className="text-xs font-bold tracking-[0.14em] text-[#14594f] uppercase">{copy.eyebrow}</p>
                    <h1 className="mt-3 max-w-4xl text-4xl leading-tight font-bold tracking-[-0.04em] text-balance sm:text-5xl">{copy.title}</h1>
                    <p className="mt-5 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">{copy.intro}</p>
                    <p className="mt-6 inline-flex min-h-9 items-center rounded-full bg-[#e4f1ed] px-4 text-sm font-bold text-[#14594f]">
                        {copy.available_count.replace(':count', String(services.data.length))}
                    </p>
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8" aria-label={copy.eyebrow}>
                {services.data.length === 0 ? (
                    <div className="rounded-2xl border border-slate-200 bg-white p-8 text-center">
                        <h2 className="text-xl font-bold">{copy.empty_title}</h2>
                        <p className="mt-2 text-sm leading-6 text-slate-600">{copy.empty_body}</p>
                    </div>
                ) : (
                    <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        {services.data.map((service) => (
                            <article
                                key={service.slug}
                                className="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_12px_36px_-30px_rgba(15,23,42,0.5)]"
                            >
                                <h2 className="text-xl font-bold tracking-[-0.025em] text-slate-950">{service.name}</h2>
                                <p className="mt-3 flex-1 text-sm leading-6 text-slate-600">{service.description}</p>
                                <dl className="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                                    <div className="flex items-start gap-3">
                                        <PhilippinePeso className="mt-0.5 size-4 shrink-0 text-[#14594f]" aria-hidden="true" />
                                        <div>
                                            <dt className="font-semibold text-slate-500">{copy.fee}</dt>
                                            <dd className="font-bold text-slate-900">{formatFee(service.fee_centavos)}</dd>
                                        </div>
                                    </div>
                                    <div className="flex items-start gap-3">
                                        <Clock3 className="mt-0.5 size-4 shrink-0 text-[#14594f]" aria-hidden="true" />
                                        <div>
                                            <dt className="font-semibold text-slate-500">{copy.processing_time}</dt>
                                            <dd className="text-slate-800">{service.processing_time}</dd>
                                        </div>
                                    </div>
                                    <div className="flex items-start gap-3">
                                        <CalendarClock className="mt-0.5 size-4 shrink-0 text-[#14594f]" aria-hidden="true" />
                                        <div>
                                            <dt className="font-semibold text-slate-500">{copy.appointment}</dt>
                                            <dd className="text-slate-800">
                                                {service.appointment_required ? copy.appointment_required : copy.appointment_not_required}
                                            </dd>
                                        </div>
                                    </div>
                                </dl>
                                <Link
                                    href={route('services.show', service.slug)}
                                    className="focus-ring mt-6 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#14594f] px-4 text-sm font-bold text-white hover:bg-[#0e463e]"
                                >
                                    {copy.view_details}
                                    <ArrowRight className="size-4" aria-hidden="true" />
                                </Link>
                            </article>
                        ))}
                    </div>
                )}
            </section>
        </PublicLayout>
    );
}
