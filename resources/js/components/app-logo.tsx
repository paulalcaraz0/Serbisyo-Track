import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="text-primary flex aspect-square size-9 items-center justify-center">
                <AppLogoIcon className="size-9" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-none font-semibold">SerbisyoTrack</span>
                <span className="text-muted-foreground truncate text-[0.68rem] leading-none">Barangay Haraya demo</span>
            </div>
        </>
    );
}
