<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Megaphone, Plus, Users, Calendar, User, ArrowRight, Speaker } from 'lucide-vue-next';

const props = defineProps({
    campanhas: Object,
});

// Função para obter o status e a classe correspondente
const getStatusInfo = (campanha) => {
    if (campanha.total_enviado > 0) {
        return { text: 'Enviado', class: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' };
    }
    return { text: 'Agendado', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' };
};

// Função para formatar a data
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Função para pegar as iniciais do nome
const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};
</script>

<template>
    <Head title="Comunicação Direcionada" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Comunicação Direcionada
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Card Principal -->
                <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">

                    <!-- Ícone no Topo -->
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <Megaphone :size="32" class="text-white" />
                    </div>

                    <!-- Cabeçalho do Card -->
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pt-12 p-6 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Campanhas de Comunicação</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie e gerencie suas campanhas de comunicação para os cidadãos.</p>
                        </div>
                        <Link :href="route('admin.campanhas.create')" class="flex-shrink-0 inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 dark:focus:ring-offset-gray-900">
                            <Plus class="w-4 h-4 mr-2"/>
                            Nova Campanha
                        </Link>
                    </div>

                    <!-- Lista de Campanhas e Estado Vazio -->
                    <div class="p-4 md:p-6">
                        <!-- Estado Vazio -->
                        <div v-if="campanhas.data.length === 0" class="text-center py-16">
                            <Speaker class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Nenhuma campanha criada</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Clique em "Nova Campanha" para criar e enviar sua primeira comunicação.</p>
                        </div>

                        <!-- Lista de Cards de Campanhas -->
                        <div v-else class="space-y-4">
                            <Link v-for="campanha in campanhas.data" :key="campanha.id" :href="route('admin.campanhas.show', campanha.id)" class="block p-5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 hover:border-emerald-500 dark:hover:border-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all duration-200 group">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-start gap-4">
                                    <div class="flex-grow">
                                        <div class="flex items-center gap-3">
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusInfo(campanha).class">
                                                {{ getStatusInfo(campanha).text }}
                                            </span>
                                        </div>
                                        <p class="mt-2 text-lg font-bold text-gray-900 dark:text-white group-hover:text-emerald-800 dark:group-hover:text-emerald-300">{{ campanha.titulo }}</p>
                                    </div>
                                    <div class="flex-shrink-0 flex items-center gap-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 opacity-0 group-hover:opacity-100 transition-opacity self-start sm:self-center">
                                        Ver Detalhes
                                        <ArrowRight class="h-4 w-4" />
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-2" title="Público Atingido">
                                        <Users class="h-4 w-4" />
                                        <span class="font-medium">{{ campanha.total_enviado || 'N/D' }} Cidadãos</span>
                                    </div>
                                    <div class="flex items-center gap-2" title="Data de Criação">
                                        <Calendar class="h-4 w-4" />
                                        <span>{{ formatDate(campanha.created_at) }}</span>
                                    </div>
                                    <div v-if="campanha.createdBy" class="flex items-center gap-2" title="Criada Por">
                                        <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(campanha.createdBy.name) }}</span>
                                        </div>
                                        <span>{{ campanha.createdBy.name }}</span>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <Pagination class="pt-6" :links="campanhas.links" v-if="campanhas.data.length > 0"/>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
