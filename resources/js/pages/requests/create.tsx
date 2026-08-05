import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft, Check, FileText, LockKeyhole, Paperclip, Trash2 } from 'lucide-react';
import { cloneElement, type FormEvent, type ReactElement, useEffect, useRef, useState } from 'react';

interface Requirement {
    name: string;
    details: string | null;
    is_required: boolean;
}

interface PublicService {
    slug: string;
    name: string;
    appointment_required: boolean;
    requirements: Requirement[];
}

interface RequestFormData {
    [key: string]: string | boolean | File[];
    service_slug: string;
    resident_name: string;
    contact_email: string;
    contact_phone: string;
    preferred_contact: string;
    general_location: string;
    request_details: string;
    appointment_requested: boolean;
    appointment_date: string;
    appointment_time_window: string;
    appointment_note: string;
    attachments: File[];
    privacy_consent: boolean;
    website: string;
}

interface Props {
    service: { data: PublicService };
    appointmentDateBounds: { min: string; max: string };
    attachmentRules: { maxFiles: number; maxMegabytes: number; accept: string };
}

function Field({ id, label, help, error, children }: { id: string; label: string; help?: string; error?: string; children: ReactElement }) {
    const helpId = help ? `${id}-help` : undefined;
    const errorId = error ? `${id}-error` : undefined;
    const describedBy = [helpId, errorId].filter(Boolean).join(' ') || undefined;

    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            {cloneElement(children, {
                'aria-describedby': describedBy,
                'aria-invalid': error ? true : undefined,
            } as React.HTMLAttributes<HTMLElement>)}
            {help && (
                <p id={helpId} className="text-muted-foreground text-xs leading-5">
                    {help}
                </p>
            )}
            <InputError id={errorId} message={error} />
        </div>
    );
}

export default function CreateRequest({ service: resource, appointmentDateBounds, attachmentRules }: Props) {
    const { translations } = usePage<SharedData>().props;
    const copy = translations.requests;
    const service = resource.data;
    const [currentStep, setCurrentStep] = useState(1);
    const errorSummaryRef = useRef<HTMLDivElement>(null);
    const { data, setData, post, processing, errors } = useForm<RequestFormData>({
        service_slug: service.slug,
        resident_name: '',
        contact_email: '',
        contact_phone: '',
        preferred_contact: 'email',
        general_location: '',
        request_details: '',
        appointment_requested: service.appointment_required,
        appointment_date: '',
        appointment_time_window: '',
        appointment_note: '',
        attachments: [],
        privacy_consent: false,
        website: '',
    });
    const errorMessages = [...new Set(Object.values(errors))];
    const attachmentError = Object.entries(errors).find(([key]) => key === 'attachments' || key.startsWith('attachments.'))?.[1];
    const wantsAppointment = service.appointment_required || data.appointment_requested;
    const steps = [copy.steps.contact, copy.steps.details, copy.steps.review];
    const textareaClass =
        'flex min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-base text-foreground placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 md:text-sm';
    const selectClass =
        'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

    useEffect(() => {
        if (errorMessages.length === 0) return;

        const frame = window.requestAnimationFrame(() => errorSummaryRef.current?.focus());

        return () => window.cancelAnimationFrame(frame);
    }, [errors, errorMessages.length]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('requests.store'), {
            forceFormData: true,
            preserveScroll: true,
            onError: (formErrors) => {
                const keys = Object.keys(formErrors);
                const contactFields = ['resident_name', 'contact_email', 'contact_phone', 'preferred_contact', 'general_location'];
                setCurrentStep(keys.some((key) => contactFields.includes(key)) ? 1 : keys.some((key) => key !== 'privacy_consent') ? 2 : 3);
            },
        });
    };

    const addFiles = (files: FileList | null) => {
        if (!files) return;
        setData('attachments', Array.from(files).slice(0, attachmentRules.maxFiles));
    };

    const formatAppointment = () => {
        if (!wantsAppointment || !data.appointment_date) return copy.none;
        const windowLabel = data.appointment_time_window === 'morning' ? copy.morning : copy.afternoon;
        return `${data.appointment_date} · ${windowLabel}`;
    };

    return (
        <PublicLayout>
            <Head title={copy.meta_title}>
                <meta name="robots" content="noindex,nofollow,noarchive" />
            </Head>

            <section className="border-border bg-card border-b">
                <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                    <Link
                        href={route('services.show', service.slug)}
                        className="focus-ring text-primary inline-flex min-h-11 items-center gap-2 rounded-lg text-sm font-bold hover:underline"
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        {copy.back_to_service}
                    </Link>
                    <p className="text-primary mt-6 text-xs font-bold tracking-[0.14em] uppercase">{copy.eyebrow}</p>
                    <h1 className="mt-2 text-3xl font-bold tracking-[-0.035em] sm:text-4xl">{copy.title.replace(':service', service.name)}</h1>
                    <p className="text-muted-foreground mt-4 max-w-3xl leading-7">{copy.intro}</p>
                </div>
            </section>

            <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
                <div className="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-950 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
                    <div className="flex items-start gap-3">
                        <AlertTriangle className="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                        <div>
                            <h2 className="font-bold">{copy.demo_warning_title}</h2>
                            <p className="mt-1 text-sm leading-6">{copy.demo_warning_body}</p>
                        </div>
                    </div>
                </div>

                <nav aria-label={copy.step_of.replace(':current', String(currentStep)).replace(':total', '3')} className="mb-6">
                    <ol className="grid grid-cols-3 gap-2">
                        {steps.map((label, index) => {
                            const number = index + 1;
                            return (
                                <li
                                    key={label}
                                    aria-current={number === currentStep ? 'step' : undefined}
                                    className={`rounded-xl border px-3 py-3 text-center text-xs font-bold sm:text-sm ${number === currentStep ? 'border-primary bg-secondary text-primary' : number < currentStep ? 'bg-card border-emerald-200 text-emerald-800 dark:border-emerald-800 dark:text-emerald-300' : 'border-border bg-card text-muted-foreground'}`}
                                >
                                    <span className="mx-auto mb-1 flex size-6 items-center justify-center rounded-full bg-current/10">
                                        {number < currentStep ? <Check className="size-3.5" aria-hidden="true" /> : number}
                                    </span>
                                    {label}
                                </li>
                            );
                        })}
                    </ol>
                </nav>

                {errorMessages.length > 0 && (
                    <div
                        ref={errorSummaryRef}
                        role="alert"
                        tabIndex={-1}
                        className="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-950 dark:border-red-900 dark:bg-red-950/40 dark:text-red-100"
                    >
                        <h2 className="font-bold">{copy.error_title}</h2>
                        <ul className="mt-2 list-disc space-y-1 pl-5 text-sm">
                            {errorMessages.map((message) => (
                                <li key={message}>{message}</li>
                            ))}
                        </ul>
                    </div>
                )}

                <form onSubmit={submit} className="border-border bg-card rounded-2xl border p-5 shadow-sm sm:p-8">
                    <div className="sr-only" aria-hidden="true">
                        <label htmlFor="website">Website</label>
                        <input
                            id="website"
                            name="website"
                            tabIndex={-1}
                            autoComplete="off"
                            value={data.website}
                            onChange={(e) => setData('website', e.target.value)}
                        />
                    </div>

                    {currentStep === 1 && (
                        <section aria-labelledby="contact-step-title">
                            <h2 id="contact-step-title" className="text-2xl font-bold tracking-[-0.025em]">
                                {copy.contact_title}
                            </h2>
                            <p className="text-muted-foreground mt-2 text-sm leading-6">{copy.contact_intro}</p>
                            <div className="mt-7 grid gap-6 sm:grid-cols-2">
                                <div className="sm:col-span-2">
                                    <Field id="resident_name" label={copy.resident_name} help={copy.resident_name_help} error={errors.resident_name}>
                                        <Input
                                            id="resident_name"
                                            value={data.resident_name}
                                            onChange={(e) => setData('resident_name', e.target.value)}
                                            maxLength={100}
                                            autoComplete="name"
                                        />
                                    </Field>
                                </div>
                                <Field id="contact_email" label={copy.email} error={errors.contact_email}>
                                    <Input
                                        id="contact_email"
                                        type="email"
                                        value={data.contact_email}
                                        onChange={(e) => setData('contact_email', e.target.value)}
                                        maxLength={150}
                                        autoComplete="email"
                                    />
                                </Field>
                                <Field id="contact_phone" label={copy.phone} error={errors.contact_phone}>
                                    <Input
                                        id="contact_phone"
                                        type="tel"
                                        value={data.contact_phone}
                                        onChange={(e) => setData('contact_phone', e.target.value)}
                                        maxLength={30}
                                        autoComplete="tel"
                                    />
                                </Field>
                                <Field id="preferred_contact" label={copy.preferred_contact} error={errors.preferred_contact}>
                                    <select
                                        id="preferred_contact"
                                        className={selectClass}
                                        value={data.preferred_contact}
                                        onChange={(e) => setData('preferred_contact', e.target.value)}
                                    >
                                        <option value="email">{copy.contact_by_email}</option>
                                        <option value="phone">{copy.contact_by_phone}</option>
                                    </select>
                                </Field>
                                <Field
                                    id="general_location"
                                    label={copy.general_location}
                                    help={copy.general_location_help}
                                    error={errors.general_location}
                                >
                                    <Input
                                        id="general_location"
                                        value={data.general_location}
                                        onChange={(e) => setData('general_location', e.target.value)}
                                        maxLength={255}
                                        autoComplete="address-level3"
                                    />
                                </Field>
                            </div>
                        </section>
                    )}

                    {currentStep === 2 && (
                        <section aria-labelledby="details-step-title">
                            <h2 id="details-step-title" className="text-2xl font-bold tracking-[-0.025em]">
                                {copy.details_title}
                            </h2>
                            <p className="text-muted-foreground mt-2 text-sm leading-6">{copy.details_intro}</p>
                            <div className="mt-7 space-y-8">
                                <Field
                                    id="request_details"
                                    label={copy.request_details}
                                    help={copy.request_details_help}
                                    error={errors.request_details}
                                >
                                    <textarea
                                        id="request_details"
                                        className={textareaClass}
                                        value={data.request_details}
                                        onChange={(e) => setData('request_details', e.target.value)}
                                        minLength={20}
                                        maxLength={2000}
                                    />
                                </Field>

                                <fieldset className="border-border rounded-xl border p-5">
                                    <legend className="px-2 text-lg font-bold">{copy.appointment_title}</legend>
                                    <p className="text-muted-foreground text-sm leading-6">
                                        {service.appointment_required ? copy.appointment_required : copy.appointment_optional}
                                    </p>
                                    {!service.appointment_required && (
                                        <label
                                            htmlFor="appointment_requested"
                                            className="mt-4 flex min-h-11 cursor-pointer items-center gap-3 font-semibold"
                                        >
                                            <Checkbox
                                                id="appointment_requested"
                                                checked={data.appointment_requested}
                                                onCheckedChange={(checked) => setData('appointment_requested', checked === true)}
                                            />
                                            <span>{copy.request_appointment}</span>
                                        </label>
                                    )}
                                    {wantsAppointment && (
                                        <div className="mt-5 grid gap-5 sm:grid-cols-2">
                                            <Field id="appointment_date" label={copy.appointment_date} error={errors.appointment_date}>
                                                <Input
                                                    id="appointment_date"
                                                    type="date"
                                                    min={appointmentDateBounds.min}
                                                    max={appointmentDateBounds.max}
                                                    value={data.appointment_date}
                                                    onChange={(e) => setData('appointment_date', e.target.value)}
                                                />
                                            </Field>
                                            <Field id="appointment_time_window" label={copy.appointment_time} error={errors.appointment_time_window}>
                                                <select
                                                    id="appointment_time_window"
                                                    className={selectClass}
                                                    value={data.appointment_time_window}
                                                    onChange={(e) => setData('appointment_time_window', e.target.value)}
                                                >
                                                    <option value="">—</option>
                                                    <option value="morning">{copy.morning}</option>
                                                    <option value="afternoon">{copy.afternoon}</option>
                                                </select>
                                            </Field>
                                            <div className="sm:col-span-2">
                                                <Field
                                                    id="appointment_note"
                                                    label={copy.appointment_note}
                                                    help={copy.appointment_help}
                                                    error={errors.appointment_note}
                                                >
                                                    <textarea
                                                        id="appointment_note"
                                                        className={`${textareaClass} min-h-24`}
                                                        value={data.appointment_note}
                                                        onChange={(e) => setData('appointment_note', e.target.value)}
                                                        maxLength={500}
                                                    />
                                                </Field>
                                            </div>
                                        </div>
                                    )}
                                </fieldset>

                                <section aria-labelledby="attachments-title" className="border-border rounded-xl border p-5">
                                    <div className="flex items-start gap-3">
                                        <Paperclip className="text-primary mt-0.5 size-5" aria-hidden="true" />
                                        <div>
                                            <h3 id="attachments-title" className="font-bold">
                                                {copy.attachments_title}
                                            </h3>
                                            <p id="attachments-help" className="text-muted-foreground mt-1 text-sm leading-6">
                                                {copy.attachments_help
                                                    .replace(':count', String(attachmentRules.maxFiles))
                                                    .replace(':size', String(attachmentRules.maxMegabytes))}
                                            </p>
                                        </div>
                                    </div>
                                    <Input
                                        id="attachments"
                                        className="mt-4 h-auto py-3"
                                        type="file"
                                        multiple
                                        accept={attachmentRules.accept}
                                        aria-labelledby="attachments-title"
                                        aria-describedby={attachmentError ? 'attachments-help attachments-error' : 'attachments-help'}
                                        aria-invalid={attachmentError ? true : undefined}
                                        onChange={(e) => addFiles(e.target.files)}
                                    />
                                    <InputError id="attachments-error" message={attachmentError} className="mt-2" />
                                    {data.attachments.length > 0 && (
                                        <div className="mt-4">
                                            <p className="text-sm font-bold">{copy.selected_files}</p>
                                            <ul className="mt-2 space-y-2">
                                                {data.attachments.map((file, index) => (
                                                    <li
                                                        key={`${file.name}-${file.lastModified}`}
                                                        className="bg-muted flex items-center justify-between gap-3 rounded-lg p-3 text-sm"
                                                    >
                                                        <span className="min-w-0 truncate">{file.name}</span>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                setData(
                                                                    'attachments',
                                                                    data.attachments.filter((_, fileIndex) => fileIndex !== index),
                                                                )
                                                            }
                                                            className="focus-ring shrink-0 rounded-lg p-2 text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/50"
                                                            aria-label={copy.remove_file.replace(':name', file.name)}
                                                        >
                                                            <Trash2 className="size-4" aria-hidden="true" />
                                                        </button>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    )}
                                </section>
                            </div>
                        </section>
                    )}

                    {currentStep === 3 && (
                        <section aria-labelledby="review-step-title">
                            <h2 id="review-step-title" className="text-2xl font-bold tracking-[-0.025em]">
                                {copy.review_title}
                            </h2>
                            <p className="text-muted-foreground mt-2 text-sm leading-6">{copy.review_intro}</p>
                            <dl className="mt-7 grid gap-4 sm:grid-cols-2">
                                <div className="bg-muted rounded-xl p-4">
                                    <dt className="text-muted-foreground text-xs font-bold tracking-wide uppercase">{copy.review_service}</dt>
                                    <dd className="mt-2 font-bold">{service.name}</dd>
                                </div>
                                <div className="bg-muted rounded-xl p-4">
                                    <dt className="text-muted-foreground text-xs font-bold tracking-wide uppercase">{copy.review_contact}</dt>
                                    <dd className="mt-2 font-bold">
                                        {data.preferred_contact === 'email' ? copy.contact_by_email : copy.contact_by_phone}
                                    </dd>
                                </div>
                                <div className="bg-muted rounded-xl p-4 sm:col-span-2">
                                    <dt className="text-muted-foreground text-xs font-bold tracking-wide uppercase">{copy.review_details}</dt>
                                    <dd className="mt-2 text-sm leading-6 whitespace-pre-line">{data.request_details || '—'}</dd>
                                </div>
                                <div className="bg-muted rounded-xl p-4">
                                    <dt className="text-muted-foreground text-xs font-bold tracking-wide uppercase">{copy.review_appointment}</dt>
                                    <dd className="mt-2 text-sm font-semibold">{formatAppointment()}</dd>
                                </div>
                                <div className="bg-muted rounded-xl p-4">
                                    <dt className="text-muted-foreground text-xs font-bold tracking-wide uppercase">{copy.review_attachments}</dt>
                                    <dd className="mt-2 text-sm font-semibold">{data.attachments.length || copy.none}</dd>
                                </div>
                            </dl>
                            <div className="border-primary/30 bg-secondary/70 mt-7 rounded-xl border p-5">
                                <label htmlFor="privacy_consent" className="flex cursor-pointer items-start gap-3">
                                    <Checkbox
                                        id="privacy_consent"
                                        className="mt-0.5"
                                        checked={data.privacy_consent}
                                        aria-describedby={
                                            errors.privacy_consent ? 'privacy-consent-help privacy-consent-error' : 'privacy-consent-help'
                                        }
                                        aria-invalid={errors.privacy_consent ? true : undefined}
                                        onCheckedChange={(checked) => setData('privacy_consent', checked === true)}
                                    />
                                    <span className="text-sm leading-6">{copy.privacy_consent}</span>
                                </label>
                                <p id="privacy-consent-help" className="text-muted-foreground mt-3 pl-7 text-xs">
                                    <Link className="text-primary font-bold underline" href={route('privacy')}>
                                        {translations.common.privacy}
                                    </Link>{' '}
                                    · {copy.consent_help}
                                </p>
                                <InputError id="privacy-consent-error" message={errors.privacy_consent} className="mt-2 pl-7" />
                            </div>
                        </section>
                    )}

                    <div className="border-border/60 mt-8 flex flex-wrap items-center justify-between gap-3 border-t pt-6">
                        {currentStep > 1 ? (
                            <Button type="button" variant="outline" onClick={() => setCurrentStep((step) => step - 1)} disabled={processing}>
                                <ArrowLeft />
                                {copy.back}
                            </Button>
                        ) : (
                            <span />
                        )}
                        {currentStep < 3 ? (
                            <Button type="button" onClick={() => setCurrentStep((step) => step + 1)}>
                                {copy.continue}
                            </Button>
                        ) : (
                            <Button type="submit" disabled={processing}>
                                <LockKeyhole />
                                {processing ? copy.submitting : copy.submit}
                            </Button>
                        )}
                    </div>
                </form>

                <section className="border-border bg-card mt-6 rounded-2xl border p-5" aria-labelledby="requirements-reminder">
                    <div className="flex items-center gap-2">
                        <FileText className="text-primary size-5" aria-hidden="true" />
                        <h2 id="requirements-reminder" className="font-bold">
                            {translations.services.requirements}
                        </h2>
                    </div>
                    <ul className="text-muted-foreground mt-3 space-y-2 text-sm">
                        {service.requirements.map((requirement) => (
                            <li key={requirement.name}>
                                • {requirement.name}{' '}
                                {requirement.is_required ? `(${translations.services.required})` : `(${translations.services.optional})`}
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </PublicLayout>
    );
}
