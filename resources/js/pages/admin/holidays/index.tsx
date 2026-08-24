import { buttonVariants } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type AdminHoliday } from '@/types/admin';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { CalendarDays, CalendarX2, Edit3, Plus } from 'lucide-react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface Props {
    holidays: { data: AdminHoliday[]; links: PaginationLink[]; meta: { from: number | null; to: number | null; total: number } };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Holiday calendar', href: '/admin/holidays' },
];

const dateFormatter = new Intl.DateTimeFormat('en-PH', { dateStyle: 'full' });

export default function HolidaysIndex({ holidays }: Props) {
    const { flash } = usePage<SharedData>().props;
    const today = new Date().toISOString().slice(0, 10);

    const removeHoliday = (holiday: AdminHoliday) => {
        if (window.confirm(`Remove “${holiday.name_en}” (${holiday.date}) from the holiday calendar?`)) {
            router.delete(route('admin.holidays.destroy', holiday.id), { preserveScroll: true });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Holiday calendar" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-primary text-sm font-semibold">Administrator</p>
                        <h1 className="mt-1 text-2xl font-bold tracking-tight">Holiday calendar</h1>
                        <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                            Non-working days are skipped when service due-date targets are calculated. Weekends are always skipped.
                        </p>
                    </div>
                    <Link href={route('admin.holidays.create')} className={cn(buttonVariants({ size: 'lg' }), 'min-h-11')}>
                        <Plus />
                        Add holiday
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

                <section aria-labelledby="holiday-results-title" className="bg-card overflow-hidden rounded-xl border">
                    <div className="border-b px-4 py-3">
                        <h2 id="holiday-results-title" className="font-bold">
                            Declared holidays
                        </h2>
                        <p className="text-muted-foreground text-sm">
                            Showing {holidays.meta.from ?? 0}–{holidays.meta.to ?? 0} of {holidays.meta.total}
                        </p>
                    </div>
                    {holidays.data.length === 0 ? (
                        <div className="p-8 text-center">
                            <CalendarDays className="text-muted-foreground mx-auto size-8" aria-hidden="true" />
                            <h3 className="mt-3 font-bold">No holidays declared</h3>
                            <p className="text-muted-foreground mt-2 text-sm">Add non-working days so due dates stay realistic.</p>
                        </div>
                    ) : (
                        <div className="divide-y">
                            {holidays.data.map((holiday) => {
                                const past = holiday.date < today;

                                return (
                                    <article
                                        key={holiday.id}
                                        className="grid gap-4 p-4 lg:grid-cols-[minmax(14rem,1fr)_minmax(12rem,1fr)_auto] lg:items-center"
                                    >
                                        <div>
                                            <p className={`font-bold ${past ? 'text-muted-foreground' : ''}`}>
                                                {dateFormatter.format(new Date(`${holiday.date}T00:00:00`))}
                                            </p>
                                            <span
                                                className={`mt-1 inline-block w-fit rounded-full px-3 py-1 text-xs font-bold ${
                                                    past
                                                        ? 'bg-muted text-muted-foreground'
                                                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200'
                                                }`}
                                            >
                                                {past ? 'Past' : 'Upcoming'}
                                            </span>
                                        </div>
                                        <div>
                                            <p>{holiday.name_en}</p>
                                            <p className="text-muted-foreground mt-1 text-sm">{holiday.name_fil}</p>
                                            {holiday.is_recurring && (
                                                <p className="text-muted-foreground mt-1 text-xs font-semibold">Repeats every year</p>
                                            )}
                                        </div>
                                        <div className="flex flex-wrap gap-2 lg:justify-end">
                                            <Link
                                                href={route('admin.holidays.edit', holiday.id)}
                                                className={buttonVariants({ variant: 'outline', size: 'sm' })}
                                            >
                                                <Edit3 />
                                                Edit
                                            </Link>
                                            <button
                                                type="button"
                                                onClick={() => removeHoliday(holiday)}
                                                className={cn(buttonVariants({ variant: 'outline', size: 'sm' }))}
                                            >
                                                <CalendarX2 />
                                                Remove
                                            </button>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    )}
                    {holidays.links.length > 3 && (
                        <nav aria-label="Holiday result pages" className="flex flex-wrap gap-2 border-t p-4">
                            {holidays.links.map((link, index) =>
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
