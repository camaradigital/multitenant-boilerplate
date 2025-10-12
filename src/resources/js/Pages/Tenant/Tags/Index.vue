<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Tags, Plus, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    tags: Object,
});

const confirmingTagDeletion = ref(false);
const tagToDelete = ref(null);

const confirmDeletion = (tag) => {
    tagToDelete.value = tag;
    confirmingTagDeletion.value = true;
};

const deleteTag = () => {
    router.delete(route('admin.tags.destroy', tagToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingTagDeletion.value = false;
    tagToDelete.value = null;
};
</script>

<template>
    <Head title="Tags" />

    <TenantLayout title="Gerenciar Tags">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Gerenciar Tags</h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Tags :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Gerenciar Tags</h2>
                        <p class="form-subtitle">Crie, edite e remova tags para categorizar cidadãos.</p>
                    </div>
                    <Link :href="route('admin.tags.create')" class="btn-primary flex-shrink-0">
                        <Plus class="w-4 h-4 mr-2"/>
                        Nova Tag
                    </Link>
                </div>

                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nome da Tag</th>
                                    <th scope="col" class="px-6 py-3">Cor</th>
                                    <th scope="col" class="px-6 py-3">Criado em</th>
                                    <th scope="col" class="px-6 py-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="tags.data.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center">Nenhuma tag encontrada.</td>
                                </tr>
                                <tr v-for="tag in tags.data" :key="tag.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ tag.nome_tag }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <span class="inline-block w-6 h-6 rounded-full border border-gray-300 dark:border-gray-600" :style="{ backgroundColor: tag.cor }"></span>
                                            <span class="ml-3 font-mono text-gray-600 dark:text-gray-400">{{ tag.cor }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                        {{ new Date(tag.created_at).toLocaleDateString('pt-BR') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link :href="route('admin.tags.edit', tag.id)" class="table-action-btn hover:text-blue-600 dark:hover:text-blue-400" title="Editar Tag">
                                                <Pencil class="w-5 h-5" />
                                            </Link>
                                            <button @click="confirmDeletion(tag)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Tag">
                                                <Trash2 class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="pt-6" :links="tags.links" v-if="tags.data.length > 0"/>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="confirmingTagDeletion"
            title="Excluir Tag"
            :message="`Tem certeza de que deseja excluir a tag '${tagToDelete?.nome_tag}'? Esta ação é irreversível.`"
            @close="closeModal"
            @confirm="deleteTag"
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
