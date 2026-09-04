import InputError from '@/components/input-error';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { CalendarClock, CheckCircle2, Download, FileText, History, MessageSquareText, Send, ShieldCheck, Trash2 } from 'lucide-react';
import { type FormEvent, useRef } from 'react';

interface Props {
    trackedRequest: {
        reference: string;
        serviceName: string;
        status: string;
        statusLabel: string;
        statusDescription: string;
        canRespond: boolean;
        requestedInformationMessage: string | null;
        submittedAt: string | null;
        updatedAt: string | null;
        appointment: { preferredDate: string | null; preferredTimeWindow: string; status: string } | null;
        attachments: { publicId: string; name: string; sizeBytes: number }[];
        history: { status: string | null; message: string; occurredAt: string }[];
    };
    attachmentRules: { maxFiles: number; maxMegabytes: number; accept: string };
}

interface ResponseFormData {
    [key: string]: string | File[];
    response_details: string;
    attachments: File[];
    website: string;
}

export default function TrackingShow({ trackedRequest, attachmentRules }: Props) {
    const { flash, locale, translations } = usePage<SharedData>().props;
    const copy = translations.tracking;
    const responseCopy = translations.resident_response;
    const fileInputRef = useRef<HTMLInputElement>(null);
    const responseForm = useForm<ResponseFormData>({ response_details: '', attachments: [], website: '' });
    const attachmentError = Object.entries(responseForm.errors).find(([key]) => key === 'attachments' || key.startsWith('attachments.'))?.[1];
    const dateLocale = locale === 'fil' ? 'fil-PH' : 'en-PH';
    const formatDate = (value: string | null, options: Intl.DateTimeFormatOptions) =>
        value ? new Intl.DateTimeFormat(dateLocale, options).format(new Date(value)) : '—';
    const timeLabel = trackedRequest.appointment?.preferredTimeWindow === 'morning' ? translations.requests.morning : translations.requests.afternoon;

    const submitResponse = (event: FormEvent) => {
        event.preventDefault();
        responseForm.post(route('tracking.responses.store', { reference: trackedRequest.reference }), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                responseForm.reset();
                if (fileInputRef.current) fileInputRef.current.value = '';
            },
        });
    };

    const addResponseFiles = (files: FileList | null) => {
        if (!files) return;
        responseForm.setData('attachments', Array.from(files).slice(0, attachmentRules.maxFiles));
    };

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
                {flash.success && (
                    <div
                        role="status"
                        aria-live="polite"
                        className="mt-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-5 text-sm font-semibold text-emerald-950 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-100"
                    >
                        {flash.success}
                    </div>
                )}
                {trackedRequest.canRespond && (
                    <section
                        className="border-primary/30 bg-card mt-6 rounded-2xl border p-6 shadow-sm sm:p-8"
                        aria-labelledby="resident-response-title"
                    >
                        <div className="flex items-start gap-4">
                            <div className="bg-secondary text-primary flex size-11 shrink-0 items-center justify-center rounded-xl">
                                <MessageSquareText className="size-5" aria-hidden="true" />
                            </div>
                            <div>
                                <h2 id="resident-response-title" className="text-xl font-bold">
                                    {responseCopy.title}
                                </h2>
                                <p className="text-muted-foreground mt-2 text-sm leading-6">{responseCopy.intro}</p>
                            </div>
                        </div>

                        {trackedRequest.requestedInformationMessage && (
                            <div className="border-primary/20 bg-secondary/50 mt-6 rounded-xl border p-4">
                                <p className="text-primary text-xs font-bold tracking-wide uppercase">{responseCopy.staff_request_label}</p>
                                <p className="mt-2 text-sm leading-6">{trackedRequest.requestedInformationMessage}</p>
                            </div>
                        )}

                        <form onSubmit={submitResponse} className="mt-6 space-y-5">
                            <div className="sr-only" aria-hidden="true">
                                <Label htmlFor="response_website">Website</Label>
                                <Input
                                    id="response_website"
                                    name="website"
                                    tabIndex={-1}
                                    autoComplete="off"
                                    value={responseForm.data.website}
                                    onChange={(event) => responseForm.setData('website', event.target.value)}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="response_details">{responseCopy.details_label}</Label>
                                <textarea
                                    id="response_details"
                                    className="border-input bg-background text-foreground placeholder:text-muted-foreground focus-visible:ring-ring min-h-32 w-full rounded-md border px-3 py-2 text-base focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-hidden md:text-sm"
                                    minLength={10}
                                    maxLength={2000}
                                    required
                                    aria-describedby="response-details-help response-details-error"
                                    aria-invalid={responseForm.errors.response_details ? true : undefined}
                                    value={responseForm.data.response_details}
                                    onChange={(event) => responseForm.setData('response_details', event.target.value)}
                                />
                                <p id="response-details-help" className="text-muted-foreground text-xs leading-5">
                                    {responseCopy.details_help}
                                </p>
                                <InputError id="response-details-error" message={responseForm.errors.response_details} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="response_attachments">{responseCopy.attachments_label}</Label>
                                <p id="response-attachments-help" className="text-muted-foreground text-xs leading-5">
                                    {responseCopy.attachments_help
                                        .replace(':count', String(attachmentRules.maxFiles))
                                        .replace(':size', String(attachmentRules.maxMegabytes))}
                                </p>
                                <Input
                                    ref={fileInputRef}
                                    id="response_attachments"
                                    className="h-auto py-3"
                                    type="file"
                                    multiple
                                    accept={attachmentRules.accept}
                                    aria-describedby={
                                        attachmentError ? 'response-attachments-help response-attachments-error' : 'response-attachments-help'
                                    }
                                    aria-invalid={attachmentError ? true : undefined}
                                    onChange={(event) => addResponseFiles(event.target.files)}
                                />
                                <InputError id="response-attachments-error" message={attachmentError} />
                                {responseForm.data.attachments.length > 0 && (
                                    <div className="pt-2">
                                        <p className="text-sm font-bold">{responseCopy.selected_files}</p>
                                        <ul className="mt-2 space-y-2">
                                            {responseForm.data.attachments.map((file, index) => (
                                                <li
                                                    key={`${file.name}-${file.lastModified}`}
                                                    className="bg-muted flex items-center justify-between gap-3 rounded-lg p-3 text-sm"
                                                >
                                                    <span className="min-w-0 truncate">{file.name}</span>
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            responseForm.setData(
                                                                'attachments',
                                                                responseForm.data.attachments.filter((_, fileIndex) => fileIndex !== index),
                                                            )
                                                        }
                                                        className="focus-ring shrink-0 rounded-lg p-2 text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/50"
                                                        aria-label={responseCopy.remove_file.replace(':name', file.name)}
                                                    >
                                                        <Trash2 className="size-4" aria-hidden="true" />
                                                    </button>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                            </div>

                            <div className="border-border bg-muted text-muted-foreground flex items-start gap-3 rounded-xl border p-4 text-xs leading-5">
                                <ShieldCheck className="text-primary mt-0.5 size-4 shrink-0" aria-hidden="true" />
                                <p>{responseCopy.privacy_note}</p>
                            </div>

                            <Button type="submit" size="lg" disabled={responseForm.processing}>
                                <Send className="size-4" aria-hidden="true" />
                                {responseForm.processing ? responseCopy.submitting : responseCopy.submit}
                            </Button>
                        </form>
                    </section>
                )}
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
                            {[...trackedRequest.history].reverse().map((event, index) => (
                                <li key={`${event.occurredAt}-${event.status}-${index}`} className="relative">
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
