<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import KpiCard from '@/Components/KpiCard.vue';
// Você precisará de um componente multiselect. Se não tiver, instale um como o vue-multiselect.
// import Multiselect from 'vue-multiselect';

const props = defineProps({
    cidadao: Object,
    timeline: Array,
    todasAsTags: Array,
});

const notaForm = useForm({
    titulo: '',
    nota: '',
});

const tagsForm = useForm({
    tags: props.cidadao.tags.map(t => t.id),
});

const submitNota = () => {
    notaForm.post(route('cidadaos.relacionamento.storeNota', props.cidadao.id), {
        onSuccess: () => notaForm.reset(),
        preserveScroll: true,
    });
};

const updateTags = () => {
    tagsForm.post(route('cidadaos.relacionamento.syncTags', props.cidadao.id), {
        preserveScroll: true,
    });
};

const getIconForTimeline = (tipo) => {
    const icons = {
        solicitacao: '✅',
        feedback: '⭐',
        mensagem_gabinete: '💬',
        notificacao: '🔔',
        nota_interna: '📝',
        candidatura: '📄',
    };
    return icons[tipo] || '⚙️';
};

</script>

<template>
    <AppLayout title="Perfil do Cidadão">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Perfil 360° do Cidadão
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="p-6 bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg mb-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ cidadao.name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ cidadao.email }} | CPF: {{ cidadao.cpf }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Bairro: {{ cidadao.bairro || 'Não informado' }}</p>
                        </div>
                        <div class="text-right">
                             <span class="text-lg font-bold text-gray-700 dark:text-gray-300">Engajamento</span>
                            <p class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">{{ cidadao.engagement_score }}</p>
                        </div>
                    </div>
                     <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <KpiCard title="Solicitações" :value="cidadao.solicitacoes_servico.length" />
                        <KpiCard title="Mensagens Enviadas" :value="cidadao.gabinete_messages.length" />
                        <KpiCard title="Feedbacks" :value="cidadao.pesquisas_satisfacao.length" />
                        <KpiCard title="Candidaturas" :value="cidadao.candidaturas.length" />
                    </div>
                </div>

                 <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div class="md:col-span-1 p-6 bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Notas de Relacionamento</h3>
                         <form @submit.prevent="submitNota">
                            <div class="mb-4">
                                <InputLabel for="titulo" value="Título" />
                                <TextInput id="titulo" v-model="notaForm.titulo" type="text" class="mt-1 block w-full" />
                                <InputError :message="notaForm.errors.titulo" class="mt-2" />
                            </div>
                             <div class="mb-4">
                                <InputLabel for="nota" value="Nota" />
                                <textarea id="nota" v-model="notaForm.nota" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full" rows="4"></textarea>
                                <InputError :message="notaForm.errors.nota" class="mt-2" />
                            </div>
                             <PrimaryButton :class="{ 'opacity-25': notaForm.processing }" :disabled="notaForm.processing">
                                Salvar Nota
                            </PrimaryButton>
                        </form>
                    </div>
                    <div class="md:col-span-2 p-6 bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                         <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Tags Estratégicas</h3>
                         <form @submit.prevent="updateTags">
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Componente Multiselect a ser implementado.</p>
                             <PrimaryButton class="mt-4" :class="{ 'opacity-25': tagsForm.processing }" :disabled="tagsForm.processing">
                                Atualizar Tags
                            </PrimaryButton>
                         </form>
                    </div>
                </div>

                <div class="p-6 bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Linha do Tempo de Interações</h3>
                    <div class="space-y-6">
                        <div v-for="(item, index) in timeline" :key="index" class="relative flex gap-x-3">
                            <div class="absolute left-0 top-0 flex w-6 justify-center -bottom-6">
                                <div class="w-px bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                             <div class="relative flex h-6 w-6 flex-none items-center justify-center bg-white dark:bg-gray-800">
                                <div class="h-1.5 w-1.5 rounded-full bg-gray-100 dark:bg-gray-600 ring-1 ring-gray-300 dark:ring-gray-500"></div>
                            </div>

                            <div class="flex-auto py-0.5 text-sm leading-5 text-gray-500 dark:text-gray-400">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ getIconForTimeline(item.tipo) }}
                                    <template v-if="item.tipo === 'solicitacao'">
                                        Solicitou o serviço: <span class="font-bold">{{ item.data.servico.nome }}</span>
                                    </template>
                                    <template v-if="item.tipo === 'feedback'">
                                        Avaliou o atendimento com <span class="font-bold">{{ item.data.nota }} estrelas</span>. Comentário: "{{item.data.comentario}}"
                                    </template>
                                    <template v-if="item.tipo === 'mensagem_gabinete'">
                                        Enviou mensagem ao Gabinete: <span class="font-bold">"{{ item.data.assunto }}"</span>
                                    </template>
                                     <template v-if="item.tipo === 'nota_interna'">
                                        Nota interna por <span class="font-bold">{{ item.data.registrado_por.name }}</span>: "{{item.data.titulo}}"
                                    </template>
                                     <template v-if="item.tipo === 'candidatura'">
                                        Candidatou-se à vaga: <span class="font-bold">{{ item.data.vaga.titulo }}</span>
                                    </template>
                                     <template v-if="item.tipo === 'notificacao'">
                                        Recebeu a notificação: <span class="font-bold">"{{ item.data.titulo }}"</span>
                                    </template>
                                </span>
                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ new Date(item.timestamp).toLocaleString() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
