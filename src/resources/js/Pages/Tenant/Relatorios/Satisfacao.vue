<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import KpiCard from '@/Components/KpiCard.vue';
import StarRating from '@/Components/StarRating.vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { Smile } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    pesquisas: Object,
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
});

const filtrar = () => {
    form.get(route('admin.relatorios.satisfacao'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const limparFiltros = () => {
    form.reset();
    filtrar();
};

const chartData = computed(() => {
    const labels = props.estatisticas.distribuicaoNotas ? Object.keys(props.estatisticas.distribuicaoNotas) : [];
    const data = props.estatisticas.distribuicaoNotas ? Object.values(props.estatisticas.distribuicaoNotas) : [];

    return {
        labels: labels.map(label => `${label} Estrela(s)`),
        datasets: [{
            label: 'Total de Avaliações',
            backgroundColor: ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e'],
            borderColor: ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e'],
            borderRadius: 4,
            data: data,
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
        legend: { display: false },
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: { precision: 0 },
        }
    }
};
</script>

<template>
    <Head title="Relatório de Satisfação" />

    <TenantLayout title="Painel de Satisfação">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Painel de Satisfação do Cidadão
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Smile :size="32" class="icon-in-badge" /></div>

                <div class="p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Painel de Satisfação</h2>
                        <p class="form-subtitle">Analise o feedback dos cidadãos sobre os serviços prestados.</p>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <KpiCard title="Nota Média Geral" :value="estatisticas.notaMedia" unit="/ 5" colorClass="text-emerald-700 dark:text-emerald-200" />
                        <KpiCard title="Total de Respostas" :value="estatisticas.totalRespostas" unit="no período" colorClass="text-sky-700 dark:text-sky-200" />
                        <KpiCard title="Taxa de Resposta" :value="estatisticas.taxaResposta" unit="%" colorClass="text-amber-700 dark:text-amber-200" />
                    </div>
                </div>

                <div class="px-4 md:px-6 pt-4 pb-6 border-t-dynamic">
                    <form @submit.prevent="filtrar">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Início</label>
                                <input type="date" id="data_inicio" v-model="form.data_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Fim</label>
                                <input type="date" id="data_fim" v-model="form.data_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label for="tipo_servico_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Serviço</label>
                                <select id="tipo_servico_id" v-model="form.tipo_servico_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Todos</option>
                                    <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="funcionario_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Funcionário</label>
                                <select id="funcionario_id" v-model="form.funcionario_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
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
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h3 class="role-name mb-4">Distribuição das Notas</h3>
                            <div class="h-80 relative">
                                <Bar v-if="pesquisas.total > 0" :data="chartData" :options="chartOptions" />
                                <div v-else class="flex items-center justify-center h-full">
                                    <p class="text-center text-gray-500 dark:text-gray-400">Nenhuma avaliação no período.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                             <h3 class="role-name mb-4">Comentários Recentes</h3>
                             <div class="space-y-4">
                                <div v-if="pesquisas.data.length === 0" class="flex items-center justify-center h-full py-16">
                                    <p class="text-center text-gray-500 dark:text-gray-400">Nenhum comentário no período.</p>
                                </div>
                                <div v-for="pesquisa in pesquisas.data" :key="pesquisa.id" class="border-b border-gray-200 dark:border-gray-700 pb-3 last:border-b-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ pesquisa.cidadao.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Serviço: {{ pesquisa.solicitacao_servico.servico.nome }}</p>
                                        </div>
                                        <StarRating :rating="pesquisa.nota" />
                                    </div>
                                    <p v-if="pesquisa.comentario" class="mt-2 text-sm text-gray-600 dark:text-gray-400 italic">"{{ pesquisa.comentario }}"</p>
                                </div>
                            </div>
                             <Pagination class="mt-6" :links="pesquisas.links" v-if="pesquisas.data.length > 0"/>
                        </div>
                    </div>
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
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }
</style>
