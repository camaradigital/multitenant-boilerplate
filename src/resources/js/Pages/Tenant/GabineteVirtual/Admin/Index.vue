<script setup>
import { ref, watch } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { debounce } from 'lodash';
import { Inbox, Search, MailX, ArrowRight, ChevronsUpDown } from 'lucide-vue-next';

const props = defineProps({
    mensagens: Object,
    filters: Object,
});

const search = ref(props.filters.search);
const status = ref(props.filters.status || 'todos');

const processingId = ref(null);

// Lógica de filtro: Permanece a mesma.
watch([search, status], debounce(function ([searchValue, statusValue]) {
    router.get(route('admin.gabinete-virtual.index'), {
        search: searchValue,
        status: statusValue === 'todos' ? undefined : statusValue,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

// Lógica de atualização de status: Permanece a mesma.
const updateStatus = (mensagemId, newStatus) => {
    processingId.value = mensagemId;

    router.patch(route('admin.gabinete-virtual.updateStatus', mensagemId), {
        status: newStatus,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            processingId.value = null;
        }
    });
};

// Lógica de classes de status: Permanece a mesma.
const getStatusClass = (status) => {
    switch (status) {
        case 'Pendente':
            return 'bg-yellow-500 hover:bg-yellow-600 text-white shadow-md shadow-yellow-500/30';
        case 'Resolvido':
            return 'bg-green-500 hover:bg-green-600 text-white shadow-md shadow-green-500/30';
        case 'Sem Solução':
            return 'bg-red-500 hover:bg-red-600 text-white shadow-md shadow-red-500/30';
        default:
            // Corrigido para ser mais discreto, mas ter um hover de cor
            return 'bg-gray-400 hover:bg-gray-500 text-white shadow-md shadow-gray-400/30';
    }
};

// Função auxiliar para obter as iniciais do nome: Permanece a mesma.
const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

// Função para formatar a data de forma mais limpa: Permanece a mesma.
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Gabinete Virtual" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Caixa de Entrada - Gabinete Virtual
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">

                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <Inbox :size="32" class="text-white" />
                    </div>

                    <div class="pt-12 p-6 text-center border-b border-gray-200 dark:border-white/10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Caixa de Entrada Virtual</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie as mensagens e solicitações enviadas pelos cidadãos.</p>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex flex-col md:flex-row gap-4 items-center">
                            <div class="w-full md:w-2/3">
                                <label for="search-filter" class="sr-only">Buscar</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Search class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        id="search-filter"
                                        v-model="search"
                                        class="block w-full rounded-md border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 pl-10 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                                        placeholder="Buscar por assunto ou cidadão..."
                                    />
                                </div>
                            </div>
                            <div class="w-full md:w-1/3">
                                <label for="status-filter" class="sr-only">Filtrar por Status</label>
                                <select id="status-filter" v-model="status" class="block w-full rounded-md border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                                    <option value="todos">Todos os Status</option>
                                    <option value="Pendente">Pendente</option>
                                    <option value="Resolvido">Resolvido</option>
                                    <option value="Sem Solução">Sem Solução</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div v-if="mensagens.data.length === 0" class="text-center py-12">
                            <MailX class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Nenhuma Mensagem Encontrada</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aguardando novas mensagens ou ajuste seus filtros de busca.</p>
                        </div>

                        <div v-else class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                            <table class="w-full min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5 dark:text-gray-300">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 tracking-wider">Cidadão</th>
                                        <th scope="col" class="px-6 py-3 tracking-wider">Assunto</th>
                                        <th scope="col" class="px-6 py-3 tracking-wider">Data de Envio</th>
                                        <th scope="col" class="px-6 py-3 tracking-wider">Status</th> <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ver</span></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                    <tr v-for="mensagem in mensagens.data" :key="mensagem.id" class="bg-white dark:bg-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                                                        <span class="font-semibold text-emerald-700 dark:text-emerald-300">{{ getInitials(mensagem.user.name) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ mensagem.user.name }}</div>
                                                    <div class="text-gray-500 dark:text-gray-400 text-xs">{{ mensagem.user.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300">{{ mensagem.assunto }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(mensagem.created_at) }}</td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Dropdown align="left" width="48">
                                                <template #trigger>
                                                    <button
                                                        :title="`Status atual: ${mensagem.status}`"
                                                        class="inline-flex items-center justify-center w-full min-w-[120px] h-10 px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg transition ease-in-out duration-150 disabled:opacity-75"
                                                        :class="[
                                                            getStatusClass(mensagem.status),
                                                            { 'ring-2 ring-offset-2': mensagem.id === processingId }
                                                        ]"
                                                        :style="{
                                                            '--tw-ring-color':
                                                                mensagem.status === 'Pendente' ? '#f59e0b' :
                                                                mensagem.status === 'Resolvido' ? '#10b981' :
                                                                mensagem.status === 'Sem Solução' ? '#ef4444' : '#6b7280'
                                                        }"
                                                        :disabled="mensagem.id === processingId"
                                                    >
                                                        <template v-if="mensagem.id === processingId">
                                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </template>
                                                        <template v-else>
                                                            <span class="mr-2">{{ mensagem.status }}</span>
                                                            <ChevronsUpDown class="h-4 w-4" />
                                                        </template>
                                                    </button>
                                                </template>
                                                <template #content>
                                                    <DropdownLink
                                                        as="button"
                                                        @click="updateStatus(mensagem.id, 'Pendente')"
                                                        :class="{ 'font-bold bg-gray-100 dark:bg-gray-700': mensagem.status === 'Pendente' }"
                                                    >
                                                        Pendente
                                                    </DropdownLink>
                                                    <DropdownLink
                                                        as="button"
                                                        @click="updateStatus(mensagem.id, 'Resolvido')"
                                                        :class="{ 'font-bold bg-gray-100 dark:bg-gray-700': mensagem.status === 'Resolvido' }"
                                                    >
                                                        Resolvido
                                                    </DropdownLink>
                                                    <DropdownLink
                                                        as="button"
                                                        @click="updateStatus(mensagem.id, 'Sem Solução')"
                                                        :class="{ 'font-bold bg-gray-100 dark:bg-gray-700': mensagem.status === 'Sem Solução' }"
                                                    >
                                                        Sem Solução
                                                    </DropdownLink>
                                                </template>
                                            </Dropdown>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.gabinete-virtual.show', mensagem.id)" class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-semibold transition-colors duration-200">
                                                Ver
                                                <ArrowRight class="h-4 w-4" />
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
        </div>
    </TenantLayout>
</template>
