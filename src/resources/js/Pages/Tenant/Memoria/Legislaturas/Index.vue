<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { CalendarRange, Plus, Users, CalendarX, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    legislaturas: Object,
});

// Função para formatar o intervalo de datas, tornando o template mais limpo.
const formatDateRange = (startDate, endDate) => {
    const options = { timeZone: 'UTC', day: '2-digit', month: '2-digit', year: 'numeric' };
    const start = new Date(startDate).toLocaleDateString('pt-BR', options);
    const end = new Date(endDate).toLocaleDateString('pt-BR', options);
    return `${start} a ${end}`;
};
</script>

<template>
    <Head title="Legislaturas" />
    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Memória Legislativa - Legislaturas
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="content-container">
                    <div class="form-icon">
                        <CalendarRange :size="32" class="icon-in-badge" />
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Legislaturas Cadastradas</h2>
                            <p class="form-subtitle">Gerencie os períodos legislativos e sua composição.</p>
                        </div>
                        <div class="w-full md:w-auto">
                            <Link :href="route('admin.legislaturas.create')" class="btn-primary w-full">
                                <Plus class="h-4 w-4 mr-2" />
                                <span>Nova Legislatura</span>
                            </Link>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="legislaturas.data.length > 0" class="space-y-4">
                            <Link
                                v-for="leg in legislaturas.data"
                                :key="leg.id"
                                :href="route('admin.legislaturas.edit', leg.id)"
                                class="legislature-card group"
                            >
                                <div class="card-image-container">
                                    <img v-if="leg.foto_principal_url" :src="leg.foto_principal_url" :alt="leg.titulo" class="card-image">
                                    <div v-else class="card-image-placeholder">
                                        <CalendarRange class="h-8 w-8 text-gray-400" />
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3">
                                        <p class="role-name truncate" :title="leg.titulo">{{ leg.titulo }}</p>
                                        <span v-if="leg.is_atual" class="badge-atual">
                                            <CheckCircle2 class="h-3.5 w-3.5 mr-1" />
                                            Atual
                                        </span>
                                    </div>
                                    <p class="form-subtitle flex items-center mt-1">
                                        <CalendarRange class="w-4 h-4 mr-2 text-gray-400" />
                                        {{ formatDateRange(leg.data_inicio, leg.data_fim) }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-4 ml-4">
                                    <span class="member-count hidden sm:inline-flex">
                                        <Users class="w-3.5 h-3.5 mr-1.5" />
                                        {{ leg.mandatos_count }} {{ leg.mandatos_count === 1 ? 'membro' : 'membros' }}
                                    </span>
                                    <span class="btn-secondary !px-4 !py-2 group-hover:bg-emerald-50 dark:group-hover:bg-gray-600">
                                        Gerenciar
                                    </span>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="text-center py-16 px-6">
                            <CalendarX class="mx-auto h-16 w-16 text-gray-400" />
                            <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-200">Nenhuma Legislatura Cadastrada</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                Clique no botão abaixo para adicionar a primeira legislatura e começar a organizar.
                            </p>
                            <div class="mt-6">
                                <Link :href="route('admin.legislaturas.create')" class="btn-primary">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Criar Primeira Legislatura
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-if="legislaturas.data.length > 0" class="px-6 pb-4 border-t-dynamic pt-4">
                        <Pagination :links="legislaturas.links" />
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes */
.content-container { @apply relative w-full pt-16 rounded-2xl shadow-lg; @apply bg-white border border-gray-200/80; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm text-gray-500 dark:text-gray-400; }

/* Ícone no topo (Restaurado) */
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white dark:text-[#0A1E1C]; }

/* Card da Legislatura Aprimorado */
.legislature-card { @apply bg-white dark:bg-white/5 p-4 rounded-xl border border-gray-200 dark:border-white/10 flex items-center gap-4 transition-all duration-300; @apply hover:shadow-lg hover:border-emerald-400 dark:hover:border-emerald-500 hover:scale-[1.02]; }
.card-image-container { @apply shrink-0 w-24 h-16 rounded-lg overflow-hidden; }
.card-image { @apply w-full h-full object-cover; }
.card-image-placeholder { @apply w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700/50; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }

/* Badges */
.member-count { @apply items-center text-xs font-medium px-2.5 py-1 rounded-full; @apply bg-gray-100 text-gray-800; @apply dark:bg-gray-700 dark:text-gray-300; }
.badge-atual { @apply inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-full; @apply bg-emerald-100 text-emerald-800 border border-emerald-300; @apply dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/30; }

/* Botões */
.btn-primary { @apply inline-flex items-center justify-center px-5 py-2.5 rounded-lg font-semibold text-sm uppercase tracking-wider transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-900 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500; @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm transition-colors; }
</style>
