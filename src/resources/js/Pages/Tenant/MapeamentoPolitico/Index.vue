<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { User, Activity, BrainCircuit, Medal, Map, LayoutGrid, Network } from 'lucide-vue-next';

const props = defineProps({
    liderancasPorBairro: Object,
});

// --- CONTROLE DE VISUALIZAÇÃO ---
const viewMode = ref('map'); // 'map' ou 'cards'

// --- LÓGICA PARA O MODO 'CARDS' ---
const sortBy = ref('score'); // 'name' ou 'score'

const bairrosCards = computed(() => {
    const bairroArray = Object.keys(props.liderancasPorBairro).map(nome => {
        const lideres = props.liderancasPorBairro[nome];
        const maxScore = lideres.length > 0 ? Math.max(...lideres.map(l => l.engagement_score)) : 0;
        return { nome, maxScore, lideres };
    });

    if (sortBy.value === 'score') {
        return bairroArray.sort((a, b) => b.maxScore - a.maxScore);
    }
    return bairroArray.sort((a, b) => a.nome.localeCompare(b.nome));
});

const getRankingClasses = (index) => {
    if (index === 0) return 'bg-gradient-to-r from-amber-400 to-yellow-500 text-white shadow-lg';
    if (index === 1) return 'bg-gradient-to-r from-slate-400 to-gray-500 text-white shadow-md';
    if (index === 2) return 'bg-gradient-to-r from-orange-400 to-amber-600 text-white shadow';
    return 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300';
};

const getScoreStrength = (score) => {
    const maxExpectedScore = 100;
    const percentage = Math.min((score / maxExpectedScore) * 100, 100);
    return `${percentage}%`;
};

// --- LÓGICA PARA O MODO 'MAPA' ---
const activeBairro = ref(null);

const bairrosMap = computed(() => {
    return Object.keys(props.liderancasPorBairro)
        .map(nome => {
            const lideres = props.liderancasPorBairro[nome];
            const maxScore = lideres.length > 0 ? Math.max(...lideres.map(l => l.engagement_score)) : 0;
            return { nome, maxScore, lideres };
        })
        .sort((a, b) => b.maxScore - a.maxScore);
});

const selectedBairroData = computed(() => {
    if (!activeBairro.value) {
        return bairrosMap.value.length > 0 ? bairrosMap.value[0] : null;
    }
    return bairrosMap.value.find(b => b.nome === activeBairro.value);
});

const getColorForBairro = (bairroNome) => {
    let hash = 0;
    for (let i = 0; i < bairroNome.length; i++) {
        hash = bairroNome.charCodeAt(i) + ((hash << 5) - hash);
    }
    const h = hash % 360;
    return `hsl(${h}, 50%, 85%)`;
};

const getBorderColorForBairro = (bairroNome) => {
    let hash = 0;
    for (let i = 0; i < bairroNome.length; i++) {
        hash = bairroNome.charCodeAt(i) + ((hash << 5) - hash);
    }
    const h = hash % 360;
    return `hsl(${h}, 60%, 60%)`;
};
</script>

<template>
    <Head title="Mapeamento Político" />
    <TenantLayout title="Mapeamento Político">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Painel de Mapeamento Político
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Network :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Painel de Mapeamento Político</h2>
                        <p class="form-subtitle">Análise dos cidadãos com maior pontuação de engajamento, agrupados por bairro, para identificar contatos estratégicos.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                         <button @click="viewMode = 'map'" class="btn-secondary" :class="{'bg-gray-300 dark:bg-gray-600': viewMode === 'map'}">
                            <Map class="w-4 h-4 mr-2" />
                            Mapa
                        </button>
                        <button @click="viewMode = 'cards'" class="btn-secondary" :class="{'bg-gray-300 dark:bg-gray-600': viewMode === 'cards'}">
                            <LayoutGrid class="w-4 h-4 mr-2" />
                            Cards
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                     <div v-if="bairrosMap.length > 0">
                        <div v-if="viewMode === 'map'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2 p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex items-center justify-center">
                                <svg viewBox="0 0 400 300" class="w-full h-full">
                                    <g>
                                        <path v-for="(bairro, index) in bairrosMap" :key="bairro.nome"
                                            :d="generateRandomPath(index, bairrosMap.length)"
                                            :fill="getColorForBairro(bairro.nome)"
                                            :stroke="getBorderColorForBairro(bairro.nome)"
                                            stroke-width="1.5"
                                            @mouseover="activeBairro = bairro.nome"
                                            class="cursor-pointer transition-all duration-300"
                                            :class="{
                                                'opacity-100 scale-105 -translate-y-1': activeBairro === bairro.nome || (!activeBairro && index === 0),
                                                'opacity-60 hover:opacity-100': activeBairro && activeBairro !== bairro.nome,
                                            }"
                                            style="transform-origin: center center;"
                                        />
                                        <text v-for="(bairro, index) in bairrosMap" :key="`text-${bairro.nome}`"
                                            :x="getPathCentroid(index, bairrosMap.length)[0]"
                                            :y="getPathCentroid(index, bairrosMap.length)[1]"
                                            text-anchor="middle"
                                            alignment-baseline="middle"
                                            class="text-[8px] sm:text-[10px] font-bold fill-current text-gray-700 dark:text-gray-200 pointer-events-none transition-opacity duration-300"
                                            :class="{
                                                'opacity-100': activeBairro === bairro.nome || (!activeBairro && index === 0),
                                                'opacity-50': activeBairro && activeBairro !== bairro.nome
                                            }"
                                            >
                                            {{ bairro.nome }}
                                        </text>
                                    </g>
                                </svg>
                            </div>
                            <div class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                <div v-if="selectedBairroData">
                                    <h3 class="role-name pb-3 mb-4">
                                        {{ selectedBairroData.nome }}
                                    </h3>
                                    <ul class="space-y-4">
                                        <li v-for="lider in selectedBairroData.lideres" :key="lider.id" class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                                            <div class="flex justify-between items-center">
                                                <Link :href="route('admin.cidadaos.show', lider.id)" class="font-semibold text-gray-800 dark:text-gray-200 hover:underline text-sm">
                                                    {{ lider.name }}
                                                </Link>
                                                <div class="flex items-center gap-1.5 text-sm font-bold text-red-500 flex-shrink-0 ml-2" title="Índice de Engajamento">
                                                    <Activity class="w-4 h-4" />
                                                    <span>{{ lider.engagement_score }}</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5 mt-2" v-if="lider.tags.length">
                                                <span v-for="tag in lider.tags" :key="tag.id" class="px-2 py-0.5 text-[10px] font-bold tracking-wide rounded-full text-white" :style="{ backgroundColor: tag.cor }">
                                                    {{ tag.nome_tag.toUpperCase() }}
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="viewMode === 'cards'">
                            <div class="flex justify-end mb-6">
                                <div class="flex items-center gap-2">
                                    <label for="sort" class="input-label">Ordenar por:</label>
                                    <select id="sort" v-model="sortBy" class="input-form text-sm">
                                        <option value="score">Maior Engajamento</option>
                                        <option value="name">Nome do Bairro (A-Z)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                <div v-for="bairroData in bairrosCards" :key="bairroData.nome" class="flex flex-col p-6 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-md transition-all duration-300 hover:shadow-2xl hover:border-emerald-400 dark:hover:border-emerald-600">
                                    <h3 class="role-name pb-3 mb-4">
                                        {{ bairroData.nome }}
                                    </h3>
                                    <ul class="space-y-5 flex-grow">
                                        <li v-for="(lider, index) in bairroData.lideres" :key="lider.id" class="flex items-center gap-4">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm" :class="getRankingClasses(index)">
                                                {{ index + 1 }}º
                                            </div>
                                            <div class="flex-grow">
                                                <div class="flex justify-between items-start">
                                                    <Link :href="route('admin.cidadaos.show', lider.id)" class="font-semibold text-gray-800 dark:text-gray-200 hover:text-emerald-600 dark:hover:text-emerald-400 text-base leading-tight">
                                                        {{ lider.name }}
                                                    </Link>
                                                    <div class="flex items-center gap-1.5 text-sm font-bold text-red-500 flex-shrink-0 ml-2" title="Índice de Engajamento">
                                                        <Activity class="w-4 h-4" />
                                                        <span>{{ lider.engagement_score }}</span>
                                                    </div>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1 mt-1.5">
                                                    <div class="bg-red-500 h-1 rounded-full" :style="{ width: getScoreStrength(lider.engagement_score) }"></div>
                                                </div>
                                                <div class="flex flex-wrap gap-1.5 mt-2" v-if="lider.tags.length">
                                                    <span v-for="tag in lider.tags" :key="tag.id" class="px-2 py-0.5 text-[10px] font-bold tracking-wide rounded-full text-white" :style="{ backgroundColor: tag.cor }">
                                                        {{ tag.nome_tag.toUpperCase() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div v-if="!bairroData.lideres.length" class="flex-grow flex items-center justify-center text-sm text-gray-400 italic">
                                        Nenhum líder encontrado.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-20">
                        <BrainCircuit class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600" />
                        <h3 class="mt-4 font-semibold text-lg text-gray-700 dark:text-gray-300">Nenhum dado para exibir</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Não foram encontrados cidadãos com bairros cadastrados para gerar o mapeamento.</p>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script lang="ts">
// Funções auxiliares para gerar o mapa SVG (não precisam ser reativas)
const generateRandomPath = (index, total) => {
    const cols = Math.ceil(Math.sqrt(total));
    const rows = Math.ceil(total / cols);
    const cellWidth = 400 / cols;
    const cellHeight = 300 / rows;

    const col = index % cols;
    const row = Math.floor(index / cols);

    const x = col * cellWidth;
    const y = row * cellHeight;

    const randomness = 0.4;
    const random = (mult) => (Math.random() - 0.5) * cellWidth * randomness * mult;

    const p1 = `${x + random(0.5)},${y + random(0.5)}`;
    const p2 = `${x + cellWidth / 2 + random(1)},${y + random(0.2)}`;
    const p3 = `${x + cellWidth + random(0.5)},${y + random(0.5)}`;
    const p4 = `${x + cellWidth + random(0.2)},${y + cellHeight / 2 + random(1)}`;
    const p5 = `${x + cellWidth + random(0.5)},${y + cellHeight + random(0.5)}`;
    const p6 = `${x + cellWidth / 2 + random(1)},${y + cellHeight + random(0.2)}`;
    const p7 = `${x + random(0.5)},${y + cellHeight + random(0.5)}`;
    const p8 = `${x + random(0.2)},${y + cellHeight / 2 + random(1)}`;

    return `M${p1} L${p2} L${p3} L${p4} L${p5} L${p6} L${p7} L${p8} Z`;
};

const getPathCentroid = (index, total) => {
    const cols = Math.ceil(Math.sqrt(total));
    const rows = Math.ceil(total / cols);
    const cellWidth = 400 / cols;
    const cellHeight = 300 / rows;

    const col = index % cols;
    const row = Math.floor(index / cols);

    const x = col * cellWidth + cellWidth / 2;
    const y = row * cellHeight + cellHeight / 2;

    return [x, y];
};
</script>

<style scoped>
/* Estilos unificados do modelo */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-name { @apply text-xl font-bold text-emerald-600 dark:text-emerald-400; }
.input-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300; }

/* Estilos de botões */
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }

/* Estilo para inputs e selects do formulário */
.input-form { @apply block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500; }
</style>
