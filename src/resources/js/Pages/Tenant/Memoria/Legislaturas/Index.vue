<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { CalendarRange, Plus, Users } from 'lucide-vue-next';

const props = defineProps({
    legislaturas: Object,
});
</script>

<template>
    <Head title="Legislaturas" />
    <TenantLayout title="Legislaturas">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Memória Legislativa - Legislaturas
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
                <div class="form-icon"><CalendarRange :size="32" class="icon-in-badge" /></div>
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Legislaturas</h2>
                        <p class="form-subtitle">Gerencie os períodos legislativos e sua composição.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <Link :href="route('admin.legislaturas.create')" class="btn-primary"><Plus class="h-4 w-4 mr-2" />Nova Legislatura</Link>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <div v-if="legislaturas.data.length > 0" class="space-y-4">
                        <div v-for="leg in legislaturas.data" :key="leg.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ leg.titulo }}</p>
                                <p class="form-subtitle">
                                    {{ new Date(leg.data_inicio).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }} a {{ new Date(leg.data_fim).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }}
                                </p>
                            </div>
                            <!-- Badge de Contagem de Membros -->
                            <div class="flex items-center gap-4">
                                <span class="member-count">
                                    <Users class="w-3 h-3 mr-1.5" />
                                    {{ leg.mandatos_count }} {{ leg.mandatos_count === 1 ? 'membro' : 'membros' }}
                                </span>
                                <Link :href="route('admin.legislaturas.edit', leg.id)" class="btn-secondary !px-4 !py-2">
                                    Gerenciar
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10"><p class="text-gray-500">Nenhuma legislatura registrada.</p></div>
                </div>
                <div v-if="legislaturas.data.length > 0" class="px-6 pb-4">
                    <Pagination :links="legislaturas.links" />
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-600; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }

/* Estilo para a contagem de membros */
.member-count {
    @apply inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full;
    @apply bg-emerald-100 text-emerald-800;
    @apply dark:bg-emerald-900/50 dark:text-emerald-300;
}
</style>
