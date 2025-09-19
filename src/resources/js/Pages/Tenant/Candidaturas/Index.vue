<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Briefcase, User, Mail, Calendar, Download, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    vaga: Object,
    candidaturas: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('pt-BR', options);
};

const getStatusClass = (status) => {
    const classes = {
        enviada: 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
        visualizada: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
        em_processo: 'bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300',
        aprovada: 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
        rejeitada: 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-300';
};
</script>

<template>
    <Head :title="`Candidaturas para ${vaga.titulo}`" />

    <TenantLayout :title="`Candidaturas para ${vaga.titulo}`">
        <template #header>
            <div class="flex items-center space-x-4">
                 <Link :href="route('admin.vagas.index')" class="back-link">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Candidaturas para: {{ vaga.titulo }}
                </h2>
            </div>
        </template>

        <div class="py-12 px-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="content-container">
                    <div class="p-6 border-b-dynamic">
                        <h3 class="header-title">{{ vaga.titulo }}</h3>
                        <p class="form-subtitle">{{ vaga.empresa.nome_fantasia }}</p>
                    </div>

                    <div class="p-6">
                        <div v-if="candidaturas.data.length > 0" class="space-y-4">
                            <div v-for="candidatura in candidaturas.data" :key="candidatura.id" class="candidato-card">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                             <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-800/50 flex items-center justify-center">
                                                <User class="h-6 w-6 text-emerald-600 dark:text-emerald-300" />
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-base font-bold text-gray-900 dark:text-white">{{ candidatura.user.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ candidatura.user.email }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 pl-12 border-l-2 border-gray-200 dark:border-gray-700 ml-5">
                                         <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                                            <Calendar class="w-4 h-4 mr-2" />
                                            Enviada em: {{ formatDate(candidatura.created_at) }}
                                        </div>
                                        <p v-if="candidatura.mensagem_apresentacao" class="text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
                                            {{ candidatura.mensagem_apresentacao }}
                                        </p>
                                        <p v-else class="text-sm text-gray-400 italic">
                                            Nenhuma mensagem de apresentação foi enviada.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2 mt-4 sm:mt-0 ml-auto pl-4">
                                     <span :class="getStatusClass(candidatura.status)" class="status-badge mb-2 sm:mb-0">
                                        {{ candidatura.status.charAt(0).toUpperCase() + candidatura.status.slice(1) }}
                                    </span>
                                    <a :href="route('admin.candidaturas.downloadCurriculo', { candidatura: candidatura.id })" class="btn-primary-outline" title="Baixar Currículo">
                                        <Download class="w-4 h-4 mr-2" />
                                        Currículo
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-10">
                            <p class="text-gray-500 dark:text-gray-400">Nenhuma candidatura recebida para esta vaga ainda.</p>
                        </div>
                    </div>

                    <div v-if="candidaturas.links.length > 3" class="px-6 pb-4">
                        <Pagination :links="candidaturas.links" />
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
.content-container { @apply w-full rounded-2xl shadow-sm bg-white border border-gray-200; @apply dark:bg-gray-800/50 dark:border-gray-700; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-gray-700; }
.header-title { @apply text-xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.candidato-card { @apply bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-start; }
.status-badge { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium; }
.btn-primary-outline { @apply flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply ring-1 ring-inset text-emerald-700 ring-emerald-300 hover:bg-emerald-50 dark:text-emerald-300 dark:ring-emerald-700 dark:hover:bg-emerald-900/50; }
.back-link { @apply p-2 rounded-full text-gray-500 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors; }
</style>
