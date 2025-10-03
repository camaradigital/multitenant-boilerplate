<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { Smile, Star, MessageSquare, TrendingUp, ListChecks, Percent, Search, X } from 'lucide-vue-next';

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

// --- Reatividade para Dark Mode ---
const isDarkMode = ref(false);
const textColor = computed(() => isDarkMode.value ? '#cbd5e1' : '#4b5563');
const gridColor = computed(() => isDarkMode.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)');

onMounted(() => {
    const observer = new MutationObserver(() => {
        isDarkMode.value = document.documentElement.classList.contains('dark');
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    isDarkMode.value = document.documentElement.classList.contains('dark');
});

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' });
};

// --- LÓGICA DO GRÁFICO CORRIGIDA ---

// 1. Crie um mapa de cores por nota
const ratingColors = {
    '1': '#ef4444', // Vermelho
    '2': '#f97316', // Laranja
    '3': '#eab308', // Amarelo
    '4': '#84cc16', // Lima
    '5': '#22c55e', // Verde
};

const chartData = computed(() => {
    // A ordem das chaves (1, 2, 3, 4, 5) é mantida, e depois invertemos para o gráfico
    const labels = props.estatisticas.distribuicaoNotas ? Object.keys(props.estatisticas.distribuicaoNotas).reverse() : [];
    const data = props.estatisticas.distribuicaoNotas ? Object.values(props.estatisticas.distribuicaoNotas).reverse() : [];

    // 2. Gere o array de cores dinamicamente com base nos labels
    const backgroundColors = labels.map(label => ratingColors[label] || '#6b7280'); // Usa a cor do mapa ou um cinza padrão

    return {
        labels: labels.map(label => `${label} Estrela(s)`),
        datasets: [{
            label: 'Total de Avaliações',
            backgroundColor: backgroundColors, // 3. Use o array de cores dinâmico
            borderColor: backgroundColors,
            borderRadius: 4,
            data: data,
        }]
    };
});


const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
        legend: { display: false },
        title: {
            display: true,
            text: 'Distribuição das Avaliações',
            color: textColor.value,
            font: { size: 16, weight: 'bold' }
        }
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: { precision: 0, color: textColor.value },
            grid: { color: gridColor.value }
        },
        y: {
            ticks: { color: textColor.value },
            grid: { display: false }
        }
    }
}));
</script>

<template>
    <Head title="Relatório de Satisfação" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Painel de Satisfação do Cidadão
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <Smile :size="32" class="text-white" />
                    </div>

                    <div class="pt-12 p-6 text-center border-b border-gray-200 dark:border-white/10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Painel de Satisfação</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analise o feedback dos cidadãos sobre os serviços prestados.</p>
                    </div>

                    <!-- KPIs -->
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-full text-emerald-600 dark:text-emerald-300"><TrendingUp class="h-6 w-6"/></div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nota Média Geral</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ estatisticas.notaMedia }} <span class="text-base font-normal">/ 5</span></p>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-full text-blue-600 dark:text-blue-300"><ListChecks class="h-6 w-6"/></div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total de Respostas</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ estatisticas.totalRespostas }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                            <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-full text-amber-600 dark:text-amber-300"><Percent class="h-6 w-6"/></div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Taxa de Resposta</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ estatisticas.taxaResposta }}<span class="text-base font-normal">%</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-white/10">
                        <form @submit.prevent="filtrar">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                                <div>
                                    <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Início</label>
                                    <input type="date" id="data_inicio" v-model="form.data_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                <div>
                                    <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Fim</label>
                                    <input type="date" id="data_fim" v-model="form.data_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                <div>
                                    <label for="tipo_servico_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Serviço</label>
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
                                <div class="flex gap-2">
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

                    <!-- Resultados -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                            <!-- Coluna do Gráfico -->
                            <div class="min-h-[400px]">
                                <div class="h-96 relative">
                                    <Bar v-if="pesquisas.total > 0" :data="chartData" :options="chartOptions" />
                                    <div v-else class="flex items-center justify-center h-full text-center text-gray-500 dark:text-gray-400">
                                        Nenhuma avaliação encontrada para os filtros selecionados.
                                    </div>
                                </div>
                            </div>
                            <!-- Coluna dos Comentários -->
                            <div>
                                <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">Comentários Recentes</h3>
                                <div v-if="pesquisas.data.length === 0" class="flex flex-col items-center justify-center h-full text-center text-gray-500 dark:text-gray-400 py-16">
                                    <MessageSquare class="h-12 w-12 mb-4" />
                                    <p>Nenhum comentário encontrado para os filtros selecionados.</p>
                                </div>
                                <div v-else class="space-y-4 max-h-96 overflow-y-auto pr-2">
                                    <div v-for="pesquisa in pesquisas.data" :key="pesquisa.id" class="p-4 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <span class="font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(pesquisa.cidadao.name) }}</span>
                                            </div>
                                            <div class="flex-grow">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ pesquisa.cidadao.name }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ pesquisa.solicitacao_servico.servico.nome }} • {{ formatDate(pesquisa.created_at) }}
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center flex-shrink-0">
                                                        <Star v-for="i in 5" :key="i" class="h-4 w-4" :class="[i <= pesquisa.nota ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-gray-600']" />
                                                    </div>
                                                </div>
                                                <blockquote v-if="pesquisa.comentario" class="mt-2 text-sm text-gray-600 dark:text-gray-400 italic border-l-4 border-gray-200 dark:border-gray-700 pl-3">
                                                    {{ pesquisa.comentario }}
                                                </blockquote>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <Pagination class="mt-6" :links="pesquisas.links" v-if="pesquisas.data.length > 0"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
