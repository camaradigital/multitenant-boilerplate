<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InputError from '@/Components/InputError.vue';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { FilePlus, ArrowLeft, LoaderCircle, Users } from 'lucide-vue-next';

const props = defineProps({
    bairros: Array,
});

const form = useForm({
    titulo: '',
    mensagem: '',
    segmentacao: {
        idade_min: null,
        idade_max: null,
        genero: '',
        renda_min: null,
        renda_max: null,
        bairros: [],
    },
});

const totalPublico = ref(null);
const calculating = ref(false);

const calcularPublico = async () => {
    calculating.value = true;
    totalPublico.value = null;
    try {
        const response = await axios.post(route('admin.campanhas.calcular-publico'), { segmentacao: form.segmentacao });
        totalPublico.value = response.data.total;
    } catch (error) {
        console.error("Erro ao calcular público:", error);
        totalPublico.value = 'N/A'; // Feedback de erro
    } finally {
        calculating.value = false;
    }
};

const submit = () => {
    form.post(route('admin.campanhas.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Nova Campanha de Comunicação" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Nova Campanha de Comunicação
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                 <!-- Card Principal -->
                <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">
                        <!-- Ícone no Topo -->
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <FilePlus :size="32" class="text-white" />
                    </div>

                    <form @submit.prevent="submit">
                        <!-- Cabeçalho -->
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pt-12 p-6 border-b border-gray-200 dark:border-white/10">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Nova Campanha</h2>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preencha os detalhes, segmente o público e envie sua mensagem.</p>
                            </div>
                            <Link :href="route('admin.campanhas.index')" class="flex-shrink-0 inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                <ArrowLeft class="w-4 h-4 mr-2"/>
                                Voltar
                            </Link>
                        </div>

                        <!-- Layout em Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
                            <!-- Coluna Esquerda: Detalhes da Campanha -->
                            <div class="lg:col-span-2 space-y-6">
                                <div>
                                    <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">1. Detalhes da Campanha</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label for="titulo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Título da Campanha</label>
                                            <input id="titulo" v-model="form.titulo" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" required />
                                            <InputError :message="form.errors.titulo" class="mt-2" />
                                        </div>
                                        <div>
                                            <label for="mensagem" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem</label>
                                            <textarea id="mensagem" v-model="form.mensagem" rows="12" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" required></textarea>
                                            <InputError :message="form.errors.mensagem" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Coluna Direita: Segmentação -->
                            <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border dark:border-white/10 space-y-6">
                                <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-300">2. Segmentação</h3>
                                <div class="space-y-4">
                                      <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Faixa Etária</label>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <input v-model="form.segmentacao.idade_min" type="number" placeholder="De" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" />
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                            <input v-model="form.segmentacao.idade_max" type="number" placeholder="Até" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" />
                                        </div>
                                    </div>
                                    <div>
                                        <label for="genero" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gênero</label>
                                        <select id="genero" v-model="form.segmentacao.genero" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                            <option value="">Todos</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Feminino">Feminino</option>
                                            <option value="Outro">Outro</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Renda Familiar (R$)</label>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <input v-model="form.segmentacao.renda_min" type="number" placeholder="De" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" />
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                            <input v-model="form.segmentacao.renda_max" type="number" placeholder="Até" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" />
                                        </div>
                                    </div>
                                    <div>
                                        <label for="bairros" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bairros</label>
                                        <v-select multiple v-model="form.segmentacao.bairros" :options="bairros" class="v-select-style mt-1" placeholder="Selecione os bairros"></v-select>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 dark:border-white/10 pt-6 space-y-4">
                                     <button type="button" @click="calcularPublico" :disabled="calculating" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 disabled:opacity-50">
                                        <LoaderCircle class="mr-2 h-4 w-4" :class="{ 'animate-spin': calculating }" />
                                        Calcular Público
                                    </button>
                                     <div class="relative min-h-[80px] flex items-center justify-center bg-gray-100 dark:bg-gray-900/50 rounded-lg p-4 transition-all duration-300">
                                         <div v-if="calculating" class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                            <LoaderCircle class="h-5 w-5 animate-spin" />
                                            <span class="text-sm font-medium">Calculando...</span>
                                        </div>
                                         <div v-else-if="totalPublico !== null" class="flex items-center gap-4 text-emerald-800 dark:text-emerald-300">
                                            <Users class="h-8 w-8 flex-shrink-0" />
                                            <div class="text-left">
                                                <span class="font-bold text-3xl">{{ totalPublico }}</span>
                                                <p class="text-xs font-medium">cidadãos estimados</p>
                                            </div>
                                        </div>
                                        <div v-else class="text-center text-sm text-gray-500 dark:text-gray-400">
                                            Clique em "Calcular Público" para ver a estimativa.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rodapé com Ação Principal -->
                        <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-white/10 flex items-center justify-end rounded-b-2xl">
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 disabled:opacity-50" :class="{ 'opacity-50': form.processing }" :disabled="form.processing || totalPublico === null">
                                Criar Rascunho da Campanha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style>
/* Estilização customizada para o vue-select para se adequar ao tema */
.v-select-style {
    --vs-colors--lightest: #d1fae5; /* emerald-100 */
    --vs-colors--light: #6ee7b7; /* emerald-300 */
    --vs-colors--dark: #059669; /* emerald-600 */
    --vs-colors--darkest: #047857; /* emerald-700 */

    --vs-border-color: #d1d5db; /* gray-300 */
    --vs-border-radius: 0.375rem; /* rounded-md */
    --vs-font-size: 0.875rem; /* text-sm */
    --vs-line-height: 1.25rem;
    --vs-search-input-placeholder-color: #6b7280; /* gray-500 */
}

.dark .v-select-style {
    --vs-border-color: #4b5563; /* gray-600 */
    --vs-dropdown-bg: #374151; /* gray-700 */
    --vs-dropdown-color: #f9fafb; /* gray-50 */
    --vs-dropdown-option-color: #f9fafb;
    --vs-selected-bg: var(--vs-colors--dark);
    --vs-selected-color: #ffffff;
    --vs-search-input-color: #f9fafb;
    --vs-search-input-placeholder-color: #9ca3af; /* gray-400 */
    --vs-dropdown-option--active-bg: var(--vs-colors--dark);
    --vs-dropdown-option--active-color: #ffffff;
    --vs-clear-button-color: #9ca3af;
    --vs-open-indicator-color: #9ca3af;
}

.v-select-style .vs__dropdown-toggle {
    background-color: transparent;
    padding-top: 2px;
    padding-bottom: 2px;
}
.dark .v-select-style .vs__dropdown-toggle {
    background-color: #1f2937; /* gray-800 */
}
.dark .v-select-style.vs--open .vs__dropdown-toggle {
     background-color: #374151; /* gray-700 */
}
</style>
