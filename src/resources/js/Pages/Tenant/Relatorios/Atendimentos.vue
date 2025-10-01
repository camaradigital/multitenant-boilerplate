<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import KpiCard from '@/Components/KpiCard.vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { Briefcase, BarChart3, Clock, Star, FileDown } from 'lucide-vue-next';

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
    const params = new URLSearchParams(formData);
    for (const [key, value] of params.entries()) {
        if (!value) {
            params.delete(key);
        }
    }
    return params.toString();
};

const exportUrl = computed(() => {
    return `${route('admin.relatorios.atendimentos.exportar')}?${cleanParams(form.data())}`;
});

const exportPdfUrl = computed(() => {
    return `${route('admin.relatorios.atendimentos.exportarPDF')}?${cleanParams(form.data())}`;
});

const chartData = computed(() => {
    const labels = props.estatisticas.distribuicaoStatus ? Object.keys(props.estatisticas.distribuicaoStatus) : [];
    const data = props.estatisticas.distribuicaoStatus ? Object.values(props.estatisticas.distribuicaoStatus) : [];

    return {
        labels: labels,
        datasets: [{
            label: 'Total de Solicitações',
            backgroundColor: '#43DB9E',
            borderColor: '#43DB9E',
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

    <TenantLayout title="Painel de Atendimentos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Painel de Atendimentos
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Briefcase :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Painel de Atendimentos</h2>
                        <p class="form-subtitle">Visão geral e estatísticas sobre as solicitações de serviço.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a :href="exportPdfUrl" target="_blank" class="btn-export-pdf" :class="{ 'opacity-50 pointer-events-none': !solicitacoes.total }">
                            <FileDown class="w-4 h-4 mr-2"/> PDF
                        </a>
                        <a :href="exportUrl" target="_blank" class="btn-export-xlsx" :class="{ 'opacity-50 pointer-events-none': !solicitacoes.total }">
                            <FileDown class="w-4 h-4 mr-2"/> XLSX
                        </a>
                    </div>
                </div>

                <div class="p-4 md:p-6">
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
                </div>

                <div class="px-4 md:px-6 pt-4 pb-6 border-t-dynamic">
                    <form @submit.prevent="filtrar">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período</label>
                                <div class="flex items-center mt-1 space-x-2">
                                    <input type="date" id="data_inicio" v-model="form.data_inicio" class="input-form">
                                    <span class="text-gray-500 dark:text-gray-400">até</span>
                                    <input type="date" id="data_fim" v-model="form.data_fim" class="input-form">
                                </div>
                            </div>
                            <div>
                                <label for="tipo_servico_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Serviço</label>
                                <select id="tipo_servico_id" v-model="form.tipo_servico_id" class="mt-1 input-form">
                                    <option value="">Todos</option>
                                    <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="funcionario_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Funcionário</label>
                                <select id="funcionario_id" v-model="form.funcionario_id" class="mt-1 input-form">
                                    <option value="">Todos</option>
                                    <option v-for="func in funcionarios" :key="func.id" :value="func.id">{{ func.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-4 space-x-3">
                            <button type="button" @click="limparFiltros" :disabled="form.processing" class="btn-secondary">Limpar</button>
                            <button type="submit" :disabled="form.processing" class="btn-primary">Analisar</button>
                        </div>
                    </form>
                </div>

                <div class="p-4 md:p-6 border-t-dynamic">
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="lg:col-span-2">
                            <h3 class="role-name mb-4">Solicitações por Status</h3>
                            <div class="h-80 relative">
                                <Bar v-if="solicitacoes.total > 0" :data="chartData" :options="chartOptions" />
                                <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                                    <p>Nenhum dado para exibir.</p>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-3">
                            <h3 class="role-name mb-4">Detalhes dos Atendimentos</h3>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5">
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
                                            <td colspan="5" class="px-6 py-10 text-center">Nenhum atendimento encontrado.</td>
                                        </tr>
                                        <tr v-for="solicitacao in solicitacoes.data" :key="solicitacao.id" class="bg-white border-b dark:bg-gray-800/50 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                            <td class="px-6 py-4">
                                                <a :href="route('admin.solicitacoes.show', solicitacao.id)" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline">
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
                            <Pagination class="pt-6" :links="solicitacoes.links" v-if="solicitacoes.data.length > 0"/>
                        </div>
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
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }

/* Estilos de botões */
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }
.btn-export-pdf { @apply btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500; }
.btn-export-xlsx { @apply btn-base bg-green-600 text-white hover:bg-green-700 focus:ring-green-500; }

/* Estilo para inputs e selects do formulário */
.input-form { @apply block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500; }
</style>
