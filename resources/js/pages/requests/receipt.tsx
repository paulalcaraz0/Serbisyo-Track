import { Button, buttonVariants } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { CalendarClock, CheckCircle2, FileCheck2, Printer, ShieldCheck } from 'lucide-react';

interface Props {
    receipt: {
        reference: string;
        pin: string | null;
        serviceName: string;
        submittedAt: string | null;
        appointment: { date: string | null; timeWindow: string } | null;
        attachments: { name: string; sizeBytes: number }[];
    };
}

export default function RequestReceipt({ receipt }: Props) {
    const { locale, translations } = usePage<SharedData>().props;
    const copy = translations.requests;
    const formatDate = (value: string | null, options: Intl.DateTimeFormatOptions) =>
        value ? new Intl.DateTimeFormat(locale === 'fil' ? 'fil-PH' : 'en-PH', options).format(new Date(value)) : '—';
    const timeLabel = receipt.appointment?.timeWindow === 'morning' ? copy.morning : copy.afternoon;

    return (
        <PublicLayout>
            <Head title={copy.receipt_meta_title}>
                <meta name="robots" content="noindex,nofollow,noarchive" />
            </Head>
            <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                <article className="border-border bg-card overflow-hidden rounded-3xl border shadow-sm">
                    <div className="bg-primary text-primary-foreground px-6 py-8 sm:px-10">
                        <CheckCircle2 className="size-10" aria-hidden="true" />
                        <p className="mt-5 text-xs font-bold tracking-[0.14em] uppercase">{copy.receipt_eyebrow}</p>
                        <h1 className="mt-2 text-3xl font-bold tracking-[-0.035em]">{copy.receipt_title}</h1>
                        <p className="text-primary-foreground/80 mt-3 max-w-2xl text-sm leading-6">{copy.receipt_intro}</p>
                    </div>
                    <div className="space-y-7 p-6 sm:p-10">
                        <section className="grid gap-4 sm:grid-cols-2" aria-label="Tracking credentials">
                            <div className="border-border bg-muted rounded-2xl border p-5">
                                <p className="text-muted-foreground text-xs font-bold tracking-wide uppercase">{copy.reference}</p>
                                <p className="text-foreground mt-2 font-mono text-xl font-bold break-all">{receipt.reference}</p>
                            </div>
                            <div className="rounded-2xl border border-amber-300 bg-amber-50 p-5 dark:border-amber-800 dark:bg-amber-950/40">
                                <p className="text-xs font-bold tracking-wide text-amber-800 uppercase dark:text-amber-300">{copy.tracking_pin}</p>
                                <p className="mt-2 font-mono text-3xl font-black tracking-[0.18em] text-amber-950 dark:text-amber-100">
                                    {receipt.pin ?? '••••••'}
                                </p>
                            </div>
                        </section>
                        <div className="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-950 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
                            <ShieldCheck className="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                            <div>
                                <h2 className="font-bold">{receipt.pin ? copy.pin_once_title : copy.tracking_pin}</h2>
                                <p className="mt-1 text-sm leading-6">{receipt.pin ? copy.pin_once_body : copy.pin_hidden}</p>
                            </div>
                        </div>
                        <dl className="border-border/60 grid gap-4 border-t pt-7 sm:grid-cols-2">
                            <div>
                                <dt className="text-muted-foreground text-sm font-semibold">{translations.tracking.service}</dt>
                                <dd className="mt-1 font-bold">{receipt.serviceName}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground text-sm font-semibold">{copy.submitted}</dt>
                                <dd className="mt-1 font-bold">{formatDate(receipt.submittedAt, { dateStyle: 'medium', timeStyle: 'short' })}</dd>
                            </div>
                            {receipt.appointment && (
                                <div className="sm:col-span-2">
                                    <dt className="text-muted-foreground flex items-center gap-2 text-sm font-semibold">
                                        <CalendarClock className="size-4" aria-hidden="true" />
                                        {copy.requested_schedule}
                                    </dt>
                                    <dd className="mt-1 font-bold">
                                        {formatDate(receipt.appointment.date, { dateStyle: 'long' })} · {timeLabel}
                                    </dd>
                                </div>
                            )}
                            <div className="sm:col-span-2">
                                <dt className="text-muted-foreground flex items-center gap-2 text-sm font-semibold">
                                    <FileCheck2 className="size-4" aria-hidden="true" />
                                    {copy.receipt_files}
                                </dt>
                                <dd className="mt-2">
                                    {receipt.attachments.length ? (
                                        <ul className="space-y-1 text-sm">
                                            {receipt.attachments.map((file) => (
                                                <li key={file.name}>
                                                    {file.name} ({(file.sizeBytes / 1024).toFixed(1)} KB)
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        copy.none
                                    )}
                                </dd>
                            </div>
                        </dl>
                        <div className="border-border/60 flex flex-wrap gap-3 border-t pt-7" data-print-hidden="true">
                            <Button type="button" variant="outline" onClick={() => window.print()}>
                                <Printer />
                                {copy.print_receipt}
                            </Button>
                            <Link href={route('tracking.show', receipt.reference)} className={buttonVariants()}>
                                {copy.view_status}
                            </Link>
                            <Link href={route('services.index')} className={cn(buttonVariants({ variant: 'ghost' }))}>
                                {copy.another_service}
                            </Link>
                        </div>
                    </div>
                </article>
            </div>
        </PublicLayout>
    );
}
