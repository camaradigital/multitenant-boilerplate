<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import {
    ArrowLeft, User, Mail, Phone, Home, FileText, ClipboardList, Star, MessageSquare,
    Bell, StickyNote, Paperclip, Activity, Award, ClipboardX // ADICIONADO: Ícone que faltava
} from 'lucide-vue-next';

const props = defineProps({
    cidadao: Object,
    customFields: Object,
    kpis: Object,
    timeline: Array,
    todasAsTags: Array,
});

// --- LÓGICA DAS ABAS ---
const activeTab = ref('todos'); // Aba inicial é 'todos'

const tabs = [
    { id: 'todos', label: 'Todas Interações', icon: Activity },
    { id: 'solicitacao', label: 'Solicitações', icon: FileText },
    { id: 'mensagem_gabinete', label: 'Gabinete Virtual', icon: MessageSquare },
    { id: 'nota_interna', label: 'Notas Internas', icon: StickyNote },
    { id: 'candidatura', label: 'Candidaturas', icon: Paperclip },
];

// Computed property que filtra a timeline com base na aba ativa
const filteredTimeline = computed(() => {
    if (activeTab.value === 'todos') {
        return props.timeline;
    }
    return props.timeline.filter(evento => evento.tipo === activeTab.value);
});
// --- FIM DA LÓGICA DAS ABAS ---

// Formulários para Notas e Tags
const notaForm = useForm({
    titulo: '',
    nota: '',
});
const tagForm = useForm({
    tag_id: '',
});

const submitNota = () => {
    notaForm.post(route('tenant.cidadaos.notas.store', props.cidadao.id), {
        preserveScroll: true,
        onSuccess: () => notaForm.reset(),
    });
};

const addTag = () => {
    tagForm.post(route('tenant.cidadaos.tags.attach', props.cidadao.id), {
        preserveScroll: true,
        onSuccess: () => tagForm.reset(),
    });
};

const removeTag = (tagId) => {
    useForm({}).delete(route('tenant.cidadaos.tags.detach', [props.cidadao.id, tagId]), {
        preserveScroll: true,
    });
};

// Seus computed properties existentes (mantidos)
const profileData = computed(() => props.cidadao.profile_data || {});

const formattedPhone = computed(() => {
    const phone = profileData.value.telefone?.replace(/\D/g, '');
    if (!phone) return 'Não informado';
    if (phone.length === 10) return `(${phone.substring(0, 2)}) ${phone.substring(2, 6)}-${phone.substring(6, 10)}`;
    if (phone.length === 11) return `(${phone.substring(0, 2)}) ${phone.substring(2, 7)}-${phone.substring(7, 11)}`;
    return phone;
});

const formattedCpf = computed(() => {
    const cpf = props.cidadao.cpf?.replace(/\D/g, '');
    if (!cpf) return 'Não informado';
    return `${cpf.substring(0, 3)}.${cpf.substring(3, 6)}.${cpf.substring(6, 9)}-${cpf.substring(9, 11)}`;
});

const formatDate = (dateString, includeTime = false) => {
    if (!dateString) return 'Não informado';
    const options: Intl.DateTimeFormatOptions = { timeZone: 'UTC', day: '2-digit', month: '2-digit', year: 'numeric' };
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    return new Date(dateString).toLocaleString('pt-BR', options);
};

const fullAddress = computed(() => {
    const data = profileData.value;
    const parts = [
        data.endereco_logradouro,
        data.endereco_numero,
        props.cidadao.bairro?.nome, // CORRIGIDO: Usa a relação 'bairro'
        data.endereco_cidade ? `${data.endereco_cidade} - ${data.endereco_estado}` : null,
    ].filter(Boolean);
    return parts.join(', ') || 'Não informado';
});

// Mapeamento de ícones e estilos para a timeline
const timelineEventMap = {
    solicitacao: { icon: FileText, title: 'Solicitação de Serviço', bg: 'bg-blue-100 dark:bg-blue-900/50', color: 'text-blue-600 dark:text-blue-400' },
    feedback: { icon: Star, title: 'Feedback de Serviço', bg: 'bg-yellow-100 dark:bg-yellow-900/50', color: 'text-yellow-600 dark:text-yellow-400' },
    mensagem_gabinete: { icon: MessageSquare, title: 'Mensagem ao Gabinete', bg: 'bg-purple-100 dark:bg-purple-900/50', color: 'text-purple-600 dark:text-purple-400' },
    notificacao: { icon: Bell, title: 'Notificação Recebida', bg: 'bg-gray-100 dark:bg-gray-700/50', color: 'text-gray-600 dark:text-gray-400' },
    nota_interna: { icon: StickyNote, title: 'Nota Interna', bg: 'bg-orange-100 dark:bg-orange-900/50', color: 'text-orange-600 dark:text-orange-400' },
    candidatura: { icon: Paperclip, title: 'Candidatura à Vaga', bg: 'bg-indigo-100 dark:bg-indigo-900/50', color: 'text-indigo-600 dark:text-indigo-400' },
};
</script>

<template>
    <Head :title="`Dossiê de ${cidadao.name}`" />
    <TenantLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('admin.cidadaos.index')" class="p-2 rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10 transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Dossiê do Cidadão
                </h2>
            </div>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-8">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                     <KpiCard title="Índice de Engajamento" :value="cidadao.engagement_score" :icon="Activity" colorClass="bg-red-500 text-white" />
                     <KpiCard title="Total de Solicitações" :value="kpis.total_solicitacoes" :icon="ClipboardList" colorClass="bg-blue-500 text-white" />
                     <KpiCard title="Mensagens Enviadas" :value="kpis.mensagens_enviadas" :icon="MessageSquare" colorClass="bg-purple-500 text-white" />
                     <KpiCard title="Satisfação Média" :value="kpis.satisfacao_media ? `${kpis.satisfacao_media} / 5` : 'N/A'" :icon="Award" colorClass="bg-yellow-500 text-white" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <div class="lg:col-span-1 space-y-6">
                        <div class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm text-center">
                            <div class="w-24 h-24 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mx-auto mb-4 border-4 border-white dark:border-gray-800">
                                <User class="w-12 h-12 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ cidadao.name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ formattedCpf }}</p>
                            <span class="mt-4 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
                                  :class="cidadao.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'">
                                {{ cidadao.is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>

                        <div class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">Dados de Contato</h4>
                            <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                                <li class="flex items-start gap-3"><Mail class="w-4 h-4 mt-0.5 text-gray-400 flex-shrink-0" /><a :href="`mailto:${cidadao.email}`" class="hover:underline text-emerald-600 dark:text-emerald-400 break-all">{{ cidadao.email }}</a></li>
                                <li class="flex items-start gap-3"><Phone class="w-4 h-4 mt-0.5 text-gray-400 flex-shrink-0" /><a :href="`tel:${profileData.telefone}`" class="hover:underline text-emerald-600 dark:text-emerald-400">{{ formattedPhone }}</a></li>
                                <li class="flex items-start gap-3"><Home class="w-4 h-4 mt-0.5 text-gray-400 flex-shrink-0" /><span>{{ fullAddress }}</span></li>
                            </ul>
                        </div>

                         <div class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">Informações Pessoais</h4>
                            <ul class="space-y-3 text-sm">
                                <li class="flex justify-between items-center text-gray-700 dark:text-gray-300"><span class="text-gray-500 dark:text-gray-400">Data Nasc.:</span> <span class="font-medium">{{ formatDate(profileData.data_nascimento) }}</span></li>
                                <li class="flex justify-between items-center text-gray-700 dark:text-gray-300"><span class="text-gray-500 dark:text-gray-400">Gênero:</span> <span class="font-medium">{{ profileData.genero || 'Não informado' }}</span></li>
                                <li class="flex justify-between items-center text-gray-700 dark:text-gray-300"><span class="text-gray-500 dark:text-gray-400">Nome da Mãe:</span> <span class="font-medium text-right">{{ profileData.nome_mae || 'Não informado' }}</span></li>
                            </ul>
                        </div>

                        <div class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">Tags de Relacionamento</h4>
                            <div class="flex flex-wrap gap-2 items-center min-h-[2rem]">
                                <span v-for="tag in cidadao.tags" :key="tag.id" class="px-3 py-1 text-xs font-semibold rounded-full text-white" :style="{ backgroundColor: tag.cor }">
                                    {{ tag.nome_tag }}
                                    <button @click="removeTag(tag.id)" class="ml-1 font-bold opacity-70 hover:opacity-100">&times;</button>
                                </span>
                            </div>
                            <form @submit.prevent="addTag" class="flex items-center gap-2 mt-4">
                                <select v-model="tagForm.tag_id" class="flex-grow border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                    <option value="">Adicionar Tag...</option>
                                    <option v-for="tag in todasAsTags" :key="tag.id" :value="tag.id">{{ tag.nome_tag }}</option>
                                </select>
                                <PrimaryButton :disabled="!tagForm.tag_id || tagForm.processing" class="!py-2">Add</PrimaryButton>
                            </form>
                        </div>

                         <div class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                             <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">Registrar Interação</h4>
                             <form @submit.prevent="submitNota">
                                 <div class="space-y-4">
                                     <div>
                                         <InputLabel for="titulo" value="Título da Nota" />
                                         <TextInput id="titulo" v-model="notaForm.titulo" type="text" class="mt-1 block w-full" />
                                         <InputError :message="notaForm.errors.titulo" class="mt-2" />
                                     </div>
                                      <div>
                                         <InputLabel for="nota" value="Descrição (Ligação, Visita, etc.)" />
                                         <textarea id="nota" v-model="notaForm.nota" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3"></textarea>
                                         <InputError :message="notaForm.errors.nota" class="mt-2" />
                                     </div>
                                 </div>
                                 <div class="flex items-center justify-end mt-4">
                                     <PrimaryButton :class="{ 'opacity-25': notaForm.processing }" :disabled="notaForm.processing">
                                         Salvar Nota
                                     </PrimaryButton>
                                 </div>
                            </form>
                         </div>
                    </div>

                    <div class="lg:col-span-2 p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                            <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                                        :class="[activeTab === tab.id ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600', 'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2']">
                                    <component :is="tab.icon" class="w-4 h-4" />
                                    {{ tab.label }}
                                </button>
                            </nav>
                        </div>

                        <div v-if="filteredTimeline.length > 0">
                            <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-3">
                                <div v-for="(evento, index) in filteredTimeline" :key="index" class="mb-8 ml-8">
                                    <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-8 ring-white dark:ring-gray-800" :class="timelineEventMap[evento.tipo]?.bg || 'bg-gray-100 dark:bg-gray-700/50'">
                                        <component :is="timelineEventMap[evento.tipo]?.icon || HelpCircle" class="w-3 h-3" :class="timelineEventMap[evento.tipo]?.color || 'text-gray-600 dark:text-gray-400'" />
                                    </span>
                                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-2">
                                            <h4 class="font-bold text-gray-900 dark:text-white text-base">
                                                {{ timelineEventMap[evento.tipo]?.title || 'Evento' }}
                                            </h4>
                                            <time class="block text-xs font-normal text-gray-400 dark:text-gray-500 mt-1 sm:mt-0">{{ formatDate(evento.data, true) }}</time>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ evento.descricao }}</p>
                                        <p v-if="evento.detalhes" class="text-sm mt-2 p-3 bg-gray-100 dark:bg-gray-700 rounded-md whitespace-pre-wrap font-mono">{{ evento.detalhes }}</p>
                                        <Link v-if="evento.tipo === 'solicitacao'" :href="route('admin.solicitacoes.show', evento.id)" class="inline-block mt-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                            Ver Protocolo #{{ evento.id }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-16">
                            <ClipboardX class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                            <h4 class="mt-4 font-semibold text-gray-700 dark:text-gray-300">Nenhuma Interação Nesta Categoria</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Não há registros para a aba selecionada.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
