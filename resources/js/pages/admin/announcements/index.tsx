import { buttonVariants } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type AdminAnnouncement } from '@/types/admin';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Edit3, Megaphone, Plus, Trash2 } from 'lucide-react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface Props {
    announcements: { data: AdminAnnouncement[]; links: PaginationLink[]; meta: { from: number | null; to: number | null; total: number } };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Announcements', href: '/admin/announcements' },
];

const dateTimeFormatter = new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short' });

const levelBadge = (level: AdminAnnouncement['level']): string =>
    level === 'critical'
        ? 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-200'
        : level === 'warning'
          ? 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-200'
          : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-200';

export default function AnnouncementsIndex({ announcements }: Props) {
    const { flash } = usePage<SharedData>().props;

    const removeAnnouncement = (announcement: AdminAnnouncement) => {
        if (window.confirm('Remove this announcement permanently?')) {
            router.delete(route('admin.announcements.destroy', announcement.id), { preserveScroll: true });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Office announcements" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-primary text-sm font-semibold">Administrator</p>
                        <h1 className="mt-1 text-2xl font-bold tracking-tight">Office announcements</h1>
                        <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                            Active announcements appear as a banner on every public and staff page in both languages.
                        </p>
                    </div>
                    <Link href={route('admin.announcements.create')} className={cn(buttonVariants({ size: 'lg' }), 'min-h-11')}>
                        <Plus />
                        New announcement
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

                <section aria-labelledby="announcement-results-title" className="bg-card overflow-hidden rounded-xl border">
                    <div className="border-b px-4 py-3">
                        <h2 id="announcement-results-title" className="font-bold">
                            All announcements
                        </h2>
                        <p className="text-muted-foreground text-sm">
                            Showing {announcements.meta.from ?? 0}–{announcements.meta.to ?? 0} of {announcements.meta.total}
                        </p>
                    </div>
                    {announcements.data.length === 0 ? (
                        <div className="p-8 text-center">
                            <Megaphone className="text-muted-foreground mx-auto size-8" aria-hidden="true" />
                            <h3 className="mt-3 font-bold">No announcements yet</h3>
                            <p className="text-muted-foreground mt-2 text-sm">Publish office closures, service interruptions, or general notices.</p>
                        </div>
                    ) : (
                        <div className="divide-y">
                            {announcements.data.map((announcement) => (
                                <article
                                    key={announcement.id}
                                    className="grid gap-4 p-4 lg:grid-cols-[minmax(16rem,1fr)_minmax(12rem,1fr)_auto] lg:items-center"
                                >
                                    <div>
                                        <span
                                            className={`w-fit rounded-full px-3 py-1 text-xs font-bold uppercase ${levelBadge(announcement.level)}`}
                                        >
                                            {announcement.level}
                                        </span>
                                        <p className="mt-2 text-sm leading-6 font-semibold">{announcement.message_en}</p>
                                        <p className="text-muted-foreground mt-1 text-sm">{announcement.message_fil}</p>
                                    </div>
                                    <div className="text-muted-foreground text-sm">
                                        <span
                                            className={`w-fit rounded-full px-3 py-1 text-xs font-bold ${
                                                announcement.is_active
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200'
                                                    : 'bg-muted'
                                            }`}
                                        >
                                            {announcement.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                        <p className="mt-2">
                                            {announcement.starts_at ? dateTimeFormatter.format(new Date(announcement.starts_at)) : 'Immediately'}
                                            {' → '}
                                            {announcement.ends_at ? dateTimeFormatter.format(new Date(announcement.ends_at)) : 'Until removed'}
                                        </p>
                                    </div>
                                    <div className="flex flex-wrap gap-2 lg:justify-end">
                                        <Link
                                            href={route('admin.announcements.edit', announcement.id)}
                                            className={buttonVariants({ variant: 'outline', size: 'sm' })}
                                        >
                                            <Edit3 />
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            onClick={() => removeAnnouncement(announcement)}
                                            className={cn(buttonVariants({ variant: 'outline', size: 'sm' }))}
                                        >
                                            <Trash2 />
                                            Remove
                                        </button>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                    {announcements.links.length > 3 && (
                        <nav aria-label="Announcement result pages" className="flex flex-wrap gap-2 border-t p-4">
                            {announcements.links.map((link, index) =>
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
