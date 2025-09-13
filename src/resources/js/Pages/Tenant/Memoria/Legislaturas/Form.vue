<script setup>
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Trash2, UserPlus, ArrowLeft, Edit, ShieldAlert, Image as ImageIcon } from 'lucide-vue-next';

// --- PROPS ---
const props = defineProps({
    legislatura: Object, // Será nulo na criação
    politicos: Array,    // Lista de todos os políticos para o dropdown
});

// --- STATE ---
const isEditing = !!props.legislatura;
const photoPreview = ref(props.legislatura?.foto_principal_url || null);

// --- FORMS ---
const formLegislatura = useForm({
    _method: isEditing ? 'put' : 'post',
    titulo: props.legislatura?.titulo || '',
    data_inicio: props.legislatura?.data_inicio?.substring(0, 10) || '',
    data_fim: props.legislatura?.data_fim?.substring(0, 10) || '',
    texto_destaque: props.legislatura?.texto_destaque || '',
    foto_principal: null,
});

const formMandato = useForm({
    politico_id: '',
    cargo: '',
});

// --- MODAL STATE ---
const showConfirmationModal = ref(false);
const confirmationAction = ref(() => {});
const confirmationTitle = ref('');
const confirmationMessage = ref('');

const showEditMandatoModal = ref(false);
const formEditMandato = useForm({
    id: null,
    cargo: '',
});

// --- COMPUTED ---
// Filtra a lista de políticos para mostrar apenas aqueles que ainda não foram adicionados
const availablePoliticos = computed(() => {
    if (!isEditing) return props.politicos;
    const addedPoliticoIds = props.legislatura.mandatos.map(m => m.politico_id);
    return props.politicos.filter(p => !addedPoliticoIds.includes(p.id));
});

// --- METHODS ---
const updatePhotoPreview = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    formLegislatura.foto_principal = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submitLegislatura = () => {
    const url = isEditing ? route('admin.legislaturas.update', props.legislatura.id) : route('admin.legislaturas.store');
    formLegislatura.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            // Limpa o campo de arquivo do formulário após o sucesso para evitar reenvio
            if (!isEditing) formLegislatura.reset();
            formLegislatura.foto_principal = null;
        }
    });
};

const addMembro = () => {
    formMandato.post(route('admin.mandatos.store', props.legislatura.id), {
        onSuccess: () => formMandato.reset(),
        preserveScroll: true,
    });
};

const openEditModal = (mandato) => {
    formEditMandato.id = mandato.id;
    formEditMandato.cargo = mandato.cargo;
    showEditMandatoModal.value = true;
};

const updateMembro = () => {
    formEditMandato.put(route('admin.mandatos.update', formEditMandato.id), {
        onSuccess: () => {
            showEditMandatoModal.value = false;
            formEditMandato.reset();
        },
        preserveScroll: true,
    });
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

const removeMembro = (mandatoId) => {
    const action = () => router.delete(route('admin.mandatos.destroy', mandatoId), { preserveScroll: true });
    openConfirmationModal(action, 'Remover Membro', 'Tem certeza que deseja remover este membro da legislatura?');
};

const deleteLegislatura = () => {
    const action = () => router.delete(route('admin.legislaturas.destroy', props.legislatura.id));
    openConfirmationModal(action, 'Excluir Legislatura', 'Esta ação é permanente e removerá todos os mandatos associados. Deseja continuar?');
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Legislatura' : 'Nova Legislatura'" />
    <TenantLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('admin.legislaturas.index')" class="table-action-btn mr-4"><ArrowLeft class="w-5 h-5" /></Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ isEditing ? 'Editar Legislatura' : 'Nova Legislatura' }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Formulário Principal da Legislatura -->
                <form @submit.prevent="submitLegislatura" class="content-container p-8">
                    <h3 class="header-title mb-6">Dados da Legislatura</h3>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-3">
                                <label for="titulo" class="form-label">Título</label>
                                <input type="text" v-model="formLegislatura.titulo" id="titulo" class="form-input" required>
                                <div v-if="formLegislatura.errors.titulo" class="form-error">{{ formLegislatura.errors.titulo }}</div>
                            </div>
                            <div>
                                <label for="data_inicio" class="form-label">Data de Início</label>
                                <input type="date" v-model="formLegislatura.data_inicio" id="data_inicio" class="form-input" required>
                                <div v-if="formLegislatura.errors.data_inicio" class="form-error">{{ formLegislatura.errors.data_inicio }}</div>
                            </div>
                            <div>
                                <label for="data_fim" class="form-label">Data de Fim</label>
                                <input type="date" v-model="formLegislatura.data_fim" id="data_fim" class="form-input" required>
                                <div v-if="formLegislatura.errors.data_fim" class="form-error">{{ formLegislatura.errors.data_fim }}</div>
                            </div>
                        </div>
                        <div>
                            <label for="texto_destaque" class="form-label">Texto de Destaque</label>
                            <textarea v-model="formLegislatura.texto_destaque" id="texto_destaque" rows="5" class="form-input"></textarea>
                            <div v-if="formLegislatura.errors.texto_destaque" class="form-error">{{ formLegislatura.errors.texto_destaque }}</div>
                        </div>
                        <div>
                            <label for="foto_principal" class="form-label">Foto Principal (da Posse)</label>
                            <div class="flex items-center gap-4">
                                <div class="shrink-0">
                                    <img v-if="photoPreview" :src="photoPreview" class="h-20 w-20 rounded-lg object-cover" alt="Preview da foto">
                                    <div v-else class="h-20 w-20 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <ImageIcon class="h-8 w-8 text-gray-400" />
                                    </div>
                                </div>
                                <div class="w-full">
                                    <input type="file" @input="updatePhotoPreview" class="form-file-input" id="foto_principal" accept="image/*">
                                    <div v-if="formLegislatura.errors.foto_principal" class="form-error">{{ formLegislatura.errors.foto_principal }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <button type="submit" :disabled="formLegislatura.processing" class="btn-primary">Salvar Dados da Legislatura</button>
                    </div>
                </form>

                <!-- Seção de Composição (Apenas na Edição) -->
                <div v-if="isEditing" class="content-container p-8">
                    <h3 class="header-title mb-6">Composição da Câmara</h3>
                    <!-- Formulário para Adicionar Membro -->
                    <form @submit.prevent="addMembro" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end p-4 border dark:border-gray-700 rounded-xl mb-6">
                        <div>
                            <label for="politico_id" class="form-label">Político</label>
                            <select v-model="formMandato.politico_id" id="politico_id" class="form-input" required>
                                <option value="" disabled>Selecione...</option>
                                <option v-for="p in availablePoliticos" :key="p.id" :value="p.id">{{ p.nome_politico }}</option>
                            </select>
                            <div v-if="formMandato.errors.politico_id" class="form-error">{{ formMandato.errors.politico_id }}</div>
                        </div>
                        <div>
                            <label for="cargo" class="form-label">Cargo</label>
                            <input type="text" v-model="formMandato.cargo" id="cargo" class="form-input" placeholder="Ex: Presidente, Vereador" required>
                            <div v-if="formMandato.errors.cargo" class="form-error">{{ formMandato.errors.cargo }}</div>
                        </div>
                        <button type="submit" :disabled="formMandato.processing" class="btn-primary h-12"><UserPlus class="w-4 h-4 mr-2"/> Adicionar Membro</button>
                    </form>

                    <!-- Lista de Membros Adicionados -->
                    <div class="space-y-2">
                        <div v-for="mandato in legislatura.mandatos" :key="mandato.id" class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ mandato.politico.nome_politico }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ mandato.cargo }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal(mandato)" class="table-action-btn hover:text-emerald-500"><Edit class="w-5 h-5"/></button>
                                <button @click="removeMembro(mandato.id)" class="table-action-btn hover:text-red-500"><Trash2 class="w-5 h-5"/></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zona de Perigo -->
                <div v-if="isEditing" class="content-container p-8 border-red-500/50 border">
                    <h3 class="header-title text-red-600 dark:text-red-400 mb-4">Zona de Perigo</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">A exclusão de uma legislatura é uma ação permanente e removerá todos os mandatos associados. Esta ação não pode ser desfeita.</p>
                    <button @click="deleteLegislatura" class="btn-danger">Excluir Legislatura</button>
                </div>
            </div>
        </div>

        <!-- MODAL DE CONFIRMAÇÃO -->
        <div v-if="showConfirmationModal" class="fixed inset-0 bg-black/60 z-50 flex justify-center items-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
                <div class="p-8 text-center">
                    <ShieldAlert class="w-16 h-16 text-red-500 mx-auto mb-4" />
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ confirmationTitle }}</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ confirmationMessage }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showConfirmationModal = false" class="btn-secondary">Cancelar</button>
                    <button @click="confirmAndExecute" class="btn-danger">Confirmar</button>
                </div>
            </div>
        </div>

        <!-- MODAL DE EDIÇÃO DE MANDATO -->
        <div v-if="showEditMandatoModal" class="fixed inset-0 bg-black/60 z-50 flex justify-center items-center p-4">
            <form @submit.prevent="updateMembro" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Editar Cargo</h3>
                    <div>
                        <label for="edit-cargo" class="form-label">Novo Cargo</label>
                        <input type="text" v-model="formEditMandato.cargo" id="edit-cargo" class="form-input" required>
                        <div v-if="formEditMandato.errors.cargo" class="form-error">{{ formEditMandato.errors.cargo }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button type="button" @click="showEditMandatoModal = false" class="btn-secondary">Cancelar</button>
                    <button type="submit" :disabled="formEditMandato.processing" class="btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>

    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes */
.content-container { @apply w-full rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 text-left; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1 text-left; }
.form-file-input { @apply block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-danger { @apply inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest bg-red-600 hover:bg-red-700 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150; }
.btn-secondary { @apply inline-flex items-center justify-center w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
</style>
