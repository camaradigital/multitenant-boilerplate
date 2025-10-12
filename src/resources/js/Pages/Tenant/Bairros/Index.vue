<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { MapPin, Plus, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    bairros: Object,
});

const confirmingBairroDeletion = ref(false);
const bairroToDelete = ref(null);

const confirmDeletion = (bairro) => {
    bairroToDelete.value = bairro;
    confirmingBairroDeletion.value = true;
};

const deleteBairro = () => {
    router.delete(route('admin.bairros.destroy', bairroToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingBairroDeletion.value = false;
    bairroToDelete.value = null;
};
</script>

<template>
    <Head title="Bairros" />

    <TenantLayout title="Gerenciar Bairros">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Gerenciar Bairros</h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><MapPin :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Gerenciar Bairros</h2>
                        <p class="form-subtitle">Adicione, edite e remova os bairros do município.</p>
                    </div>
                    <Link :href="route('admin.bairros.create')" class="btn-primary flex-shrink-0">
                        <Plus class="w-4 h-4 mr-2"/>
                        Novo Bairro
                    </Link>
                </div>

                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nome</th>
                                    <th scope="col" class="px-6 py-3">Tipo Logradouro</th>
                                    <th scope="col" class="px-6 py-3">Criado em</th>
                                    <th scope="col" class="px-6 py-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="bairros.data.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center">Nenhum bairro encontrado.</td>
                                </tr>
                                <tr v-for="bairro in bairros.data" :key="bairro.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ bairro.nome }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                        {{ bairro.tipo_logradouro }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                        {{ new Date(bairro.created_at).toLocaleDateString('pt-BR') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link :href="route('admin.bairros.edit', bairro.id)" class="table-action-btn hover:text-blue-600 dark:hover:text-blue-400" title="Editar Bairro">
                                                <Pencil class="w-5 h-5" />
                                            </Link>
                                            <button @click="confirmDeletion(bairro)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Bairro">
                                                <Trash2 class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="pt-6" :links="bairros.links" v-if="bairros.data.length > 0"/>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="confirmingBairroDeletion"
            title="Excluir Bairro"
            :message="`Tem certeza de que deseja excluir o bairro '${bairroToDelete?.nome}'? Esta ação é irreversível.`"
            @close="closeModal"
            @confirm="deleteBairro"
        />
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
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
</style>
