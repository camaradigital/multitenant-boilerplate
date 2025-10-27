<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Importa os ícones Lucide que serão usados
import { Home, Users, Settings, Target, Send, BarChart2, CheckCircle, XCircle, HeartPulse, Database, Server } from 'lucide-vue-next';

// Define as propriedades que vêm do controller (novas e antigas)
const props = defineProps({
    // Novas props
    tenantsAtivos: { type: Number, default: 0 },
    totalLeads: { type: Number, default: 0 },
    campanhasEnviadasHoje: { type: Number, default: 0 },
    totalUsuarios: { type: Number, default: 0 }, // Nome novo
    loginsFalhosHoje: { type: Number, default: 0 }, // Nome novo
    saudeSistema: { type: Object, default: () => ({}) },

    // Props antigas (mantidas para compatibilidade ou uso futuro)
    totalTenants: { type: Number, default: 0 },
    pendingRequests: { type: Number, default: 0 },
    completedRequestsToday: { type: Number, default: 0 },

    // Mapeamentos para nomes antigos (se o frontend ainda usar)
    totalUsers: { type: Number, default: 0 }, // Deprecated: use totalUsuarios
    failedLoginsToday: { type: Number, default: 0 } // Deprecated: use loginsFalhosHoje
});

// Helper para classes de status de saúde
const healthStatusClass = (status) => {
    switch (status) {
        case 'up': return 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400';
        case 'partial': return 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-400';
        case 'down':
        case 'error':
        default: return 'bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400';
    }
};

const overallStatusText = computed(() => {
    switch (props.saudeSistema?.overall_status) {
        case 'up': return 'Operacional';
        case 'partial': return 'Parcialmente Operacional';
        case 'error':
        default: return 'Fora de Operação';
    }
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
                <div class="p-6 md:px-8 text-left mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Bem-vindo(a) de volta! 👋</h2>
                    <p class="text-base mt-2 text-gray-500 dark:text-gray-400">Visão geral do seu painel de controle central do CACSystem.</p>
                </div>

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

                <h3 class="px-6 md:px-8 text-xl font-bold text-gray-800 dark:text-gray-200 mb-5">Métricas do Sistema</h3>
                 <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6 px-6 md:px-8 mb-10">
                    <div class="stat-card">
                        <div class="flex items-center">
                            <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400"><Home class="h-5 w-5" /></div>
                            <p class="stat-title">Tenants Ativos</p>
                        </div>
                        <p class="stat-value">{{ props.tenantsAtivos }} / {{ props.totalTenants }}</p> </div>
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
                        <p class="stat-value">{{ props.totalUsuarios }}</p> </div>
                    <div class="stat-card"> <div class="flex items-center">
                            <div class="stat-icon bg-cyan-100 dark:bg-cyan-900/50 text-cyan-600 dark:text-cyan-400"><Send class="h-5 w-5" /></div>
                            <p class="stat-title">Campanhas Hoje</p>
                        </div>
                        <p class="stat-value">{{ props.campanhasEnviadasHoje }}</p>
                    </div>
                     <div class="stat-card"> <div class="flex items-center">
                            <div class="stat-icon bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-400"><BarChart2 class="h-5 w-5" /></div>
                            <p class="stat-title">Solic. Pendentes</p>
                        </div>
                        <p class="stat-value">{{ props.pendingRequests }}</p>
                    </div>
                     <div class="stat-card">
                        <div class="flex items-center">
                            <div class="stat-icon bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400"><XCircle class="h-5 w-5" /></div>
                            <p class="stat-title">Logins Falhos Hoje</p>
                        </div>
                        <p class="stat-value">{{ props.loginsFalhosHoje }}</p> </div>
                    </div>

                 <h3 class="px-6 md:px-8 text-xl font-bold text-gray-800 dark:text-gray-200 mb-5">Saúde do Sistema</h3>
                 <div class="px-6 md:px-8">
                     <div class="bg-white dark:bg-[#102C26]/60 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-green-400/10 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div :class="healthStatusClass(props.saudeSistema?.overall_status)" class="w-10 h-10 rounded-full flex items-center justify-center mr-4">
                                    <HeartPulse class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">Status Geral</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Verificação dos principais componentes do sistema.</p>
                                </div>
                            </div>
                            <span :class="healthStatusClass(props.saudeSistema?.overall_status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                {{ overallStatusText }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-200 dark:border-green-400/10 pt-4">
                            <div class="flex items-center space-x-3">
                                <Database :class="props.saudeSistema?.central_database === 'up' ? 'text-green-500' : 'text-red-500'" class="h-5 w-5 flex-shrink-0" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Banco Central:</span>
                                <span :class="healthStatusClass(props.saudeSistema?.central_database)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
                                    {{ props.saudeSistema?.central_database === 'up' ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Server :class="props.saudeSistema?.redis === 'up' ? 'text-green-500' : 'text-red-500'" class="h-5 w-5 flex-shrink-0" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Redis:</span>
                                <span :class="healthStatusClass(props.saudeSistema?.redis)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
                                    {{ props.saudeSistema?.redis === 'up' ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                             <div class="flex items-center space-x-3">
                                <Database :class="(props.saudeSistema?.active_tenants === props.saudeSistema?.total_tenants && props.saudeSistema?.total_tenants > 0) ? 'text-green-500' : (props.saudeSistema?.active_tenants > 0 ? 'text-yellow-500' : 'text-red-500')" class="h-5 w-5 flex-shrink-0" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Bancos Tenants:</span>
                                <span :class="healthStatusClass(props.saudeSistema?.overall_status)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
                                    {{ props.saudeSistema?.active_tenants }} / {{ props.saudeSistema?.total_tenants }} Online
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="props.saudeSistema?.tenants_databases && Object.keys(props.saudeSistema.tenants_databases).length > 0" class="bg-white dark:bg-[#102C26]/60 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-green-400/10">
                         <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Status dos Bancos de Dados dos Tenants</h4>
                         <ul class="divide-y divide-gray-200 dark:divide-green-400/10 max-h-60 overflow-y-auto">
                            <li v-for="(tenant, id) in props.saudeSistema.tenants_databases" :key="id" class="py-3 flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                     <Database :class="tenant.status === 'up' ? 'text-green-500' : 'text-red-500'" class="h-5 w-5 flex-shrink-0" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ tenant.subdomain }}</p>
                                    </div>
                                </div>
                                <span :class="healthStatusClass(tenant.status)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
                                    {{ tenant.status === 'up' ? 'Online' : 'Offline' }}
                                </span>
                            </li>
                         </ul>
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
    @apply text-sm font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap; /* Evita quebra de linha */
}
.stat-value {
    @apply text-3xl font-extrabold text-gray-900 dark:text-white mt-2 text-left;
}
</style>
