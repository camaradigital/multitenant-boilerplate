<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { Briefcase, BarChart3, Clock, Star, FileDown, Search, X, ListX } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    solicitacoes: Object,
    tiposServico: Array,
    funcionarios: Array,
    filtros: Object,
    estatisticas: Object,
});

const form = useForm({
    data_inicio: props.filtros.data_inicio || '',
    data_fim: props.filtros.data_fim || '',
    tipo_servico_id: props.filtros.tipo_servico_id || '',
    funcionario_id: props.filtros.funcionario_id || '',
    status: props.filtros.status || '',
});

const filtrar = () => {
    form.get(route('admin.relatorios.atendimentos'), {
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

const exportUrl = computed(() => `${route('admin.relatorios.atendimentos.exportar')}?${new URLSearchParams(cleanParams(form.data()))}`);
const exportPdfUrl = computed(() => `${route('admin.relatorios.atendimentos.exportarPDF')}?${new URLSearchParams(cleanParams(form.data()))}`);

// --- Helpers e Reatividade ---
const getInitials = (name) => (!name ? 'N/A' : name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase());
const formatDate = (dateString) => new Date(dateString).toLocaleDateString('pt-BR', { timeZone: 'UTC' });

const getStatusClass = (status) => {
    const classes = {
        'Pendente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
        'Em Andamento': 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
        'Finalizado': 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
        'Cancelado': 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const isDarkMode = ref(false);
const textColor = computed(() => isDarkMode.value ? '#cbd5e1' : '#4b5563');
const gridColor = computed(() => isDarkMode.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)');

onMounted(() => {
    const observer = new MutationObserver(() => isDarkMode.value = document.documentElement.classList.contains('dark'));
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    isDarkMode.value = document.documentElement.classList.contains('dark');
});

// --- Dados do Gráfico ---
const statusColors = {
    'Pendente': '#eab308',
    'Em Andamento': '#3b82f6',
    'Finalizado': '#22c55e',
    'Cancelado': '#ef4444',
};

const chartData = computed(() => {
    const labels = props.estatisticas.distribuicaoStatus ? Object.keys(props.estatisticas.distribuicaoStatus) : [];
    const data = props.estatisticas.distribuicaoStatus ? Object.values(props.estatisticas.distribuicaoStatus) : [];
    const backgroundColors = labels.map(label => statusColors[label] || '#6b7280');

    return {
        labels,
        datasets: [{
            label: 'Total de Solicitações',
            backgroundColor: backgroundColors,
            borderRadius: 4,
            data: data,
        }]
    };
});

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        title: {
            display: true,
            text: 'Solicitações por Status',
            color: textColor.value,
            font: { size: 16, weight: 'bold' }
        }
    },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0, color: textColor.value }, grid: { color: gridColor.value } },
        x: { ticks: { color: textColor.value }, grid: { display: false } }
    }
}));
</script>

<template>
    <Head title="Painel de Atendimentos" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Painel de Atendimentos
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                 <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <Briefcase :size="32" class="text-white" />
                    </div>

                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pt-12 p-6 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Painel de Atendimentos</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visão geral e estatísticas sobre as solicitações de serviço.</p>
                        </div>
                        <div class="flex-shrink-0">
                             <Dropdown align="right" width="48" :disabled="!solicitacoes.total">
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

                    <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-full text-blue-600 dark:text-blue-300"><BarChart3 class="h-6 w-6"/></div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total de Solicitações</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ estatisticas.totalSolicitacoes }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                            <div class="p-3 bg-rose-100 dark:bg-rose-900/50 rounded-full text-rose-600 dark:text-rose-300"><Clock class="h-6 w-6"/></div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tempo Médio Finalização</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ estatisticas.tempoMedioHoras || 0 }} <span class="text-base font-normal">horas</span></p>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                            <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-full text-amber-600 dark:text-amber-300"><Star class="h-6 w-6"/></div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Serviço Mais Solicitado</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white truncate" :title="estatisticas.servicoMaisSolicitado || 'N/A'">{{ estatisticas.servicoMaisSolicitado || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-white/10">
                        <form @submit.prevent="filtrar">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                                <div class="lg:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período</label>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <input type="date" v-model="form.data_inicio" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                        <input type="date" v-model="form.data_fim" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                </div>
                                <div>
                                    <label for="tipo_servico_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serviço</label>
                                    <select id="tipo_servico_id" v-model="form.tipo_servico_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                        <option value="">Todos</option>
                                        <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="funcionario_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Funcionário</label>
                                    <select id="funcionario_id" v-model="form.funcionario_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                        <option value="">Todos</option>
                                        <option v-for="func in funcionarios" :key="func.id" :value="func.id">{{ func.name }}</option>
                                    </select>
                                </div>
                                 <div class="flex gap-2 lg:col-span-2 justify-end">
                                    <button type="button" @click="limparFiltros" title="Limpar Filtros" class="inline-flex items-center justify-center p-2.5 h-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md shadow-sm transition-colors">
                                        <X class="h-5 w-5"/>
                                    </button>
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50" :disabled="form.processing">
                                        <Search class="w-4 h-4 mr-2"/>
                                        Analisar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
                            <div class="lg:col-span-2 h-80">
                                <Bar v-if="solicitacoes.total > 0" :data="chartData" :options="chartOptions" />
                                <div v-else class="flex flex-col items-center justify-center h-full text-center text-gray-500 dark:text-gray-400">
                                    <BarChart3 class="h-12 w-12 mb-4" />
                                    <p>Nenhum dado para exibir no gráfico.</p>
                                </div>
                            </div>

                            <div class="lg:col-span-3">
                                <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">Detalhes dos Atendimentos</h3>
                                <div v-if="solicitacoes.data.length === 0" class="flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 py-16">
                                    <ListX class="h-12 w-12 mb-4" />
                                    <p>Nenhum atendimento encontrado para os filtros selecionados.</p>
                                </div>
                                <div v-else class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5">
                                            <tr>
                                                <th scope="col" class="px-6 py-3">Protocolo</th>
                                                <th scope="col" class="px-6 py-3">Cidadão</th>
                                                <th scope="col" class="px-6 py-3">Serviço</th>
                                                <th scope="col" class="px-6 py-3">Data</th>
                                                <th scope="col" class="px-6 py-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="solicitacao in solicitacoes.data" :key="solicitacao.id" class="bg-white border-b dark:bg-gray-800/50 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                                <td class="px-6 py-4">
                                                    <a :href="route('admin.solicitacoes.show', solicitacao.id)" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline">#{{ solicitacao.id }}</a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(solicitacao.cidadao?.name) }}</span>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div class="font-medium text-gray-900 dark:text-white">{{ solicitacao.cidadao?.name || 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">{{ solicitacao.servico?.nome || 'N/A' }}</td>
                                                <td class="px-6 py-4">{{ formatDate(solicitacao.created_at) }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(solicitacao.status)">{{ solicitacao.status }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <Pagination class="pt-6" :links="solicitacoes.links" v-if="solicitacoes.data.length > 0"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
