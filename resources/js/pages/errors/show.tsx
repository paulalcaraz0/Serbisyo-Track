import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/react';
import { AlertTriangle, ArrowRight, Home, Search, ShieldCheck } from 'lucide-react';

interface Props {
    appName: string;
    status: number;
    copy: {
        meta_title: string;
        eyebrow: string;
        home: string;
        services: string;
        track: string;
        help: string;
        statuses: Record<string, { title: string; description: string }>;
    };
}

export default function ErrorPage({ appName, status, copy }: Props) {
    const statusCopy = copy.statuses[String(status)] ?? copy.statuses['500'] ?? { title: copy.meta_title, description: copy.help };

    return (
        <div className="public-shell bg-background text-foreground min-h-screen">
            <Head title={`${status} · ${copy.meta_title}`}>
                <meta name="robots" content="noindex,nofollow,noarchive" />
            </Head>

            <a href="#main-content" className="skip-link">
                Skip to main content
            </a>
            <div className="bg-primary text-primary-foreground">
                <div className="mx-auto flex max-w-4xl items-center gap-3 px-4 py-3 text-sm font-semibold sm:px-6 lg:px-8">
                    <ShieldCheck className="size-4 shrink-0" aria-hidden="true" />
                    {appName} · Portfolio demonstration
                </div>
            </div>

            <main
                id="main-content"
                tabIndex={-1}
                className="mx-auto flex min-h-[calc(100vh-2.75rem)] max-w-4xl items-center px-4 py-14 sm:px-6 sm:py-20 lg:px-8"
            >
                <div className="border-border bg-card w-full rounded-3xl border p-6 shadow-sm sm:p-10">
                    <div className="flex size-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-200">
                        <AlertTriangle className="size-7" aria-hidden="true" />
                    </div>
                    <p className="text-primary mt-7 text-sm font-bold tracking-[0.12em] uppercase">
                        {copy.eyebrow.replace(':status', String(status))}
                    </p>
                    <h1 id="error-title" className="text-foreground mt-2 max-w-2xl text-3xl font-bold tracking-[-0.035em] sm:text-4xl">
                        {statusCopy.title}
                    </h1>
                    <p className="text-muted-foreground mt-4 max-w-2xl text-base leading-7">{statusCopy.description}</p>
                    <p className="text-muted-foreground mt-3 max-w-2xl text-sm leading-6">{copy.help}</p>

                    <div className="mt-8 flex flex-wrap gap-3">
                        <Button asChild>
                            <a href="/">
                                <Home aria-hidden="true" />
                                {copy.home}
                            </a>
                        </Button>
                        <Button asChild variant="outline">
                            <a href="/services">
                                <Search aria-hidden="true" />
                                {copy.services}
                            </a>
                        </Button>
                        <Button asChild variant="outline">
                            <a href="/track">
                                {copy.track}
                                <ArrowRight aria-hidden="true" />
                            </a>
                        </Button>
                    </div>
                </div>
            </main>
        </div>
    );
}
