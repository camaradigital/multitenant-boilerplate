<script setup lang="ts">

import { ref, computed, type Ref } from 'vue';
import { Head, useForm, Link, router, usePage, type InertiaForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ArrowLeft, UploadCloud, File, Trash2, Download, AlertTriangle, User, Briefcase, Calendar, Star, Info } from 'lucide-vue-next';
import {
    Dialog, DialogPanel, DialogTitle, DialogDescription, TransitionRoot, TransitionChild,
} from '@headlessui/vue';

// --- TIPAGENS (MANTIDAS) ---
interface User {
    id: number;
    name: string;
    roles?: string[];
}

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: unknown;
}

interface Status {
    id: number;
    nome: string;
    cor: string;
    is_final?: boolean;
}

interface Atendente {
    id: number;
    name: string;
}

interface Documento {
    id: number;
    nome_original: string;
    tamanho: number;
    uploader?: { name: string };
}

interface Solicitacao {
    id: number;
    status?: Status;
    atendente?: Atendente;
    user_id: number;
    cidadao?: { name: string };
    servico?: { nome: string };
    created_at: string;
    finalizado_em?: string;
    documentos: Documento[];
    observacoes: string;
    pesquisa_satisfacao?: {
        nota: number;
        comentario: string;
    }; // Detalhamento da tipagem para facilitar
}

const props = defineProps<{
    solicitacao: Solicitacao;
    statusDisponiveis: Status[];
    atendentesDisponiveis: Atendente[];
}>();

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);

const isCidadao = computed(() => user.value?.roles?.includes('Cidadao'));

// --- FORMS (MANTIDOS) ---
const formStatus = useForm<{
    status_id: number | null;
    atendente_id: number | null;
    observacoes: string;
}>({
    status_id: props.solicitacao.status?.id || null,
    atendente_id: props.solicitacao.atendente?.id || null,
    observacoes: '',
});

const formDocumento: InertiaForm<{
    documento: File | null;
}> = useForm<{
    documento: File | null;
}>({
    documento: null,
});

const formAvaliacao = useForm<{
    nota: number;
    comentario: string;
}>({
    nota: 0,
    comentario: '',
});

// --- ESTADOS (MANTIDOS) ---
const hoverRating = ref(0);
const isDeleteModalOpen = ref(false);
const documentoParaExcluir: Ref<Documento | null> = ref(null);
const fileInput: Ref<HTMLInputElement | null> = ref(null);

// --- LÓGICA DE MODAL E ARQUIVO (MANTIDAS) ---
const openDeleteModal = (documento: Documento) => {
    documentoParaExcluir.value = documento;
    isDeleteModalOpen.value = true;
};
const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    documentoParaExcluir.value = null;
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        formDocumento.documento = target.files[0];
    }
};

const handleFileDrop = (event: DragEvent) => {
    if (event.dataTransfer?.files[0]) {
        formDocumento.documento = event.dataTransfer.files[0];
    }
};

// --- SUBMISSIONS (MANTIDAS) ---
const submitStatus = () => formStatus.put(route('admin.solicitacoes.update', props.solicitacao.id), { onSuccess: () => formStatus.reset('observacoes'), preserveScroll: true });
const submitDocumento = () => formDocumento.post(route('admin.documentos.store', props.solicitacao.id), { onSuccess: () => formDocumento.reset(), preserveScroll: true });
const deleteDocumento = () => {
    if (!documentoParaExcluir.value) return;
    router.delete(route('admin.documentos.destroy', documentoParaExcluir.value.id), { preserveScroll: true, onSuccess: () => closeDeleteModal() });
};
const submitAvaliacao = () => formAvaliacao.post(route('solicitacoes.avaliar', props.solicitacao.id), { preserveScroll: true });

// --- COMPUTED / HELPERS (MANTIDAS) ---
const podeAvaliar = computed(() => {
    if (!isCidadao.value) return false;
    // O cidadão só pode avaliar sua própria solicitação se ela estiver finalizada e ainda não avaliada.
    return props.solicitacao.user_id === user.value.id && props.solicitacao.status?.is_final && !props.solicitacao.pesquisa_satisfacao;
});

const formatarObservacoes = (texto: string) => !texto ? 'Nenhuma observação registrada.' : texto.replace(/\n/g, '<br />');
const formatarTamanho = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024, sizes = ['Bytes', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};
const getStatusTextColor = (cor: string) => {
    if (!cor) return '#1f2937';
    const hex = cor.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16), g = parseInt(hex.substring(2, 4), 16), b = parseInt(hex.substring(4, 6), 16);
    // Lógica para determinar se o texto deve ser claro ou escuro com base no brilho do fundo
    return ((r * 299 + g * 587 + b * 114) / 1000) > 125 ? '#1f2937' : '#ffffff';
};
</script>

<template>
    <Head :title="`Solicitação #${solicitacao.id}`" />
    <TenantLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="isCidadao ? route('gabinete-virtual.index') : route('admin.solicitacoes.index')" class="p-2 rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10 transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detalhes da Solicitação #{{ solicitacao.id }}
                </h2>
            </div>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <div class="lg:col-span-2 space-y-8">
                    <div class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-3">Informações Gerais</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                            <div class="flex items-start gap-3"><User class="w-5 h-5 mt-0.5 text-emerald-500"/><div class="flex flex-col"><span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cidadão</span><span class="font-semibold text-gray-800 dark:text-gray-200">{{ solicitacao.cidadao?.name || 'N/A' }}</span></div></div>
                            <div class="flex items-start gap-3"><Briefcase class="w-5 h-5 mt-0.5 text-emerald-500"/><div class="flex flex-col"><span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serviço Solicitado</span><span class="font-semibold text-gray-800 dark:text-gray-200">{{ solicitacao.servico?.nome || 'N/A' }}</span></div></div>
                            <div class="flex items-start gap-3"><Info class="w-5 h-5 mt-0.5 text-emerald-500"/><div class="flex flex-col"><span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status Atual</span><span v-if="solicitacao.status" class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full w-fit shadow-md" :style="{ backgroundColor: solicitacao.status.cor, color: getStatusTextColor(solicitacao.status.cor) }">{{ solicitacao.status.nome }}</span><span v-else class="font-semibold text-red-500">Não definido</span></div></div>
                            <div class="flex items-start gap-3"><User class="w-5 h-5 mt-0.5 text-emerald-500"/><div class="flex flex-col"><span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Atendente</span><span class="font-semibold text-gray-800 dark:text-gray-200">{{ solicitacao.atendente?.name || 'Não atribuído' }}</span></div></div>
                            <div class="flex items-start gap-3"><Calendar class="w-5 h-5 mt-0.5 text-emerald-500"/><div class="flex flex-col"><span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Abertura</span><span class="font-semibold text-gray-800 dark:text-gray-200">{{ new Date(solicitacao.created_at).toLocaleString('pt-BR') }}</span></div></div>
                            <div v-if="solicitacao.finalizado_em" class="flex items-start gap-3"><Calendar class="w-5 h-5 mt-0.5 text-emerald-500"/><div class="flex flex-col"><span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Finalização</span><span class="font-semibold text-gray-800 dark:text-gray-200">{{ new Date(solicitacao.finalizado_em).toLocaleString('pt-BR') }}</span></div></div>
                        </div>
                    </div>

                    <div class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-3">Documentos Anexados ({{ solicitacao.documentos.length }})</h3>
                        <div v-if="solicitacao.documentos.length > 0" class="space-y-3">
                            <div v-for="doc in solicitacao.documentos" :key="doc.id" class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors border border-gray-100 dark:border-gray-700/50">
                                <div class="flex items-center gap-3 flex-1 overflow-hidden">
                                    <File class="w-5 h-5 text-emerald-500 flex-shrink-0"/>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">{{ doc.nome_original }}</p>
                                        <p class="text-xs text-gray-500">por {{ doc.uploader?.name || 'N/A' }} ({{ formatarTamanho(doc.tamanho) }})</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a :href="route('admin.documentos.download', doc.id)" class="p-2 rounded-full text-gray-500 hover:bg-emerald-100 hover:text-emerald-600 dark:text-gray-400 dark:hover:bg-emerald-900/50 dark:hover:text-emerald-400 transition-colors" title="Baixar">
                                        <Download class="w-5 h-5"/>
                                    </a>
                                    <button v-if="!isCidadao" @click="openDeleteModal(doc)" class="p-2 rounded-full text-gray-500 hover:bg-red-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-900/50 dark:hover:text-red-400 transition-colors" title="Excluir">
                                        <Trash2 class="w-5 h-5"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-center text-gray-500 dark:text-gray-400 py-4">Nenhum documento anexado.</p>

                        <div v-if="!isCidadao" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <form @submit.prevent="submitDocumento" class="space-y-4">
                                <label for="documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anexar Novo Documento</label>
                                <label @dragover.prevent @drop.prevent="handleFileDrop" for="documento-input" class="flex justify-center w-full h-32 px-4 transition bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-xl appearance-none cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <span class="flex flex-col items-center justify-center space-y-1"><UploadCloud class="w-8 h-8 text-gray-600 dark:text-gray-400" /><span class="font-medium text-gray-600 dark:text-gray-400 text-center">Arraste e solte o arquivo ou <span class="text-emerald-600 dark:text-emerald-400 font-bold">clique para selecionar</span></span></span>
                                    <input type="file" @input="handleFileSelect" id="documento-input" class="hidden" ref="fileInput" />
                                </label>
                                <div v-if="formDocumento.documento" class="mt-2 flex items-center justify-between p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                                    <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300 truncate">{{ formDocumento.documento.name }}</span>
                                    <button type="button" @click="formDocumento.documento = null" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                        <Trash2 class="w-4 h-4"/>
                                    </button>
                                </div>
                                <progress v-if="formDocumento.progress" :value="formDocumento.progress.percentage" max="100" class="w-full h-2 rounded-full [&::-webkit-progress-bar]:rounded-full [&::-webkit-progress-bar]:bg-gray-200 dark:[&::-webkit-progress-bar]:bg-gray-700 [&::-webkit-progress-value]:rounded-full [&::-webkit-progress-value]:bg-emerald-500 [&::-moz-progress-bar]:bg-emerald-500"/>
                                <div v-if="formDocumento.errors.documento" class="text-sm text-red-600 dark:text-red-400">{{ formDocumento.errors.documento }}</div>
                                <PrimaryButton :class="{ 'opacity-50': formDocumento.processing || !formDocumento.documento }" :disabled="formDocumento.processing || !formDocumento.documento">Enviar Documento</PrimaryButton>
                            </form>
                        </div>
                    </div>

                    <div class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-3">Histórico de Observações</h3>
                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg font-mono leading-relaxed max-h-96 overflow-y-auto" v-html="formatarObservacoes(solicitacao.observacoes)"></div>
                    </div>

                    <div v-if="podeAvaliar" class="p-6 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 shadow-lg">
                        <h3 class="text-xl font-bold text-emerald-800 dark:text-emerald-200">Avalie este Atendimento</h3>
                        <p class="mt-1 text-sm text-emerald-600 dark:text-emerald-400">Sua opinião é muito importante para nós.</p>
                        <form @submit.prevent="submitAvaliacao" class="mt-6 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-emerald-800 dark:text-emerald-300 mb-2">Sua Nota</label>
                                <div class="flex items-center space-x-1" @mouseleave="hoverRating = 0">
                                    <button type="button" v-for="star in 5" :key="star" @mouseenter="hoverRating = star" @click="formAvaliacao.nota = star" class="p-1 rounded-md transition-colors hover:bg-amber-100 dark:hover:bg-amber-900/30">
                                        <Star class="w-8 h-8 transition-colors" :class="(hoverRating || formAvaliacao.nota) >= star ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-gray-600'" />
                                    </button>
                                </div>
                                <div v-if="formAvaliacao.errors.nota" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formAvaliacao.errors.nota }}</div>
                            </div>
                            <div>
                                <label for="comentario" class="block text-sm font-medium text-emerald-800 dark:text-emerald-300 mb-2">Deixe um comentário (opcional)</label>
                                <textarea id="comentario" v-model="formAvaliacao.comentario" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                                <div v-if="formAvaliacao.errors.comentario" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formAvaliacao.errors.comentario }}</div>
                            </div>
                            <div class="flex justify-end"><PrimaryButton :disabled="formAvaliacao.processing || formAvaliacao.nota === 0">Enviar Avaliação</PrimaryButton></div>
                        </form>
                    </div>

                    <div v-else-if="solicitacao.pesquisa_satisfacao && isCidadao" class="p-6 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 shadow-lg">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-green-800 dark:text-green-200">Avaliação Enviada</h3>
                            <div class="flex items-center space-x-1">
                                <Star v-for="n in 5" :key="n" class="w-5 h-5" :class="solicitacao.pesquisa_satisfacao.nota >= n ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-gray-600'" />
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-green-700 dark:text-green-300">Obrigado por compartilhar sua opinião! Sua nota foi **{{ solicitacao.pesquisa_satisfacao.nota }}/5**.</p>
                        <div v-if="solicitacao.pesquisa_satisfacao.comentario" class="mt-4 p-3 text-sm rounded-lg bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800">
                             "{{ solicitacao.pesquisa_satisfacao.comentario }}"
                        </div>
                    </div>

                    <div v-else-if="solicitacao.pesquisa_satisfacao && !isCidadao" class="p-6 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 shadow-lg">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-green-800 dark:text-green-200">Avaliação Recebida do Cidadão</h3>
                            <div class="flex items-center space-x-1">
                                <Star v-for="n in 5" :key="n" class="w-5 h-5" :class="solicitacao.pesquisa_satisfacao.nota >= n ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-gray-600'" />
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-green-700 dark:text-green-300">O cidadão avaliou o atendimento com a nota **{{ solicitacao.pesquisa_satisfacao.nota }}/5**.</p>
                        <div v-if="solicitacao.pesquisa_satisfacao.comentario" class="mt-4">
                            <p class="text-sm font-semibold text-green-800 dark:text-green-300 mb-2">Comentário:</p>
                            <div class="p-3 text-sm rounded-lg bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800">
                                 "{{ solicitacao.pesquisa_satisfacao.comentario }}"
                            </div>
                        </div>
                    </div>

                </div>

                <div v-if="!isCidadao" class="lg:col-span-1 p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg h-fit sticky top-24">
                    <form @submit.prevent="submitStatus">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-3">Atualizar Solicitação</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alterar Status</label>
                                <select v-model="formStatus.status_id" id="status" class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                    <option v-for="s in statusDisponiveis" :key="s.id" :value="s.id">{{ s.nome }}</option>
                                </select>
                                <div v-if="formStatus.errors.status_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formStatus.errors.status_id }}</div>
                            </div>
                            <div>
                                <label for="atendente" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Atribuir Atendente</label>
                                <select v-model="formStatus.atendente_id" id="atendente" class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                    <option :value="null">Ninguém</option>
                                    <option v-for="a in atendentesDisponiveis" :key="a.id" :value="a.id">{{ a.name }}</option>
                                </select>
                                <div v-if="formStatus.errors.atendente_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formStatus.errors.atendente_id }}</div>
                            </div>
                            <div>
                                <label for="observacoes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adicionar Observação</label>
                                <textarea v-model="formStatus.observacoes" id="observacoes" rows="4" class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" placeholder="Digite uma nova anotação..."></textarea>
                                <div v-if="formStatus.errors.observacoes" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formStatus.errors.observacoes }}</div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <PrimaryButton class="w-full" :class="{ 'opacity-50': formStatus.processing }" :disabled="formStatus.processing">Salvar Alterações</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <TransitionRoot appear :show="isDeleteModalOpen" as="template">
            <Dialog as="div" @close="closeDeleteModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-bold leading-6 text-gray-900 dark:text-gray-100 flex items-center">
                                    <AlertTriangle class="h-6 w-6 text-red-500 mr-3" />
                                    Confirmar Exclusão
                                </DialogTitle>
                                <div class="mt-4">
                                    <DialogDescription class="text-sm text-gray-500 dark:text-gray-400">
                                        Tem certeza que deseja excluir o documento <span class="font-bold text-gray-700 dark:text-gray-200">{{ documentoParaExcluir?.nome_original }}</span>? Esta ação não pode ser desfeita.
                                    </DialogDescription>
                                </div>
                                <div class="mt-6 flex justify-end space-x-3">
                                    <SecondaryButton @click="closeDeleteModal">Cancelar</SecondaryButton>
                                    <button @click="deleteDocumento" class="inline-flex justify-center rounded-lg border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 transition-colors">
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
