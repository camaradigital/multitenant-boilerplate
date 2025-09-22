<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { ClipboardList, ArrowLeft } from 'lucide-vue-next';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    cidadaos: Array,
    servicos: Array,
    exigirRendaJuridico: Boolean,
});

const form = useForm({
    user_id: '',
    servico_id: '',
    observacoes: '',
    renda_familiar: '',
});

const cidadaosForSelect = computed(() =>
    props.cidadaos.map(c => ({
        ...c,
        label: `${c.name} - ${c.cpf || 'CPF não informado'}`
    }))
);

const servicoSelecionado = computed(() => {
    return props.servicos.find(s => s.id === form.servico_id);
});

const mostrarCampoRenda = computed(() => {
    return props.exigirRendaJuridico && servicoSelecionado.value?.is_juridico;
});

watch(() => form.user_id, (newUserId) => {
    if (newUserId) {
        const cidadao = props.cidadaos.find(c => c.id === newUserId);
        if (cidadao && cidadao.profile_data && cidadao.profile_data.renda_familiar) {
            form.renda_familiar = cidadao.profile_data.renda_familiar;
        } else {
            form.renda_familiar = '';
        }
    }
});

const submit = () => {
    form.post(route('admin.solicitacoes.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nova Solicitação" />

    <TenantLayout title="Nova Solicitação">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Criar Nova Solicitação de Serviço
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-4xl">
                <div class="form-icon"><ClipboardList :size="32" class="icon-in-badge" /></div>

                <div class="p-6 border-b-dynamic">
                    <h2 class="header-title">Registrar Nova Solicitação</h2>
                    <p class="form-subtitle">Preencha os dados abaixo para criar uma nova solicitação.</p>
                </div>

                <form @submit.prevent="submit">
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="cidadao" class="form-label">Cidadão</label>
                            <v-select
                                id="cidadao"
                                v-model="form.user_id"
                                :options="cidadaosForSelect"
                                :reduce="cidadao => cidadao.id"
                                label="label"
                                class="v-select-custom"
                                placeholder="Digite ou selecione um cidadão"
                            >
                                <template #no-options>
                                    Nenhum cidadão encontrado.
                                </template>
                            </v-select>
                            <div v-if="form.errors.user_id" class="form-error">{{ form.errors.user_id }}</div>
                        </div>

                        <div>
                            <label for="servico" class="form-label">Serviço Solicitado</label>
                            <select v-model="form.servico_id" id="servico" class="form-input" required>
                                <option disabled value="">Selecione um serviço</option>
                                <option v-for="servico in servicos" :key="servico.id" :value="servico.id">{{ servico.nome }}</option>
                            </select>
                            <div v-if="form.errors.servico_id" class="form-error">{{ form.errors.servico_id }}</div>
                        </div>

                        <div v-if="mostrarCampoRenda">
                            <label for="renda_familiar" class="form-label">Renda Familiar do Cidadão (R$)</label>
                            <input
                                type="number"
                                step="0.01"
                                v-model="form.renda_familiar"
                                id="renda_familiar"
                                class="form-input"
                                placeholder="Necessário para serviços jurídicos"
                                :required="exigirRendaJuridico"
                            >
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Este valor será salvo ou atualizado no perfil do cidadão.</p>
                            <div v-if="form.errors.renda_familiar" class="form-error">{{ form.errors.renda_familiar }}</div>
                        </div>

                        <div>
                            <label for="observacoes" class="form-label">Observações Iniciais (Opcional)</label>
                            <textarea v-model="form.observacoes" id="observacoes" rows="3" class="form-input"></textarea>
                            <div v-if="form.errors.observacoes" class="form-error">{{ form.errors.observacoes }}</div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center rounded-b-3xl">
                        <Link :href="route('admin.solicitacoes.index')" class="btn-secondary">
                           <ArrowLeft class="w-4 h-4 mr-2" />
                            Voltar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="btn-primary">
                            Registrar Solicitação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Copie e cole todos os estilos do seu arquivo original aqui */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400 disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4 bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }

:deep(.v-select-custom) {
    --vs-font-size: 0.875rem;
    --vs-line-height: 1.25rem;
}

:deep(.v-select-custom .vs__dropdown-toggle) {
    @apply block w-full text-sm rounded-xl transition-all h-12 py-0 px-0 bg-gray-50 border-gray-300 text-gray-900 dark:bg-gray-700/50 dark:border-gray-600 dark:text-white;
    border-width: 1px;
}

:deep(.v-select-custom .vs__dropdown-toggle:focus-within) {
    @apply ring-1 ring-emerald-500 border-emerald-500 dark:ring-green-500 dark:border-green-500;
}

:deep(.v-select-custom .vs__selected-options) {
    @apply p-0 flex-nowrap;
    padding: 0.875rem 1rem;
}

:deep(.v-select-custom .vs__search) {
    @apply m-0 p-0 text-gray-900 dark:text-white;
    &::placeholder {
        @apply text-gray-400 dark:text-gray-400;
    }
}

:deep(.v-select-custom .vs__selected) {
    @apply m-0 p-0 text-sm text-gray-900 dark:text-white;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

:deep(.v-select-custom .vs__actions) {
    @apply p-0;
    padding-right: 0.5rem;
}

:deep(.v-select-custom .vs__clear svg) {
    fill: #9ca3af;
}

:deep(.v-select-custom .vs__dropdown-menu) {
    @apply bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-xl;
}

:deep(.v-select-custom .vs__option) {
    @apply text-sm text-gray-900 dark:text-gray-200;
}

:deep(.v-select-custom .vs__option--highlight) {
    @apply bg-emerald-600 text-white;
}

:deep(.v-select-custom .vs__no-options) {
    @apply p-4 text-sm text-center text-gray-500;
}
</style>
