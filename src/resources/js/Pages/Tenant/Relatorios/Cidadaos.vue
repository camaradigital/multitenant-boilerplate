<script setup>
import { computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { Search, FileText } from 'lucide-vue-next';

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
    return `${route('admin.relatorios.cidadaos.exportar')}?${params.toString()}`;
});

// Gera a URL de exportação para PDF com os filtros atuais
const exportPdfUrl = computed(() => {
    const params = new URLSearchParams(form.data());
    return `${route('admin.relatorios.cidadaos.exportarPDF')}?${params.toString()}`;
});
</script>

<template>
    <Head title="Relatório de Cidadãos" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório de Cidadãos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Bloco de Filtros -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filtros de Análise</h3>
                        <form @submit.prevent="filtrar">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="lg:col-span-1">
                                    <label for="busca" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar por Nome ou CPF</label>
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <Search class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input type="text" id="busca" v-model="form.busca" class="block w-full rounded-md border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="Digite para buscar...">
                                    </div>
                                </div>
                                <div>
                                    <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período de Cadastro</label>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <input type="date" id="data_inicio" v-model="form.data_inicio" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        <span class="text-gray-500">até</span>
                                        <input type="date" id="data_fim" v-model="form.data_fim" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    </div>
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        <option value="">Todos</option>
                                        <option value="1">Ativo</option>
                                        <option value="0">Inativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-4 space-x-3">
                                <a :href="exportPdfUrl" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 disabled:opacity-25 transition" :class="{ 'opacity-25 pointer-events-none': !cidadaos.total }">
                                    <FileText class="w-4 h-4 mr-2"/> PDF
                                </a>
                                <a :href="exportXlsxUrl" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 disabled:opacity-25 transition" :class="{ 'opacity-25 pointer-events-none': !cidadaos.total }">
                                    <FileText class="w-4 h-4 mr-2"/> XLSX
                                </a>
                                <SecondaryButton type="button" @click="limparFiltros" :disabled="form.processing">Limpar</SecondaryButton>
                                <PrimaryButton :disabled="form.processing">Filtrar</PrimaryButton>
                            </div>
                        </form>
                    </section>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
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
                                    <td colspan="6" class="px-6 py-4 text-center">Nenhum cidadão encontrado.</td>
                                </tr>
                                <tr v-for="cidadao in cidadaos.data" :key="cidadao.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ cidadao.name }}</td>
                                    <td class="px-6 py-4">{{ cidadao.cpf || 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ cidadao.email }}</td>
                                    <td class="px-6 py-4">{{ new Date(cidadao.created_at).toLocaleDateString('pt-BR') }}</td>
                                    <td class="px-6 py-4 text-center font-medium">{{ cidadao.solicitacoes_count }}</td>
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
                    <Pagination class="mt-6" :links="cidadaos.links" v-if="cidadaos.data.length > 0"/>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

