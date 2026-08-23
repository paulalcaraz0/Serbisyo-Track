import { useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const getStoredAppearance = (): Appearance => {
    const storedAppearance = localStorage.getItem('appearance');

    return storedAppearance === 'light' || storedAppearance === 'dark' || storedAppearance === 'system' ? storedAppearance : 'system';
};

const applyTheme = (appearance: Appearance) => {
    const isDark = appearance === 'dark' || (appearance === 'system' && mediaQuery.matches);

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

    const themeColor = document.querySelector<HTMLMetaElement>('meta[name="theme-color"]');
    themeColor?.setAttribute('content', isDark ? '#101319' : '#335885');
};

const handleSystemThemeChange = () => {
    applyTheme(getStoredAppearance());
};

export function initializeTheme() {
    applyTheme(getStoredAppearance());

    mediaQuery.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>(getStoredAppearance);

    const updateAppearance = (mode: Appearance) => {
        setAppearance(mode);
        localStorage.setItem('appearance', mode);
        applyTheme(mode);
    };

    useEffect(() => {
        const handleStorageChange = (event: StorageEvent) => {
            if (event.key === 'appearance') {
                const nextAppearance = getStoredAppearance();
                setAppearance(nextAppearance);
                applyTheme(nextAppearance);
            }
        };

        window.addEventListener('storage', handleStorageChange);

        return () => window.removeEventListener('storage', handleStorageChange);
    }, []);

    return { appearance, updateAppearance };
}
