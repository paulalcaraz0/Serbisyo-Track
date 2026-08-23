import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { type StaffRequestSummary } from '@/types/requests';
import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangle, CalendarClock, ChevronRight, ClipboardList, type LucideIcon, Search, UserRoundCheck, UsersRound } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    requests: { data: StaffRequestSummary[]; links: PaginationLink[]; meta: { from: number | null; to: number | null; total: number } };
    filters: { search: string; status: string; assignment: string; service: string; overdue: boolean; sort: string };
    summary: { open: number; mine: number; unassigned: number; overdue: number };
    services: { slug: string; name_en: string }[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Requests', href: '/staff/requests' },
];

const statusOptions = [
    ['all', 'All statuses'],
    ['submitted', 'Submitted'],
    ['acknowledged', 'Acknowledged'],
    ['needs_information', 'Needs information'],
    ['scheduled', 'Scheduled'],
    ['in_progress', 'In progress'],
    ['ready_for_release', 'Ready for release'],
    ['completed', 'Completed'],
    ['rejected', 'Rejected'],
    ['cancelled', 'Cancelled'],
];

export default function RequestQueue({ requests, filters, summary, services }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const [assignment, setAssignment] = useState(filters.assignment);
    const [service, setService] = useState(filters.service);
    const [overdue, setOverdue] = useState(filters.overdue);
    const [sort, setSort] = useState(filters.sort);
    const summaryCards: { label: string; value: number; icon: LucideIcon; color: string }[] = [
        { label: 'Open', value: summary.open, icon: ClipboardList, color: 'text-foreground' },
        { label: 'Assigned to me', value: summary.mine, icon: UserRoundCheck, color: 'text-primary' },
        { label: 'Unassigned', value: summary.unassigned, icon: UsersRound, color: 'text-amber-700 dark:text-amber-300' },
        { label: 'Overdue', value: summary.overdue, icon: AlertTriangle, color: 'text-red-700 dark:text-red-300' },
    ];
    const selectClass = 'bg-background h-10 w-full rounded-md border px-3 text-sm';
    const applyFilters = (event: FormEvent) => {
        event.preventDefault();
        router.get(
            route('staff.requests.index'),
            { search, status, assignment, service, overdue: overdue ? 1 : 0, sort },
            { preserveState: true, replace: true },
        );
    };
    const date = (value: string) => new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Request queue" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header>
                    <p className="text-primary text-sm font-semibold">Staff operations</p>
                    <h1 className="mt-1 text-2xl font-bold tracking-tight">Request queue</h1>
                    <p className="text-muted-foreground mt-2 max-w-3xl text-sm leading-6">
                        Review resident requests, claim unassigned work, monitor due dates, and open the protected request workspace.
                    </p>
                </header>

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Request totals">
                    {summaryCards.map(({ label, value, icon: Icon, color }) => (
                        <article key={label} className="bg-card rounded-xl border p-4">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-muted-foreground text-sm font-semibold">{label}</p>
                                <Icon className={`size-5 ${color}`} aria-hidden="true" />
                            </div>
                            <p className={`mt-2 text-3xl font-bold ${color}`}>{value}</p>
                        </article>
                    ))}
                </section>

                <form
                    onSubmit={applyFilters}
                    className="bg-card grid gap-3 rounded-xl border p-4 md:grid-cols-2 xl:grid-cols-[minmax(12rem,1fr)_11rem_12rem_12rem_9rem_auto]"
                    aria-label="Filter requests"
                >
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Reference or service</span>
                        <Input value={search} onChange={(e) => setSearch(e.target.value)} maxLength={100} placeholder="Search" />
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Status</span>
                        <select className={selectClass} value={status} onChange={(e) => setStatus(e.target.value)}>
                            {statusOptions.map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Assignment</span>
                        <select className={selectClass} value={assignment} onChange={(e) => setAssignment(e.target.value)}>
                            <option value="mine_and_unassigned">Mine + unassigned</option>
                            <option value="mine">Assigned to me</option>
                            <option value="unassigned">Unassigned</option>
                            <option value="assigned">Any assigned</option>
                            <option value="all">All requests</option>
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Service</span>
                        <select className={selectClass} value={service} onChange={(e) => setService(e.target.value)}>
                            <option value="all">All services</option>
                            {services.map((item) => (
                                <option key={item.slug} value={item.slug}>
                                    {item.name_en}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Sort</span>
                        <select className={selectClass} value={sort} onChange={(e) => setSort(e.target.value)}>
                            <option value="oldest">Oldest first</option>
                            <option value="newest">Newest first</option>
                            <option value="updated">Recently updated</option>
                            <option value="due">Due date</option>
                        </select>
                    </label>
                    <div className="flex flex-wrap items-end gap-3 md:col-span-2 xl:col-span-1">
                        <label className="flex min-h-10 items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" checked={overdue} onChange={(e) => setOverdue(e.target.checked)} className="size-5 rounded" />
                            Overdue only
                        </label>
                        <Button type="submit">
                            <Search />
                            Apply
                        </Button>
                    </div>
                </form>

                <section className="bg-card overflow-hidden rounded-xl border" aria-labelledby="queue-results">
                    <div className="border-b px-4 py-3">
                        <h2 id="queue-results" className="font-bold">
                            Requests
                        </h2>
                        <p className="text-muted-foreground text-sm">
                            Showing {requests.meta.from ?? 0}–{requests.meta.to ?? 0} of {requests.meta.total}
                        </p>
                    </div>
                    {requests.data.length === 0 ? (
                        <div className="p-10 text-center">
                            <ClipboardList className="text-muted-foreground mx-auto size-8" aria-hidden="true" />
                            <h3 className="mt-3 font-bold">No requests match these filters</h3>
                            <p className="text-muted-foreground mt-2 text-sm">Adjust the filters or check again when new requests arrive.</p>
                        </div>
                    ) : (
                        <div className="divide-y">
                            {requests.data.map((item) => (
                                <article
                                    key={item.reference}
                                    className="grid gap-4 p-4 lg:grid-cols-[minmax(15rem,1fr)_11rem_12rem_12rem_auto] lg:items-center"
                                >
                                    <div>
                                        <Link
                                            href={route('staff.requests.show', item.reference)}
                                            className="focus-ring text-primary rounded font-mono text-sm font-bold hover:underline"
                                        >
                                            {item.reference}
                                        </Link>
                                        <h3 className="mt-1 font-bold">{item.service.name}</h3>
                                        <p className="text-muted-foreground mt-1 text-xs">Submitted {date(item.submitted_at)}</p>
                                    </div>
                                    <span className="bg-secondary text-secondary-foreground w-fit rounded-full px-3 py-1 text-xs font-bold">
                                        {item.status_label}
                                    </span>
                                    <div className="text-sm">
                                        <p className="text-muted-foreground text-xs font-semibold">Assigned to</p>
                                        <p className="mt-1 font-semibold">{item.assignee?.name ?? 'Unassigned'}</p>
                                    </div>
                                    <div className="text-sm">
                                        <p className="text-muted-foreground flex items-center gap-1 text-xs font-semibold">
                                            <CalendarClock className="size-3.5" />
                                            Due
                                        </p>
                                        <p className={`mt-1 font-semibold ${item.is_overdue ? 'text-red-700 dark:text-red-300' : ''}`}>
                                            {item.due_at ? date(item.due_at) : 'No target'}
                                        </p>
                                    </div>
                                    <Link
                                        href={route('staff.requests.show', item.reference)}
                                        className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'justify-self-start lg:justify-self-end')}
                                    >
                                        Open
                                        <ChevronRight />
                                    </Link>
                                </article>
                            ))}
                        </div>
                    )}
                    {requests.links.length > 3 && (
                        <nav aria-label="Request pages" className="flex flex-wrap gap-2 border-t p-4">
                            {requests.links.map((link, index) =>
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
