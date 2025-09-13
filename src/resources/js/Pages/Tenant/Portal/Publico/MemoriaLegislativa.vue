<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import TenantPublicLayout from '@/Layouts/TenantPublicLayout.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue'; // Importa o layout de usuário autenticado
import { Calendar, Users, ChevronDown, X } from 'lucide-vue-next';
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';

// Props
const props = defineProps({
    timelineJson: Object,
    tenant: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});

// Acesso às props globais do Inertia
const page = usePage();

// Propriedade computada para verificar se o usuário está logado
const isLoggedIn = computed(() => !!page.props.auth.user);

// Propriedade computada para determinar qual layout usar
const currentLayout = computed(() => {
    return isLoggedIn.value ? TenantLayout : TenantPublicLayout;
});


// --- ESTADO REATIVO ---
const isMounted = ref(false);
const isModalOpen = ref(false);
const selectedCouncilor = ref(null);
let timelineInstance;

// --- FUNÇÕES DO MODAL ---
function openModal(councilorData) {
    selectedCouncilor.value = councilorData;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedCouncilor.value = null;
    }, 300);
}


// --- LÓGICA DE CARREGAMENTO DE ASSETS ---
function loadAsset(tagName, attributes) {
    return new Promise((resolve, reject) => {
        const existingElement = tagName === 'script' ? document.querySelector(`script[src="${attributes.src}"]`) : document.querySelector(`link[href="${attributes.href}"]`);
        if (existingElement) {
            resolve();
            return;
        }
        const element = document.createElement(tagName);
        Object.assign(element, attributes);
        element.onload = () => resolve();
        element.onerror = () => reject(new Error(`Falha ao carregar o asset: ${attributes.src || attributes.href}`));
        if (tagName === 'script') document.body.appendChild(element);
        else document.head.appendChild(element);
    });
}


// --- LIFECYCLE HOOKS ---
onMounted(async () => {
    isMounted.value = true;
    try {
        await Promise.all([
            loadAsset('link', { rel: 'stylesheet', href: '/vendor/timelinejs3/css/timeline.css', title: 'timeline-styles' }),
            loadAsset('script', { src: '/vendor/timelinejs3/js/timeline.js' }),
        ]);

        await nextTick();

        if (props.timelineJson?.events?.length > 0 && window.TL) {
            const timelineOptions = {
                lang: 'pt-br',
                language: new URL('/vendor/timelinejs3/js/locale/pt-br.json', window.location.origin).href,
                initial_zoom: 0,
                hash_bookmark: true,
                scale_factor: 1,
                font: 'Poppins',
                timenav_position: 'top',
                height: '150vh'
            };
            timelineInstance = new window.TL.Timeline('timeline-embed', props.timelineJson, timelineOptions);

            const timelineContainer = document.getElementById('timeline-embed');
            if (timelineContainer) {
                timelineContainer.addEventListener('click', (event) => {
                    const memberProfile = event.target.closest('.member-profile');
                    if (memberProfile?.dataset.councilor) {
                        try {
                            const councilorData = JSON.parse(memberProfile.dataset.councilor);
                            openModal(councilorData);
                        } catch (e) {
                            console.error("Erro ao processar dados do vereador:", e);
                        }
                    }
                });
            }
        }
    } catch (error) {
        console.error("Falha ao carregar assets da timeline:", error);
    }
});

onUnmounted(() => {
    // Limpeza
});
</script>

<template>
    <Head title="Memória Legislativa" />

    <component :is="currentLayout" :tenant="tenant" :can-login="canLogin" :can-register="canRegister">
        <div class="page-background py-16 sm:py-24">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="text-center mb-12 relative">
                    <div class="absolute top-0 right-0 z-10">
                        <Menu as="div" class="relative">
                            <MenuButton class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                Opções <ChevronDown class="h-5 w-5" />
                            </MenuButton>
                            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                                <MenuItems class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <a href="#timeline-container" :class="[active ? 'bg-gray-100 dark:bg-gray-700' : '', 'block px-4 py-2 text-sm text-gray-700 dark:text-gray-200']">Ir para Timeline</a>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <a href="#about-section" :class="[active ? 'bg-gray-100 dark:bg-gray-700' : '', 'block px-4 py-2 text-sm text-gray-700 dark:text-gray-200']">Sobre o Projeto</a>
                                        </MenuItem>
                                    </div>
                                </MenuItems>
                            </Transition>
                        </Menu>
                    </div>

                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl flex items-center justify-center gap-3">
                        <Calendar class="h-8 w-8 text-emerald-600 dark:text-green-400" /> Memória Legislativa
                    </h1>
                    <p class="mt-4 text-lg leading-8 text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                        Explore a história e conheça os representantes que moldaram o futuro da nossa cidade.
                    </p>
                </div>

                <Transition enter-active-class="transition ease-out duration-700" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                    <div v-if="isMounted" id="timeline-container" class="timeline-container">
                        <div v-if="timelineJson?.events?.length > 0">
                            <div id="timeline-embed" style="width: 100%; height: 80vh; min-height: 700px;"></div>

                            <Disclosure as="div" id="about-section" class="p-6 border-t border-emerald-500/20 dark:border-green-400/20" v-slot="{ open }">
                                <DisclosureButton class="w-full flex justify-between items-center text-lg font-semibold text-gray-800 dark:text-white hover:text-emerald-600 dark:hover:text-green-400 transition-colors">
                                    <span class="flex items-center gap-2"><Users class="h-6 w-6" /> Sobre a Memória Legislativa</span>
                                    <ChevronDown class="h-5 w-5 transition-transform" :class="{ 'rotate-180': open }" />
                                </DisclosureButton>
                                <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
                                    <DisclosurePanel class="mt-4 text-gray-600 dark:text-gray-300 prose max-w-none">
                                        <p>A Memória Legislativa é um projeto dedicado a preservar e compartilhar a história dos representantes que contribuíram para o desenvolvimento da nossa cidade através das legislaturas.</p>
                                    </DisclosurePanel>
                                </Transition>
                            </Disclosure>
                        </div>
                        <div v-else class="text-center py-20">
                             <p class="text-gray-600 dark:text-gray-400">Nenhum dado disponível para exibir na linha do tempo.</p>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </component>

    <Transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" @click.self="closeModal">
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="selectedCouncilor" class="relative bg-white dark:bg-[#102523] rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col border dark:border-green-400/20">
                    <div class="flex items-start justify-between p-5 border-b rounded-t dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <img :src="selectedCouncilor.photo_url" :alt="selectedCouncilor.name" class="w-16 h-16 rounded-full object-cover border-2 border-emerald-200 dark:border-green-400/50">
                            <div>
                                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ selectedCouncilor.name }}
                                </h3>
                                <p class="text-base text-gray-500 dark:text-gray-400">{{ selectedCouncilor.role }}</p>
                            </div>
                        </div>
                        <button @click="closeModal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <X class="w-5 h-5" />
                            <span class="sr-only">Fechar modal</span>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 overflow-y-auto">
                        <p class="text-base leading-relaxed text-gray-600 dark:text-gray-300 whitespace-pre-wrap">
                            {{ selectedCouncilor.biography || 'Biografia não disponível.' }}
                        </p>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

:global(body) {
    font-family: 'Poppins', sans-serif;
}

.page-background {
    @apply bg-gray-100 dark:bg-transparent transition-colors duration-500;
}
.dark .page-background {
    background: radial-gradient(ellipse at top left, #0D2C2A, #0A1E1C);
}

.timeline-container {
    @apply p-0 sm:p-2 rounded-3xl shadow-2xl transition-colors duration-500;
    @apply bg-white/70 border border-gray-200;
    backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
    @apply dark:bg-[#102C26]/80 dark:border-2 dark:border-green-400/25;
    overflow: hidden;
}

:deep(.tl-timeline) {
    --timeline-background-color: transparent;
    --timeline-color: #4b5563;
    --timeline-meta-color: #6b7280;
    --timeline-headline-color: #111827;
    --timeline-line-color: #e5e7eb;
    --timeline-marker-color: #f9fafb;
    --timeline-marker-line-color: #d1d5db;
    --timeline-arrow-color: #ffffff;
    font-family: 'Poppins', sans-serif !important;
}

:deep(.dark .tl-timeline) {
    --timeline-background-color: transparent;
    --timeline-color: #d1d5db;
    --timeline-meta-color: #9ca3af;
    --timeline-headline-color: #f9fafb;
    --timeline-line-color: #374151;
    --timeline-marker-color: #1f2937;
    --timeline-marker-line-color: #4b5563;
    --timeline-arrow-color: #1f2937;
}

:deep(.tl-timenav) {
    border-bottom: 1px solid var(--timeline-line-color);
}

:deep(.tl-slidenav-next .tl-slidenav-arrow, .tl-slidenav-previous .tl-slidenav-arrow) {
    border-color: var(--timeline-headline-color);
}
:deep(.tl-slidenav-next:hover .tl-slidenav-arrow, .tl-slidenav-previous:hover .tl-slidenav-arrow) {
    border-color: #10b981;
}
:deep(.dark .tl-slidenav-next:hover .tl-slidenav-arrow, .dark .tl-slidenav-previous:hover .tl-slidenav-arrow) {
    border-color: #34d399;
}

:deep(.tl-slide-title) {
    max-height: 200px;
}
:deep(.tl-slide-content) {
    padding-top: 30px;
}

:deep(.tl-slide-content .timeline-summary) {
    font-size: 1.125rem;
    line-height: 1.75;
    color: var(--timeline-color);
    margin-bottom: 1.5rem;
}

:deep(.tl-slide-content h4) {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--timeline-headline-color);
    border-bottom: 2px solid var(--timeline-line-color);
    padding-bottom: 0.5rem;
}

:deep(.mesa-diretora-grid) {
    display: flex;
    flex-wrap: nowrap;
    justify-content: flex-start;
    gap: 1.5rem;
    margin-top: 1rem;
    overflow-x: auto;
    padding-bottom: 1rem;
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f3f4f6;
}

.dark :deep(.mesa-diretora-grid) {
    scrollbar-color: #4b5563 #1f2937;
}

:deep(.gallery-grid) {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

:deep(.member-profile) {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    width: 120px;
    flex-shrink: 0;
    transition: transform 0.2s ease, filter 0.2s ease;
    cursor: pointer;
}

:deep(.member-profile:hover) {
    transform: translateY(-4px);
    filter: brightness(1.1);
}

:deep(.member-profile img) {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--timeline-line-color);
    background-color: var(--timeline-marker-color);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

:deep(.member-profile .member-name) {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--timeline-headline-color);
    margin-top: 0.5rem;
    line-height: 1.25;
}

:deep(.member-profile .member-role) {
    font-size: 0.75rem;
    color: var(--timeline-meta-color);
    text-transform: capitalize;
}
</style>
