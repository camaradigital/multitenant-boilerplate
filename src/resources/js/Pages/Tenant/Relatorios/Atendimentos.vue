<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import KpiCard from '@/Components/KpiCard.vue'; // 1. Importar o componente reutilizável
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { BarChart3, Clock, Star } from 'lucide-vue-next'; // 2. Importar os ícones do Lucide

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

const exportUrl = computed(() => {
    const params = new URLSearchParams(form.data());
    return `${route('admin.relatorios.atendimentos.exportar')}?${params.toString()}`;
});

// Adiciona a URL de exportação para PDF
const exportPdfUrl = computed(() => {
    const params = new URLSearchParams(form.data());
    return `${route('admin.relatorios.atendimentos.exportarPDF')}?${params.toString()}`;
});

const chartData = computed(() => {
    const labels = props.estatisticas.distribuicaoStatus ? Object.keys(props.estatisticas.distribuicaoStatus) : [];
    const data = props.estatisticas.distribuicaoStatus ? Object.values(props.estatisticas.distribuicaoStatus) : [];

    return {
        labels: labels,
        datasets: [{
            label: 'Total de Solicitações',
            backgroundColor: '#4f46e5',
            borderColor: '#4f46e5',
            borderRadius: 4,
            data: data,
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { precision: 0 }
        }
    }
};

// 3. Prepara os dados para o componente KpiCard, passando os ícones como objetos
const kpiData = computed(() => [
    {
        title: 'Total de Solicitações',
        value: props.estatisticas.totalSolicitacoes,
        unit: 'no período',
        icon: BarChart3,
        colorClass: 'text-sky-700 dark:text-sky-200'
    },
    {
        title: 'Tempo Médio de Finalização',
        value: props.estatisticas.tempoMedioHoras,
        unit: 'horas',
        icon: Clock,
        colorClass: 'text-rose-700 dark:text-rose-200'
    },
    {
        title: 'Serviço Mais Solicitado',
        value: props.estatisticas.servicoMaisSolicitado,
        icon: Star,
        colorClass: 'text-amber-700 dark:text-amber-200'
    }
]);
</script>

<template>
    <Head title="Painel de Atendimentos" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Painel de Atendimentos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- SEÇÃO DE KPIs RENDERIZADA COM O COMPONENTE REUTILIZÁVEL -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <KpiCard
                        v-for="(item, index) in kpiData"
                        :key="index"
                        :title="item.title"
                        :value="item.value"
                        :unit="item.unit"
                        :icon="item.icon"
                        :colorClass="item.colorClass"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filtros de Análise</h3>
                        <form @submit.prevent="filtrar">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Início</label>
                                    <input type="date" id="data_inicio" v-model="form.data_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                </div>
                                <div>
                                    <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Fim</label>
                                    <input type="date" id="data_fim" v-model="form.data_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                </div>
                                <div>
                                    <label for="tipo_servico_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Serviço</label>
                                    <select id="tipo_servico_id" v-model="form.tipo_servico_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        <option value="">Todos</option>
                                        <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="funcionario_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Funcionário</label>
                                    <select id="funcionario_id" v-model="form.funcionario_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        <option value="">Todos</option>
                                        <option v-for="func in funcionarios" :key="func.id" :value="func.id">{{ func.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-4 space-x-3">
                                <!-- Botão Exportar PDF adicionado -->
                                <a :href="exportPdfUrl" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 disabled:opacity-25 transition" :class="{ 'opacity-25 pointer-events-none': !solicitacoes.total }">
                                    Exportar PDF
                                </a>
                                <a :href="exportUrl" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 disabled:opacity-25 transition" :class="{ 'opacity-25 pointer-events-none': !solicitacoes.total }">
                                    Exportar XLSX
                                </a>
                                <SecondaryButton type="button" @click="limparFiltros" :disabled="form.processing">Limpar</SecondaryButton>
                                <PrimaryButton :disabled="form.processing">Analisar</PrimaryButton>
                            </div>
                        </form>
                    </section>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="lg:col-span-2 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Solicitações por Status</h3>
                        <div class="h-80 relative">
                             <Bar v-if="solicitacoes.total > 0" :data="chartData" :options="chartOptions" />
                             <div v-else class="flex items-center justify-center h-full text-gray-500">
                                 Nenhum dado para exibir no gráfico.
                             </div>
                        </div>
                    </div>

                    <div class="lg:col-span-3 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Detalhes dos Atendimentos</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Protocolo</th>
                                        <th scope="col" class="px-6 py-3">Cidadão</th>
                                        <th scope="col" class="px-6 py-3">Serviço</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="solicitacoes.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center">Nenhum atendimento encontrado.</td>
                                    </tr>
                                    <tr v-for="solicitacao in solicitacoes.data" :key="solicitacao.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4">
                                            <a :href="route('admin.solicitacoes.show', solicitacao.id)" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline" @click.prevent="$inertia.visit(route('admin.solicitacoes.show', solicitacao.id))">
                                                #{{ solicitacao.id }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">{{ solicitacao.cidadao?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ solicitacao.servico?.nome || 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ solicitacao.status }}</td>
                                        <td class="px-6 py-4">{{ new Date(solicitacao.created_at).toLocaleDateString('pt-BR') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination class="mt-6" :links="solicitacoes.links" v-if="solicitacoes.data.length > 0"/>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
    /* Estilos para o KpiCard importado */
    .kpi-card { @apply bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center; }
    .kpi-title { @apply text-sm font-medium text-gray-600 dark:text-gray-400; }
    .kpi-value { @apply text-3xl font-extrabold text-gray-900 dark:text-white mt-1; }
    .kpi-unit { @apply text-lg font-semibold text-gray-500 dark:text-gray-500; }
    .kpi-icon-wrapper { @apply ml-auto p-3 bg-gray-100 dark:bg-gray-700/50 rounded-full; }
</style>

