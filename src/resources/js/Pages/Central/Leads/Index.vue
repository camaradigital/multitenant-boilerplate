<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { Building2, PlusCircle, Pencil, Trash2, ServerCrash, Phone, Mail, Globe } from 'lucide-vue-next';

defineProps({
    leads: Array,
});

const form = useForm({
    nome: '',
    telefone: '',
    email: '',
    site: '',
    endereco: '',
});

const showModal = ref(false);
const editingLead = ref(null);

const openModal = (lead = null) => {
    if (lead) {
        editingLead.value = lead;
        form.nome = lead.nome;
        form.telefone = lead.telefone;
        form.email = lead.email;
        form.site = lead.site;
        form.endereco = lead.endereco;
    } else {
        editingLead.value = null;
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitLead = () => {
    const routeName = editingLead.value ? 'central.leads.update' : 'central.leads.store';
    const routeParams = editingLead.value ? { lead: editingLead.value.id } : {};

    form.post(route(routeName, routeParams), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

const deleteLead = (id) => {
    if (confirm('Tem certeza que deseja excluir este lead?')) {
        useForm({}).delete(route('central.leads.destroy', id), {
            preserveScroll: true,
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
                        <button @click="openModal()" class="btn btn-primary">
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
                                <th class="px-6 py-3">E-mail</th>
                                <th class="px-6 py-3">Telefone</th>
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
                                <td class="px-6 py-4">{{ lead.email }}</td>
                                <td class="px-6 py-4">{{ lead.telefone }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button @click="openModal(lead)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Lead"><Pencil class="h-4 w-4" /></button>
                                        <button @click="deleteLead(lead.id)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Lead"><Trash2 class="h-4 w-4" /></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal para Adicionar/Editar Lead -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
            <form @submit.prevent="submitLead" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg m-4" @click.stop>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ editingLead ? 'Editar Lead' : 'Adicionar Novo Lead' }}</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="nome" class="form-label">Nome</label>
                            <input id="nome" v-model="form.nome" type="text" class="form-input" required>
                            <div v-if="form.errors.nome" class="form-error">{{ form.errors.nome }}</div>
                        </div>
                        <div>
                            <label for="email" class="form-label">E-mail</label>
                            <input id="email" v-model="form.email" type="email" class="form-input" required>
                             <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                        </div>
                        <div>
                            <label for="telefone" class="form-label">Telefone</label>
                            <input id="telefone" v-model="form.telefone" type="text" class="form-input">
                             <div v-if="form.errors.telefone" class="form-error">{{ form.errors.telefone }}</div>
                        </div>
                        <div>
                            <label for="site" class="form-label">Site</label>
                            <input id="site" v-model="form.site" type="url" class="form-input" placeholder="https://exemplo.com">
                             <div v-if="form.errors.site" class="form-error">{{ form.errors.site }}</div>
                        </div>
                         <div>
                            <label for="endereco" class="form-label">Endereço</label>
                            <textarea id="endereco" v-model="form.endereco" class="form-input !h-24" rows="3"></textarea>
                             <div v-if="form.errors.endereco" class="form-error">{{ form.errors.endereco }}</div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-100 dark:bg-gray-700/50 flex justify-end gap-4 rounded-b-lg">
                    <button type="button" @click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ editingLead ? 'Salvar Alterações' : 'Adicionar Lead' }}</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Estilos reutilizados do seu design */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.btn { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; }
.btn-primary { @apply focus:ring-offset-white dark:focus:ring-offset-[#0A1E1C] bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600; }
.data-table { @apply w-full text-sm text-left text-gray-600 dark:text-gray-300; }
.table-header { @apply text-xs text-gray-500 uppercase bg-gray-50 dark:text-gray-400 dark:bg-green-500/5; }
.table-header th { @apply font-semibold px-6 py-3; }
.table-row { @apply border-b border-gray-200 dark:border-green-400/10 transition-colors duration-200; }
.table-row:hover { @apply bg-gray-50/50 dark:bg-green-400/5; }
.table-action-btn { @apply p-1.5 rounded-md transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.form-label { @apply block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-2; }
</style>
