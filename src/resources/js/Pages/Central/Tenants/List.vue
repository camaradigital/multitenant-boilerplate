<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
// ADICIONADO o ícone 'Eye'
import { Building2, PlusCircle, Pencil, Trash2, ServerCrash, Eye } from 'lucide-vue-next';

// Usando a prop 'tenants: Array' que funciona para você.
defineProps({
    tenants: Array,
});

// Ações atualizadas para usar o router do Inertia
const editTenant = (tenant) => {
    router.get(route('central.tenants.edit', tenant.id));
};

const deleteTenant = (tenant) => {
    if (confirm(`Tem certeza que deseja excluir o tenant "${tenant.name}"? Esta ação é irreversível!`)) {
        router.delete(route('central.tenants.destroy', tenant.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Gerenciar Câmaras" />

    <AppLayout title="Gestão de Câmaras">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Listagem de Câmaras
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">

                <div class="form-icon">
                    <Building2 :size="32" class="icon-in-badge" />
                </div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Câmaras Cadastradas</h2>
                        <p class="form-subtitle">Gerencie os tenants e suas configurações.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <Link :href="route('central.tenants.create')" class="btn btn-primary">
                            <PlusCircle class="h-4 w-4 mr-2" />
                            Nova Câmara
                        </Link>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead class="table-header">
                            <tr>
                                <th class="px-6 py-3">Nome da Câmara</th>
                                <th class="px-6 py-3">Subdomínio</th>
                                <th class="px-6 py-3">E-mail Admin</th>
                                <th class="px-6 py-3">Banco de Dados</th>
                                <th class="px-6 py-3 text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Lógica de exibição para Array simples -->
                            <tr v-if="!tenants || tenants.length === 0">
                                <td colspan="5" class="p-8 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <ServerCrash class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-600" />
                                        <p class="font-semibold text-gray-700 dark:text-gray-300">Nenhuma câmara encontrada</p>
                                        <p class="text-sm">Cadastre uma nova câmara para começar.</p>
                                    </div>
                                </td>
                            </tr>
                            <!-- Loop direto no array 'tenants' -->
                            <tr v-else v-for="tenant in tenants" :key="tenant.id" class="table-row">
                                <th scope="row" class="px-6 py-4 font-semibold whitespace-nowrap text-gray-800 dark:text-white">
                                    {{ tenant.name }}
                                </th>
                                <td class="px-6 py-4">
                                    <a :href="`http://${tenant.subdomain}.localhost`" target="_blank" class="link-hover">
                                        {{ tenant.subdomain }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">{{ tenant.admin_email }}</td>
                                <td class="px-6 py-4">{{ tenant.database_name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- BOTÃO DE VISUALIZAR ADICIONADO -->
                                        <Link :href="route('central.tenants.show', tenant.id)" class="table-action-btn hover:text-blue-600 dark:hover:text-blue-400" title="Visualizar Câmara">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <button @click="editTenant(tenant)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Câmara">
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button @click="deleteTenant(tenant)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Câmara">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.content-container {
    @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300;
    @apply bg-white border border-gray-200;
    @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm;
}
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }

.form-icon {
    @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg;
    @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30;
}
.icon-in-badge { @apply text-white; }

.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

.btn-primary {
    @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2;
    @apply focus:ring-offset-white dark:focus:ring-offset-[#0A1E1C];
    @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500;
    @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400;
}

.data-table { @apply w-full text-sm text-left text-gray-600 dark:text-gray-300; }
.table-header { @apply text-xs text-gray-500 uppercase bg-gray-50 dark:text-gray-400 dark:bg-green-500/5; }
.table-header th { @apply font-semibold px-6 py-3; }
.table-row { @apply border-b border-gray-200 dark:border-green-400/10 transition-colors duration-200; }
.table-row:hover { @apply bg-gray-50/50 dark:bg-green-400/5; }
.table-row td, .table-row th { @apply px-6 py-4; }
.link-hover {
    @apply hover:underline transition-colors;
    @apply hover:text-emerald-600 dark:hover:text-green-300;
}
.table-action-btn {
    @apply p-1.5 rounded-md transition-colors;
    @apply text-gray-500 hover:bg-gray-200;
    @apply dark:text-gray-400 dark:hover:bg-white/10;
}
</style>
