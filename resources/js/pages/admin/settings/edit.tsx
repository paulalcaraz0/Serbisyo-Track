import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type OfficeSettings } from '@/types/admin';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Save, ShieldCheck } from 'lucide-react';
import { type FormEvent } from 'react';

interface Props {
    officeSettings: { data: OfficeSettings };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Office settings', href: '/admin/settings' },
];

export default function OfficeSettingsEdit({ officeSettings: resource }: Props) {
    const { flash } = usePage<SharedData>().props;
    const settings = resource.data;
    const form = useForm({
        office_name_en: settings.office_name_en,
        office_name_fil: settings.office_name_fil,
        contact_email: settings.contact_email,
        contact_phone: settings.contact_phone,
        address_en: settings.address_en,
        address_fil: settings.address_fil,
        retention_days: String(settings.retention_days),
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, retention_days: Number(data.retention_days) }));
        form.patch(route('admin.settings.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Office and retention settings" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header>
                    <p className="text-primary text-sm font-semibold">Administrator</p>
                    <h1 className="mt-1 text-2xl font-bold tracking-tight">Office and retention settings</h1>
                    <p className="text-muted-foreground mt-2 max-w-3xl text-sm leading-6">
                        Office contact information appears in the public footer. Retention controls when closed requests and private files are
                        permanently purged.
                    </p>
                </header>

                {flash.success && (
                    <div className="max-w-4xl rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-100">
                        {flash.success}
                    </div>
                )}

                <form onSubmit={submit} className="bg-card max-w-4xl space-y-7 rounded-xl border p-5 sm:p-7">
                    <section aria-labelledby="office-details">
                        <h2 id="office-details" className="text-lg font-bold">
                            Public office details
                        </h2>
                        <div className="mt-5 grid gap-5 sm:grid-cols-2">
                            {[
                                ['office_name_en', 'Office name (English)'],
                                ['office_name_fil', 'Office name (Filipino)'],
                                ['contact_email', 'Public email'],
                                ['contact_phone', 'Public phone'],
                                ['address_en', 'Address (English)'],
                                ['address_fil', 'Address (Filipino)'],
                            ].map(([key, label]) => {
                                const field = key as keyof typeof form.data;
                                return (
                                    <div key={key} className="space-y-2">
                                        <Label htmlFor={key}>{label}</Label>
                                        <Input
                                            id={key}
                                            type={key === 'contact_email' ? 'email' : 'text'}
                                            value={form.data[field]}
                                            onChange={(event) => form.setData(field, event.target.value)}
                                            maxLength={key.startsWith('address') ? 255 : 150}
                                        />
                                        <InputError message={form.errors[field]} />
                                    </div>
                                );
                            })}
                        </div>
                    </section>

                    <section className="border-t pt-7" aria-labelledby="retention-settings">
                        <h2 id="retention-settings" className="flex items-center gap-2 text-lg font-bold">
                            <ShieldCheck className="text-primary size-5" />
                            Retention policy
                        </h2>
                        <div className="mt-5 max-w-sm space-y-2">
                            <Label htmlFor="retention_days">Keep closed requests for this many days</Label>
                            <Input
                                id="retention_days"
                                type="number"
                                min={30}
                                max={3650}
                                value={form.data.retention_days}
                                onChange={(event) => form.setData('retention_days', event.target.value)}
                            />
                            <InputError message={form.errors.retention_days} />
                        </div>
                        <p className="text-muted-foreground mt-3 max-w-2xl text-sm leading-6">
                            The scheduled cleanup runs daily at 2:30 AM. It permanently removes eligible closed requests, appointments, activities,
                            and private attachments. Audit records remain.
                        </p>
                    </section>

                    <div className="flex justify-end border-t pt-6">
                        <Button type="submit" disabled={form.processing}>
                            <Save />
                            {form.processing ? 'Saving…' : 'Save settings'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
