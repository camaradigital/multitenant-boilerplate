<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Search, Users, FileDown } from 'lucide-vue-next';

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

// Gera a URL de exportação para XLSX com os filtros atuais
const exportXlsxUrl = computed(() => {
    const params = new URLSearchParams(form.data());
    // Remove chaves com valores vazios para não poluir a URL de exportação
    for (const [key, value] of params.entries()) {
        if (!value) {
            params.delete(key);
        }
    }
    return `${route('admin.relatorios.cidadaos.exportar')}?${params.toString()}`;
});

// Gera a URL de exportação para PDF com os filtros atuais
const exportPdfUrl = computed(() => {
    const params = new URLSearchParams(form.data());
    // Remove chaves com valores vazios para não poluir a URL de exportação
    for (const [key, value] of params.entries()) {
        if (!value) {
            params.delete(key);
        }
    }
    return `${route('admin.relatorios.cidadaos.exportarPDF')}?${params.toString()}`;
});
</script>

<template>
    <Head title="Relatório de Cidadãos" />

    <TenantLayout title="Relatório de Cidadãos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório de Cidadãos
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Users :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Relatório de Cidadãos</h2>
                        <p class="form-subtitle">Filtre e visualize os dados dos cidadãos cadastrados.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                         <a :href="exportPdfUrl" target="_blank" class="btn-export-pdf" :class="{ 'opacity-50 pointer-events-none': !cidadaos.total }">
                            <FileDown class="w-4 h-4 mr-2"/> PDF
                        </a>
                        <a :href="exportXlsxUrl" class="btn-export-xlsx" :class="{ 'opacity-50 pointer-events-none': !cidadaos.total }">
                            <FileDown class="w-4 h-4 mr-2"/> XLSX
                        </a>
                    </div>
                </div>

                <div class="px-4 md:px-6 pt-4 pb-6">
                    <form @submit.prevent="filtrar">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="w-full">
                                <label for="busca" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar por Nome ou CPF</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Search class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input type="text" id="busca" v-model="form.busca" class="block w-full rounded-md border-gray-300 pl-10 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" placeholder="Digite para buscar...">
                                </div>
                            </div>
                            <div class="w-full">
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período de Cadastro</label>
                                <div class="flex items-center mt-1 space-x-2">
                                    <input type="date" id="data_inicio" v-model="form.data_inicio" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                    <span class="text-gray-500 dark:text-gray-400">até</span>
                                    <input type="date" id="data_fim" v-model="form.data_fim" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                            </div>
                             <div class="w-full">
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Todos</option>
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-4 space-x-3">
                             <button type="button" @click="limparFiltros" :disabled="form.processing" class="btn-secondary">Limpar</button>
                             <button type="submit" :disabled="form.processing" class="btn-primary">Filtrar</button>
                        </div>
                    </form>
                </div>

                <div class="p-4 md:p-6 border-t-dynamic">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nome</th>
                                    <th scope="col" class="px-6 py-3">CPF</th>
                                    <th scope="col" class="px-6 py-3">E-mail</th>
                                    <th scope="col" class="px-6 py-3">Data de Cadastro</th>
                                    <th scope="col" class="px-6 py-3 text-center">Nº de Solicitações</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="cidadaos.data.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Nenhum cidadão encontrado com os filtros aplicados.</td>
                                </tr>
                                <tr v-for="cidadao in cidadaos.data" :key="cidadao.id" class="bg-white border-b dark:bg-gray-800/50 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ cidadao.name }}</td>
                                    <td class="px-6 py-4">{{ cidadao.cpf || 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ cidadao.email }}</td>
                                    <td class="px-6 py-4">{{ new Date(cidadao.created_at).toLocaleDateString('pt-BR') }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-base text-gray-700 dark:text-gray-200">{{ cidadao.solicitacoes_count }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="cidadao.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                            {{ cidadao.is_active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="pt-6" :links="cidadaos.links" v-if="cidadaos.data.length > 0"/>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos importados diretamente do modelo para consistência */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }
.btn-export-pdf { @apply btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500; }
.btn-export-xlsx { @apply btn-base bg-green-600 text-white hover:bg-green-700 focus:ring-green-500; }
</style>
