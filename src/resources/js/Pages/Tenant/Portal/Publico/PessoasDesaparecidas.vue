<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import TenantPublicLayout from '@/Layouts/TenantPublicLayout.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { initFlowbite } from 'flowbite';

import { AlertTriangle, Users, Calendar, MapPin, Search } from 'lucide-vue-next';

const props = defineProps({
    pessoas: Object,
    tenant: Object,
    filtros: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();

const isLoggedIn = computed(() => !!page.props.auth.user);

const currentLayout = computed(() => {
    return isLoggedIn.value ? TenantLayout : TenantPublicLayout;
});

const filterForm = ref({
    search: props.filtros?.search || '',
});

onMounted(() => initFlowbite());
router.on('success', () => initFlowbite());

let debounceTimeout = null;
watch(filterForm, (newValue) => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('portal.pessoas-desaparecidas.index'), { search: newValue.search }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }, 500);
}, { deep: true });

// --- FUNÇÃO DE FORMATAÇÃO DE DATA APRIMORADA COM AJUSTE ---
const formatDate = (dateString) => {
    // Verifica se a string existe, não está vazia e é realmente uma string.
    if (!dateString || typeof dateString !== 'string') {
        return 'Data não informada';
    }

    // A string de data está vindo com microssegundos. O JavaScript Date não suporta isso.
    // Usamos um regex para remover tudo a partir do ponto decimal e o 'Z' no final.
    const cleanDateString = dateString.replace(/\.\d{6}Z$/, '');

    // Tenta criar um novo objeto de data.
    const dateObject = new Date(cleanDateString);

    // Se a data for inválida (NaN), retorna uma mensagem com a string original para ajudar na depuração.
    if (isNaN(dateObject.getTime())) {
        console.error('Erro ao converter a data. String original:', dateString);
        return `Formato de data inválido: ${dateString}`;
    }

    // Formata a data para um formato de exibição amigável.
    return dateObject.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Pessoas Desaparecidas" />

    <div class="page-container font-sans">
        <component :is="currentLayout" :tenant="tenant" :can-login="canLogin" :can-register="canRegister">
            <div class="flex justify-center items-start py-12 px-4">
                <div class="content-container w-full max-w-7xl">

                    <div class="form-icon">
                        <Users :size="32" class="icon-in-badge" />
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Pessoas Desaparecidas</h2>
                            <p class="form-subtitle">Ajude a encontrar e reunir famílias. Sua informação faz a diferença.</p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 p-4">
                        <div class="w-full md:w-2/3 lg:w-1/2 mx-auto">
                            <form class="flex items-center" @submit.prevent>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-gray-500">
                                        <Search class="w-5 h-5" />
                                    </div>
                                    <input type="text" v-model="filterForm.search" placeholder="Buscar por nome..." class="form-input !pl-11">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div class="bg-yellow-100 dark:bg-yellow-800/30 border border-yellow-300 dark:border-yellow-600 p-5 rounded-xl mb-8 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <AlertTriangle class="h-5 w-5 text-yellow-500" aria-hidden="true" />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200 font-medium">
                                        Se você tiver qualquer informação sobre alguma das pessoas listadas, por favor, entre em contato com as autoridades competentes ou com a {{ tenant.name }} através dos canais oficiais.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="pessoas.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                               <div v-for="pessoa in pessoas.data" :key="pessoa.id" class="pessoa-card group">
                                    <div class="relative overflow-hidden rounded-t-2xl">
                                        <img :src="pessoa.foto_url" :alt="'Foto de ' + pessoa.nome_completo" class="w-full h-80 object-cover object-center transform transition-transform duration-500 group-hover:scale-110">
                                        <div class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-4 py-1.5 rounded-bl-lg shadow-lg">DESAPARECIDO</div>
                                    </div>
                                    <div class="p-5 flex-grow flex flex-col">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ pessoa.nome_completo }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ pessoa.idade }} anos</p>
                                        <div class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300 border-t border-gray-200 dark:border-gray-700 pt-3">
                                            <p class="flex items-center"><Calendar class="w-4 h-4 mr-2.5 text-emerald-500 flex-shrink-0" /> <span class="font-medium mr-1.5">Visto em:</span>{{ formatDate(pessoa.data_desaparecimento) }}</p>
                                            <p class="flex items-center"><MapPin class="w-4 h-4 mr-2.5 text-emerald-500 flex-shrink-0" /> <span class="font-medium mr-1.5">Local:</span>{{ pessoa.local_desaparecimento }}</p>
                                        </div>
                                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 line-clamp-3 leading-relaxed flex-grow">{{ pessoa.detalhes }}</p>
                                    </div>
                                </div>
                        </div>

                        <div v-else class="py-16 text-center text-gray-500 dark:text-gray-400">
                               <div class="flex flex-col items-center justify-center">
                                    <Users class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-600" />
                                    <p class="font-semibold text-lg text-gray-700 dark:text-gray-300">Nenhum registro encontrado</p>
                                    <p class="text-sm mt-1">Não há pessoas desaparecidas publicadas no momento.</p>
                                </div>
                        </div>
                    </div>

                    <nav v-if="pessoas.links && pessoas.links.length > 3" class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4 border-t-dynamic">
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            Mostrando <span class="font-semibold text-gray-900 dark:text-white">{{ pessoas.from || 0 }}-{{ pessoas.to || 0 }}</span> de <span class="font-semibold text-gray-900 dark:text-white">{{ pessoas.total || 0 }}</span>
                        </span>
                        <ul class="inline-flex items-stretch -space-x-px">
                            <li v-for="(link, index) in pessoas.links" :key="index">
                                <span v-if="!link.url" class="pagination-link is-disabled" v-html="link.label"></span>
                                <Link v-else :href="link.url" preserve-scroll preserve-state class="pagination-link" :class="{'is-active': link.active}" v-html="link.label"></Link>
                            </li>
                        </ul>
                    </nav>

                </div>
            </div>
        </component>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.header-title {
    font-family: 'Poppins', sans-serif;
}
.page-container { @apply bg-gray-100 dark:bg-transparent min-h-screen; }
.dark .page-container { background: radial-gradient(circle, #0D2C2A, #0A1E1C); }
.content-container {
    @apply relative w-full pt-16 rounded-3xl shadow-xl bg-white border border-gray-200;
    @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm;
}
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon {
    @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg;
    @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30;
}
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.header-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-300; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-300; }
.form-input {
    @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5;
    @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400;
    @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500;
    @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500;
    @apply dark:focus:ring-green-500 dark:focus:border-green-500;
}
.pagination-link {
    @apply flex items-center justify-center text-sm py-2 px-3 leading-tight border transition-colors duration-200;
    @apply text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700;
    @apply dark:text-gray-400 dark:bg-transparent dark:border-gray-700 dark:hover:bg-gray-800/50 dark:hover:text-white;
}
.pagination-link.is-active {
    @apply z-10 text-emerald-600 bg-emerald-50 border-emerald-300;
    @apply dark:text-white dark:bg-green-500/10 dark:border-green-500/50;
}
.pagination-link.is-disabled { @apply cursor-not-allowed opacity-50; }
ul li:first-child .pagination-link { @apply rounded-l-lg; }
ul li:last-child .pagination-link { @apply rounded-r-lg; }

.pessoa-card {
    @apply bg-white dark:bg-gray-800/50 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1.5 border border-gray-100 dark:border-gray-700/50 flex flex-col;
}
</style>
