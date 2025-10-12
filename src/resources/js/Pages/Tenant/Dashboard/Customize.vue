<script setup lang="ts">
import { route } from 'ziggy-js';
import { ref, onMounted, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { VueDraggable } from 'vue-draggable-plus';
import { GripVertical, Eye, EyeOff, Zap, GaugeCircle, BarChart3, List, ChevronDown, Link as LinkIcon, Info, LayoutDashboard, PlusCircle, Folder, FilePieChart } from 'lucide-vue-next';

interface Role {
  id: number;
  name: string;
}

interface AvailableLink {
  identifier: string;
  name: string;
  friendly_name: string;
}

interface WidgetGroup {
  title: string;
  prefixes: string[];
}

interface Preference {
  id: number | null;
  widget_identifier: string;
  is_visible: boolean;
  order: number;
  settings: Record<string, any> | string;
}

const props = defineProps<{
  preferences: Preference[];
  allRoles: Role[];
  selectedRole: Role | null;
  availableLinks: AvailableLink[];
  widgetGroups: Record<string, WidgetGroup>;
}>();

const isClient = ref(false);
const openGroups = ref<Record<string, boolean>>({});

const iconMap = {
    actions: Zap,
    metrics: GaugeCircle,
    main: BarChart3,
    side: List,
    reports: FilePieChart,
    default: Folder,
};

const getGroupIcon = (groupKey: string) => {
    return iconMap[groupKey as keyof typeof iconMap] || iconMap.default;
};


onMounted(() => {
    isClient.value = true;
    Object.keys(props.widgetGroups).forEach(key => {
        openGroups.value[key] = groupedWidgets.value[key]?.length > 0;
    });
});

const widgetFriendlyNames: Record<string, string> = {
    'action.novoAtendimento': 'Ação: Novo Atendimento',
    'action.novoCidadao': 'Ação: Novo Cidadão',
    'action.novaVaga': 'Ação: Nova Vaga de Emprego',
    'metric.atendimentosHoje': 'Métrica: Atendimentos de Hoje',
    'metric.solicitacoesPendentes': 'Métrica: Solicitações Pendentes',
    'metric.novosCidadaosHoje': 'Métrica: Novos Cidadãos Hoje',
    'metric.totalCidadaos': 'Métrica: Total de Cidadãos',
    'metric.mensagensNaoLidas': 'Métrica: Mensagens Não Lidas',
    'metric.vagasAbertas': 'Métrica: Vagas de Emprego Abertas',
    'metric.sugestoesPendentes': 'Métrica: Sugestões de Lei Pendentes',
    'metric.tempoMedio': 'Métrica: Tempo Médio de Atendimento (7d)',
    'metric.satisfacaoMedia': 'Métrica: Satisfação Média (30d)',
    'chart.atendimentos7d': 'Gráfico: Atendimentos (7 dias)',
    'chart.novosCidadaos30d': 'Gráfico: Novos Cidadãos (30 dias)',
    'chart.solicitacoesPorStatus': 'Gráfico: Solicitações por Status',
    'chart.demandasPorBairro': 'Gráfico: Demandas por Bairro',
    'list.solicitacoesRecentes': 'Lista: Solicitações Recentes',
    'list.ultimosCidadaosCadastrados': 'Lista: Últimos Cidadãos Cadastrados',
    'list.ultimasMensagensGabinete': 'Lista: Últimas Mensagens do Gabinete',
};

const getFriendlyName = (identifier: string): string => {
    if (widgetFriendlyNames[identifier]) {
        return widgetFriendlyNames[identifier].replace(/^(Ação|Métrica|Gráfico|Lista): /, '');
    }
    if (identifier.startsWith('link.')) {
        const linkName = identifier.substring(5);
        const foundLink = props.availableLinks.find(l => l.name === linkName);
        return foundLink ? foundLink.friendly_name : linkName;
    }
    return identifier;
};

const groupedWidgets = ref<Record<string, Preference[]>>({});

// ##### CORREÇÃO APLICADA AQUI #####
const initializeWidgets = () => {
    groupedWidgets.value = Object.fromEntries(
        Object.entries(props.widgetGroups).map(([key, meta]) => [
            key,
            props.preferences
                // Filtra usando os prefixos, que é a forma correta
                .filter(p => meta.prefixes.some(prefix => p.widget_identifier.startsWith(prefix)))
                .map(p => ({ ...p, settings: typeof p.settings === 'string' ? JSON.parse(p.settings) : p.settings }))
        ])
    );
};
// ###################################

onMounted(initializeWidgets);
watch(() => props.preferences, initializeWidgets, { deep: true });

const groupOrder = computed(() => Object.keys(props.widgetGroups));

const selectedRoleId = ref<number | null>(props.selectedRole?.id ?? null);

const form = useForm<{
  widgets: Array<{
    id: number | null;
    widget_identifier: string;
    is_visible: boolean;
    order: number;
    settings: Record<string, any>;
  }>;
  role_id: number | null;
}>({
    widgets: [],
    role_id: props.selectedRole?.id ?? null,
});

const handleRoleChange = () => {
    form.role_id = selectedRoleId.value;
    if (selectedRoleId.value) {
        router.get(route('admin.dashboard.customize.edit', { role: selectedRoleId.value }), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const toggleVisibility = (widget: Preference) => {
    widget.is_visible = !widget.is_visible;
};

const toggleGroup = (key: string) => {
    openGroups.value[key] = !openGroups.value[key];
};

const activeLinks = computed(() => {
    return groupOrder.value.flatMap(key => groupedWidgets.value[key] || [])
                       .filter(w => w.widget_identifier.startsWith('link.'));
});

const availableLinksFiltered = computed(() => {
    const activeLinkIdentifiers = activeLinks.value.map(w => w.widget_identifier);
    return props.availableLinks.filter(link => !activeLinkIdentifiers.includes(link.identifier));
});

const addLinkWidget = (link: AvailableLink) => {
    // Determina o grupo correto para links a partir da definição do backend
    const linkGroupKey = Object.keys(props.widgetGroups).find(groupKey =>
        props.widgetGroups[groupKey].prefixes.includes('link.')
    ) || 'side';

    if (!groupedWidgets.value[linkGroupKey]) {
        groupedWidgets.value[linkGroupKey] = [];
    }
    groupedWidgets.value[linkGroupKey].push({
        id: null,
        widget_identifier: link.identifier,
        is_visible: true,
        order: 999,
        settings: { group: linkGroupKey }
    });
};

const submit = () => {
    if (!form.role_id) return;

    const flattenedWidgets = groupOrder.value.flatMap(key => groupedWidgets.value[key] || []);

    form.widgets = flattenedWidgets.map((widget, index) => ({
        id: widget.id,
        widget_identifier: widget.widget_identifier,
        is_visible: widget.is_visible,
        order: index + 1,
        settings: widget.settings as Record<string, any>,
    }));

    form.put(route('admin.dashboard.customize.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Personalizar Dashboard" />
    <TenantLayout title="Personalizar Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Personalizar Dashboard
            </h2>
        </template>
        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><LayoutDashboard :size="32" class="icon-in-badge" /></div>

                <form @submit.prevent="submit">
                    <div class="p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Personalizar Dashboard</h2>
                            <p class="form-subtitle">Arraste e organize os widgets para cada papel de usuário.</p>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <h3 class="role-name mb-4">1. Selecione o Papel</h3>
                        <div class="max-w-xl">
                             <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Escolha o papel que deseja configurar. As alterações serão aplicadas a todos os usuários com este papel.
                            </p>
                            <div>
                                <label for="role_select" class="sr-only">Selecione um Papel</label>
                                <select
                                    id="role_select"
                                    v-model="selectedRoleId"
                                    @change="handleRoleChange"
                                    class="input-form"
                                >
                                    <option :value="null" disabled>-- Selecione um Papel --</option>
                                    <option v-for="role in allRoles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedRole" class="p-4 md:p-6 border-t-dynamic">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2">
                                <h3 class="role-name mb-2">
                                    2. Organize os Widgets para: <span class="font-bold text-emerald-500">{{ selectedRole.name }}</span>
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                    Clique em uma categoria para expandir e arraste os widgets para reordenar ou ocultar.
                                </p>
                                <div v-if="isClient" class="space-y-4">
                                    <div v-for="key in groupOrder" :key="key">
                                        <div v-if="widgetGroups[key] && groupedWidgets[key]" class="rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 transition-all duration-300">
                                            <button type="button" @click="toggleGroup(key)" class="w-full flex items-center justify-between p-4 text-left bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" :aria-expanded="openGroups[key]" :aria-controls="`group-content-${key}`">
                                                <div class="flex items-center space-x-3">
                                                    <component :is="getGroupIcon(key)" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ widgetGroups[key].title }}</h3>
                                                </div>
                                                <ChevronDown class="h-5 w-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{'rotate-180': openGroups[key]}" />
                                            </button>
                                            <div v-show="openGroups[key]" :id="`group-content-${key}`" class="border-t border-gray-200 dark:border-white/10">
                                                <div v-if="groupedWidgets[key].length > 0">
                                                    <ul class="divide-y divide-gray-200 dark:divide-white/10">
                                                        <VueDraggable v-model="groupedWidgets[key]" :item-key="widget => widget.id || widget.widget_identifier" handle=".handle" ghost-class="ghost">
                                                            <li v-for="widget in groupedWidgets[key]" :key="widget.id || widget.widget_identifier" class="flex items-center justify-between p-4 transition-colors duration-150" :class="{'opacity-40 bg-gray-50 dark:bg-black/10': !widget.is_visible, 'hover:bg-gray-50 dark:hover:bg-white/5': widget.is_visible}">
                                                                <div class="flex items-center min-w-0">
                                                                    <div class="handle cursor-move mr-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                                                        <GripVertical class="h-5 w-5" />
                                                                    </div>
                                                                    <span class="font-medium text-gray-800 dark:text-gray-200 truncate pr-2">{{ getFriendlyName(widget.widget_identifier) }}</span>
                                                                </div>
                                                                <div class="flex items-center flex-shrink-0 ml-4">
                                                                    <button type="button" @click.stop="toggleVisibility(widget)" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 focus:ring-emerald-500 transition-colors" :aria-label="widget.is_visible ? 'Ocultar widget' : 'Mostrar widget'">
                                                                        <Eye v-if="widget.is_visible" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                                        <EyeOff v-else class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                                    </button>
                                                                </div>
                                                            </li>
                                                        </VueDraggable>
                                                    </ul>
                                                </div>
                                                <p v-else class="text-sm text-center text-gray-500 dark:text-gray-400 p-4">Nenhum widget nesta categoria.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-1">
                                <h3 class="role-name mb-2">3. Adicionar Links</h3>
                                 <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                    Clique para adicionar um atalho na categoria "Listas e Links".
                                </p>
                                <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm max-h-[400px] overflow-y-auto">
                                    <ul v-if="availableLinksFiltered.length > 0" class="space-y-1">
                                        <li v-for="link in availableLinksFiltered" :key="link.identifier">
                                            <button
                                                type="button"
                                                @click="addLinkWidget(link)"
                                                class="w-full text-left flex items-center p-2 rounded-md hover:bg-emerald-50 dark:hover:bg-emerald-900/50 group transition-colors"
                                            >
                                                <PlusCircle class="h-4 w-4 mr-2 text-emerald-500 flex-shrink-0" />
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-emerald-800 dark:group-hover:text-emerald-200">
                                                    {{ link.friendly_name }}
                                                </span>
                                            </button>
                                        </li>
                                    </ul>
                                    <p v-else class="text-sm text-center text-gray-500 dark:text-gray-400 py-4">
                                        Todos os links já foram adicionados.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-4 md:p-6 border-t-dynamic">
                         <div class="text-center py-16 px-6 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                           <div class="flex justify-center items-center mx-auto h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900/50">
                                <Info class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Nenhum Papel Selecionado</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Por favor, selecione um papel na caixa acima para começar a personalizar.</p>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 border-t-dynamic flex items-center justify-end space-x-4">
                        <Link :href="route('tenant.dashboard')" class="btn-secondary">
                            Cancelar
                        </Link>
                        <button type="submit" class="btn-primary" :disabled="!selectedRole || form.processing" :class="{ 'opacity-25': !selectedRole || form.processing }">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
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
.input-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300; }

/* Estilos de botões */
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }

/* Estilo para inputs e selects do formulário */
.input-form { @apply block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500; }

/* Estilo para o drag-and-drop */
.ghost {
    opacity: 0.5;
    background: theme('colors.emerald.100');
    border: 1px dashed theme('colors.emerald.500');
}
.dark .ghost {
    background: theme('colors.emerald.900' / 0.5);
    border: 1px dashed theme('colors.emerald.400');
}
</style>
