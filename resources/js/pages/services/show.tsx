import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, CalendarClock, Clock3, Mail, PhilippinePeso, Phone, Search } from 'lucide-react';

interface Requirement {
    name: string;
    details: string | null;
    is_required: boolean;
}

interface PublicService {
    slug: string;
    name: string;
    description: string;
    eligibility: string;
    fee_centavos: number;
    processing_time: string;
    office_hours: string;
    procedure_steps: string[];
    appointment_required: boolean;
    contact: { email: string | null; phone: string | null };
    requirements: Requirement[];
}

interface Props {
    service: { data: PublicService };
}

export default function ServiceDetails({ service: resource }: Props) {
    const { locale, translations } = usePage<SharedData>().props;
    const copy = translations.services;
    const service = resource.data;
    const fee =
        service.fee_centavos === 0
            ? copy.free
            : new Intl.NumberFormat(locale === 'fil' ? 'fil-PH' : 'en-PH', { style: 'currency', currency: 'PHP' }).format(service.fee_centavos / 100);

    return (
        <PublicLayout>
            <Head title={service.name} />

            <section className="border-b border-slate-200 bg-white">
                <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                    <Link
                        href={route('services.index')}
                        className="focus-ring inline-flex min-h-11 items-center gap-2 rounded-lg text-sm font-bold text-[#14594f] hover:underline"
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        {copy.back_to_services}
                    </Link>
                    <h1 className="mt-6 max-w-4xl text-4xl leading-tight font-bold tracking-[-0.04em] text-balance sm:text-5xl">{service.name}</h1>
                    <p className="mt-5 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">{service.description}</p>
                </div>
            </section>

            <div className="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1fr_20rem] lg:px-8">
                <div className="space-y-8">
                    <section className="rounded-2xl border border-slate-200 bg-white p-6" aria-labelledby="eligibility-title">
                        <h2 id="eligibility-title" className="text-2xl font-bold tracking-[-0.025em]">
                            {copy.eligibility}
                        </h2>
                        <p className="mt-4 leading-7 text-slate-600">{service.eligibility}</p>
                    </section>

                    <section className="rounded-2xl border border-slate-200 bg-white p-6" aria-labelledby="requirements-title">
                        <h2 id="requirements-title" className="text-2xl font-bold tracking-[-0.025em]">
                            {copy.requirements}
                        </h2>
                        <ul className="mt-5 space-y-4">
                            {service.requirements.map((requirement) => (
                                <li key={requirement.name} className="rounded-xl bg-slate-50 p-4">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h3 className="font-bold">{requirement.name}</h3>
                                        <span className="rounded-full bg-[#e4f1ed] px-2.5 py-1 text-xs font-bold text-[#14594f]">
                                            {requirement.is_required ? copy.required : copy.optional}
                                        </span>
                                    </div>
                                    {requirement.details && <p className="mt-2 text-sm leading-6 text-slate-600">{requirement.details}</p>}
                                </li>
                            ))}
                        </ul>
                    </section>

                    <section className="rounded-2xl border border-slate-200 bg-white p-6" aria-labelledby="procedure-title">
                        <h2 id="procedure-title" className="text-2xl font-bold tracking-[-0.025em]">
                            {copy.procedure}
                        </h2>
                        <ol className="mt-5 space-y-4">
                            {service.procedure_steps.map((step, index) => (
                                <li key={step} className="flex items-start gap-4">
                                    <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#14594f] text-sm font-bold text-white">
                                        {index + 1}
                                    </span>
                                    <p className="pt-1 text-sm leading-6 text-slate-700">{step}</p>
                                </li>
                            ))}
                        </ol>
                    </section>
                </div>

                <aside className="space-y-5" aria-label="Service summary">
                    <dl className="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 text-sm">
                        <div className="flex gap-3">
                            <PhilippinePeso className="mt-0.5 size-5 shrink-0 text-[#14594f]" aria-hidden="true" />
                            <div>
                                <dt className="font-semibold text-slate-500">{copy.fee}</dt>
                                <dd className="mt-1 font-bold">{fee}</dd>
                            </div>
                        </div>
                        <div className="flex gap-3">
                            <Clock3 className="mt-0.5 size-5 shrink-0 text-[#14594f]" aria-hidden="true" />
                            <div>
                                <dt className="font-semibold text-slate-500">{copy.processing_time}</dt>
                                <dd className="mt-1 leading-6">{service.processing_time}</dd>
                            </div>
                        </div>
                        <div className="flex gap-3">
                            <CalendarClock className="mt-0.5 size-5 shrink-0 text-[#14594f]" aria-hidden="true" />
                            <div>
                                <dt className="font-semibold text-slate-500">{copy.appointment}</dt>
                                <dd className="mt-1 leading-6">
                                    {service.appointment_required ? copy.appointment_required : copy.appointment_not_required}
                                </dd>
                            </div>
                        </div>
                        <div>
                            <dt className="font-semibold text-slate-500">{copy.office_hours}</dt>
                            <dd className="mt-1 leading-6">{service.office_hours}</dd>
                        </div>
                    </dl>

                    <section className="rounded-2xl border border-slate-200 bg-white p-6" aria-labelledby="contact-title">
                        <h2 id="contact-title" className="font-bold">
                            {copy.contact}
                        </h2>
                        <div className="mt-4 space-y-3 text-sm">
                            {service.contact.email && (
                                <a
                                    className="focus-ring flex min-h-11 items-center gap-2 rounded-lg text-[#14594f] hover:underline"
                                    href={`mailto:${service.contact.email}`}
                                >
                                    <Mail className="size-4" aria-hidden="true" />
                                    {service.contact.email}
                                </a>
                            )}
                            {service.contact.phone && (
                                <a
                                    className="focus-ring flex min-h-11 items-center gap-2 rounded-lg text-[#14594f] hover:underline"
                                    href={`tel:${service.contact.phone.replace(/[^+\d]/g, '')}`}
                                >
                                    <Phone className="size-4" aria-hidden="true" />
                                    {service.contact.phone}
                                </a>
                            )}
                        </div>
                    </section>

                    <section className="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                        <h2 className="font-bold text-amber-950">{copy.next_step_title}</h2>
                        <p className="mt-2 text-sm leading-6 text-amber-900">{copy.next_step_body}</p>
                        <div className="mt-4 space-y-2">
                            <Link
                                href={route('requests.create', service.slug)}
                                className="focus-ring inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#14594f] px-4 text-sm font-bold text-white hover:bg-[#0e463e]"
                            >
                                {copy.start_request}
                                <ArrowRight className="size-4" aria-hidden="true" />
                            </Link>
                            <Link
                                href={route('tracking.index')}
                                className="focus-ring inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-amber-300 bg-white px-4 text-sm font-bold text-amber-950 hover:bg-amber-100"
                            >
                                <Search className="size-4" aria-hidden="true" />
                                {copy.track_request}
                            </Link>
                        </div>
                    </section>
                </aside>
            </div>
        </PublicLayout>
    );
}
