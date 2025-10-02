<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InputError from '@/Components/InputError.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { MessageSquare, ChevronsUpDown } from 'lucide-vue-next';

const props = defineProps({
    mensagem: Object,
});

const formResposta = useForm({
    resposta: '',
});

const formStatus = useForm({
    status: props.mensagem.status,
});

const submitResposta = () => {
    formResposta.post(route('admin.gabinete-virtual.storeResposta', props.mensagem.id), {
        onSuccess: () => formResposta.reset(),
        preserveScroll: true,
    });
};

const updateStatus = (newStatus) => {
    formStatus.status = newStatus;
    formStatus.patch(route('admin.gabinete-virtual.updateStatus', props.mensagem.id), {
        preserveScroll: true,
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Pendente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'Resolvido':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'Sem Solução':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};
</script>

<template>
    <Head :title="`Mensagem: ${mensagem.assunto}`" />

    <TenantLayout :title="`Mensagem: ${mensagem.assunto}`">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalhes da Mensagem
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-4xl">
                <div class="form-icon"><MessageSquare :size="32" class="icon-in-badge" /></div>

                <!-- Cabeçalho da Mensagem -->
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">{{ mensagem.assunto }}</h2>
                        <p class="form-subtitle">
                            De: <strong>{{ mensagem.user.name }}</strong> ({{ mensagem.user.email }})
                        </p>
                        <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">
                            Enviada em: {{ new Date(mensagem.created_at).toLocaleString('pt-BR') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                         <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="btn-dropdown">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(mensagem.status)">
                                        {{ mensagem.status }}
                                    </span>
                                    <ChevronsUpDown class="ml-2 h-4 w-4 text-gray-500 dark:text-gray-400" />
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink as="button" @click="updateStatus('Pendente')">Pendente</DropdownLink>
                                <DropdownLink as="button" @click="updateStatus('Resolvido')">Resolvido</DropdownLink>
                                <DropdownLink as="button" @click="updateStatus('Sem Solução')">Sem Solução</DropdownLink>
                            </template>
                        </Dropdown>
                         <Link :href="route('admin.gabinete-virtual.index')" class="btn-secondary">Voltar</Link>
                    </div>
                </div>

                <!-- Corpo da Mensagem Original -->
                <div class="p-4 md:p-6">
                    <h3 class="role-name mb-4">Mensagem Original</h3>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ mensagem.mensagem }}</p>
                </div>

                <!-- Histórico de Respostas -->
                <div v-if="mensagem.respostas.length > 0" class="p-4 md:p-6 border-t-dynamic">
                    <h3 class="role-name mb-4">Histórico da Conversa</h3>
                     <div class="space-y-4">
                        <div v-for="resposta in mensagem.respostas" :key="resposta.id" class="bg-gray-100 dark:bg-white/5 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Respondido por: {{ resposta.user.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(resposta.created_at).toLocaleString('pt-BR') }}</p>
                            </div>
                            <p class="mt-2 text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ resposta.resposta }}</p>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Nova Resposta -->
                <div class="p-4 md:p-6 border-t-dynamic">
                     <form @submit.prevent="submitResposta">
                        <h3 class="role-name">Responder ao Cidadão</h3>
                        <div class="mt-4">
                            <label for="resposta" class="hidden">Sua Resposta</label>
                            <textarea
                                id="resposta"
                                v-model="formResposta.resposta"
                                class="input-form"
                                rows="5"
                                placeholder="Digite sua resposta aqui..."
                            ></textarea>
                            <InputError :message="formResposta.errors.resposta" class="mt-2" />
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn-primary" :class="{ 'opacity-25': formResposta.processing }" :disabled="formResposta.processing">
                                Enviar Resposta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos unificados do modelo */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }

/* Estilos de botões */
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }
.btn-dropdown { @apply inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-xl text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150; }

/* Estilo para inputs e selects do formulário */
.input-form { @apply block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500; }
</style>
