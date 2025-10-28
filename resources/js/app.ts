import 'vue-sonner/style.css';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent, App as VueApp } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import { can as canHelper, createCan } from './lib/permissions';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) }) as VueApp;
        // Provide global $can helper and v-can directive
        app.config.globalProperties.$can = canHelper;
        app.directive('can', {
            mounted(el, binding) {
                const can = createCan();
                const required = binding.value as string | string[];
                // store original display
                const original = (el as HTMLElement).style.display || '';
                (el as any).__vcan_display = original;
                (el as HTMLElement).style.display = can(required)
                    ? original
                    : 'none';
            },
            updated(el, binding) {
                const can = createCan();
                const required = binding.value as string | string[];
                const original = (el as any).__vcan_display ?? '';
                (el as HTMLElement).style.display = can(required)
                    ? original
                    : 'none';
            },
        });

        app.use(plugin).mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
