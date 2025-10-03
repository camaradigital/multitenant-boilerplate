<script setup lang="ts">
import { computed } from 'vue';
import { route } from 'ziggy-js'; // Import here
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { ArrowLeft, User, Calendar, Mail, Phone, Home, ClipboardList, Briefcase, Tag, FileText, ClipboardX } from 'lucide-vue-next';

const props = defineProps({
    cidadao: Object,
    customFields: Object,
});

const profileData = computed(() => {
    let data = props.cidadao.profile_data;
    if (typeof data === 'string') {
        try { data = JSON.parse(data); } catch (e) { data = {}; }
    }
    return data || {};
});

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

const formatDate = (dateString) => {
    if (!dateString) return 'Não informado';
    return new Date(dateString).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

const fullAddress = computed(() => {
    const data = profileData.value;
    const parts = [
        data.endereco_logradouro,
        data.endereco_numero,
        data.endereco_bairro,
        data.endereco_cidade ? `${data.endereco_cidade} - ${data.endereco_estado}` : null,
    ].filter(Boolean);
    return parts.join(', ') || 'Não informado';
});

const getStatusStyle = (cor) => {
    if (!cor) return { backgroundColor: '#e5e7eb', color: '#1f2937' }; // gray-200, gray-800
    const hex = cor.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16), g = parseInt(hex.substring(2, 4), 16), b = parseInt(hex.substring(4, 6), 16);
    return { backgroundColor: cor, color: ((r * 299 + g * 587 + b * 114) / 1000) > 125 ? '#1f2937' : '#ffffff' };
};
</script>

<template>
    <Head :title="`Perfil de ${cidadao.name}`" />
    <TenantLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('admin.relatorios.cidadaos')" class="p-2 rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10 transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Perfil do Cidadão
                </h2>
            </div>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

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
                            <li class="flex justify-between items-center text-gray-700 dark:text-gray-300"><span class="text-gray-500 dark:text-gray-400">Nome da Mãe:</span> <span class="font-medium">{{ profileData.nome_mae || 'Não informado' }}</span></li>
                        </ul>
                    </div>

                    <div v-if="customFields?.length > 0 && Object.keys(profileData).some(key => customFields.map(f => f.name).includes(key))"
                         class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">Informações Adicionais</h4>
                        <ul class="space-y-3 text-sm">
                            <li v-for="field in customFields.filter(f => profileData[f.name])" :key="field.id" class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500 dark:text-gray-400">{{ field.label }}:</span>
                                <span class="font-medium text-right">{{ profileData[field.name] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-2 p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Histórico de Atendimentos</h3>
                    <div v-if="cidadao.solicitacoes?.length > 0">
                        <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-3">
                            <div v-for="(solicitacao, index) in cidadao.solicitacoes" :key="solicitacao.id" class="mb-8 ml-8">
                                <span class="absolute flex items-center justify-center w-6 h-6 bg-emerald-100 dark:bg-emerald-900 rounded-full -left-3 ring-8 ring-white dark:ring-gray-800">
                                    <FileText class="w-3 h-3 text-emerald-600 dark:text-emerald-400" />
                                </span>
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-2">
                                        <Link :href="route('admin.solicitacoes.show', solicitacao.id)" class="font-bold text-gray-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 text-base">{{ solicitacao.servico.nome }}</Link>
                                        <time class="block sm:hidden text-xs font-normal text-gray-400 dark:text-gray-500 mt-1">{{ formatDate(solicitacao.created_at) }}</time>
                                        <span v-if="solicitacao.status" class="px-2.5 py-0.5 text-xs font-semibold rounded-full mt-2 sm:mt-0" :style="getStatusStyle(solicitacao.status.cor)">
                                            {{ solicitacao.status.nome }}
                                        </span>
                                    </div>
                                    <time class="hidden sm:block mb-2 text-xs font-normal text-gray-400 dark:text-gray-500">Protocolo <Link :href="route('admin.solicitacoes.show', solicitacao.id)" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline">#{{ solicitacao.id }}</Link> • {{ formatDate(solicitacao.created_at) }}</time>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Atendente: <span class="font-medium text-gray-800 dark:text-gray-200">{{ solicitacao.atendente?.name || 'Não atribuído' }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-16">
                        <ClipboardX class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                        <h4 class="mt-4 font-semibold text-gray-700 dark:text-gray-300">Nenhum Atendimento</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Este cidadão ainda não possui solicitações registradas.</p>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
