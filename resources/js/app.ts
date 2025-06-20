import '../css/app.css';
import './bootstrap';

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import '@fortawesome/fontawesome-free/css/all.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import {createApp, DefineComponent, h} from 'vue';

import.meta.glob([
    '../fonts/**/*',
])

const appName = 'Haarlem Festival'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent<DefineComponent>(`./Pages/${name}.vue`, import.meta.glob<false, '', DefineComponent>('./Pages/**/*.vue')),
    progress: {
        color: '#2565c7;',
    },
    setup: ({el, App, props, plugin}) => {
        createApp({render: () => h(App, props)})
            .use(plugin)
            .mount(el);
    },
}).then(() => {
});
