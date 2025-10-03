<script setup>
import { useForm, usePage, Link, Head } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InputError from '@/Components/InputError.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { MessageSquare, ChevronsUpDown, ArrowLeft, SendHorizonal } from 'lucide-vue-next';
import { computed } from 'vue';

// Definição mais clara da prop 'mensagem'
const props = defineProps({
    mensagem: {
        type: Object,
        required: true,
        // É sempre bom garantir que a estrutura base exista
        validator: (value) => {
            return value && value.user && value.respostas !== undefined;
        }
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

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
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
        case 'Resolvido':
            return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
        case 'Sem Solução':
            return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-300';
    }
};

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Mensagem: ${mensagem.assunto}`" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalhes da Mensagem
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="relative pt-16 bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">

                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <MessageSquare :size="32" class="text-white" />
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-6 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ mensagem.assunto }}</h2>
                            <p class="text-sm mt-1 text-gray-500 dark:text-gray-400">
                                Conversa com <strong>{{ mensagem.user.name }}</strong>
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                             <Link :href="route('admin.gabinete-virtual.index')" class="inline-flex items-center justify-center w-10 h-10 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                                <ArrowLeft class="h-5 w-5" />
                            </Link>
                           <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="inline-flex items-center justify-between w-full min-w-[140px] px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-lg text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(formStatus.status)">
                                            {{ formStatus.status }}
                                        </span>
                                        <ChevronsUpDown class="ml-2 -mr-1 h-4 w-4 text-gray-500 dark:text-gray-400" />
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink as="button" @click="updateStatus('Pendente')" :class="{ 'font-bold bg-gray-100 dark:bg-gray-700': formStatus.status === 'Pendente' }">Pendente</DropdownLink>
                                    <DropdownLink as="button" @click="updateStatus('Resolvido')" :class="{ 'font-bold bg-gray-100 dark:bg-gray-700': formStatus.status === 'Resolvido' }">Resolvido</DropdownLink>
                                    <DropdownLink as="button" @click="updateStatus('Sem Solução')" :class="{ 'font-bold bg-gray-100 dark:bg-gray-700': formStatus.status === 'Sem Solução' }">Sem Solução</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 space-y-6">
                        <div class="flex items-start gap-3 justify-start">
                             <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(mensagem.user.name) }}</span>
                            </div>
                            <div class="w-full max-w-lg">
                                 <div class="bg-gray-100 dark:bg-white/5 rounded-b-lg rounded-r-lg p-4">
                                    <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">{{ mensagem.mensagem }}</p>
                                </div>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    {{ mensagem.user.name }} • {{ formatDate(mensagem.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div v-for="resposta in mensagem.respostas" :key="resposta.id" class="flex items-start gap-3 justify-end">
                            <div class="w-full max-w-lg text-right">
                                 <div class="bg-emerald-600 text-white rounded-b-lg rounded-l-lg p-4 inline-block text-left">
                                    <p class="whitespace-pre-wrap leading-relaxed">{{ resposta.resposta }}</p>
                                </div>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    {{ resposta.user.name }}
                                    <template v-if="resposta.user.id === currentUser.id">(Você)</template>
                                    • {{ formatDate(resposta.created_at) }}
                                </p>
                            </div>
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                                <span class="font-semibold text-emerald-700 dark:text-emerald-300">{{ getInitials(resposta.user.name) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
                        <form @submit.prevent="submitResposta">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                                    <span class="font-semibold text-emerald-700 dark:text-emerald-300">{{ getInitials(currentUser.name) }}</span>
                                </div>
                                <div class="flex-grow">
                                    <textarea
                                        id="resposta"
                                        v-model="formResposta.resposta"
                                        class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 transition"
                                        rows="4"
                                        placeholder="Digite sua resposta aqui..."
                                    ></textarea>
                                    <InputError :message="formResposta.errors.resposta" class="mt-2" />
                                </div>
                                 <button
                                    type="submit"
                                    class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 transition-all disabled:opacity-50"
                                    :class="{ 'opacity-50': formResposta.processing || !formResposta.resposta }"
                                    :disabled="formResposta.processing || !formResposta.resposta"
                                >
                                    <SendHorizonal class="h-5 w-5" />
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
