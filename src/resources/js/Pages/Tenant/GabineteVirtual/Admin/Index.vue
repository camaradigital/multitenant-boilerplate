<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash';
import { Inbox, Search } from 'lucide-vue-next';

const props = defineProps({
    mensagens: Object,
    filters: Object,
});

const search = ref(props.filters.search);
const status = ref(props.filters.status || 'todos');

watch([search, status], debounce(function ([searchValue, statusValue]) {
    router.get(route('admin.gabinete-virtual.index'), {
        search: searchValue,
        status: statusValue === 'todos' ? undefined : statusValue,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const getStatusClass = (status) => {
    switch (status) {
        case 'Pendente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'Resolvido':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'Sem Solução':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};
</script>

<template>
    <Head title="Gabinete Virtual" />

    <TenantLayout title="Gabinete Virtual">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Caixa de Entrada - Gabinete Virtual
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Inbox :size="32" class="icon-in-badge" /></div>

                <div class="p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Caixa de Entrada Virtual</h2>
                        <p class="form-subtitle">Gerencie as mensagens e solicitações enviadas pelos cidadãos.</p>
                    </div>
                </div>

                <div class="px-4 md:px-6 pt-4">
                     <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="w-full md:w-2/3">
                            <label for="search-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar por Assunto ou Cidadão</label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <Search class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    id="search-filter"
                                    v-model="search"
                                    class="input-form pl-10"
                                    placeholder="Digite para buscar..."
                                />
                            </div>
                        </div>
                        <div class="w-full md:w-1/3">
                            <label for="status-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por Status</label>
                            <select id="status-filter" v-model="status" class="mt-1 input-form">
                                <option value="todos">Todos os Status</option>
                                <option value="Pendente">Pendente</option>
                                <option value="Resolvido">Resolvido</option>
                                <option value="Sem Solução">Sem Solução</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-4 md:p-6 border-t-dynamic">
                     <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                             <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Cidadão</th>
                                    <th scope="col" class="px-6 py-3">Assunto</th>
                                    <th scope="col" class="px-6 py-3">Data</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="mensagens.data.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center">Nenhuma mensagem encontrada.</td>
                                </tr>
                                <tr v-for="mensagem in mensagens.data" :key="mensagem.id" class="bg-white border-b dark:bg-gray-800/50 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ mensagem.user.name }}</td>
                                    <td class="px-6 py-4">{{ mensagem.assunto }}</td>
                                    <td class="px-6 py-4">{{ new Date(mensagem.created_at).toLocaleString('pt-BR') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(mensagem.status)">
                                            {{ mensagem.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('admin.gabinete-virtual.show', mensagem.id)" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline">
                                            Ver Detalhes
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="pt-6" :links="mensagens.links" v-if="mensagens.data.length > 0"/>
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
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

/* Estilo para inputs e selects do formulário */
.input-form { @apply block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500; }
</style>
