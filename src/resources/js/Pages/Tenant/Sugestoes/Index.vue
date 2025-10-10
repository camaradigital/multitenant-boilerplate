<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash';
import { Lightbulb, Search } from 'lucide-vue-next';

const props = defineProps({
    sugestoes: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('admin.sugestoes.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const getStatusClass = (status) => {
    const classes = {
        'Recebida': 'badge-pending',
        'Em Análise': 'badge-analyzing',
        'Arquivada': 'badge-archived',
        'Aprovada': 'badge-active',
    };
    return classes[status] || 'badge-archived';
};
</script>

<template>
    <Head title="Sugestões de Projetos de Lei" />

    <TenantLayout title="Sugestões de Projetos de Lei">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Sugestões de Projetos de Lei
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="content-container">
                    <div class="form-icon"><Lightbulb :size="32" class="icon-in-badge" /></div>

                    <div class="p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Sugestões Recebidas</h2>
                            <p class="form-subtitle">Gerencie e analise as sugestões de projetos de lei enviadas através do portal.</p>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 border-b-dynamic">
                        <div class="w-full md:w-1/2 lg:w-1/3">
                            <label for="search-filter" class="form-label">Buscar por Título, Cidadão ou Protocolo</label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <Search class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    id="search-filter"
                                    v-model="search"
                                    class="form-input pl-11"
                                    placeholder="Digite para buscar..."
                                />
                            </div>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="sugestoes.data.length > 0">
                             <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5 dark:text-gray-300">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Protocolo</th>
                                            <th scope="col" class="px-6 py-3">Título</th>
                                            <th scope="col" class="px-6 py-3">Cidadão</th>
                                            <th scope="col" class="px-6 py-3">Data</th>
                                            <th scope="col" class="px-6 py-3">Status</th>
                                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-white/10">
                                        <tr v-for="sugestao in sugestoes.data" :key="sugestao.id" class="hover:bg-gray-50 dark:hover:bg-black/10">
                                            <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">{{ sugestao.protocolo }}</td>
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white max-w-xs truncate">{{ sugestao.titulo }}</td>
                                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ sugestao.cidadao_nome }}</td>
                                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ new Date(sugestao.created_at).toLocaleDateString('pt-BR') }}</td>
                                            <td class="px-6 py-4">
                                                <span :class="getStatusClass(sugestao.status)" class="badge-base">
                                                    {{ sugestao.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <Link :href="route('admin.sugestoes.show', sugestao.id)" class="btn-secondary-sm">Ver Detalhes</Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                         <div v-else class="empty-state">
                            <Lightbulb class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">Nenhuma sugestão encontrada</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nenhum registro corresponde à sua busca.</p>
                        </div>
                    </div>

                     <div class="px-6 pb-4 border-t border-gray-200 dark:border-white/10 pt-4" v-if="sugestoes.data.length > 0">
                        <Pagination :links="sugestoes.links" />
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos unificados do modelo */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white dark:text-[#0A1E1C]; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.empty-state { @apply text-center py-12 px-6; }

/* Estilo para inputs do formulário */
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }

.btn-secondary-sm { @apply inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }

/* --- ESTILOS DE BADGE --- */
.badge-base { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold; }
.badge-pending { @apply bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200; }
.badge-analyzing { @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200; }
.badge-archived { @apply bg-gray-100 text-gray-800 dark:bg-gray-700/40 dark:text-gray-300; }
.badge-active { @apply bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200; }
</style>
