import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { BarChart3, Download, Search, Timer } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface Filters {
    [key: string]: string;
    date_from: string;
    date_to: string;
    service: string;
    status: string;
}
interface Analytics {
    summary: { total: number; open: number; overdue: number; completed: number; completion_rate: number; average_resolution_hours: number | null };
    status_breakdown: { status: string; label: string; count: number }[];
    service_breakdown: { slug: string; name: string; total: number; open: number; completed: number; overdue: number }[];
    trend: { date: string; count: number }[];
}
interface Props {
    filters: Filters;
    analytics: Analytics;
    services: { slug: string; name_en: string }[];
    statuses: { value: string; label: string }[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/admin/reports' },
];

export default function ReportsIndex({ filters, analytics, services, statuses }: Props) {
    const [form, setForm] = useState(filters);
    const maxTrend = Math.max(...analytics.trend.map((point) => point.count), 1);
    const exportUrl = `${route('admin.reports.export')}?${new URLSearchParams(form).toString()}`;

    const apply = (event: FormEvent) => {
        event.preventDefault();
        router.get(route('admin.reports.index'), form, { preserveState: true, replace: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Operational reports" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-primary text-sm font-semibold">Administrator</p>
                        <h1 className="mt-1 text-2xl font-bold tracking-tight">Operational reports</h1>
                        <p className="text-muted-foreground mt-2 max-w-3xl text-sm leading-6">
                            Aggregate workload and outcomes without selecting resident contact details, request descriptions, or internal notes.
                        </p>
                    </div>
                    <a href={exportUrl} className={buttonVariants({ variant: 'outline', size: 'lg' })}>
                        <Download />
                        Export sanitized CSV
                    </a>
                </header>

                <form onSubmit={apply} className="bg-card grid gap-3 rounded-xl border p-4 md:grid-cols-[10rem_10rem_minmax(12rem,1fr)_12rem_auto]">
                    <label className="space-y-1 text-sm font-semibold">
                        <span>From</span>
                        <Input type="date" value={form.date_from} onChange={(event) => setForm({ ...form, date_from: event.target.value })} />
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>To</span>
                        <Input type="date" value={form.date_to} onChange={(event) => setForm({ ...form, date_to: event.target.value })} />
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Service</span>
                        <select
                            className="bg-background h-10 w-full rounded-md border px-3"
                            value={form.service}
                            onChange={(event) => setForm({ ...form, service: event.target.value })}
                        >
                            <option value="all">All services</option>
                            {services.map((service) => (
                                <option key={service.slug} value={service.slug}>
                                    {service.name_en}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Status</span>
                        <select
                            className="bg-background h-10 w-full rounded-md border px-3"
                            value={form.status}
                            onChange={(event) => setForm({ ...form, status: event.target.value })}
                        >
                            <option value="all">All statuses</option>
                            {statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </select>
                    </label>
                    <Button type="submit" className="self-end">
                        <Search />
                        Apply
                    </Button>
                </form>

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-6" aria-label="Report summary">
                    {[
                        ['Total', analytics.summary.total],
                        ['Open', analytics.summary.open],
                        ['Overdue', analytics.summary.overdue],
                        ['Completed', analytics.summary.completed],
                        ['Completion rate', `${analytics.summary.completion_rate}%`],
                        [
                            'Average resolution',
                            analytics.summary.average_resolution_hours === null ? '—' : `${analytics.summary.average_resolution_hours}h`,
                        ],
                    ].map(([label, value]) => (
                        <div key={label} className="bg-card rounded-xl border p-4">
                            <p className="text-muted-foreground text-xs font-semibold uppercase">{label}</p>
                            <p className="mt-2 text-2xl font-bold">{value}</p>
                        </div>
                    ))}
                </section>

                <section className="bg-card rounded-xl border p-5" aria-labelledby="submission-trend">
                    <div className="flex items-center gap-3">
                        <BarChart3 className="text-primary size-5" />
                        <div>
                            <h2 id="submission-trend" className="font-bold">
                                Submission trend
                            </h2>
                            <p className="text-muted-foreground text-sm">Daily request volume for the selected period</p>
                        </div>
                    </div>
                    <div className="mt-6 overflow-x-auto pb-2">
                        <div
                            className="flex h-44 min-w-full items-end gap-1 border-b"
                            style={{ width: `${Math.max(analytics.trend.length * 10, 640)}px` }}
                        >
                            {analytics.trend.map((point) => (
                                <div
                                    key={point.date}
                                    className="group relative flex h-full flex-1 items-end"
                                    title={`${point.date}: ${point.count} requests`}
                                >
                                    <div
                                        className="bg-primary min-h-1 w-full rounded-t-sm"
                                        style={{ height: `${Math.max(3, (point.count / maxTrend) * 100)}%` }}
                                        aria-label={`${point.date}: ${point.count} requests`}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <div className="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
                    <section className="bg-card rounded-xl border p-5" aria-labelledby="status-breakdown">
                        <h2 id="status-breakdown" className="font-bold">
                            Status breakdown
                        </h2>
                        <div className="mt-4 space-y-3">
                            {analytics.status_breakdown.map((item) => (
                                <div key={item.status} className="flex items-center justify-between gap-4">
                                    <span className="text-sm">{item.label}</span>
                                    <strong>{item.count}</strong>
                                </div>
                            ))}
                        </div>
                    </section>
                    <section className="bg-card overflow-hidden rounded-xl border" aria-labelledby="service-workload">
                        <div className="flex items-center gap-3 border-b p-5">
                            <Timer className="text-primary size-5" />
                            <h2 id="service-workload" className="font-bold">
                                Service workload
                            </h2>
                        </div>
                        {analytics.service_breakdown.length === 0 ? (
                            <p className="p-6 text-sm">No request data for this period.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[36rem] text-left text-sm">
                                    <thead className="bg-muted/60">
                                        <tr>
                                            <th className="px-4 py-3">Service</th>
                                            <th className="px-4 py-3">Total</th>
                                            <th className="px-4 py-3">Open</th>
                                            <th className="px-4 py-3">Completed</th>
                                            <th className="px-4 py-3">Overdue</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {analytics.service_breakdown.map((item) => (
                                            <tr key={item.slug}>
                                                <th className="px-4 py-3 font-semibold">{item.name}</th>
                                                <td className="px-4 py-3">{item.total}</td>
                                                <td className="px-4 py-3">{item.open}</td>
                                                <td className="px-4 py-3">{item.completed}</td>
                                                <td className="px-4 py-3">{item.overdue}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}
