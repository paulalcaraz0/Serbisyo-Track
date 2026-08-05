import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type RequestActivity, type StaffRequestRecord } from '@/types/requests';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { AlertTriangle, CalendarClock, Download, FileText, History, LockKeyhole, MessageSquareText, Save, UserRoundCheck } from 'lucide-react';
import { type FormEvent } from 'react';

interface Props {
    requestRecord: { data: StaffRequestRecord };
    staffOptions: { id: number; name: string; role: string }[];
    allowedTransitions: { value: string; label: string }[];
}

const activityLabels: Record<RequestActivity['event_type'], string> = {
    submitted: 'Request submitted',
    assignment: 'Assignment changed',
    status_change: 'Status changed',
    internal_note: 'Internal note',
    appointment: 'Appointment updated',
};

export default function RequestWorkspace({ requestRecord: resource, staffOptions, allowedTransitions }: Props) {
    const record = resource.data;
    const { auth, flash, translations } = usePage<SharedData>().props;
    const currentUser = auth.user!;
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Requests', href: '/staff/requests' },
        { title: record.reference, href: route('staff.requests.show', record.reference) },
    ];
    const assignmentForm = useForm<{ assignee_id: string }>({ assignee_id: record.assignee ? String(record.assignee.id) : '' });
    const transitionForm = useForm<{ status: string; public_message_en: string; public_message_fil: string; private_note: string }>({
        status: '',
        public_message_en: '',
        public_message_fil: '',
        private_note: '',
    });
    const noteForm = useForm<{ body: string }>({ body: '' });
    const appointmentForm = useForm<{ status: string; confirmed_start_at: string; private_note: string }>({
        status: 'confirmed',
        confirmed_start_at: '',
        private_note: '',
    });
    const textareaClass = 'bg-background min-h-24 w-full rounded-md border px-3 py-2 text-sm';
    const selectClass = 'bg-background h-10 w-full rounded-md border px-3 text-sm';
    const date = (value: string | null, withTime = true) =>
        value
            ? new Intl.DateTimeFormat('en-PH', withTime ? { dateStyle: 'medium', timeStyle: 'short' } : { dateStyle: 'long' }).format(new Date(value))
            : '—';
    const terminal = ['completed', 'rejected', 'cancelled'].includes(record.status);
    const canShowAssignment =
        record.permissions.assign && (currentUser.role === 'administrator' || record.assignee === null || record.assignee.id === currentUser.id);

    const submitAssignment = (event: FormEvent) => {
        event.preventDefault();
        if (currentUser.role !== 'administrator') {
            assignmentForm.transform(() => ({ assignee_id: record.assignee ? '' : String(currentUser.id) }));
        }
        assignmentForm.patch(route('staff.requests.assignment', record.reference), { preserveScroll: true, errorBag: 'assignment' });
    };
    const submitTransition = (event: FormEvent) => {
        event.preventDefault();
        transitionForm.post(route('staff.requests.transitions', record.reference), {
            preserveScroll: true,
            errorBag: 'transition',
            onSuccess: () => transitionForm.reset(),
        });
    };
    const submitNote = (event: FormEvent) => {
        event.preventDefault();
        noteForm.post(route('staff.requests.notes', record.reference), { preserveScroll: true, errorBag: 'note', onSuccess: () => noteForm.reset() });
    };
    const submitAppointment = (event: FormEvent) => {
        event.preventDefault();
        appointmentForm.patch(route('staff.requests.appointment', record.reference), {
            preserveScroll: true,
            errorBag: 'appointment',
            onSuccess: () => appointmentForm.reset('private_note'),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Request ${record.reference}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <Link href={route('staff.requests.index')} className="text-primary text-sm font-semibold hover:underline">
                            ← Back to request queue
                        </Link>
                        <h1 className="mt-3 font-mono text-2xl font-bold tracking-tight">{record.reference}</h1>
                        <p className="text-muted-foreground mt-1 font-semibold">{record.service.name}</p>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="bg-secondary text-secondary-foreground rounded-full px-3 py-1 text-sm font-bold">{record.status_label}</span>
                        {record.is_overdue && (
                            <span className="flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-sm font-bold text-red-800">
                                <AlertTriangle className="size-4" />
                                Overdue
                            </span>
                        )}
                    </div>
                </header>

                {flash.success && (
                    <div
                        role="status"
                        aria-live="polite"
                        className="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900"
                    >
                        {flash.success}
                    </div>
                )}

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Request summary">
                    <Summary label="Submitted" value={date(record.submitted_at)} />
                    <Summary label="Due target" value={date(record.due_at)} warning={record.is_overdue} />
                    <Summary label="Assigned to" value={record.assignee?.name ?? 'Unassigned'} />
                    <Summary label="Contact channel" value={record.resident.preferred_contact} />
                </section>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_23rem]">
                    <div className="space-y-6">
                        <section className="bg-card rounded-xl border p-5 sm:p-6" aria-labelledby="resident-request">
                            <h2 id="resident-request" className="text-lg font-bold">
                                Resident request
                            </h2>
                            <dl className="mt-5 grid gap-5 sm:grid-cols-2">
                                <Detail label="Name" value={record.resident.name} />
                                <Detail label="General location" value={record.resident.general_location ?? 'Not provided'} />
                                <Detail label="Email" value={record.resident.email ?? 'Not provided'} />
                                <Detail label="Phone" value={record.resident.phone ?? 'Not provided'} />
                                <div className="sm:col-span-2">
                                    <dt className="text-muted-foreground text-xs font-bold uppercase">Request details</dt>
                                    <dd className="mt-2 rounded-lg bg-slate-50 p-4 text-sm leading-6 whitespace-pre-line dark:bg-slate-900">
                                        {record.request_details}
                                    </dd>
                                </div>
                            </dl>
                            <p className="text-muted-foreground mt-4 text-xs">Privacy consent recorded {date(record.consented_at)}.</p>
                        </section>

                        {record.appointment && (
                            <section className="bg-card rounded-xl border p-5 sm:p-6" aria-labelledby="appointment-details">
                                <h2 id="appointment-details" className="flex items-center gap-2 text-lg font-bold">
                                    <CalendarClock className="text-primary size-5" />
                                    Appointment
                                </h2>
                                <dl className="mt-4 grid gap-4 sm:grid-cols-2">
                                    <Detail
                                        label="Resident preference"
                                        value={`${date(record.appointment.preferred_date, false)} · ${record.appointment.preferred_time_window}`}
                                    />
                                    <Detail label="Appointment status" value={translations.appointment_statuses[record.appointment.status]} />
                                    <Detail label="Confirmed schedule" value={date(record.appointment.confirmed_start_at)} />
                                    <Detail label="Resident note" value={record.appointment.resident_note ?? 'None'} />
                                </dl>
                            </section>
                        )}

                        <section className="bg-card rounded-xl border p-5 sm:p-6" aria-labelledby="attachments">
                            <h2 id="attachments" className="flex items-center gap-2 text-lg font-bold">
                                <FileText className="text-primary size-5" />
                                Private attachments
                            </h2>
                            {record.attachments.length ? (
                                <ul className="mt-4 divide-y">
                                    {record.attachments.map((file) => (
                                        <li key={file.public_id} className="flex flex-wrap items-center justify-between gap-3 py-3">
                                            <div>
                                                <p className="font-semibold">{file.name}</p>
                                                <p className="text-muted-foreground text-xs">
                                                    {file.mime_type} · {(file.size_bytes / 1024).toFixed(1)} KB
                                                </p>
                                            </div>
                                            <a
                                                className="focus-ring inline-flex min-h-10 items-center gap-2 rounded-lg border px-3 text-sm font-semibold"
                                                href={route('staff.requests.attachments.show', {
                                                    serviceRequest: record.reference,
                                                    attachment: file.public_id,
                                                })}
                                            >
                                                <Download className="size-4" />
                                                Download
                                            </a>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="text-muted-foreground mt-3 text-sm">No attachments.</p>
                            )}
                        </section>

                        <section className="bg-card rounded-xl border p-5 sm:p-6" aria-labelledby="activity-history">
                            <h2 id="activity-history" className="flex items-center gap-2 text-lg font-bold">
                                <History className="text-primary size-5" />
                                Complete request timeline
                            </h2>
                            <ol className="mt-5 space-y-5 border-l-2 pl-5">
                                {[...record.activities].reverse().map((activity) => (
                                    <li key={activity.id} className="relative">
                                        <span className="bg-primary absolute top-1.5 -left-[1.7rem] size-3 rounded-full border-2 border-white" />
                                        <div className="flex flex-wrap items-start justify-between gap-2">
                                            <h3 className="font-bold">{activityLabels[activity.event_type]}</h3>
                                            <time className="text-muted-foreground text-xs">{date(activity.created_at)}</time>
                                        </div>
                                        <p className="text-muted-foreground mt-1 text-xs">
                                            {activity.actor ? `By ${activity.actor}` : 'System'}
                                            {activity.subject_user ? ` · ${activity.subject_user}` : ''}
                                        </p>
                                        {activity.from_status && activity.to_status && (
                                            <p className="mt-2 text-sm font-semibold">
                                                {translations.statuses[activity.from_status]?.label} →{' '}
                                                {translations.statuses[activity.to_status]?.label}
                                            </p>
                                        )}
                                        {activity.public_message_en && (
                                            <div className="mt-2 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-950">
                                                <p className="text-xs font-bold uppercase">Public · English</p>
                                                <p className="mt-1 leading-6">{activity.public_message_en}</p>
                                                {activity.public_message_fil && (
                                                    <>
                                                        <p className="mt-3 text-xs font-bold uppercase">Public · Filipino</p>
                                                        <p className="mt-1 leading-6">{activity.public_message_fil}</p>
                                                    </>
                                                )}
                                            </div>
                                        )}
                                        {activity.private_details && (
                                            <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950">
                                                <p className="flex items-center gap-1 text-xs font-bold uppercase">
                                                    <LockKeyhole className="size-3.5" />
                                                    Internal only
                                                </p>
                                                <p className="mt-1 leading-6 whitespace-pre-line">{activity.private_details}</p>
                                            </div>
                                        )}
                                    </li>
                                ))}
                            </ol>
                        </section>
                    </div>

                    <aside className="space-y-5">
                        {canShowAssignment && (
                            <form onSubmit={submitAssignment} className="bg-card rounded-xl border p-5">
                                <h2 className="flex items-center gap-2 font-bold">
                                    <UserRoundCheck className="text-primary size-5" />
                                    Assignment
                                </h2>
                                {currentUser.role === 'administrator' && (
                                    <div className="mt-4 space-y-2">
                                        <Label htmlFor="assignee_id">Assign to</Label>
                                        <select
                                            id="assignee_id"
                                            className={selectClass}
                                            value={assignmentForm.data.assignee_id}
                                            onChange={(e) => assignmentForm.setData('assignee_id', e.target.value)}
                                        >
                                            <option value="">Unassigned</option>
                                            {staffOptions.map((staff) => (
                                                <option key={staff.id} value={staff.id}>
                                                    {staff.name} ({staff.role})
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                )}
                                <InputError message={assignmentForm.errors.assignee_id} className="mt-2" />
                                <Button className="mt-4 w-full" type="submit" variant="outline" disabled={assignmentForm.processing}>
                                    {currentUser.role === 'administrator' ? 'Save assignment' : record.assignee ? 'Release request' : 'Claim request'}
                                </Button>
                            </form>
                        )}

                        {record.permissions.transition && allowedTransitions.length > 0 && (
                            <form onSubmit={submitTransition} className="bg-card rounded-xl border p-5">
                                <h2 className="font-bold">Update status</h2>
                                <div className="mt-4 space-y-4">
                                    <FormField label="New status" id="status" error={transitionForm.errors.status}>
                                        <select
                                            id="status"
                                            className={selectClass}
                                            value={transitionForm.data.status}
                                            onChange={(e) => transitionForm.setData('status', e.target.value)}
                                            required
                                        >
                                            <option value="">Choose status</option>
                                            {allowedTransitions.map((item) => (
                                                <option key={item.value} value={item.value}>
                                                    {item.label}
                                                </option>
                                            ))}
                                        </select>
                                    </FormField>
                                    <FormField
                                        label="Public guidance · English"
                                        id="public_message_en"
                                        error={transitionForm.errors.public_message_en}
                                    >
                                        <textarea
                                            id="public_message_en"
                                            className={textareaClass}
                                            maxLength={500}
                                            value={transitionForm.data.public_message_en}
                                            onChange={(e) => transitionForm.setData('public_message_en', e.target.value)}
                                        />
                                    </FormField>
                                    <FormField
                                        label="Public guidance · Filipino"
                                        id="public_message_fil"
                                        error={transitionForm.errors.public_message_fil}
                                    >
                                        <textarea
                                            id="public_message_fil"
                                            className={textareaClass}
                                            maxLength={500}
                                            value={transitionForm.data.public_message_fil}
                                            onChange={(e) => transitionForm.setData('public_message_fil', e.target.value)}
                                        />
                                    </FormField>
                                    <FormField
                                        label="Internal note (optional)"
                                        id="transition_private_note"
                                        error={transitionForm.errors.private_note}
                                    >
                                        <textarea
                                            id="transition_private_note"
                                            className={textareaClass}
                                            maxLength={1000}
                                            value={transitionForm.data.private_note}
                                            onChange={(e) => transitionForm.setData('private_note', e.target.value)}
                                        />
                                    </FormField>
                                </div>
                                <p className="text-muted-foreground mt-3 text-xs leading-5">
                                    Blank public fields use the standard bilingual explanation. Custom guidance requires both languages.
                                </p>
                                <Button className="mt-4 w-full" type="submit" disabled={transitionForm.processing}>
                                    <Save />
                                    Update status
                                </Button>
                            </form>
                        )}

                        {record.permissions.add_note && (
                            <form onSubmit={submitNote} className="bg-card rounded-xl border p-5">
                                <h2 className="flex items-center gap-2 font-bold">
                                    <MessageSquareText className="text-primary size-5" />
                                    Internal note
                                </h2>
                                <p className="text-muted-foreground mt-2 text-xs leading-5">
                                    Notes are encrypted and never shown in resident tracking.
                                </p>
                                <div className="mt-4">
                                    <FormField label="Note" id="internal_note" error={noteForm.errors.body}>
                                        <textarea
                                            id="internal_note"
                                            className={textareaClass}
                                            maxLength={2000}
                                            value={noteForm.data.body}
                                            onChange={(e) => noteForm.setData('body', e.target.value)}
                                            required
                                        />
                                    </FormField>
                                </div>
                                <Button className="mt-4 w-full" type="submit" variant="outline" disabled={noteForm.processing}>
                                    Add internal note
                                </Button>
                            </form>
                        )}

                        {record.appointment && record.permissions.manage_appointment && (
                            <form onSubmit={submitAppointment} className="bg-card rounded-xl border p-5">
                                <h2 className="font-bold">Manage appointment</h2>
                                <div className="mt-4 space-y-4">
                                    <FormField label="Action" id="appointment_status">
                                        <select
                                            id="appointment_status"
                                            className={selectClass}
                                            value={appointmentForm.data.status}
                                            onChange={(e) => appointmentForm.setData('status', e.target.value)}
                                        >
                                            <option value="confirmed">Confirm schedule</option>
                                            <option value="reschedule_requested">Request reschedule</option>
                                            <option value="cancelled">Cancel appointment</option>
                                        </select>
                                    </FormField>
                                    {appointmentForm.data.status === 'confirmed' && (
                                        <FormField
                                            label="Confirmed date and time"
                                            id="confirmed_start_at"
                                            error={appointmentForm.errors.confirmed_start_at}
                                        >
                                            <Input
                                                id="confirmed_start_at"
                                                type="datetime-local"
                                                value={appointmentForm.data.confirmed_start_at}
                                                onChange={(e) => appointmentForm.setData('confirmed_start_at', e.target.value)}
                                            />
                                        </FormField>
                                    )}
                                    <FormField label="Internal scheduling note" id="appointment_private_note">
                                        <textarea
                                            id="appointment_private_note"
                                            className={textareaClass}
                                            maxLength={1000}
                                            value={appointmentForm.data.private_note}
                                            onChange={(e) => appointmentForm.setData('private_note', e.target.value)}
                                        />
                                    </FormField>
                                </div>
                                <Button className="mt-4 w-full" type="submit" variant="outline" disabled={appointmentForm.processing}>
                                    Save appointment
                                </Button>
                            </form>
                        )}

                        {terminal && (
                            <div className="rounded-xl border border-slate-200 bg-slate-50 p-5 text-sm">
                                <p className="font-bold">Request closed</p>
                                <p className="text-muted-foreground mt-2 leading-6">
                                    Closed requests keep their complete timeline but cannot be reassigned or changed.
                                </p>
                            </div>
                        )}
                    </aside>
                </div>
            </div>
        </AppLayout>
    );
}

function Summary({ label, value, warning = false }: { label: string; value: string; warning?: boolean }) {
    return (
        <div className="bg-card rounded-xl border p-4">
            <p className="text-muted-foreground text-xs font-bold uppercase">{label}</p>
            <p className={`mt-2 text-sm font-semibold capitalize ${warning ? 'text-red-700' : ''}`}>{value}</p>
        </div>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="text-muted-foreground text-xs font-bold uppercase">{label}</dt>
            <dd className="mt-1 font-semibold break-words">{value}</dd>
        </div>
    );
}

function FormField({ label, id, error, children }: { label: string; id: string; error?: string; children: React.ReactNode }) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            {children}
            <InputError message={error} />
        </div>
    );
}
