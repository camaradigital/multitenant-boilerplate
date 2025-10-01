<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link, router, usePage } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ArrowLeft, UploadCloud, File, Trash2, Download, AlertTriangle } from 'lucide-vue-next';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    DialogDescription,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';

const props = defineProps({
    solicitacao: Object,
    statusDisponiveis: Array,
    atendentesDisponiveis: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// --- LÓGICA CORRIGIDA ---
// O backend envia 'roles' como um array de strings (ex: ['Cidadao']).
// A verificação foi ajustada para usar '.includes()' em vez de '.some()',
// que resolve o problema de visibilidade.
const isCidadao = computed(() => {
    return user.value && user.value.roles && user.value.roles.includes('Cidadao');
});

// Formulário para atualizar o status e atendente
const formStatus = useForm({
    status_id: props.solicitacao.status ? props.solicitacao.status.id : null,
    atendente_id: props.solicitacao.atendente ? props.solicitacao.atendente.id : null,
    observacoes: '',
});

// Formulário para o upload de documentos
const formDocumento = useForm({
    documento: null,
});

// Formulário para a pesquisa de satisfação
const formAvaliacao = useForm({
    nota: 0,
    comentario: '',
});

const hoverRating = ref(0);

// Lógica para o modal de confirmação
const isDeleteModalOpen = ref(false);
const documentoParaExcluir = ref(null);

const openDeleteModal = (documento) => {
    documentoParaExcluir.value = documento;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    documentoParaExcluir.value = null;
};

const submitStatus = () => {
    formStatus.put(route('admin.solicitacoes.update', props.solicitacao.id), {
        onSuccess: () => formStatus.reset('observacoes'),
        preserveScroll: true,
    });
};

const submitDocumento = () => {
    formDocumento.post(route('admin.documentos.store', props.solicitacao.id), {
        onSuccess: () => formDocumento.reset(),
        preserveScroll: true,
    });
};

const deleteDocumento = () => {
    if (!documentoParaExcluir.value) return;
    router.delete(route('admin.documentos.destroy', documentoParaExcluir.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};

const submitAvaliacao = () => {
    formAvaliacao.post(route('solicitacoes.avaliar', props.solicitacao.id), {
        preserveScroll: true,
    });
};

const podeAvaliar = computed(() => {
    if (!isCidadao.value) {
        return false;
    }
    return props.solicitacao.user_id === user.value.id &&
           props.solicitacao.status?.is_final &&
           !props.solicitacao.pesquisa_satisfacao;
});

const formatarObservacoes = (texto) => {
    if (!texto) return 'Nenhuma observação registrada.';
    return texto.replace(/\n/g, '<br />');
};

const formatarTamanho = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

</script>

<template>
    <Head :title="`Solicitação #${solicitacao.id}`" />
    <TenantLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('admin.solicitacoes.index')" class="table-action-btn">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detalhes da Solicitação #{{ solicitacao.id }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 space-y-6">
                    <!-- Card de Informações Gerais -->
                    <div class="content-container p-6">
                        <h3 class="header-title mb-4">Informações Gerais</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Cidadão</span>
                                <span class="info-value">{{ solicitacao.cidadao?.name || 'Não encontrado' }}</span>
                            </div>
                             <div class="info-item">
                                <span class="info-label">Serviço Solicitado</span>
                                <span class="info-value">{{ solicitacao.servico?.nome || 'Não encontrado' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status Atual</span>
                                <span v-if="solicitacao.status" class="badge-base" :style="{ backgroundColor: solicitacao.status.cor, color: solicitacao.status.cor === '#FFFFFF' ? '#000' : '#FFF' }">{{ solicitacao.status.nome }}</span>
                                <span v-else class="info-value text-red-500">Não definido</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Atendente Responsável</span>
                                <span class="info-value">{{ solicitacao.atendente?.name || 'Não atribuído' }}</span>
                            </div>
                             <div class="info-item">
                                <span class="info-label">Data de Abertura</span>
                                <span class="info-value">{{ new Date(solicitacao.created_at).toLocaleString('pt-BR') }}</span>
                            </div>
                            <div v-if="solicitacao.finalizado_em" class="info-item">
                                <span class="info-label">Data de Finalização</span>
                                <span class="info-value">{{ new Date(solicitacao.finalizado_em).toLocaleString('pt-BR') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Documentos -->
                    <div class="content-container p-6">
                        <h3 class="header-title mb-4">Documentos Anexados</h3>
                        <div v-if="solicitacao.documentos.length > 0" class="space-y-3">
                            <div v-for="doc in solicitacao.documentos" :key="doc.id" class="document-item">
                                <div class="flex items-center gap-3 flex-1">
                                    <File class="w-5 h-5 text-gray-500"/>
                                    <div>
                                        <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ doc.nome_original }}</p>
                                        <p class="text-xs text-gray-500">Enviado por {{ doc.uploader?.name || 'Desconhecido' }} em {{ new Date(doc.created_at).toLocaleDateString('pt-BR') }} ({{ formatarTamanho(doc.tamanho) }})</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a :href="route('admin.documentos.download', doc.id)" class="table-action-btn" title="Baixar"><Download class="w-5 h-5"/></a>
                                    <!-- Apenas quem gerencia pode excluir -->
                                    <button v-if="!isCidadao" @click="openDeleteModal(doc)" class="table-action-btn hover:text-red-500" title="Excluir"><Trash2 class="w-5 h-5"/></button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500 dark:text-gray-400">Nenhum documento foi anexado a esta solicitação.</p>

                        <!-- Formulário de Upload (Apenas para Admin/Funcionário) -->
                        <div v-if="!isCidadao" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <form @submit.prevent="submitDocumento">
                                <label for="documento" class="form-label">Anexar Novo Documento</label>
                                <input type="file" @input="formDocumento.documento = $event.target.files[0]" class="form-file-input" id="documento" />
                                <progress v-if="formDocumento.progress" :value="formDocumento.progress.percentage" max="100" class="w-full mt-2">
                                    {{ formDocumento.progress.percentage }}%
                                </progress>
                                <div v-if="formDocumento.errors.documento" class="form-error">{{ formDocumento.errors.documento }}</div>
                                <PrimaryButton class="mt-4" :class="{ 'opacity-25': formDocumento.processing }" :disabled="formDocumento.processing || !formDocumento.documento">
                                    <UploadCloud class="w-4 h-4 mr-2"/> Enviar Documento
                                </PrimaryButton>
                            </form>
                        </div>
                    </div>

                    <!-- Card de Histórico de Observações -->
                    <div class="content-container p-6">
                        <h3 class="header-title mb-4">Histórico de Observações</h3>
                        <div class="historico-observacoes" v-html="formatarObservacoes(solicitacao.observacoes)"></div>
                    </div>

                    <!-- SEÇÃO DE AVALIAÇÃO (Apenas para o Cidadão dono da solicitação) -->
                    <div v-if="podeAvaliar" class="content-container p-6">
                        <h3 class="header-title">Avalie este Atendimento</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Sua opinião é muito importante para melhorarmos nossos serviços.</p>

                        <form @submit.prevent="submitAvaliacao" class="mt-6 space-y-6">
                            <div>
                                <label class="form-label">Sua Nota</label>
                                <div class="flex items-center space-x-2 mt-2" @mouseleave="hoverRating = 0">
                                    <template v-for="star in 5" :key="star">
                                        <svg @mouseenter="hoverRating = star"
                                             @click="formAvaliacao.nota = star"
                                             class="w-8 h-8 cursor-pointer"
                                             :class="(hoverRating || formAvaliacao.nota) >= star ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </template>
                                </div>
                                <div v-if="formAvaliacao.errors.nota" class="form-error">{{ formAvaliacao.errors.nota }}</div>
                            </div>
                            <div>
                                <label for="comentario" class="form-label">Deixe um comentário (opcional)</label>
                                <textarea id="comentario" v-model="formAvaliacao.comentario" rows="4" class="form-input"></textarea>
                                <div v-if="formAvaliacao.errors.comentario" class="form-error">{{ formAvaliacao.errors.comentario }}</div>
                            </div>
                            <div v-if="formAvaliacao.errors.geral" class="form-error">{{ formAvaliacao.errors.geral }}</div>
                            <div class="flex items-center justify-end">
                                <PrimaryButton :class="{ 'opacity-25': formAvaliacao.processing }" :disabled="formAvaliacao.processing || formAvaliacao.nota === 0">
                                    Enviar Avaliação
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>

                    <!-- Mensagem de "Obrigado" após avaliar -->
                    <div v-if="solicitacao.pesquisa_satisfacao" class="bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 p-6 rounded-md">
                        <h3 class="text-lg font-medium text-green-800 dark:text-green-200">Avaliação Enviada</h3>
                        <p class="mt-2 text-sm text-green-700 dark:text-green-300">Obrigado por compartilhar sua opinião!</p>
                    </div>
                </div>

                <!-- Coluna de Ações (Apenas para Admin/Funcionário) -->
                <div v-if="!isCidadao" class="content-container p-6 h-fit">
                    <form @submit.prevent="submitStatus">
                        <h3 class="header-title mb-4">Atualizar Solicitação</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="status" class="form-label">Alterar Status</label>
                                <select v-model="formStatus.status_id" id="status" class="form-input">
                                    <option v-for="s in statusDisponiveis" :key="s.id" :value="s.id">{{ s.nome }}</option>
                                </select>
                                <div v-if="formStatus.errors.status_id" class="form-error">{{ formStatus.errors.status_id }}</div>
                            </div>
                            <div>
                                <label for="atendente" class="form-label">Atribuir Atendente</label>
                                <select v-model="formStatus.atendente_id" id="atendente" class="form-input">
                                    <option :value="null">Ninguém</option>
                                    <option v-for="a in atendentesDisponiveis" :key="a.id" :value="a.id">{{ a.name }}</option>
                                </select>
                                <div v-if="formStatus.errors.atendente_id" class="form-error">{{ formStatus.errors.atendente_id }}</div>
                            </div>
                            <div>
                                <label for="observacoes" class="form-label">Adicionar Observação</label>
                                <textarea v-model="formStatus.observacoes" id="observacoes" rows="4" class="form-input" placeholder="Digite uma nova anotação..."></textarea>
                                <div v-if="formStatus.errors.observacoes" class="form-error">{{ formStatus.errors.observacoes }}</div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <PrimaryButton :class="{ 'opacity-25': formStatus.processing }" :disabled="formStatus.processing">
                                Salvar Alterações
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmação de Exclusão -->
        <TransitionRoot appear :show="isDeleteModalOpen" as="template">
             <Dialog as="div" @close="closeDeleteModal" class="relative z-50">
                 <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                     <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                 </TransitionChild>
                 <div class="fixed inset-0 overflow-y-auto">
                     <div class="flex min-h-full items-center justify-center p-4 text-center">
                         <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                             <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all">
                                 <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex items-center">
                                     <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400 mr-3" />
                                     Confirmar Exclusão
                                 </DialogTitle>
                                 <div class="mt-2">
                                     <DialogDescription class="text-sm text-gray-500 dark:text-gray-400">
                                         Você tem certeza que deseja excluir o documento
                                         <span class="font-bold text-gray-700 dark:text-gray-200">{{ documentoParaExcluir?.nome_original }}</span>?
                                         Esta ação não pode ser desfeita.
                                     </DialogDescription>
                                 </div>
                                 <div class="mt-6 flex justify-end space-x-3">
                                     <SecondaryButton @click="closeDeleteModal">Cancelar</SecondaryButton>
                                     <button @click="deleteDocumento" class="inline-flex justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">
                                         Sim, Excluir
                                     </button>
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
.content-container { @apply w-full rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.header-title { @apply text-xl font-bold text-gray-900 dark:text-white; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.badge-base { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium; }
.info-grid { @apply grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5; }
.info-item { @apply flex flex-col; }
.info-label { @apply text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider; }
.info-value { @apply text-base font-semibold text-gray-800 dark:text-gray-200; }
.historico-observacoes { @apply mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg; font-family: monospace; line-height: 1.6; }
.document-item { @apply flex items-center justify-between bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg; }
.form-file-input { @apply block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400; }
</style>

