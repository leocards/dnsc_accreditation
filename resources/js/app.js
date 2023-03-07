import './bootstrap';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { InertiaProgress } from '@inertiajs/progress';
import { createInertiaApp, Link } from '@inertiajs/inertia-vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const clickOutside = {
    beforeMount: (el, binding) => {
        el.clickOutsideEvent = event => {
            // here I check that click was outside the el and his children
            if (!(el == event.target || el.contains(event.target))) {
                // and if it did, call method provided in attribute value
                binding.value();
            }
        };
        document.addEventListener("click", el.clickOutsideEvent);
    },
    unmounted: el => {
        document.removeEventListener("click", el.clickOutsideEvent);
    },
};

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
        .component("Link", Link)
        .directive("click-outside", clickOutside)
        .use(plugin)
        .use( createPinia() )
        .mount(el)
    },
});

InertiaProgress.init({
    color: '#16a34a',
    showSpinner: true,
    includeCSS: true,
})