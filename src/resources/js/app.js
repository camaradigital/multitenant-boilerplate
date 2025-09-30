// resources/js/app.js

import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { vMaska } from 'maska/vue';


// Obtém o nome da aplicação do arquivo .env com um fallback para 'Laravel'.
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    /**
     * Define o título de cada página dinamicamente.
     * O título será o que você definir no seu componente Vue + o nome do app.
     */
    title: (title) => `${title} - ${appName}`,

    /**
     * Resolve (encontra e carrega) o componente da página Vue correto
     * de forma dinâmica com base no nome da rota acessada.
     */
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),

    /**
     * Configura e monta a aplicação Vue.
     * Esta função é o coração da inicialização do seu app frontend.
     */
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue, props.initialPage.props.ziggy);
        app.directive('maska', vMaska);
        app.mount(el);
    },

    /**
     * Configura a barra de progresso que aparece no topo da página
     * durante as navegações do Inertia.
     */
    progress: {
        color: '#4B5563', // Cor da barra
        showSpinner: true, // Mostra um ícone de "spinner" girando
    },
});
