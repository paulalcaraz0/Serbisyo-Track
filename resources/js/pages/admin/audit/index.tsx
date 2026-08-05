import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { type AuditEvent, type PaginationLink } from '@/types/admin';
import { Head, Link, router } from '@inertiajs/react';
import { ScrollText, Search } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface Props {
    events: { data: AuditEvent[]; links: PaginationLink[]; meta: { from: number | null; to: number | null; total: number } };
    filters: { action: string; actor: string; date_from: string; date_to: string };
    actions: { value: string; label: string }[];
    actors: { id: number; name: string }[];
}
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Audit history', href: '/admin/audit-events' },
];

export default function AuditIndex({ events, filters, actions, actors }: Props) {
    const [form, setForm] = useState(filters);
    const apply = (event: FormEvent) => {
        event.preventDefault();
        router.get(route('admin.audit.index'), form, { preserveState: true, replace: true });
    };
    const date = (value: string) => new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit history" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header>
                    <p className="text-primary text-sm font-semibold">Administrator</p>
                    <h1 className="mt-1 text-2xl font-bold tracking-tight">Audit history</h1>
                    <p className="text-muted-foreground mt-2 max-w-3xl text-sm leading-6">
                        Append-only security and operations events. Metadata is allow-listed and excludes resident contact details and internal note
                        content.
                    </p>
                </header>
                <form onSubmit={apply} className="bg-card grid gap-3 rounded-xl border p-4 md:grid-cols-[minmax(12rem,1fr)_12rem_10rem_10rem_auto]">
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Action</span>
                        <select
                            className="bg-background h-10 w-full rounded-md border px-3"
                            value={form.action}
                            onChange={(event) => setForm({ ...form, action: event.target.value })}
                        >
                            <option value="all">All actions</option>
                            {actions.map((action) => (
                                <option key={action.value} value={action.value}>
                                    {action.label}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Actor</span>
                        <select
                            className="bg-background h-10 w-full rounded-md border px-3"
                            value={form.actor}
                            onChange={(event) => setForm({ ...form, actor: event.target.value })}
                        >
                            <option value="">All actors</option>
                            {actors.map((actor) => (
                                <option key={actor.id} value={actor.id}>
                                    {actor.name}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>From</span>
                        <Input type="date" value={form.date_from} onChange={(event) => setForm({ ...form, date_from: event.target.value })} />
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>To</span>
                        <Input type="date" value={form.date_to} onChange={(event) => setForm({ ...form, date_to: event.target.value })} />
                    </label>
                    <Button type="submit" className="self-end">
                        <Search />
                        Apply
                    </Button>
                </form>
                <section className="bg-card overflow-hidden rounded-xl border" aria-labelledby="audit-events">
                    <div className="flex items-center gap-3 border-b p-4">
                        <ScrollText className="text-primary size-5" />
                        <div>
                            <h2 id="audit-events" className="font-bold">
                                Events
                            </h2>
                            <p className="text-muted-foreground text-sm">
                                Showing {events.meta.from ?? 0}–{events.meta.to ?? 0} of {events.meta.total}
                            </p>
                        </div>
                    </div>
                    {events.data.length === 0 ? (
                        <p className="p-8 text-center text-sm">No audit events match these filters.</p>
                    ) : (
                        <ol className="divide-y">
                            {events.data.map((event) => (
                                <li key={event.id} className="grid gap-3 p-4 lg:grid-cols-[13rem_minmax(14rem,1fr)_12rem] lg:items-start">
                                    <div>
                                        <p className="font-semibold">{event.action_label}</p>
                                        <time className="text-muted-foreground mt-1 block text-xs">{date(event.created_at)}</time>
                                    </div>
                                    <div className="text-sm">
                                        <p>
                                            <span className="text-muted-foreground">Actor:</span> {event.actor?.name ?? 'System'}
                                        </p>
                                        <p className="mt-1">
                                            <span className="text-muted-foreground">Subject:</span> {event.subject_type}
                                            {event.subject_identifier ? ` · ${event.subject_identifier}` : ''}
                                        </p>
                                    </div>
                                    <dl className="space-y-1 text-xs">
                                        {Object.entries(event.metadata).map(([key, value]) => (
                                            <div key={key} className="flex justify-between gap-2">
                                                <dt className="text-muted-foreground">{key.replaceAll('_', ' ')}</dt>
                                                <dd className="font-mono">{String(value ?? '—')}</dd>
                                            </div>
                                        ))}
                                    </dl>
                                </li>
                            ))}
                        </ol>
                    )}
                    {events.links.length > 3 && (
                        <nav aria-label="Audit event pages" className="flex flex-wrap gap-2 border-t p-4">
                            {events.links.map((link, index) =>
                                link.url ? (
                                    <Link
                                        key={`${link.label}-${index}`}
                                        href={link.url}
                                        preserveScroll
                                        className={cn(buttonVariants({ variant: link.active ? 'default' : 'outline', size: 'sm' }), 'min-w-10')}
                                    >
                                        {link.label.replace('&laquo;', '‹').replace('&raquo;', '›')}
                                    </Link>
                                ) : (
                                    <span
                                        key={`${link.label}-${index}`}
                                        aria-disabled="true"
                                        className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'min-w-10 opacity-50')}
                                    >
                                        {link.label.replace('&laquo;', '‹').replace('&raquo;', '›')}
                                    </span>
                                ),
                            )}
                        </nav>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
