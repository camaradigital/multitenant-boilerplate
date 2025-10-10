<script setup>
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Trash2, UserPlus, ArrowLeft, Edit, ShieldAlert, Image as ImageIcon, Info, UploadCloud, Users } from 'lucide-vue-next';

// --- PROPS ---
const props = defineProps({
    legislatura: Object, // Será nulo na criação
    politicos: Array,    // Lista de todos os políticos para o dropdown
});

// --- STATE ---
const isEditing = !!props.legislatura;
const photoPreview = ref(props.legislatura?.foto_principal_url || null);
const isDragging = ref(false);

// --- FORMS ---
const formLegislatura = useForm({
    _method: isEditing ? 'put' : 'post',
    titulo: props.legislatura?.titulo || '',
    data_inicio: props.legislatura?.data_inicio?.substring(0, 10) || '',
    data_fim: props.legislatura?.data_fim?.substring(0, 10) || '',
    texto_destaque: props.legislatura?.texto_destaque || '',
    foto_principal: null,
    is_atual: props.legislatura?.is_atual || false,
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
const handleFileChange = (file) => {
    if (!file || !file.type.startsWith('image/')) return;
    formLegislatura.foto_principal = file;
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

const submitLegislatura = () => {
    const url = isEditing ? route('admin.legislaturas.update', props.legislatura.id) : route('admin.legislaturas.store');
    formLegislatura.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            if (isEditing) {
                // Limpa o campo do arquivo para evitar reenvio acidental
                formLegislatura.foto_principal = null;
            }
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
            <Link :href="route('admin.legislaturas.index')" class="table-action-btn mr-4">
                <ArrowLeft class="w-5 h-5" />
            </Link>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ isEditing ? 'Editar Legislatura' : 'Nova Legislatura' }}
            </h2>
        </div>
    </template>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <form @submit.prevent="submitLegislatura" class="content-container p-8">
                <h3 class="header-title mb-8">Dados da Legislatura</h3>
                <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="titulo" class="form-label">Título da Legislatura</label>
                            <input type="text" v-model="formLegislatura.titulo" id="titulo" class="form-input" placeholder="Ex: Legislatura 2021-2024" required>
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
                        <label for="texto_destaque" class="form-label">Texto de Destaque (Opcional)</label>
                        <textarea v-model="formLegislatura.texto_destaque" id="texto_destaque" rows="4" class="form-input" placeholder="Insira uma breve descrição ou citação sobre esta legislatura..."></textarea>
                        <div v-if="formLegislatura.errors.texto_destaque" class="form-error">{{ formLegislatura.errors.texto_destaque }}</div>
                    </div>
                    <div>
                        <label class="form-label">Foto Principal (da Posse)</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                            <div class="md:col-span-1">
                                <img v-if="photoPreview" :src="photoPreview" class="w-full h-40 rounded-xl object-cover shadow-md" alt="Preview da foto">
                                <div v-else class="w-full h-40 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <ImageIcon class="h-12 w-12 text-gray-400" />
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    for="foto_principal"
                                    class="file-drop-zone"
                                    :class="{ 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20': isDragging }"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="onDrop"
                                >
                                    <input type="file" @input="onFileSelect" id="foto_principal" class="sr-only" accept="image/*">
                                    <div class="text-center">
                                        <UploadCloud class="mx-auto h-10 w-10 text-gray-400" />
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Clique para enviar</span> ou arraste e solte
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">PNG, JPG, GIF até 5MB</p>
                                    </div>
                                </label>
                                <div v-if="formLegislatura.errors.foto_principal" class="form-error">{{ formLegislatura.errors.foto_principal }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/30 rounded-xl border dark:border-gray-700">
                        <div>
                           <label for="is_atual" class="font-medium text-gray-700 dark:text-gray-300">Marcar como Legislatura Atual</label>
                           <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Define esta como a legislatura vigente no portal.</p>
                        </div>
                        <label for="is_atual" class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input id="is_atual" v-model="formLegislatura.is_atual" type="checkbox" class="sr-only" />
                                <div class="toggle-bg"></div>
                                <div class="toggle-dot"></div>
                            </div>
                        </label>
                    </div>
                     <div v-if="formLegislatura.errors.is_atual" class="form-error -mt-4">{{ formLegislatura.errors.is_atual }}</div>

                    <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-500/50 rounded-xl flex items-start gap-3">
                        <Info class="w-5 h-5 text-blue-600 dark:text-blue-300 shrink-0 mt-0.5" />
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Lembrete sobre a Mesa Diretora:</strong> A composição da Mesa Diretora (Presidente, etc.) é renovada a cada 2 anos (biênio). O sistema não realiza essa atualização automaticamente. É responsabilidade do administrador atualizar os cargos dos membros nesta página ao final de cada biênio.
                        </p>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button type="submit" :disabled="formLegislatura.processing" class="btn-primary">
                        {{ isEditing ? 'Salvar Alterações' : 'Criar e Continuar' }}
                    </button>
                </div>
            </form>

            <div v-if="isEditing" class="content-container p-8">
                <h3 class="header-title mb-2">Composição da Câmara</h3>
                <p class="mb-6 text-gray-600 dark:text-gray-400">Adicione os políticos que fazem parte desta legislatura.</p>

                <form @submit.prevent="addMembro" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end p-4 border dark:border-gray-700 rounded-xl mb-8 bg-gray-50 dark:bg-gray-900/20">
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
                        <input type="text" v-model="formMandato.cargo" id="cargo" class="form-input" placeholder="Ex: Presidente, Vereador(a)" required>
                        <div v-if="formMandato.errors.cargo" class="form-error">{{ formMandato.errors.cargo }}</div>
                    </div>
                    <button type="submit" :disabled="formMandato.processing" class="btn-primary h-12 w-full"><UserPlus class="w-4 h-4 mr-2"/> Adicionar Membro</button>
                </form>

                <TransitionGroup name="list" tag="div" class="space-y-3">
                    <div v-if="legislatura.mandatos.length === 0" key="empty-state" class="text-center py-10 px-4 border-2 border-dashed dark:border-gray-700 rounded-xl">
                        <Users class="mx-auto h-12 w-12 text-gray-400"/>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-200">Nenhum membro adicionado</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use o formulário acima para começar a adicionar os membros.</p>
                    </div>
                    <div v-for="mandato in legislatura.mandatos" :key="mandato.id" class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <img class="h-12 w-12 rounded-full object-cover" :src="mandato.politico.foto_url || '/images/default-avatar.png'" :alt="mandato.politico.nome_politico">
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ mandato.politico.nome_politico }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ mandato.cargo }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="openEditModal(mandato)" class="table-action-btn hover:text-emerald-500" title="Editar Cargo"><Edit class="w-5 h-5"/></button>
                            <button @click="removeMembro(mandato.id)" class="table-action-btn hover:text-red-500" title="Remover Membro"><Trash2 class="w-5 h-5"/></button>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <div v-if="isEditing" class="content-container p-8 border-red-500/50 border">
                <h3 class="header-title text-red-600 dark:text-red-400 mb-4">Zona de Perigo</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">A exclusão de uma legislatura é uma ação permanente e removerá todos os mandatos associados. Esta ação não pode ser desfeita.</p>
                <button @click="deleteLegislatura" class="btn-danger">
                    <Trash2 class="w-4 h-4 mr-2" /> Excluir Legislatura
                </button>
            </div>
        </div>
    </div>

    <Transition name="modal-fade">
        <div v-if="showConfirmationModal || showEditMandatoModal" class="fixed inset-0 bg-black/60 z-50 flex justify-center items-center p-4">
             <Transition name="modal-scale">
                <div v-if="showConfirmationModal" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
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
            </Transition>
             <Transition name="modal-scale">
                <form v-if="showEditMandatoModal" @submit.prevent="updateMembro" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Editar Cargo</h3>
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
            </Transition>
        </div>
    </Transition>

</TenantLayout>
</template>

<style scoped>
/* Estilos consistentes com melhorias */
.content-container { @apply w-full rounded-2xl shadow-lg; @apply bg-white border border-gray-200/80; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-left; }
.form-input { @apply block w-full text-sm rounded-lg transition-all h-12 py-3 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1 text-left; }

/* Novo estilo para área de upload */
.file-drop-zone { @apply relative flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-800/50 dark:border-gray-600 dark:hover:border-gray-500 transition-colors; }

/* Estilos de Botão */
.btn-primary { @apply flex items-center justify-center px-5 py-3 rounded-lg font-semibold text-sm uppercase tracking-wider transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed; @apply focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-900; @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500; @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-danger { @apply inline-flex items-center justify-center px-5 py-3 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider transition ease-in-out duration-150 shadow-md hover:shadow-lg; @apply bg-red-600 hover:bg-red-700 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2; }
.btn-secondary { @apply inline-flex items-center justify-center w-full px-5 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-sm text-gray-700 dark:text-gray-200 uppercase tracking-wider shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }

/* Toggle Switch */
.toggle-bg { @apply w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full transition-colors; }
.toggle-dot { @apply absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform; }
input:checked ~ .toggle-bg { @apply bg-emerald-600 dark:bg-emerald-500; }
input:checked ~ .toggle-dot { @apply translate-x-5; }

/* Animações da lista de membros */
.list-enter-active, .list-leave-active { transition: all 0.5s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateX(30px); }

/* Animações do Modal */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-scale-enter-active, .modal-scale-leave-active { transition: all 0.3s ease; }
.modal-scale-enter-from, .modal-scale-leave-to { opacity: 0; transform: scale(0.95) translateY(20px); }
</style>
