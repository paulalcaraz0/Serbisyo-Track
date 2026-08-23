import { buttonVariants } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { CalendarClock, CheckCircle2, Download, FileText, History, ShieldCheck } from 'lucide-react';

interface Props {
    trackedRequest: {
        reference: string;
        serviceName: string;
        status: string;
        statusLabel: string;
        statusDescription: string;
        submittedAt: string | null;
        updatedAt: string | null;
        appointment: { preferredDate: string | null; preferredTimeWindow: string; status: string } | null;
        attachments: { publicId: string; name: string; sizeBytes: number }[];
        history: { status: string | null; message: string; occurredAt: string }[];
    };
}

export default function TrackingShow({ trackedRequest }: Props) {
    const { locale, translations } = usePage<SharedData>().props;
    const copy = translations.tracking;
    const dateLocale = locale === 'fil' ? 'fil-PH' : 'en-PH';
    const formatDate = (value: string | null, options: Intl.DateTimeFormatOptions) =>
        value ? new Intl.DateTimeFormat(dateLocale, options).format(new Date(value)) : '—';
    const timeLabel = trackedRequest.appointment?.preferredTimeWindow === 'morning' ? translations.requests.morning : translations.requests.afternoon;

    return (
        <PublicLayout>
            <Head title={copy.status_meta_title}>
                <meta name="robots" content="noindex,nofollow,noarchive" />
            </Head>
            <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                <div className="mb-6">
                    <p className="text-primary text-xs font-bold tracking-[0.14em] uppercase">{copy.status_eyebrow}</p>
                    <h1 className="mt-2 text-3xl font-bold tracking-[-0.035em] sm:text-4xl">{copy.status_title}</h1>
                    <p className="text-muted-foreground mt-2 font-mono text-sm font-bold">{trackedRequest.reference}</p>
                </div>
                <section className="bg-card rounded-3xl border p-6 shadow-sm sm:p-8" aria-labelledby="current-status">
                    <div className="flex items-start gap-4">
                        <div className="bg-secondary text-primary flex size-12 shrink-0 items-center justify-center rounded-2xl">
                            <CheckCircle2 className="size-6" aria-hidden="true" />
                        </div>
                        <div>
                            <p className="text-muted-foreground text-sm font-semibold">{copy.status_title}</p>
                            <h2 id="current-status" className="mt-1 text-2xl font-bold">
                                {trackedRequest.statusLabel}
                            </h2>
                            <p className="text-muted-foreground mt-2 leading-7">{trackedRequest.statusDescription}</p>
                        </div>
                    </div>
                </section>
                <div className="mt-6 grid gap-6 md:grid-cols-2">
                    <section className="border-border bg-card rounded-2xl border p-6" aria-labelledby="request-summary">
                        <h2 id="request-summary" className="font-bold">
                            {copy.service}
                        </h2>
                        <p className="text-primary mt-2 text-lg font-bold">{trackedRequest.serviceName}</p>
                        <dl className="border-border/60 mt-5 space-y-4 border-t pt-5 text-sm">
                            <div>
                                <dt className="text-muted-foreground font-semibold">{copy.submitted}</dt>
                                <dd className="mt-1">{formatDate(trackedRequest.submittedAt, { dateStyle: 'medium', timeStyle: 'short' })}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground font-semibold">{copy.last_updated}</dt>
                                <dd className="mt-1">{formatDate(trackedRequest.updatedAt, { dateStyle: 'medium', timeStyle: 'short' })}</dd>
                            </div>
                        </dl>
                    </section>
                    <section className="border-border bg-card rounded-2xl border p-6" aria-labelledby="appointment-summary">
                        <h2 id="appointment-summary" className="flex items-center gap-2 font-bold">
                            <CalendarClock className="text-primary size-5" aria-hidden="true" />
                            {copy.appointment}
                        </h2>
                        {trackedRequest.appointment ? (
                            <>
                                <p className="mt-4 font-bold">
                                    {copy.appointment_requested
                                        .replace(':date', formatDate(trackedRequest.appointment.preferredDate, { dateStyle: 'long' }))
                                        .replace(':time', timeLabel)}
                                </p>
                                <span className="mt-3 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-200">
                                    {translations.appointment_statuses[trackedRequest.appointment.status]}
                                </span>
                                <p className="text-muted-foreground mt-3 text-sm leading-6">{copy.appointment_note}</p>
                            </>
                        ) : (
                            <p className="text-muted-foreground mt-4 text-sm">{translations.requests.none}</p>
                        )}
                    </section>
                </div>
                <section className="border-border bg-card mt-6 rounded-2xl border p-6" aria-labelledby="public-history">
                    <h2 id="public-history" className="flex items-center gap-2 font-bold">
                        <History className="text-primary size-5" aria-hidden="true" />
                        {copy.history}
                    </h2>
                    <p className="text-muted-foreground mt-2 text-sm leading-6">{copy.history_intro}</p>
                    {trackedRequest.history.length ? (
                        <ol className="border-primary/30 mt-5 space-y-4 border-l-2 pl-5">
                            {[...trackedRequest.history].reverse().map((event) => (
                                <li key={`${event.occurredAt}-${event.status}`} className="relative">
                                    <span className="border-card bg-primary absolute top-1.5 -left-[1.7rem] size-3 rounded-full border-2" />
                                    <p className="text-muted-foreground text-xs font-semibold">
                                        {formatDate(event.occurredAt, { dateStyle: 'medium', timeStyle: 'short' })}
                                    </p>
                                    {event.status && <p className="mt-1 font-bold">{translations.statuses[event.status]?.label}</p>}
                                    <p className="text-muted-foreground mt-1 text-sm leading-6">{event.message}</p>
                                </li>
                            ))}
                        </ol>
                    ) : (
                        <p className="text-muted-foreground mt-4 text-sm">{copy.no_history}</p>
                    )}
                </section>
                <section className="border-border bg-card mt-6 rounded-2xl border p-6" aria-labelledby="attachment-list">
                    <h2 id="attachment-list" className="flex items-center gap-2 font-bold">
                        <FileText className="text-primary size-5" aria-hidden="true" />
                        {copy.attachments}
                    </h2>
                    {trackedRequest.attachments.length ? (
                        <ul className="divide-border/60 mt-4 divide-y">
                            {trackedRequest.attachments.map((file) => (
                                <li key={file.publicId} className="flex flex-wrap items-center justify-between gap-3 py-3">
                                    <div>
                                        <p className="font-semibold">{file.name}</p>
                                        <p className="text-muted-foreground text-xs">{(file.sizeBytes / 1024).toFixed(1)} KB</p>
                                    </div>
                                    <a
                                        href={route('tracking.attachments.show', { reference: trackedRequest.reference, attachment: file.publicId })}
                                        className={buttonVariants({ variant: 'outline', size: 'sm' })}
                                    >
                                        <Download />
                                        {copy.download.replace(':name', file.name)}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-muted-foreground mt-3 text-sm">{copy.no_attachments}</p>
                    )}
                </section>
                <div className="border-border bg-muted text-muted-foreground mt-6 flex items-start gap-3 rounded-2xl border p-5 text-sm leading-6">
                    <ShieldCheck className="text-primary mt-0.5 size-5 shrink-0" aria-hidden="true" />
                    <p>{copy.privacy_note}</p>
                </div>
                <div className="mt-6 flex flex-wrap gap-3">
                    <Link href={route('requests.receipt', trackedRequest.reference)} className={buttonVariants()}>
                        {copy.receipt}
                    </Link>
                    <Link href={route('tracking.index')} className={cn(buttonVariants({ variant: 'outline' }))}>
                        {copy.track_another}
                    </Link>
                </div>
            </div>
        </PublicLayout>
    );
}
