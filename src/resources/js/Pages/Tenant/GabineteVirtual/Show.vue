<script setup>
import { Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { MessageSquare } from 'lucide-vue-next';

const props = defineProps({
    mensagem: Object,
});

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

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">{{ mensagem.assunto }}</h2>
                        <p class="form-subtitle">
                            Sua mensagem enviada em: {{ new Date(mensagem.created_at).toLocaleString('pt-BR') }}
                        </p>
                    </div>
                    <Link :href="route('gabinete-virtual.index')" class="btn-secondary flex-shrink-0">Voltar</Link>
                </div>

                <div class="p-4 md:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="role-name">Sua Mensagem Original</h3>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(mensagem.status)">
                            {{ mensagem.status }}
                        </span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ mensagem.mensagem }}</p>
                </div>

                <div class="p-4 md:p-6 border-t-dynamic">
                    <h3 class="role-name mb-4">Respostas do Gabinete</h3>
                    <div v-if="mensagem.respostas.length === 0" class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhuma resposta recebida ainda.</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="resposta in mensagem.respostas" :key="resposta.id" class="bg-gray-100 dark:bg-white/5 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Respondido por: {{ resposta.user.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(resposta.created_at).toLocaleString('pt-BR') }}</p>
                            </div>
                            <p class="mt-2 text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ resposta.resposta }}</p>
                        </div>
                    </div>
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
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }
</style>
