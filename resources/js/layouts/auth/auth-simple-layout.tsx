import AppLogoIcon from '@/components/app-logo-icon';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

interface AuthLayoutProps {
    children: React.ReactNode;
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({ children, title, description }: AuthLayoutProps) {
    const { name, translations } = usePage<SharedData>().props;

    return (
        <main className="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div className="w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4">
                        <Link href={route('home')} className="focus-ring flex flex-col items-center gap-2 rounded-xl font-medium">
                            <div className="text-primary mb-1 flex size-12 items-center justify-center">
                                <AppLogoIcon className="size-12" />
                            </div>
                            <span className="text-base font-semibold">{name}</span>
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-xl font-medium">{title}</h1>
                            <p className="text-muted-foreground text-center text-sm">{description}</p>
                        </div>
                    </div>
                    {children}
                    <p className="text-muted-foreground border-border border-t pt-5 text-center text-xs leading-5">{translations.home.disclaimer}</p>
                </div>
            </div>
        </main>
    );
}
