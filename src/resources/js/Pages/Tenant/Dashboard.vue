<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {
    Users, ClipboardList, Clock, Star, BarChart3, ArrowRight, PlusCircle, Settings,
    UserPlus, Briefcase, Vote, MessageSquare, FileText, Building, Handshake, Landmark, UserCheck,
    GanttChartSquare, BookUser, ShieldCheck, FilePieChart, ShieldAlert, KeyRound, Workflow, Mic, Link as LinkIcon
} from 'lucide-vue-next';
import { Line, Bar, Pie } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    widgetData: Object,
    layout: Array,
    canCustomize: Boolean,
});

const page = usePage();
const tenant = page.props.tenant;

const isDarkMode = ref(false);
onMounted(() => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
    const observer = new MutationObserver(() => {
        isDarkMode.value = document.documentElement.classList.contains('dark');
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

const getInitials = (name) => (!name ? '??' : name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase());

const widgetRegistry = {
    // Action Cards
    'action.novoAtendimento': { component: 'ActionCard', title: 'Novo Atendimento', description: 'Inicie um novo registro de serviço.', href: route('admin.solicitacoes.create'), icon: PlusCircle, color: 'emerald' },
    'action.novoCidadao': { component: 'ActionCard', title: 'Novo Cidadão', description: 'Adicione um novo usuário ao sistema.', href: route('admin.cidadaos.create'), icon: UserPlus, color: 'sky' },
    'action.novaVaga': { component: 'ActionCard', title: 'Nova Vaga', description: 'Publique uma nova vaga de emprego.', href: route('admin.vagas.index'), icon: Briefcase, color: 'indigo' },
    'action.gerirFuncionarios': { component: 'ActionCard', title: 'Gerir Funcionários', description: 'Gerencie os usuários da equipe.', href: route('admin.funcionarios.index'), icon: Users, color: 'purple' },
    'action.gerirServicos': { component: 'ActionCard', title: 'Gerir Serviços', description: 'Configure os serviços oferecidos.', href: route('admin.servicos.index'), icon: GanttChartSquare, color: 'rose' },
    'action.verRelatorios': { component: 'ActionCard', title: 'Ver Relatórios', description: 'Acesse os relatórios do sistema.', href: route('admin.relatorios.atendimentos'), icon: BarChart3, color: 'blue' },

    // Metric Cards
    'metric.atendimentosHoje': { component: 'MetricCard', title: 'Atendimentos Hoje', value: computed(() => props.widgetData?.atendimentosHoje ?? 0), icon: ClipboardList, color: 'sky' },
    'metric.solicitacoesPendentes': { component: 'MetricCard', title: 'Solicitações Pendentes', value: computed(() => props.widgetData?.solicitacoesPendentes ?? 0), icon: Clock, color: 'yellow' },
    'metric.novosCidadaosHoje': { component: 'MetricCard', title: 'Novos Cidadãos Hoje', value: computed(() => props.widgetData?.novosCidadaosHoje ?? 0), icon: UserPlus, color: 'emerald' },
    'metric.totalCidadaos': { component: 'MetricCard', title: 'Total de Cidadãos', value: computed(() => props.widgetData?.totalCidadaos ?? 0), icon: Users, color: 'blue' },
    'metric.mensagensNaoLidas': { component: 'MetricCard', title: 'Mensagens Não Lidas', value: computed(() => props.widgetData?.mensagensNaoLidas ?? 0), icon: MessageSquare, color: 'orange' },
    'metric.vagasAbertas': { component: 'MetricCard', title: 'Vagas Abertas', value: computed(() => props.widgetData?.vagasAbertas ?? 0), icon: Briefcase, color: 'indigo' },
    'metric.sugestoesPendentes': { component: 'MetricCard', title: 'Sugestões Pendentes', value: computed(() => props.widgetData?.sugestoesPendentes ?? 0), icon: Vote, color: 'purple' },
    'metric.tempoMedio': { component: 'MetricCard', title: 'Tempo Médio (7d)', value: computed(() => `${props.widgetData?.kpis?.tempoMedioFinalizacao ?? 0}h`), icon: Clock, color: 'rose' },
    'metric.satisfacaoMedia': { component: 'MetricCard', title: 'Satisfação Média (30d)', value: computed(() => `${props.widgetData?.kpis?.notaMediaSatisfacao ?? 0} / 5`), icon: Star, color: 'emerald' },

    // Charts
    'chart.atendimentos7d': { component: 'Chart', type: 'Line', title: 'Atendimentos nos Últimos 7 Dias', dataKey: 'atendimentosChartData' },
    'chart.novosCidadaos30d': { component: 'Chart', type: 'Bar', title: 'Novos Cidadãos nos Últimos 30 Dias', dataKey: 'novosCidadaosChartData' },
    'chart.solicitacoesPorStatus': { component: 'Chart', type: 'Pie', title: 'Solicitações por Status', dataKey: 'solicitacoesPorStatusChartData' },
    'chart.demandasPorBairro': { component: 'Chart', type: 'Bar', title: 'Demandas por Bairro', dataKey: 'demandasPorBairroChartData' },

    // Listas Dinâmicas
    'list.solicitacoesRecentes': { component: 'DynamicList', title: 'Solicitações Recentes', data: computed(() => props.widgetData?.solicitacoesRecentes ?? []), itemComponent: 'SolicitacaoItem' },
    'list.ultimosCidadaosCadastrados': { component: 'DynamicList', title: 'Últimos Cidadãos Cadastrados', data: computed(() => props.widgetData?.ultimosCidadaosCadastrados ?? []), itemComponent: 'CidadaoItem' },
    'list.ultimasMensagensGabinete': { component: 'DynamicList', title: 'Últimas Mensagens do Gabinete', data: computed(() => props.widgetData?.ultimasMensagensGabinete ?? []), itemComponent: 'MensagemItem' },
};

const generateFriendlyName = (identifier) => {
    if (!identifier) return '';
    let friendlyName = identifier.substring(identifier.indexOf('link.admin.') + 'link.admin.'.length);
    friendlyName = friendlyName.replace(/\./g, ' ').replace(/-/g, ' ');
    friendlyName = friendlyName.replace(/\b\w/g, char => char.toUpperCase());
    friendlyName = friendlyName.replace('Index', '').trim();
    return friendlyName;
};


const groupedWidgets = computed(() => {
    if (!props.layout) return {};

    const visibleWidgets = props.layout
        .filter(item => item.is_visible)
        .map(item => {
            let registryItem = widgetRegistry[item.widget_identifier];

            if (!registryItem && item.widget_identifier.startsWith('link.admin.')) {
                const routeName = item.widget_identifier.substring(5);
                registryItem = {
                    component: 'QuickLink',
                    title: generateFriendlyName(item.widget_identifier),
                    href: route(routeName),
                    icon: LinkIcon,
                };
            }

            if (!registryItem) return null;

            let settings = {};
            try {
                settings = typeof item.settings === 'string' ? JSON.parse(item.settings) : item.settings;
            } catch (e) { console.error('Erro ao parsear settings:', e); }

            const widget = {
                id: item.widget_identifier,
                ...registryItem,
                group: settings?.group || 'default',
            };

            if (widget.component === 'Chart') {
                const chartInfo = computed(() => props.widgetData?.[widget.dataKey]);
                widget.chartData = computed(() => {
                    const color = isDarkMode.value ? '#34D399' : '#10B981';
                    const colors = isDarkMode.value ? ['#2dd4bf', '#60a5fa', '#a78bfa', '#f472b6', '#fb923c'] : ['#14b8a6', '#3b82f6', '#8b5cf6', '#ec4899', '#f97316'];
                    return {
                        labels: chartInfo.value?.labels ?? [],
                        datasets: [{
                            label: widget.title,
                            borderColor: widget.type === 'Line' ? color : (widget.type === 'Pie' ? (isDarkMode.value ? '#1f2937' : '#ffffff') : colors),
                            backgroundColor: widget.type === 'Line' ? (context) => {
                                const ctx = context.chart.ctx;
                                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, `${color}40`);
                                gradient.addColorStop(1, `${color}00`);
                                return gradient;
                            } : colors,
                            pointBackgroundColor: color,
                            borderWidth: widget.type === 'Line' ? 2 : (widget.type === 'Pie' ? 2 : 1),
                            tension: 0.4,
                            fill: widget.type === 'Line',
                            data: chartInfo.value?.values ?? [],
                        }],
                    };
                });
                widget.chartOptions = computed(() => {
                     const textColor = isDarkMode.value ? '#9ca3af' : '#6b7280';
                    const gridColor = isDarkMode.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
                    const baseOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: widget.type === 'Pie', position: 'bottom', labels: { color: textColor } } },
                        interaction: { intersect: false, mode: 'index' },
                    };
                    if (widget.type === 'Pie') return baseOptions;
                    return {
                        ...baseOptions,
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0, color: textColor }, grid: { color: gridColor } },
                            x: { grid: { display: false }, ticks: { color: textColor, maxRotation: 0, autoSkip: true, maxTicksLimit: 7 } },
                        },
                    };
                });
            }
            return widget;
        })
        .filter(Boolean);

    return visibleWidgets.reduce((acc, widget) => {
        (acc[widget.group] = acc[widget.group] || []).push(widget);
        return acc;
    }, {});
});


const quickLinks = computed(() => groupedWidgets.value.side?.filter(w => w.component === 'QuickLink') ?? []);
const dynamicLists = computed(() => groupedWidgets.value.side?.filter(w => w.component === 'DynamicList') ?? []);
</script>

<template>
    <Head title="Dashboard" />
    <TenantLayout>
        <template #header>
            <div class="flex justify-between items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
                <Link v-if="canCustomize" :href="route('admin.dashboard.customize.edit')">
                    <PrimaryButton>
                        <Settings class="h-4 w-4 mr-2" />
                        Personalizar
                    </PrimaryButton>
                </Link>
            </div>
        </template>
        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Bem-vindo(a), {{ page.props.auth.user.name }}! 👋
                    </h2>
                    <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
                        Visão geral do seu painel de controle para a <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ tenant.name }}</span>.
                    </p>
                </div>

                <div v-if="groupedWidgets.actions?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link v-for="action in groupedWidgets.actions" :key="action.id" :href="action.href"
                          class="group flex items-center p-5 rounded-xl border transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                          :class="{
                              'bg-emerald-50 dark:bg-emerald-900/50 border-emerald-200 dark:border-emerald-500/30 hover:border-emerald-300 dark:hover:border-emerald-500/50': action.color === 'emerald',
                              'bg-sky-50 dark:bg-sky-900/50 border-sky-200 dark:border-sky-500/30 hover:border-sky-300 dark:hover:border-sky-500/50': action.color === 'sky',
                              'bg-indigo-50 dark:bg-indigo-900/50 border-indigo-200 dark:border-indigo-500/30 hover:border-indigo-300 dark:hover:border-indigo-500/50': action.color === 'indigo',
                              'bg-rose-50 dark:bg-rose-900/50 border-rose-200 dark:border-rose-500/30 hover:border-rose-300 dark:hover:border-rose-500/50': action.color === 'rose',
                              'bg-blue-50 dark:bg-blue-900/50 border-blue-200 dark:border-blue-500/30 hover:border-blue-300 dark:hover:border-blue-500/50': action.color === 'blue',
                              'bg-purple-50 dark:bg-purple-900/50 border-purple-200 dark:border-purple-500/30 hover:border-purple-300 dark:hover:border-purple-500/50': action.color === 'purple',
                          }">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center shadow-md mr-4 flex-shrink-0" :class="{
                            'bg-emerald-600 dark:bg-emerald-500': action.color === 'emerald',
                            'bg-sky-600 dark:bg-sky-500': action.color === 'sky',
                            'bg-indigo-600 dark:bg-indigo-500': action.color === 'indigo',
                            'bg-rose-600 dark:bg-rose-500': action.color === 'rose',
                            'bg-blue-600 dark:bg-blue-500': action.color === 'blue',
                            'bg-purple-600 dark:bg-purple-500': action.color === 'purple',
                        }">
                            <component :is="action.icon" class="h-6 w-6 text-white" />
                        </div>
                        <div class="flex-grow">
                            <h3 class="font-bold" :class="{
                                'text-emerald-900 dark:text-emerald-200': action.color === 'emerald',
                                'text-sky-900 dark:text-sky-200': action.color === 'sky',
                                'text-indigo-900 dark:text-indigo-200': action.color === 'indigo',
                                'text-rose-900 dark:text-rose-200': action.color === 'rose',
                                'text-blue-900 dark:text-blue-200': action.color === 'blue',
                                'text-purple-900 dark:text-purple-200': action.color === 'purple',
                            }">{{ action.title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ action.description }}</p>
                        </div>
                        <ArrowRight class="ml-auto h-5 w-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" :class="{
                            'text-emerald-500': action.color === 'emerald',
                            'text-sky-500': action.color === 'sky',
                            'text-indigo-500': action.color === 'indigo',
                            'text-rose-500': action.color === 'rose',
                            'text-blue-500': action.color === 'blue',
                            'text-purple-500': action.color === 'purple',
                        }"/>
                    </Link>
                </div>

                <div v-if="groupedWidgets.metrics?.length">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Métricas Principais</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="metric in groupedWidgets.metrics" :key="metric.id" class="p-5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ metric.title }}</p>
                                <div class="p-2 rounded-full" :class="{
                                    'bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400': metric.color === 'sky',
                                    'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-400': metric.color === 'yellow',
                                    'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400': metric.color === 'emerald',
                                    'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400': metric.color === 'blue',
                                    'bg-orange-100 dark:bg-orange-900/50 text-orange-600 dark:text-orange-400': metric.color === 'orange',
                                    'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400': metric.color === 'indigo',
                                    'bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400': metric.color === 'purple',
                                    'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400': metric.color === 'rose',
                                }">
                                    <component :is="metric.icon" class="h-5 w-5" />
                                </div>
                            </div>
                            <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 text-left">{{ metric.value }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <div v-if="groupedWidgets.main?.length" class="lg:col-span-2 space-y-6">
                        <div v-for="widget in groupedWidgets.main" :key="widget.id" class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ widget.title }}</h3>
                            <div class="h-80">
                                <Line v-if="widget.type === 'Line'" :data="widget.chartData.value" :options="widget.chartOptions.value" />
                                <Bar v-if="widget.type === 'Bar'" :data="widget.chartData.value" :options="widget.chartOptions.value" />
                                <Pie v-if="widget.type === 'Pie'" :data="widget.chartData.value" :options="widget.chartOptions.value" />
                            </div>
                        </div>
                    </div>

                    <div v-if="groupedWidgets.side?.length" class="space-y-6">

                        <div v-if="quickLinks.length > 0" class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Ações Rápidas</h3>
                            <div class="space-y-2">
                                <Link v-for="link in quickLinks" :key="link.id" :href="link.href" class="group flex items-center w-full p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                    <component :is="link.icon" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-emerald-500 transition-colors" />
                                    <span class="font-medium text-sm text-gray-700 dark:text-gray-300">{{ link.title }}</span>
                                    <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity"/>
                                </Link>
                            </div>
                        </div>

                        <div v-for="widget in dynamicLists" :key="widget.id">
                            <div class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ widget.title }}</h3>
                                <div v-if="widget.data.length > 0" class="space-y-2 max-h-60 overflow-y-auto">

                                    <template v-if="widget.itemComponent === 'SolicitacaoItem'">
                                        <Link v-for="item in widget.data" :key="item.id" :href="route('admin.solicitacoes.show', item.id)" class="group flex items-center w-full p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                            <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3">
                                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(item.cidadao.name) }}</span>
                                            </div>
                                            <div class="flex-grow overflow-hidden">
                                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">{{ item.servico.nome }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">#{{ item.id }} • {{ item.cidadao.name }}</p>
                                            </div>
                                            <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-all flex-shrink-0"/>
                                        </Link>
                                    </template>
                                    <template v-if="widget.itemComponent === 'CidadaoItem'">
                                        <Link v-for="item in widget.data" :key="item.id" :href="route('admin.cidadaos.show', item.id)" class="group flex items-center w-full p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                            <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3">
                                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(item.name) }}</span>
                                            </div>
                                            <div class="flex-grow overflow-hidden">
                                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">{{ item.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Cadastrado em {{ new Date(item.created_at).toLocaleDateString() }}</p>
                                            </div>
                                            <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-all flex-shrink-0"/>
                                        </Link>
                                    </template>
                                    <template v-if="widget.itemComponent === 'MensagemItem'">
                                        <Link v-for="item in widget.data" :key="item.id" :href="route('admin.gabinete-virtual.show', item.id)" class="group flex items-center w-full p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                            <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3">
                                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(item.user.name) }}</span>
                                            </div>
                                            <div class="flex-grow overflow-hidden">
                                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">{{ item.assunto }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">De: {{ item.user.name }}</p>
                                            </div>
                                            <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-all flex-shrink-0"/>
                                        </Link>
                                    </template>
                                </div>
                                <p v-else class="text-sm text-center text-gray-500 dark:text-gray-400 py-8">Nenhum item recente.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </TenantLayout>
</template>
