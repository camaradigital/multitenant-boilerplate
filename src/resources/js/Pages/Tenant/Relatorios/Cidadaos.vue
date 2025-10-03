<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Search, Users, FileDown, X, ListChecks } from 'lucide-vue-next';

const props = defineProps({
    cidadaos: Object,
    filtros: Object,
});

const form = useForm({
    busca: props.filtros.busca || '',
    data_inicio: props.filtros.data_inicio || '',
    data_fim: props.filtros.data_fim || '',
    status: props.filtros.status ?? '',
});

const filtrar = () => {
    form.get(route('admin.relatorios.cidadaos'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const limparFiltros = () => {
    form.reset();
    filtrar();
};

const cleanParams = (formData) => {
    return Object.fromEntries(Object.entries(formData).filter(([_, v]) => v != null && v !== ''));
};

const exportUrl = computed(() => `${route('admin.relatorios.cidadaos.exportar')}?${new URLSearchParams(cleanParams(form.data()))}`);
const exportPdfUrl = computed(() => `${route('admin.relatorios.cidadaos.exportarPDF')}?${new URLSearchParams(cleanParams(form.data()))}`);

// --- Helpers ---
const getInitials = (name) => (!name ? '??' : name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase());
const formatDate = (dateString) => new Date(dateString).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
</script>

<template>
    <Head title="Relatório de Cidadãos" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório de Cidadãos
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                 <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <Users :size="32" class="text-white" />
                    </div>

                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pt-12 p-6 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Relatório de Cidadãos</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Filtre e visualize os dados dos cidadãos cadastrados.</p>
                        </div>
                        <div class="flex-shrink-0">
                             <Dropdown align="right" width="48" :disabled="!cidadaos.total">
                                <template #trigger>
                                    <button class="inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-gray-700 text-white hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <FileDown class="w-4 h-4 mr-2"/>
                                        Exportar
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="exportPdfUrl" as="a" target="_blank">Exportar como PDF</DropdownLink>
                                    <DropdownLink :href="exportUrl" as="a" target="_blank">Exportar como XLSX</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                        <form @submit.prevent="filtrar">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                                <div class="lg:col-span-2">
                                    <label for="busca" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar por Nome, CPF ou E-mail</label>
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <Search class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input type="text" id="busca" v-model="form.busca" class="block w-full rounded-md border-gray-300 pl-10 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" placeholder="Digite para buscar...">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período de Cadastro</label>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <input type="date" v-model="form.data_inicio" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                        <input type="date" v-model="form.data_fim" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="limparFiltros" title="Limpar Filtros" class="inline-flex items-center justify-center p-2.5 h-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md shadow-sm transition-colors">
                                        <X class="h-5 w-5"/>
                                    </button>
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50" :disabled="form.processing">
                                        <Search class="w-4 h-4 mr-2"/>
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="p-6">
                        <div class="mb-4 text-sm font-medium text-gray-600 dark:text-gray-400">
                            {{ cidadaos.total }} cidadãos encontrados.
                        </div>

                         <div v-if="cidadaos.data.length === 0" class="text-center py-16">
                            <Users class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Nenhum cidadão encontrado</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ajuste os filtros ou aguarde novos cadastros para visualizar o relatório.</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="cidadao in cidadaos.data" :key="cidadao.id" class="p-4 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 hover:border-emerald-500 dark:hover:border-emerald-400 transition-colors duration-200">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                    <div class="flex items-center gap-4 flex-grow">
                                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <span class="text-lg font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(cidadao.name) }}</span>
                                        </div>
                                        <div class="flex-grow">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-gray-900 dark:text-white">{{ cidadao.name }}</p>
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full" :class="cidadao.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'">
                                                    {{ cidadao.is_active ? 'Ativo' : 'Inativo' }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ cidadao.email }} • {{ cidadao.cpf || 'CPF não informado' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 flex flex-col sm:flex-row sm:items-center gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400 w-full sm:w-auto border-t sm:border-t-0 pt-3 sm:pt-0">
                                         <div class="flex items-center gap-2" title="Nº de Solicitações">
                                            <ListChecks class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                            <span class="font-medium">{{ cidadao.solicitacoes_count }} solicitações</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-500 dark:text-gray-500">Cadastro:</span> {{ formatDate(cidadao.created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Pagination class="pt-6" :links="cidadaos.links" v-if="cidadaos.data.length > 0"/>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
