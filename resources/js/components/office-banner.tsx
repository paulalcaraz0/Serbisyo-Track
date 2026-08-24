import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Info, OctagonAlert, TriangleAlert, X } from 'lucide-react';
import { useEffect, useState } from 'react';

const STORAGE_KEY = 'st-dismissed-announcements';

const levelStyles = {
    info: {
        wrapper: 'border-blue-200 bg-blue-50 text-blue-950 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-100',
        icon: Info,
    },
    warning: {
        wrapper: 'border-amber-300 bg-amber-50 text-amber-950 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100',
        icon: TriangleAlert,
    },
    critical: {
        wrapper: 'border-red-300 bg-red-50 text-red-950 dark:border-red-800 dark:bg-red-950/40 dark:text-red-100',
        icon: OctagonAlert,
    },
} as const;

export default function OfficeBanner() {
    const { activeAnnouncements, locale, translations } = usePage<SharedData>().props;
    const copy = translations.announcements;
    const [dismissedIds, setDismissedIds] = useState<number[]>([]);
    const [showDismissed, setShowDismissed] = useState(false);

    useEffect(() => {
        try {
            const raw = window.localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const parsed: unknown = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    setDismissedIds(parsed.filter((value): value is number => typeof value === 'number'));
                }
            }
        } catch {
            // Ignore unreadable or malformed storage; banners stay visible.
        }
    }, []);

    if (!Array.isArray(activeAnnouncements) || activeAnnouncements.length === 0) {
        return null;
    }

    const visible = showDismissed ? activeAnnouncements : activeAnnouncements.filter((item) => !dismissedIds.includes(item.id));
    const hiddenCount = activeAnnouncements.length - visible.length;

    const dismiss = (id: number) => {
        const next = [...dismissedIds.filter((value) => value !== id), id].slice(-50);
        setDismissedIds(next);
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
        } catch {
            // Storage may be unavailable; dismissal applies to this page view only.
        }
    };

    const dateFormatter = new Intl.DateTimeFormat(locale === 'fil' ? 'fil-PH' : 'en-PH', { dateStyle: 'long' });

    return (
        <div className="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8" data-print-hidden="true">
            <div className="space-y-2" aria-label={copy.banner_label}>
                {visible.map((announcement) => {
                    const style = levelStyles[announcement.level] ?? levelStyles.info;
                    const Icon = style.icon;
                    const urgent = announcement.level === 'critical';

                    return (
                        <div
                            key={announcement.id}
                            role={urgent ? 'alert' : 'status'}
                            aria-live={urgent ? 'assertive' : 'polite'}
                            className={`flex items-start gap-3 rounded-xl border p-4 text-sm leading-6 ${style.wrapper}`}
                        >
                            <Icon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            <div className="min-w-0 flex-1">
                                <p className="text-xs font-bold tracking-wide uppercase">{copy.banner_label}</p>
                                <p className="font-semibold">{announcement.message}</p>
                                {announcement.starts_at && (
                                    <p className="mt-0.5 text-xs font-medium opacity-80">
                                        {dateFormatter.format(new Date(announcement.starts_at))}
                                        {announcement.ends_at ? ` – ${dateFormatter.format(new Date(announcement.ends_at))}` : ''}
                                    </p>
                                )}
                            </div>
                            <button
                                type="button"
                                onClick={() => dismiss(announcement.id)}
                                className="focus-ring -m-1 rounded-lg p-1 opacity-70 hover:opacity-100"
                                aria-label={`${copy.dismiss}: ${announcement.message}`}
                            >
                                <X className="size-4" aria-hidden="true" />
                            </button>
                        </div>
                    );
                })}
            </div>
            {hiddenCount > 0 && (
                <button
                    type="button"
                    onClick={() => setShowDismissed(true)}
                    className="focus-ring mt-2 rounded-lg text-xs font-semibold underline underline-offset-4"
                >
                    {copy.restore} ({hiddenCount})
                </button>
            )}
        </div>
    );
}
