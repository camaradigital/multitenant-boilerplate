<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Briefcase, Building2, MapPin, Users, DollarSign, FileText, Calendar, ChevronLeft, User, Mail, CalendarClock } from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';


const props = defineProps({
    vaga: Object,
});

const formatCurrency = (value) => {
    if (value === null || value === undefined) return 'Não informado';
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return 'Não informado';
    return format(parseISO(dateString), "dd/MM/yyyy", { locale: ptBR });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'aberta':
            return 'badge-active';
        case 'fechada':
            return 'badge-inactive';
        case 'pausada':
            return 'badge-paused';
        default:
            return 'badge-info';
    }
};
</script>

<template>
    <Head :title="vaga.data.titulo" />

    <TenantLayout :title="`Vaga: ${vaga.data.titulo}`">
        <template #header>
            <div class="flex items-center">
                 <Link :href="route('admin.vagas.index')" class="table-action-btn mr-2">
                    <ChevronLeft class="w-6 h-6" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detalhes da Vaga
                </h2>
            </div>
        </template>

        <div class="py-12 px-4">
            <div class="max-w-7xl mx-auto space-y-8">
                <!-- Card de Detalhes da Vaga -->
                <div class="content-container">
                    <div class="form-icon"><Briefcase :size="32" class="icon-in-badge" /></div>

                    <div class="p-6 border-b-dynamic">
                        <h2 class="header-title">{{ vaga.data.titulo }}</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="badge-permission">
                                <Building2 class="w-3 h-3 mr-1.5" />
                                {{ vaga.data.empresa.nome_fantasia }}
                            </span>
                            <span class="badge-info">
                                <MapPin class="w-3 h-3 mr-1.5" />
                                {{ vaga.data.localizacao }}
                            </span>
                            <span :class="getStatusClass(vaga.data.status)">
                                {{ vaga.data.status.charAt(0).toUpperCase() + vaga.data.status.slice(1) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                         <div class="detail-item">
                            <DollarSign class="detail-icon" />
                            <div>
                                <p class="detail-label">Salário</p>
                                <p class="detail-value">{{ formatCurrency(vaga.data.salario) }}</p>
                            </div>
                        </div>
                         <div class="detail-item">
                            <FileText class="detail-icon" />
                            <div>
                                <p class="detail-label">Contratação</p>
                                <p class="detail-value">{{ vaga.data.tipo_contratacao }}</p>
                            </div>
                        </div>
                         <div class="detail-item">
                            <Calendar class="detail-icon" />
                            <div>
                                <p class="detail-label">Data de Expiração</p>
                                <p class="detail-value">{{ formatDate(vaga.data.data_expiracao) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pb-6 space-y-4">
                        <div>
                            <h3 class="section-title">Descrição da Vaga</h3>
                            <p class="section-content">{{ vaga.data.descricao }}</p>
                        </div>
                         <div v-if="vaga.data.responsabilidades">
                            <h3 class="section-title">Responsabilidades</h3>
                            <p class="section-content">{{ vaga.data.responsabilidades }}</p>
                        </div>
                         <div v-if="vaga.data.requisitos">
                            <h3 class="section-title">Requisitos</h3>
                            <p class="section-content">{{ vaga.data.requisitos }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card de Candidaturas -->
                 <div class="content-container">
                    <div class="form-icon"><Users :size="32" class="icon-in-badge" /></div>

                    <div class="p-6 border-b-dynamic">
                        <h2 class="header-title">Candidaturas Recebidas</h2>
                        <p class="form-subtitle">Lista de cidadãos que se aplicaram para esta vaga.</p>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="vaga.data.candidaturas.length > 0" class="space-y-4">
                            <div v-for="candidatura in vaga.data.candidaturas" :key="candidatura.id" class="role-card">
                                <div class="flex-1">
                                    <p class="role-name">{{ candidatura.user.name }}</p>
                                     <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="badge-info">
                                            <Mail class="w-3 h-3 mr-1.5" />
                                            {{ candidatura.user.email }}
                                        </span>
                                        <span class="badge-permission">
                                            <CalendarClock class="w-3 h-3 mr-1.5" />
                                            Candidatou-se em: {{ formatDate(candidatura.created_at) }}
                                        </span>
                                    </div>
                                </div>
                                <!-- Futuramente: Ações para gerenciar candidatura -->
                            </div>
                        </div>
                        <div v-else class="text-center py-10">
                            <p class="text-gray-500 dark:text-gray-400">Nenhuma candidatura recebida até o momento.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes com o seu design */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md hover:border-gray-300 dark:hover:border-white/20; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-permission { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300; }
.badge-info { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-500/10 dark:text-slate-300; }
.badge-active { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300; }
.badge-inactive { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300; }
.badge-paused { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }

/* Estilos para detalhes */
.detail-item { @apply flex items-center space-x-3; }
.detail-icon { @apply w-8 h-8 text-emerald-500 dark:text-emerald-400; }
.detail-label { @apply text-sm text-gray-500 dark:text-gray-400; }
.detail-value { @apply text-base font-semibold text-gray-800 dark:text-gray-200; }

.section-title { @apply text-lg font-bold text-gray-800 dark:text-gray-200 mb-2; }
.section-content { @apply text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line; }

</style>
