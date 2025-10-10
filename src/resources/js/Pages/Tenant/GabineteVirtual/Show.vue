<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { MessageSquare, ArrowLeft, Hourglass } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    mensagem: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

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

                    <div class="flex items-center justify-between gap-4 p-6 border-b border-gray-200 dark:border-white/10">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ mensagem.assunto }}</h2>
                            <span class="mt-1 sm:mt-0 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(mensagem.status)">
                                {{ mensagem.status }}
                            </span>
                        </div>
                        <Link :href="route('portalcidadao.gabinete-virtual.index')" class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                    </div>

                    <div class="p-4 md:p-6 space-y-6">

                        <div class="flex items-start gap-3 justify-end">
                             <div class="w-full max-w-lg text-right">
                                 <div class="bg-emerald-600 text-white rounded-b-lg rounded-l-lg p-4 inline-block text-left">
                                    <p class="whitespace-pre-wrap leading-relaxed">{{ mensagem.mensagem }}</p>
                                </div>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    Você • {{ formatDate(mensagem.created_at) }}
                                </p>
                            </div>
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                                <span class="font-semibold text-emerald-700 dark:text-emerald-300">{{ getInitials(currentUser.name) }}</span>
                            </div>
                        </div>


                        <div v-if="mensagem.respostas.length === 0" class="text-center py-10 my-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                            <Hourglass class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-4 text-md font-semibold text-gray-900 dark:text-white">Aguardando Resposta</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sua mensagem foi recebida e será respondida em breve.</p>
                        </div>

                        <div v-else class="space-y-6">
                             <div v-for="resposta in mensagem.respostas" :key="resposta.id" class="flex items-start gap-3 justify-start">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(resposta.user.name) }}</span>
                                </div>
                                <div class="w-full max-w-lg">
                                    <div class="bg-gray-100 dark:bg-white/5 rounded-b-lg rounded-r-lg p-4">
                                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">{{ resposta.resposta }}</p>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        {{ resposta.user.name }} • {{ formatDate(resposta.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
