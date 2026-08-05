import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type AdminService } from '@/types/services';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Archive, ArchiveRestore, Edit3, Plus, Search } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface Props {
    services: { data: AdminService[]; links: PaginationLink[]; meta: { from: number | null; to: number | null; total: number } };
    filters: { search: string; status: string; sort: string; direction: string };
    summary: { active: number; inactive: number; archived: number };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Services', href: '/admin/services' },
];

export default function ServicesIndex({ services, filters, summary }: Props) {
    const { flash } = usePage<SharedData>().props;
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const [sort, setSort] = useState(filters.sort);
    const [direction, setDirection] = useState(filters.direction);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        router.get(route('admin.services.index'), { search, status, sort, direction }, { preserveState: true, replace: true });
    };

    const archiveService = (service: AdminService) => {
        if (window.confirm(`Archive “${service.name_en}”? It will disappear from the public directory but its history will remain.`)) {
            router.patch(route('admin.services.archive', service.slug));
        }
    };

    const restoreService = (service: AdminService) => {
        if (window.confirm(`Restore “${service.name_en}” as inactive?`)) {
            router.patch(route('admin.services.restore', service.slug));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage services" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-primary text-sm font-semibold">Administrator</p>
                        <h1 className="mt-1 text-2xl font-bold tracking-tight">Service directory</h1>
                        <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                            Create, revise, deactivate, archive, and restore resident-facing service guidance. Services are archived instead of
                            deleted.
                        </p>
                    </div>
                    <Link href={route('admin.services.create')} className={cn(buttonVariants({ size: 'lg' }), 'min-h-11')}>
                        <Plus />
                        Create service
                    </Link>
                </div>

                {flash.success && (
                    <div
                        role="status"
                        aria-live="polite"
                        className="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-100"
                    >
                        {flash.success}
                    </div>
                )}

                <section className="grid gap-3 sm:grid-cols-3" aria-label="Service totals">
                    {[
                        ['Active', summary.active, 'text-emerald-700 dark:text-emerald-300'],
                        ['Inactive', summary.inactive, 'text-amber-700 dark:text-amber-300'],
                        ['Archived', summary.archived, 'text-muted-foreground'],
                    ].map(([label, value, color]) => (
                        <div key={label} className="bg-card rounded-xl border p-4">
                            <p className="text-muted-foreground text-sm font-semibold">{label}</p>
                            <p className={`mt-1 text-3xl font-bold ${color}`}>{value}</p>
                        </div>
                    ))}
                </section>

                <form
                    onSubmit={applyFilters}
                    className="bg-card grid gap-3 rounded-xl border p-4 md:grid-cols-[minmax(12rem,1fr)_10rem_10rem_10rem_auto]"
                    aria-label="Filter services"
                >
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Search</span>
                        <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Name or slug" maxLength={100} />
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Status</span>
                        <select
                            className="bg-background h-10 w-full rounded-md border px-3"
                            value={status}
                            onChange={(e) => setStatus(e.target.value)}
                        >
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="archived">Archived</option>
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Sort by</span>
                        <select className="bg-background h-10 w-full rounded-md border px-3" value={sort} onChange={(e) => setSort(e.target.value)}>
                            <option value="updated">Last updated</option>
                            <option value="name">Name</option>
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Direction</span>
                        <select
                            className="bg-background h-10 w-full rounded-md border px-3"
                            value={direction}
                            onChange={(e) => setDirection(e.target.value)}
                        >
                            <option value="asc">Ascending</option>
                            <option value="desc">Descending</option>
                        </select>
                    </label>
                    <Button type="submit" className="min-h-10 self-end">
                        <Search />
                        Apply
                    </Button>
                </form>

                <section aria-labelledby="service-results-title" className="bg-card overflow-hidden rounded-xl border">
                    <div className="border-b px-4 py-3">
                        <h2 id="service-results-title" className="font-bold">
                            Services
                        </h2>
                        <p className="text-muted-foreground text-sm">
                            Showing {services.meta.from ?? 0}–{services.meta.to ?? 0} of {services.meta.total}
                        </p>
                    </div>
                    {services.data.length === 0 ? (
                        <div className="p-8 text-center">
                            <h3 className="font-bold">No services match these filters</h3>
                            <p className="text-muted-foreground mt-2 text-sm">Adjust the search or status filter.</p>
                        </div>
                    ) : (
                        <div className="divide-y">
                            {services.data.map((service) => (
                                <article
                                    key={service.slug}
                                    className="grid gap-4 p-4 lg:grid-cols-[minmax(14rem,1fr)_8rem_11rem_auto] lg:items-center"
                                >
                                    <div>
                                        <h3 className="font-bold">{service.name_en}</h3>
                                        <p className="text-muted-foreground mt-1 text-sm">{service.name_fil}</p>
                                        <p className="text-muted-foreground mt-1 font-mono text-xs">{service.slug}</p>
                                    </div>
                                    <span
                                        className={`w-fit rounded-full px-3 py-1 text-xs font-bold ${service.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200' : service.status === 'inactive' ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-200' : 'bg-muted text-muted-foreground'}`}
                                    >
                                        {service.status}
                                    </span>
                                    <p className="text-muted-foreground text-sm">
                                        {service.updated_at
                                            ? `Updated ${new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium' }).format(new Date(service.updated_at))}`
                                            : 'Not yet updated'}
                                    </p>
                                    <div className="flex flex-wrap gap-2 lg:justify-end">
                                        <Link
                                            href={route('admin.services.edit', service.slug)}
                                            className={buttonVariants({ variant: 'outline', size: 'sm' })}
                                        >
                                            <Edit3 />
                                            Edit
                                        </Link>
                                        {service.status === 'archived' ? (
                                            <Button type="button" size="sm" variant="outline" onClick={() => restoreService(service)}>
                                                <ArchiveRestore />
                                                Restore
                                            </Button>
                                        ) : (
                                            <Button type="button" size="sm" variant="outline" onClick={() => archiveService(service)}>
                                                <Archive />
                                                Archive
                                            </Button>
                                        )}
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                    {services.links.length > 3 && (
                        <nav aria-label="Service result pages" className="flex flex-wrap gap-2 border-t p-4">
                            {services.links.map((link, index) =>
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
