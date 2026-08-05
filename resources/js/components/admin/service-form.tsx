import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type AdminRequirement, type AdminService } from '@/types/services';
import { useForm } from '@inertiajs/react';
import { Plus, Save, Trash2 } from 'lucide-react';
import { type FormEvent } from 'react';

interface ServiceFormData {
    [key: string]: string | boolean | string[] | AdminRequirement[];

    name_en: string;
    name_fil: string;
    description_en: string;
    description_fil: string;
    eligibility_en: string;
    eligibility_fil: string;
    fee_pesos: string;
    processing_time_en: string;
    processing_time_fil: string;
    target_business_days: string;
    office_hours_en: string;
    office_hours_fil: string;
    procedure_steps_en: string[];
    procedure_steps_fil: string[];
    appointment_required: boolean;
    contact_email: string;
    contact_phone: string;
    is_active: boolean;
    requirements: AdminRequirement[];
}

const blankRequirement: AdminRequirement = {
    name_en: '',
    name_fil: '',
    details_en: '',
    details_fil: '',
    is_required: true,
};

function Field({ id, label, error, children }: { id: string; label: string; error?: string; children: React.ReactNode }) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            {children}
            <InputError message={error} />
        </div>
    );
}

export function ServiceForm({ service }: { service?: AdminService }) {
    const { data, setData, post, put, processing, errors, transform } = useForm<ServiceFormData>({
        name_en: service?.name_en ?? '',
        name_fil: service?.name_fil ?? '',
        description_en: service?.description_en ?? '',
        description_fil: service?.description_fil ?? '',
        eligibility_en: service?.eligibility_en ?? '',
        eligibility_fil: service?.eligibility_fil ?? '',
        fee_pesos: service ? (service.fee_centavos / 100).toFixed(2) : '0.00',
        processing_time_en: service?.processing_time_en ?? '',
        processing_time_fil: service?.processing_time_fil ?? '',
        target_business_days: String(service?.target_business_days ?? 3),
        office_hours_en: service?.office_hours_en ?? '',
        office_hours_fil: service?.office_hours_fil ?? '',
        procedure_steps_en: service?.procedure_steps_en ?? [''],
        procedure_steps_fil: service?.procedure_steps_fil ?? [''],
        appointment_required: service?.appointment_required ?? false,
        contact_email: service?.contact_email ?? '',
        contact_phone: service?.contact_phone ?? '',
        is_active: service?.is_active ?? false,
        requirements: service?.requirements.length ? service.requirements : [{ ...blankRequirement }],
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        transform((formData) => ({
            ...formData,
            fee_centavos: Math.round(Number(formData.fee_pesos) * 100),
            target_business_days: Number(formData.target_business_days),
        }));

        if (service) {
            put(route('admin.services.update', service.slug), { preserveScroll: true });
        } else {
            post(route('admin.services.store'));
        }
    };

    const setStep = (language: 'en' | 'fil', index: number, value: string) => {
        const key = language === 'en' ? 'procedure_steps_en' : 'procedure_steps_fil';
        const steps = [...data[key]];
        steps[index] = value;
        setData(key, steps);
    };

    const addStep = () => {
        setData('procedure_steps_en', [...data.procedure_steps_en, '']);
        setData('procedure_steps_fil', [...data.procedure_steps_fil, '']);
    };

    const removeStep = (index: number) => {
        if (data.procedure_steps_en.length === 1) return;
        setData(
            'procedure_steps_en',
            data.procedure_steps_en.filter((_, itemIndex) => itemIndex !== index),
        );
        setData(
            'procedure_steps_fil',
            data.procedure_steps_fil.filter((_, itemIndex) => itemIndex !== index),
        );
    };

    const updateRequirement = <K extends keyof AdminRequirement>(index: number, key: K, value: AdminRequirement[K]) => {
        const requirements = data.requirements.map((requirement, itemIndex) =>
            itemIndex === index ? { ...requirement, [key]: value } : requirement,
        );
        setData('requirements', requirements);
    };

    const errorMessages = [...new Set(Object.values(errors))];
    const textareaClass =
        'flex min-h-28 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 md:text-sm';

    return (
        <form onSubmit={submit} className="space-y-6" noValidate>
            {errorMessages.length > 0 && (
                <div
                    role="alert"
                    aria-live="assertive"
                    className="border-destructive/30 bg-destructive/10 text-destructive rounded-xl border p-4 text-sm"
                >
                    <p className="font-bold">Please correct the following:</p>
                    <ul className="mt-2 list-disc space-y-1 pl-5">
                        {errorMessages.map((message) => (
                            <li key={message}>{message}</li>
                        ))}
                    </ul>
                </div>
            )}

            <section className="bg-card rounded-xl border p-5" aria-labelledby="service-content-title">
                <h2 id="service-content-title" className="text-lg font-bold">
                    Public service content
                </h2>
                <p className="text-muted-foreground mt-1 text-sm">
                    Both languages are required so residents receive complete information after switching languages.
                </p>
                <div className="mt-5 grid gap-5 lg:grid-cols-2">
                    <Field id="name_en" label="Service name — English" error={errors.name_en}>
                        <Input id="name_en" value={data.name_en} onChange={(e) => setData('name_en', e.target.value)} required maxLength={150} />
                    </Field>
                    <Field id="name_fil" label="Service name — Filipino" error={errors.name_fil}>
                        <Input id="name_fil" value={data.name_fil} onChange={(e) => setData('name_fil', e.target.value)} required maxLength={150} />
                    </Field>
                    <Field id="description_en" label="Plain-language description — English" error={errors.description_en}>
                        <textarea
                            id="description_en"
                            className={textareaClass}
                            value={data.description_en}
                            onChange={(e) => setData('description_en', e.target.value)}
                            required
                            maxLength={3000}
                        />
                    </Field>
                    <Field id="description_fil" label="Plain-language description — Filipino" error={errors.description_fil}>
                        <textarea
                            id="description_fil"
                            className={textareaClass}
                            value={data.description_fil}
                            onChange={(e) => setData('description_fil', e.target.value)}
                            required
                            maxLength={3000}
                        />
                    </Field>
                    <Field id="eligibility_en" label="Eligibility — English" error={errors.eligibility_en}>
                        <textarea
                            id="eligibility_en"
                            className={textareaClass}
                            value={data.eligibility_en}
                            onChange={(e) => setData('eligibility_en', e.target.value)}
                            required
                            maxLength={2000}
                        />
                    </Field>
                    <Field id="eligibility_fil" label="Eligibility — Filipino" error={errors.eligibility_fil}>
                        <textarea
                            id="eligibility_fil"
                            className={textareaClass}
                            value={data.eligibility_fil}
                            onChange={(e) => setData('eligibility_fil', e.target.value)}
                            required
                            maxLength={2000}
                        />
                    </Field>
                </div>
            </section>

            <section className="bg-card rounded-xl border p-5" aria-labelledby="service-operations-title">
                <h2 id="service-operations-title" className="text-lg font-bold">
                    Fee, schedule, and contact
                </h2>
                <div className="mt-5 grid gap-5 lg:grid-cols-2">
                    <Field id="fee_pesos" label="Fee in Philippine pesos" error={errors.fee_pesos}>
                        <Input
                            id="fee_pesos"
                            type="number"
                            min="0"
                            max="100000"
                            step="0.01"
                            value={data.fee_pesos}
                            onChange={(e) => setData('fee_pesos', e.target.value)}
                            required
                        />
                    </Field>
                    <div />
                    <Field id="processing_time_en" label="Processing time — English" error={errors.processing_time_en}>
                        <Input
                            id="processing_time_en"
                            value={data.processing_time_en}
                            onChange={(e) => setData('processing_time_en', e.target.value)}
                            required
                            maxLength={255}
                        />
                    </Field>
                    <Field id="processing_time_fil" label="Processing time — Filipino" error={errors.processing_time_fil}>
                        <Input
                            id="processing_time_fil"
                            value={data.processing_time_fil}
                            onChange={(e) => setData('processing_time_fil', e.target.value)}
                            required
                            maxLength={255}
                        />
                    </Field>
                    <Field id="target_business_days" label="Internal target in business days" error={errors.target_business_days}>
                        <Input
                            id="target_business_days"
                            type="number"
                            min="1"
                            max="60"
                            step="1"
                            value={data.target_business_days}
                            onChange={(e) => setData('target_business_days', e.target.value)}
                            required
                        />
                    </Field>
                    <div />
                    <Field id="office_hours_en" label="Office hours — English" error={errors.office_hours_en}>
                        <Input
                            id="office_hours_en"
                            value={data.office_hours_en}
                            onChange={(e) => setData('office_hours_en', e.target.value)}
                            required
                            maxLength={255}
                        />
                    </Field>
                    <Field id="office_hours_fil" label="Office hours — Filipino" error={errors.office_hours_fil}>
                        <Input
                            id="office_hours_fil"
                            value={data.office_hours_fil}
                            onChange={(e) => setData('office_hours_fil', e.target.value)}
                            required
                            maxLength={255}
                        />
                    </Field>
                    <Field id="contact_email" label="Contact email" error={errors.contact_email}>
                        <Input
                            id="contact_email"
                            type="email"
                            value={data.contact_email}
                            onChange={(e) => setData('contact_email', e.target.value)}
                            maxLength={255}
                        />
                    </Field>
                    <Field id="contact_phone" label="Contact phone" error={errors.contact_phone}>
                        <Input
                            id="contact_phone"
                            value={data.contact_phone}
                            onChange={(e) => setData('contact_phone', e.target.value)}
                            maxLength={30}
                        />
                    </Field>
                </div>
                <div className="mt-5 flex flex-col gap-3 sm:flex-row sm:gap-8">
                    <label className="flex min-h-11 items-center gap-3 text-sm font-semibold">
                        <input
                            type="checkbox"
                            className="border-input size-5 rounded"
                            checked={data.appointment_required}
                            onChange={(e) => setData('appointment_required', e.target.checked)}
                        />
                        Appointment required
                    </label>
                    <label className="flex min-h-11 items-center gap-3 text-sm font-semibold">
                        <input
                            type="checkbox"
                            className="border-input size-5 rounded"
                            checked={data.is_active}
                            onChange={(e) => setData('is_active', e.target.checked)}
                            disabled={Boolean(service?.archived_at)}
                        />
                        Visible in the public directory
                    </label>
                </div>
            </section>

            <section className="bg-card rounded-xl border p-5" aria-labelledby="procedure-form-title">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="procedure-form-title" className="text-lg font-bold">
                            Procedure steps
                        </h2>
                        <p className="text-muted-foreground mt-1 text-sm">Keep paired English and Filipino steps in the same order.</p>
                    </div>
                    <Button type="button" variant="outline" onClick={addStep}>
                        <Plus />
                        Add step
                    </Button>
                </div>
                <div className="mt-5 space-y-5">
                    {data.procedure_steps_en.map((step, index) => (
                        <fieldset key={index} className="rounded-lg border p-4">
                            <legend className="px-2 text-sm font-bold">Step {index + 1}</legend>
                            <div className="grid gap-4 lg:grid-cols-2">
                                <Field id={`procedure_en_${index}`} label="English">
                                    <textarea
                                        id={`procedure_en_${index}`}
                                        className={textareaClass}
                                        value={step}
                                        onChange={(e) => setStep('en', index, e.target.value)}
                                        required
                                    />
                                </Field>
                                <Field id={`procedure_fil_${index}`} label="Filipino">
                                    <textarea
                                        id={`procedure_fil_${index}`}
                                        className={textareaClass}
                                        value={data.procedure_steps_fil[index] ?? ''}
                                        onChange={(e) => setStep('fil', index, e.target.value)}
                                        required
                                    />
                                </Field>
                            </div>
                            {data.procedure_steps_en.length > 1 && (
                                <Button type="button" variant="ghost" className="text-destructive mt-3" onClick={() => removeStep(index)}>
                                    <Trash2 />
                                    Remove step
                                </Button>
                            )}
                        </fieldset>
                    ))}
                </div>
            </section>

            <section className="bg-card rounded-xl border p-5" aria-labelledby="requirements-form-title">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="requirements-form-title" className="text-lg font-bold">
                            Requirements
                        </h2>
                        <p className="text-muted-foreground mt-1 text-sm">At least one requirement is required.</p>
                    </div>
                    <Button type="button" variant="outline" onClick={() => setData('requirements', [...data.requirements, { ...blankRequirement }])}>
                        <Plus />
                        Add requirement
                    </Button>
                </div>
                <div className="mt-5 space-y-5">
                    {data.requirements.map((requirement, index) => (
                        <fieldset key={index} className="rounded-lg border p-4">
                            <legend className="px-2 text-sm font-bold">Requirement {index + 1}</legend>
                            <div className="grid gap-4 lg:grid-cols-2">
                                <Field id={`requirement_name_en_${index}`} label="Name — English">
                                    <Input
                                        id={`requirement_name_en_${index}`}
                                        value={requirement.name_en}
                                        onChange={(e) => updateRequirement(index, 'name_en', e.target.value)}
                                        required
                                    />
                                </Field>
                                <Field id={`requirement_name_fil_${index}`} label="Name — Filipino">
                                    <Input
                                        id={`requirement_name_fil_${index}`}
                                        value={requirement.name_fil}
                                        onChange={(e) => updateRequirement(index, 'name_fil', e.target.value)}
                                        required
                                    />
                                </Field>
                                <Field id={`requirement_details_en_${index}`} label="Details — English">
                                    <textarea
                                        id={`requirement_details_en_${index}`}
                                        className={textareaClass}
                                        value={requirement.details_en ?? ''}
                                        onChange={(e) => updateRequirement(index, 'details_en', e.target.value)}
                                    />
                                </Field>
                                <Field id={`requirement_details_fil_${index}`} label="Details — Filipino">
                                    <textarea
                                        id={`requirement_details_fil_${index}`}
                                        className={textareaClass}
                                        value={requirement.details_fil ?? ''}
                                        onChange={(e) => updateRequirement(index, 'details_fil', e.target.value)}
                                    />
                                </Field>
                            </div>
                            <div className="mt-3 flex flex-wrap items-center justify-between gap-3">
                                <label className="flex min-h-11 items-center gap-3 text-sm font-semibold">
                                    <input
                                        type="checkbox"
                                        className="border-input size-5 rounded"
                                        checked={requirement.is_required}
                                        onChange={(e) => updateRequirement(index, 'is_required', e.target.checked)}
                                    />
                                    Required from resident
                                </label>
                                {data.requirements.length > 1 && (
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        className="text-destructive"
                                        onClick={() =>
                                            setData(
                                                'requirements',
                                                data.requirements.filter((_, itemIndex) => itemIndex !== index),
                                            )
                                        }
                                    >
                                        <Trash2 />
                                        Remove
                                    </Button>
                                )}
                            </div>
                        </fieldset>
                    ))}
                </div>
            </section>

            <div className="bg-background/95 sticky bottom-3 flex justify-end rounded-xl border p-4 shadow-lg backdrop-blur">
                <Button type="submit" size="lg" disabled={processing}>
                    <Save />
                    {processing ? 'Saving…' : service ? 'Save changes' : 'Create service'}
                </Button>
            </div>
        </form>
    );
}
