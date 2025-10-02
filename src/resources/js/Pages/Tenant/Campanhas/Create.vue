<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InputError from '@/Components/InputError.vue';
import Spinner from '@/Components/Spinner.vue';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { FilePlus, ArrowLeft, LoaderCircle } from 'lucide-vue-next';

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
        // Opcional: Adicionar um feedback de erro para o usuário
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

    <TenantLayout title="Nova Campanha de Comunicação">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Nova Campanha de Comunicação
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><FilePlus :size="32" class="icon-in-badge" /></div>

                <form @submit.prevent="submit">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Nova Campanha</h2>
                            <p class="form-subtitle">Preencha os detalhes, segmente o público e envie sua mensagem.</p>
                        </div>
                        <Link :href="route('admin.campanhas.index')" class="btn-secondary flex-shrink-0">
                            <ArrowLeft class="w-4 h-4 mr-2"/>
                            Voltar
                        </Link>
                    </div>

                    <div class="p-4 md:p-6">
                        <h3 class="role-name mb-4">1. Detalhes da Campanha</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="titulo" class="input-label">Título da Campanha</label>
                                <input id="titulo" v-model="form.titulo" type="text" class="mt-1 input-form" required />
                                <InputError :message="form.errors.titulo" class="mt-2" />
                            </div>
                            <div>
                                <label for="mensagem" class="input-label">Mensagem</label>
                                <textarea id="mensagem" v-model="form.mensagem" rows="6" class="mt-1 input-form" required></textarea>
                                <InputError :message="form.errors.mensagem" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 border-t-dynamic">
                        <h3 class="role-name mb-4">2. Segmentação do Público</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="input-label">Faixa Etária</label>
                                <div class="flex items-center space-x-2 mt-1">
                                    <input v-model="form.segmentacao.idade_min" type="number" placeholder="De" class="input-form w-full" />
                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                    <input v-model="form.segmentacao.idade_max" type="number" placeholder="Até" class="input-form w-full" />
                                </div>
                            </div>
                            <div>
                                <label for="genero" class="input-label">Gênero</label>
                                <select id="genero" v-model="form.segmentacao.genero" class="mt-1 input-form">
                                    <option value="">Todos</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Feminino">Feminino</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Renda Familiar (R$)</label>
                                <div class="flex items-center space-x-2 mt-1">
                                    <input v-model="form.segmentacao.renda_min" type="number" placeholder="De" class="input-form w-full" />
                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                    <input v-model="form.segmentacao.renda_max" type="number" placeholder="Até" class="input-form w-full" />
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label for="bairros" class="input-label">Bairros (selecione um ou mais)</label>
                                <v-select multiple v-model="form.segmentacao.bairros" :options="bairros" class="v-select-style mt-1" placeholder="Selecione os bairros"></v-select>
                            </div>
                        </div>
                         <div class="mt-6 flex items-center space-x-4">
                            <button type="button" @click="calcularPublico" :disabled="calculating" class="btn-secondary">
                                <Spinner v-if="calculating" class="mr-2 h-4 w-4" />
                                <LoaderCircle v-else class="mr-2 h-4 w-4" />
                                Calcular Público
                            </button>
                            <div v-if="totalPublico !== null" class="text-sm text-gray-600 dark:text-gray-300 transition-opacity duration-300">
                                Público estimado: <span class="font-bold text-lg text-emerald-700 dark:text-emerald-300">{{ totalPublico }}</span> cidadãos.
                            </div>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 border-t-dynamic flex items-center justify-end">
                        <button type="submit" class="btn-primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Agendar e Enviar Campanha
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

/* Estilização para o vue-select */
.v-select-style {
  --vs-colors--lightest: theme('colors.emerald.100');
  --vs-colors--light: theme('colors.emerald.300');
  --vs-colors--dark: theme('colors.emerald.600');
  --vs-colors--darkest: theme('colors.emerald.800');

  --vs-border-color: theme('colors.gray.300');
  --vs-border-radius: theme('borderRadius.md');
  --vs-font-size: theme('fontSize.sm');
}

.dark .v-select-style {
    --vs-border-color: theme('colors.gray.600');
    --vs-dropdown-bg: theme('colors.gray.700');
    --vs-dropdown-color: theme('colors.white');
    --vs-dropdown-option-color: theme('colors.white');
    --vs-selected-bg: theme('colors.emerald.600');
    --vs-selected-color: theme('colors.white');
    --vs-search-input-color: theme('colors.white');
    --vs-dropdown-option--active-bg: theme('colors.emerald.600');
    --vs-dropdown-option--active-color: theme('colors.white');
}
</style>
