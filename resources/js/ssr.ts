import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { renderToString } from 'vue/server-renderer';

import { DefineComponent, createSSRApp, h } from 'vue';

import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
            setup: ({ App, props, plugin }) => createSSRApp({ render: () => h(App, props) }).use(plugin),
        }),
    { cluster: true },
);
