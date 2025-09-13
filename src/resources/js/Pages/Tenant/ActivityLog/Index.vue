<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { History, User, Server, Search } from 'lucide-vue-next';

const props = defineProps({
    activities: Object, // Objeto de paginação
    filtros: Object,    // Filtros atuais para preencher o formulário
});

// Formulário para gerenciar os filtros
const form = useForm({
    busca_usuario: props.filtros.busca_usuario || '',
    evento: props.filtros.evento || '',
    data_inicio: props.filtros.data_inicio || '',
    data_fim: props.filtros.data_fim || '',
});

// Função para submeter o formulário e aplicar os filtros
const filtrar = () => {
    form.get(route('admin.auditoria.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

// Função para limpar os filtros e buscar todos os resultados novamente
const limparFiltros = () => {
    form.reset();
    filtrar();
};

// Função para traduzir o tipo de evento
const formatEvent = (eventName) => {
    const translations = {
        created: 'Criou',
        updated: 'Atualizou',
        deleted: 'Excluiu',
    };
    return translations[eventName] || eventName;
};

// Função para extrair e formatar o nome do modelo
const formatSubjectType = (subjectType) => {
    if (!subjectType) return 'Registro';
    return subjectType.split('\\').pop().replace(/([A-Z])/g, ' $1').trim();
};

</script>

<template>
    <Head title="Log de Auditoria" />

    <TenantLayout title="Log de Auditoria">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Log de Auditoria
            </h2>
        </template>

        <div class="py-12 px-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Bloco de Filtros -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filtros de Auditoria</h3>
                        <form @submit.prevent="filtrar">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="lg:col-span-1">
                                    <label for="busca_usuario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usuário</label>
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <Search class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input type="text" id="busca_usuario" v-model="form.busca_usuario" class="block w-full rounded-md border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="Buscar por nome...">
                                    </div>
                                </div>
                                <div>
                                    <label for="evento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Evento</label>
                                    <select id="evento" v-model="form.evento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        <option value="">Todos</option>
                                        <option value="created">Criação</option>
                                        <option value="updated">Atualização</option>
                                        <option value="deleted">Exclusão</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período</label>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <input type="date" id="data_inicio" v-model="form.data_inicio" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        <span class="text-gray-500">até</span>
                                        <input type="date" id="data_fim" v-model="form.data_fim" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-4 space-x-3">
                                <SecondaryButton type="button" @click="limparFiltros" :disabled="form.processing">Limpar</SecondaryButton>
                                <PrimaryButton :disabled="form.processing">Filtrar</PrimaryButton>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Lista de Logs -->
                <div class="content-container">
                    <div class="p-6 border-b-dynamic">
                        <h2 class="header-title">Trilha de Auditoria</h2>
                        <p class="form-subtitle">Histórico de todas as ações importantes realizadas no sistema.</p>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="activities.data.length > 0" class="space-y-4">
                            <div v-for="activity in activities.data" :key="activity.id" class="log-card">
                                <div class="log-header">
                                    <div class="flex items-center gap-3">
                                        <div class="log-icon-wrapper">
                                            <User v-if="activity.causer" class="w-5 h-5"/>
                                            <Server v-else class="w-5 h-5"/>
                                        </div>
                                        <div>
                                            <p class="log-description">
                                                <span class="font-bold">{{ activity.causer ? activity.causer.name : 'Sistema' }}</span>
                                                {{ formatEvent(activity.event) }} o registro
                                                <span class="font-bold">{{ formatSubjectType(activity.subject_type) }}</span>
                                                <span v-if="activity.subject">#{{ activity.subject.id }}</span>.
                                            </p>
                                            <p class="log-timestamp">
                                                {{ new Date(activity.created_at).toLocaleString('pt-BR') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="activity.properties && (activity.properties.attributes || activity.properties.old)" class="log-details">
                                    <h4 class="details-title">Detalhes da Alteração</h4>
                                    <div class="details-grid">
                                        <template v-for="(newValue, key) in activity.properties.attributes" :key="key">
                                            <div v-if="activity.event === 'created' || (activity.properties.old && activity.properties.old[key] !== newValue)">
                                                <div class="details-key">{{ key }}</div>
                                                <div v-if="activity.event === 'updated'" class="details-value-old">{{ activity.properties.old[key] || 'vazio' }}</div>
                                                <div class="details-value-new" :class="{ 'col-span-2': activity.event === 'created' }">{{ newValue || 'vazio' }}</div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-10">
                            <p class="text-gray-500 dark:text-gray-400">Nenhum registro de atividade encontrado para os filtros selecionados.</p>
                        </div>
                    </div>

                    <div class="px-6 pb-4" v-if="activities.data.length > 0">
                        <Pagination :links="activities.links" />
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
.content-container { @apply w-full rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

.log-card { @apply bg-white dark:bg-white/5 p-4 rounded-xl border border-gray-200 dark:border-white/10; }
.log-header { @apply flex items-center justify-between; }
.log-icon-wrapper { @apply flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400; }
.log-description { @apply text-sm text-gray-800 dark:text-gray-200; }
.log-timestamp { @apply text-xs text-gray-500 dark:text-gray-400; }

.log-details { @apply mt-3 pt-3 border-t border-dashed border-gray-200 dark:border-gray-700; }
.details-title { @apply text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-2; }
.details-grid { @apply grid grid-cols-[auto_1fr_1fr] gap-x-4 gap-y-1 text-xs; }
.details-key { @apply font-mono text-gray-600 dark:text-gray-400; }
.details-value-old { @apply font-mono text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 rounded line-through break-all; }
.details-value-new { @apply font-mono text-green-600 dark:text-green-300 bg-green-50 dark:bg-green-500/10 px-2 rounded break-all; }
</style>
