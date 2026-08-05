import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export function PageAnnouncer() {
    const { url } = usePage();
    const [announcement, setAnnouncement] = useState('');

    useEffect(() => {
        const frame = window.requestAnimationFrame(() => setAnnouncement(document.title));

        return () => window.cancelAnimationFrame(frame);
    }, [url]);

    return (
        <div className="sr-only" role="status" aria-live="polite" aria-atomic="true">
            {announcement}
        </div>
    );
}
