<script setup>
import { ref } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { Plus, Pencil, Trash2, Users, X, ShieldAlert, Image as ImageIcon, UploadCloud, UserX, LayoutGrid, List } from 'lucide-vue-next';

// --- PROPS ---
const props = defineProps({
    politicos: Object,
});

// --- STATE ---
const isModalOpen = ref(false);
const isEditing = ref(false);
const photoPreview = ref(null);
const isDragging = ref(false);
const viewMode = ref('card');

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
    email: '', // Campo de e-mail adicionado
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
    form.email = politico.email; // Campo de e-mail adicionado
    form.biografia = politico.biografia;
    form.foto = null;
    photoPreview.value = politico.foto_url;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleFileChange = (file) => {
    if (!file || !file.type.startsWith('image/')) return;
    form.foto = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const onFileSelect = (event) => {
    handleFileChange(event.target.files[0]);
};

const onDrop = (event) => {
    isDragging.value = false;
    handleFileChange(event.dataTransfer.files[0]);
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
    openConfirmationModal(action, 'Excluir Político', 'Tem certeza que deseja excluir este político? Todos os mandatos associados a ele também serão removidos. Esta ação é permanente.');
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="content-container">
                    <div class="form-icon"><Users :size="32" class="icon-in-badge" /></div>
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Cadastro de Políticos</h2>
                            <p class="form-subtitle">Gerencie o registro de todos os políticos da casa legislativa.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                            <div v-if="politicos.data.length > 0" class="flex rounded-lg p-1 bg-gray-100 dark:bg-gray-700 w-full sm:w-auto">
                                <button
                                    @click="viewMode = 'card'"
                                    :class="viewMode === 'card' ? 'btn-toggle-active' : 'btn-toggle-inactive'"
                                    title="Visualização em Card"
                                >
                                    <LayoutGrid class="w-5 h-5" />
                                </button>
                                <button
                                    @click="viewMode = 'list'"
                                    :class="viewMode === 'list' ? 'btn-toggle-active' : 'btn-toggle-inactive'"
                                    title="Visualização em Lista"
                                >
                                    <List class="w-5 h-5" />
                                </button>
                            </div>
                            <button @click="openModal" class="btn-primary w-full sm:w-auto"><Plus class="h-4 w-4 mr-2" />Novo Político</button>
                        </div>
                    </div>
                    <div class="p-4 md:p-6">
                        <div v-if="politicos.data.length > 0">
                            <div v-if="viewMode === 'card'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                <div v-for="politico in politicos.data" :key="politico.id" class="politician-card group">
                                    <div class="relative">
                                        <img v-if="politico.foto_url" :src="politico.foto_url" :alt="politico.nome_politico" class="card-image">
                                        <div v-else class="card-image-placeholder">
                                            <Users class="h-12 w-12 text-gray-400" />
                                        </div>
                                        <div class="card-overlay">
                                            <button @click.stop.prevent="editPolitico(politico)" class="card-action-btn hover:bg-emerald-600" title="Editar"><Pencil class="w-5 h-5" /></button>
                                            <button @click.stop.prevent="openDeleteConfirmation(politico)" class="card-action-btn hover:bg-red-600" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                                        </div>
                                    </div>
                                    <div class="p-4 text-center">
                                        <p class="card-name">{{ politico.nome_politico }}</p>
                                        <p class="card-party">{{ politico.partido || 'Sem partido' }}</p>
                                        <p v-if="politico.email" class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ politico.email }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-for="politico in politicos.data" :key="politico.id" class="list-item group">
                                    <div class="flex items-center space-x-4 flex-1 min-w-0">
                                        <img v-if="politico.foto_url" :src="politico.foto_url" :alt="politico.nome_politico" class="list-image">
                                        <div v-else class="list-image-placeholder">
                                            <Users class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="list-name">{{ politico.nome_politico }}</p>
                                            <p class="list-full-name">{{ politico.nome_completo }}</p>
                                            <p v-if="politico.email" class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ politico.email }}</p>
                                        </div>
                                        <span class="list-party">{{ politico.partido || 'Sem partido' }}</span>
                                    </div>
                                    <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click.stop.prevent="editPolitico(politico)" class="table-action-btn" title="Editar"><Pencil class="w-5 h-5" /></button>
                                        <button @click.stop.prevent="openDeleteConfirmation(politico)" class="table-action-btn text-red-500 hover:bg-red-500/10" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-16 px-6">
                            <UserX class="mx-auto h-16 w-16 text-gray-400" />
                            <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-200">Nenhum Político Cadastrado</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                Clique no botão **Novo Político** acima para adicionar o primeiro político e começar a montar sua base.
                            </p>
                            </div>
                    </div>
                    <div v-if="politicos.data.length > 0" class="px-6 pb-4 border-t-dynamic pt-4">
                        <Pagination :links="politicos.links" />
                    </div>
                </div>
            </div>
        </div>

        <TransitionRoot appear :show="isModalOpen" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0"><div class="fixed inset-0 bg-black/50 backdrop-blur-sm" /></TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel">
                                <form @submit.prevent="submit" class="flex flex-col h-full">
                                    <div class="p-6 border-b-dynamic">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                            <span>{{ isEditing ? 'Editar Político' : 'Novo Político' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>
                                    </div>
                                    <div class="p-6 space-y-6 overflow-y-auto flex-1">
                                            <div>
                                                <label class="form-label">Foto de Perfil</label>
                                                <div class="flex items-center gap-6">
                                                    <img v-if="photoPreview" :src="photoPreview" class="h-24 w-24 rounded-full object-cover shadow-md" alt="Preview da foto">
                                                    <div v-else class="h-24 w-24 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center shadow-md">
                                                        <ImageIcon class="h-10 w-10 text-gray-400" />
                                                    </div>
                                                    <label for="foto" class="file-drop-zone" :class="{ 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20': isDragging }" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="onDrop">
                                                        <input type="file" @input="onFileSelect" id="foto" class="sr-only" accept="image/*">
                                                        <div class="text-center">
                                                            <UploadCloud class="mx-auto h-8 w-8 text-gray-400" />
                                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><span class="font-semibold text-emerald-600 dark:text-emerald-400">Clique</span> ou arraste</p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-500">PNG, JPG até 5MB</p>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div v-if="form.errors.foto" class="form-error mt-2">{{ form.errors.foto }}</div>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <label for="nome_completo" class="form-label">Nome Completo</label>
                                                    <input type="text" v-model="form.nome_completo" id="nome_completo" class="form-input" required>
                                                    <div v-if="form.errors.nome_completo" class="form-error">{{ form.errors.nome_completo }}</div>
                                                </div>
                                                <div>
                                                    <label for="nome_politico" class="form-label">Nome Político (de urna)</label>
                                                    <input type="text" v-model="form.nome_politico" id="nome_politico" class="form-input" required>
                                                    <div v-if="form.errors.nome_politico" class="form-error">{{ form.errors.nome_politico }}</div>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <label for="partido" class="form-label">Partido</label>
                                                    <input type="text" v-model="form.partido" id="partido" class="form-input" placeholder="Ex: Partido Democrático">
                                                    <div v-if="form.errors.partido" class="form-error">{{ form.errors.partido }}</div>
                                                </div>
                                                <div>
                                                    <label for="email" class="form-label">E-mail</label>
                                                    <input type="email" v-model="form.email" id="email" class="form-input" placeholder="email@exemplo.com">
                                                    <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="biografia" class="form-label">Biografia (Opcional)</label>
                                                <textarea v-model="form.biografia" id="biografia" rows="5" class="form-input"></textarea>
                                                <div v-if="form.errors.biografia" class="form-error">{{ form.errors.biografia }}</div>
                                            </div>
                                    </div>
                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl border-t-dynamic">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">{{ isEditing ? 'Salvar Alterações' : 'Cadastrar Político' }}</button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <TransitionRoot appear :show="showConfirmationModal" as="template">
            <Dialog as="div" @close="showConfirmationModal = false" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0"><div class="fixed inset-0 bg-black/60 backdrop-blur-sm" /></TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left align-middle shadow-xl transition-all">
                                <div class="p-6 text-center">
                                    <ShieldAlert class="w-16 h-16 text-red-500 mx-auto mb-4" />
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ confirmationTitle }}</h3>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ confirmationMessage }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                                    <button @click="showConfirmationModal = false" class="btn-secondary">Cancelar</button>
                                    <button @click="confirmAndExecute" class="btn-danger">Confirmar Exclusão</button>
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
.content-container { @apply relative w-full pt-16 rounded-2xl shadow-lg; @apply bg-white border border-gray-200/80; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white dark:text-[#0A1E1C]; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

/* Botões de Alternância de Visualização (NOVOS ESTILOS) */
.btn-toggle-active { @apply p-2 rounded-lg text-white bg-emerald-500 shadow-md transition-colors; }
.btn-toggle-inactive { @apply p-2 rounded-lg text-gray-500 hover:text-gray-800 dark:hover:text-white transition-colors; }

/* Card de Político */
.politician-card { @apply bg-white dark:bg-gray-800/50 rounded-xl overflow-hidden shadow-md border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-xl hover:-translate-y-1; }
.card-image { @apply w-full h-56 object-cover object-center transition-transform duration-300 group-hover:scale-105; }
.card-image-placeholder { @apply w-full h-56 flex items-center justify-center bg-gray-100 dark:bg-gray-700/50; }
.card-overlay { @apply absolute inset-0 bg-black/60 flex items-center justify-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300; }
.card-action-btn { @apply p-3 rounded-full bg-white/20 text-white backdrop-blur-sm transition-colors duration-300; }
.card-name { @apply text-base font-bold text-gray-800 dark:text-emerald-300 truncate; }
.card-party { @apply text-sm text-gray-500 dark:text-gray-400; }

/* Lista de Político (NOVOS ESTILOS) */
.list-item { @apply flex items-center justify-between p-4 rounded-xl bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 transition-all duration-200; }
.list-image { @apply h-10 w-10 rounded-full object-cover shadow-sm flex-shrink-0; }
.list-image-placeholder { @apply h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center flex-shrink-0; }
.list-name { @apply text-base font-semibold text-gray-900 dark:text-white truncate; }
.list-full-name { @apply text-sm text-gray-500 dark:text-gray-400 truncate; }
.list-party { @apply text-sm font-medium text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-full bg-emerald-50/50 dark:bg-gray-700/50 flex-shrink-0; }

/* Modal e Formulário */
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; max-height: calc(100vh - 4rem); display: flex; flex-direction: column; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-left; }
.form-input { @apply block w-full text-sm rounded-lg transition-all h-11 py-3 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1 text-left; }
.file-drop-zone { @apply relative flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-800/50 dark:border-gray-600 dark:hover:border-gray-500 transition-colors; }

/* Botões */
.btn-primary { @apply flex items-center justify-center px-5 py-2.5 rounded-lg font-semibold text-sm uppercase tracking-wider transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-900 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500; @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50 disabled:cursor-not-allowed; }
.btn-secondary { @apply inline-flex items-center justify-center w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-sm uppercase tracking-wider shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors; }
.btn-danger { @apply inline-flex items-center justify-center w-full px-4 py-2.5 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider bg-red-600 hover:bg-red-700 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
</style>
