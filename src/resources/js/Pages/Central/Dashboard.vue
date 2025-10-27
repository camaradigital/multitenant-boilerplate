<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Importa os ícones Lucide que serão usados
import { Home, Users, Settings, Target, Send, BarChart2, CheckCircle, XCircle } from 'lucide-vue-next';

// Define as propriedades que vêm do controller, incluindo as que você tinha no original
const props = defineProps({
    totalTenants: Number,
    totalLeads: Number,
    totalUsers: { type: Number, default: 0 },
    pendingRequests: { type: Number, default: 0 },
    completedRequestsToday: { type: Number, default: 0 },
    failedLoginsToday: { type: Number, default: 0 }
});
</script>

<template>
    <Head title="Dashboard Super Admin" />

    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard do Super Administrador
            </h2>
        </template>

        <div class="py-12 px-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Seção de Boas-Vindas -->
                <div class="p-6 md:px-8 text-left mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Bem-vindo(a) de volta! 👋</h2>
                    <p class="text-base mt-2 text-gray-500 dark:text-gray-400">Visão geral do seu painel de controle central do CACSystem.</p>
                </div>

                <!-- Grid de Ações Principais -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10 px-6 md:px-8">
                    <Link :href="route('central.tenants.index')" class="action-card group bg-emerald-50 dark:bg-emerald-900/50 border-emerald-200 dark:border-emerald-500/30">
                        <div class="action-icon bg-emerald-600 dark:bg-emerald-500">
                            <Home class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h3 class="action-title text-emerald-900 dark:text-emerald-200">Gerenciar Câmaras</h3>
                            <p class="action-description">Visualize, crie e edite os tenants ativos.</p>
                        </div>
                        <span class="action-arrow text-emerald-500">&rarr;</span>
                    </Link>

                     <Link :href="route('central.leads.index')" class="action-card group bg-blue-50 dark:bg-blue-900/50 border-blue-200 dark:border-blue-500/30">
                        <div class="action-icon bg-blue-600 dark:bg-blue-500">
                            <Target class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h3 class="action-title text-blue-900 dark:text-blue-200">Prospecção</h3>
                            <p class="action-description">Gerencie sua lista de potenciais clientes.</p>
                        </div>
                        <span class="action-arrow text-blue-500">&rarr;</span>
                    </Link>

                     <Link :href="route('central.roles_permissions.index')" class="action-card group bg-purple-50 dark:bg-purple-900/50 border-purple-200 dark:border-purple-500/30">
                        <div class="action-icon bg-purple-600 dark:bg-purple-500">
                            <Settings class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h3 class="action-title text-purple-900 dark:text-purple-200">Permissões Globais</h3>
                            <p class="action-description">Ajuste papéis e permissões do sistema.</p>
                        </div>
                        <span class="action-arrow text-purple-500">&rarr;</span>
                    </Link>
                </div>

                <!-- Grid de Métricas -->
                <h3 class="px-6 md:px-8 text-xl font-bold text-gray-800 dark:text-gray-200 mb-5">Métricas do Sistema</h3>
                 <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 px-6 md:px-8">
                    <div class="stat-card">
                        <div class="flex items-center">
                            <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400"><Home class="h-5 w-5" /></div>
                            <p class="stat-title">Tenants Ativos</p>
                        </div>
                        <p class="stat-value">{{ props.totalTenants }}</p>
                    </div>
                     <div class="stat-card">
                        <div class="flex items-center">
                            <div class="stat-icon bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400"><Target class="h-5 w-5" /></div>
                            <p class="stat-title">Leads</p>
                        </div>
                        <p class="stat-value">{{ props.totalLeads }}</p>
                    </div>
                    <div class="stat-card">
                        <div class="flex items-center">
                            <div class="stat-icon bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400"><Users class="h-5 w-5" /></div>
                            <p class="stat-title">Total de Usuários</p>
                        </div>
                        <p class="stat-value">{{ props.totalUsers }}</p>
                    </div>
                     <div class="stat-card">
                        <div class="flex items-center">
                            <div class="stat-icon bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-400"><BarChart2 class="h-5 w-5" /></div>
                            <p class="stat-title">Pendentes</p>
                        </div>
                        <p class="stat-value">{{ props.pendingRequests }}</p>
                    </div>
                     <div class="stat-card">
                        <div class="flex items-center">
                            <div class="stat-icon bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400"><CheckCircle class="h-5 w-5" /></div>
                            <p class="stat-title">Concluídas Hoje</p>
                        </div>
                        <p class="stat-value">{{ props.completedRequestsToday }}</p>
                    </div>
                     <div class="stat-card">
                        <div class="flex items-center">
                            <div class="stat-icon bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400"><XCircle class="h-5 w-5" /></div>
                            <p class="stat-title">Logins Falhos</p>
                        </div>
                        <p class="stat-value">{{ props.failedLoginsToday }}</p>
                    </div>
                 </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.action-card {
    @apply flex items-center p-6 rounded-2xl border transition-all duration-300 hover:shadow-lg hover:border-transparent hover:-translate-y-1;
}
.action-icon {
    @apply w-12 h-12 rounded-full flex items-center justify-center shadow-md mr-5 flex-shrink-0;
}
.action-title {
    @apply text-lg font-bold;
}
.action-description {
    @apply text-sm text-gray-600 dark:text-gray-400;
}
.action-arrow {
    @apply ml-auto text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300;
}

.stat-card {
    @apply bg-white dark:bg-[#102C26]/60 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-green-400/10;
}
.stat-icon {
    @apply w-10 h-10 rounded-full flex items-center justify-center mr-4;
}
.stat-title {
    @apply text-sm font-medium text-gray-600 dark:text-gray-400;
}
.stat-value {
    @apply text-3xl font-extrabold text-gray-900 dark:text-white mt-2 text-left;
}
</style>
