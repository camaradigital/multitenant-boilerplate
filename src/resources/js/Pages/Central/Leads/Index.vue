<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Building2, PlusCircle, Pencil, Trash2, ServerCrash, Phone, Mail } from 'lucide-vue-next';

// Componentes refatorados
import LeadModal from '@/Components/LeadModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

// Sugestão: Use um composable para toasts, como no exemplo anterior.
// import { useAppToast } from '@/Composables/useToast.js';
// const { showSuccess } = useAppToast();

defineProps({
    leads: Array,
});

const showLeadModal = ref(false);
const showConfirmModal = ref(false);
const editingLead = ref(null);
const leadToDelete = ref(null);

const openCreateModal = () => {
    editingLead.value = null;
    showLeadModal.value = true;
};

const openEditModal = (lead) => {
    editingLead.value = lead;
    showLeadModal.value = true;
};

const closeModal = () => {
    showLeadModal.value = false;
    showConfirmModal.value = false;
};

// Abre o modal de confirmação
const promptDelete = (lead) => {
    leadToDelete.value = lead;
    showConfirmModal.value = true;
};

// Executa a exclusão após confirmação
const confirmDelete = () => {
    if (leadToDelete.value) {
        router.delete(route('central.leads.destroy', leadToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                // showSuccess('Lead excluído com sucesso!');
                closeModal();
            },
        });
    }
};
</script>

<template>
    <Head title="Prospecção de Clientes" />

    <AppLayout title="Prospecção de Clientes">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Leads (Possíveis Clientes)
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Building2 :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Contatos para Prospecção</h2>
                        <p class="form-subtitle">Gerencie sua lista de potenciais clientes.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openCreateModal()" class="btn btn-primary">
                            <PlusCircle class="h-4 w-4 mr-2" />
                            Novo Lead
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead class="table-header">
                            <tr>
                                <th class="px-6 py-3">Nome</th>
                                <th class="px-6 py-3">Cidade</th>
                                <th class="px-6 py-3">Contato</th>
                                <th class="px-6 py-3 text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!leads || leads.length === 0">
                                <td colspan="4" class="p-8 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <ServerCrash class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-600" />
                                        <p class="font-semibold text-gray-700 dark:text-gray-300">Nenhum lead encontrado</p>
                                        <p class="text-sm">Cadastre um novo lead para começar.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else v-for="lead in leads" :key="lead.id" class="table-row">
                                <th scope="row" class="px-6 py-4 font-semibold whitespace-nowrap text-gray-800 dark:text-white">{{ lead.nome }}</th>
                                <td class="px-6 py-4">{{ lead.cidade || 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1.5">
                                        <a v-if="lead.email" :href="`mailto:${lead.email}`" class="flex items-center gap-2 text-xs hover:text-emerald-500 transition-colors">
                                            <Mail class="h-3.5 w-3.5" /> {{ lead.email }}
                                        </a>
                                        <a v-if="lead.telefone" :href="`tel:${lead.telefone}`" class="flex items-center gap-2 text-xs hover:text-emerald-500 transition-colors">
                                            <Phone class="h-3.5 w-3.5" /> {{ lead.telefone }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button @click="openEditModal(lead)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Lead"><Pencil class="h-4 w-4" /></button>
                                        <button @click="promptDelete(lead)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Lead"><Trash2 class="h-4 w-4" /></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <LeadModal :show="showLeadModal" :lead="editingLead" @close="closeModal" />

        <ConfirmationModal
            :show="showConfirmModal"
            title="Excluir Lead"
            :message="`Tem certeza que deseja excluir permanentemente o lead '${leadToDelete?.nome}'? Esta ação não pode ser desfeita.`"
            @confirm="confirmDelete"
            @close="closeModal"
        />
    </AppLayout>
</template>

<style scoped>
/* Estilos com Contraste Corrigido */

/* O container principal agora tem um fundo sólido no modo escuro */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#0D241F] dark:border dark:border-emerald-900/80; }

/* Borda de divisão com um pouco mais de presença */
.border-b-dynamic { @apply border-b border-gray-200 dark:border-emerald-800/60; }

/* Ícone de destaque, sem alterações */
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }

/* Títulos, sem alterações */
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-300; } /* Levemente mais claro */

/* Botões, sem alterações */
.btn { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; }
.btn-primary { @apply focus:ring-offset-white dark:focus:ring-offset-[#0A1E1C] bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600; }

/* Tabela com estilos de alto contraste */
.data-table { @apply w-full text-sm text-left text-gray-700 dark:text-gray-300; }

/* Cabeçalho da tabela com fundo mais opaco e texto mais claro */
.table-header { @apply text-xs uppercase; @apply bg-gray-50 text-gray-600; @apply dark:bg-emerald-900/40 dark:text-emerald-300; }
.table-header th { @apply font-semibold px-6 py-3 tracking-wider; }

/* Linhas da tabela com borda e hover mais visíveis */
.table-row { @apply border-b border-gray-200 dark:border-emerald-800/50 transition-colors duration-200; }
.table-row:hover { @apply bg-gray-50/50 dark:bg-white/5; }

/* Botões de ação da tabela */
.table-action-btn { @apply p-1.5 rounded-md transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }

/* Estilos do formulário do modal, sem alterações */
.form-label { @apply block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-2; }
</style>
