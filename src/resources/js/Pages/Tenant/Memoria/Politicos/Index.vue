<script setup>
import { ref } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { Plus, Pencil, Trash2, Users, X, ShieldAlert, Image as ImageIcon } from 'lucide-vue-next';

// --- PROPS ---
const props = defineProps({
    politicos: Object,
});

// --- STATE ---
const isModalOpen = ref(false);
const isEditing = ref(false);
const photoPreview = ref(null);

// --- MODAL DE CONFIRMAÇÃO ---
const showConfirmationModal = ref(false);
const confirmationAction = ref(() => {});
const confirmationTitle = ref('');
const confirmationMessage = ref('');

// --- FORM ---
const form = useForm({
    id: null,
    _method: 'post',
    nome_completo: '',
    nome_politico: '',
    partido: '',
    biografia: '',
    foto: null,
});

// --- METHODS ---
const openModal = () => {
    isEditing.value = false;
    form.reset();
    form._method = 'post';
    photoPreview.value = null;
    isModalOpen.value = true;
};

const editPolitico = (politico) => {
    isEditing.value = true;
    form.id = politico.id;
    form._method = 'put';
    form.nome_completo = politico.nome_completo;
    form.nome_politico = politico.nome_politico;
    form.partido = politico.partido;
    form.biografia = politico.biografia;
    form.foto = null;
    photoPreview.value = politico.foto_url;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const updatePhotoPreview = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    form.foto = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submit = () => {
    const url = isEditing.value ? route('admin.politicos.update', form.id) : route('admin.politicos.store');
    form.post(url, {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

const openDeleteConfirmation = (politico) => {
    const action = () => router.delete(route('admin.politicos.destroy', politico.id), { preserveScroll: true });
    openConfirmationModal(action, 'Excluir Político', 'Tem certeza que deseja excluir este político? Esta ação não pode ser desfeita.');
};

const openConfirmationModal = (action, title, message) => {
    confirmationAction.value = action;
    confirmationTitle.value = title;
    confirmationMessage.value = message;
    showConfirmationModal.value = true;
};

const confirmAndExecute = () => {
    confirmationAction.value();
    showConfirmationModal.value = false;
};
</script>

<template>
    <Head title="Políticos" />
    <TenantLayout title="Políticos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Memória Legislativa - Políticos
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
                <div class="form-icon"><Users :size="32" class="icon-in-badge" /></div>
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Cadastro de Políticos</h2>
                        <p class="form-subtitle">Gerencie o registro de todos os políticos da casa legislativa.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openModal" class="btn-primary"><Plus class="h-4 w-4 mr-2" />Novo Político</button>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <div v-if="politicos.data.length > 0" class="space-y-4">
                        <div v-for="politico in politicos.data" :key="politico.id" class="role-card">
                            <div class="flex items-center gap-4">
                                <img v-if="politico.foto_url" :src="politico.foto_url" :alt="politico.nome_politico" class="h-12 w-12 rounded-full object-cover">
                                <div v-else class="h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <Users class="h-6 w-6 text-gray-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="role-name">{{ politico.nome_politico }}</p>
                                    <p class="form-subtitle">{{ politico.partido || 'Sem partido' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="editPolitico(politico)" class="table-action-btn hover:text-amber-600" title="Editar"><Pencil class="w-5 h-5" /></button>
                                <button @click="openDeleteConfirmation(politico)" class="table-action-btn hover:text-red-600" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10"><p class="text-gray-500">Nenhum político registrado.</p></div>
                </div>
                <div v-if="politicos.data.length > 0" class="px-6 pb-4">
                    <Pagination :links="politicos.links" />
                </div>
            </div>
        </div>

        <!-- MODAL DE CRIAÇÃO/EDIÇÃO -->
        <TransitionRoot appear :show="isModalOpen" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0"><div class="fixed inset-0 bg-black/50 backdrop-blur-sm" /></TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel">
                                <form @submit.prevent="submit">
                                    <div class="p-6">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                            <span>{{ isEditing ? 'Editar Político' : 'Novo Político' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>
                                        <div class="mt-6 space-y-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="nome_completo" class="form-label">Nome Completo</label>
                                                    <input type="text" v-model="form.nome_completo" id="nome_completo" class="form-input" required>
                                                    <div v-if="form.errors.nome_completo" class="form-error">{{ form.errors.nome_completo }}</div>
                                                </div>
                                                <div>
                                                    <label for="nome_politico" class="form-label">Nome Político</label>
                                                    <input type="text" v-model="form.nome_politico" id="nome_politico" class="form-input" required>
                                                    <div v-if="form.errors.nome_politico" class="form-error">{{ form.errors.nome_politico }}</div>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="partido" class="form-label">Partido</label>
                                                <input type="text" v-model="form.partido" id="partido" class="form-input">
                                                <div v-if="form.errors.partido" class="form-error">{{ form.errors.partido }}</div>
                                            </div>
                                            <div>
                                                <label for="biografia" class="form-label">Biografia</label>
                                                <textarea v-model="form.biografia" id="biografia" rows="5" class="form-input"></textarea>
                                                <div v-if="form.errors.biografia" class="form-error">{{ form.errors.biografia }}</div>
                                            </div>
                                            <div>
                                                <label for="foto" class="form-label">Foto de Perfil</label>
                                                <div class="flex items-center gap-4">
                                                    <div class="shrink-0">
                                                        <img v-if="photoPreview" :src="photoPreview" class="h-20 w-20 rounded-lg object-cover" alt="Preview da foto">
                                                        <div v-else class="h-20 w-20 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                            <ImageIcon class="h-8 w-8 text-gray-400" />
                                                        </div>
                                                    </div>
                                                    <div class="w-full">
                                                        <input type="file" @input="updatePhotoPreview" class="form-file-input" id="foto" accept="image/*">
                                                        <div v-if="form.errors.foto" class="form-error">{{ form.errors.foto }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">{{ isEditing ? 'Atualizar' : 'Salvar' }}</button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO -->
        <TransitionRoot appear :show="showConfirmationModal" as="template">
            <Dialog as="div" @close="showConfirmationModal = false" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0"><div class="fixed inset-0 bg-black/60 backdrop-blur-sm" /></TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex items-center gap-3">
                                    <ShieldAlert class="w-6 h-6 text-red-500" />
                                    {{ confirmationTitle }}
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ confirmationMessage }}</p>
                                </div>
                                <div class="mt-6 flex justify-end space-x-3">
                                    <button type="button" @click="showConfirmationModal = false" class="btn-secondary">Cancelar</button>
                                    <button type="button" @click="confirmAndExecute" class="btn-danger">Confirmar Exclusão</button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

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
.role-card { @apply bg-white dark:bg-white/5 p-4 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.btn-danger { @apply inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest bg-red-600 hover:bg-red-700 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 text-left; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1 text-left; }
.form-file-input { @apply block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400; }
</style>
