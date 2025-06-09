import { ref, watch } from 'vue';

type Theme = 'light' | 'dark' | 'system';

const theme = ref<Theme>(localStorage.getItem('theme') as Theme || 'system');

export function useTheme() {
    const setTheme = (newTheme: Theme) => {
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);
        applyTheme();
    };

    const applyTheme = () => {
        const root = window.document.documentElement;
        root.classList.remove('light', 'dark');

        if (theme.value === 'system') {
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            root.classList.add(systemTheme);
        } else {
            root.classList.add(theme.value);
        }
    };

    // Watch for system theme changes
    if (theme.value === 'system') {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);
    }

    // Initial theme application
    applyTheme();

    return {
        theme,
        setTheme,
    };
} 