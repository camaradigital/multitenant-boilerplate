<script setup>
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Megaphone, Plus } from 'lucide-vue-next';

const props = defineProps({
    campanhas: Object,
});
</script>

<template>
    <Head title="Comunicação Direcionada" />

    <TenantLayout title="Comunicação Direcionada">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Comunicação Direcionada
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Megaphone :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Campanhas de Comunicação</h2>
                        <p class="form-subtitle">Crie e gerencie suas campanhas de comunicação para os cidadãos.</p>
                    </div>
                    <Link :href="route('admin.campanhas.create')" class="btn-primary flex-shrink-0">
                        <Plus class="w-4 h-4 mr-2"/>
                        Nova Campanha
                    </Link>
                </div>

                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Título</th>
                                    <th scope="col" class="px-6 py-3">Criada Em</th>
                                    <th scope="col" class="px-6 py-3">Criada Por</th>
                                    <th scope="col" class="px-6 py-3">Público Atingido</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="campanhas.data.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center">Nenhuma campanha criada ainda.</td>
                                </tr>
                                <tr v-for="campanha in campanhas.data" :key="campanha.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ campanha.titulo }}</td>
                                    <td class="px-6 py-4">{{ new Date(campanha.created_at).toLocaleString('pt-BR') }}</td>
                                    <td class="px-6 py-4">{{ campanha.createdBy?.name }}</td>
                                    <td class="px-6 py-4 font-medium">{{ campanha.total_enviado || 'Agendado' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="pt-6" :links="campanhas.links" v-if="campanhas.data.length > 0"/>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos unificados do modelo */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

/* Estilos de botões */
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
</style>
