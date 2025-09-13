<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { ArrowLeft, User, Calendar, Mail, Phone, Home, ClipboardList } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    cidadao: Object,
    customFields: Object,
});

// Descriptografa e formata o campo profile_data para uso no template
const profileData = computed(() => {
    // Se profile_data for uma string, tentamos fazer o JSON.parse para tratar os dados vindo do Laravel
    let data = props.cidadao.profile_data;
    if (typeof data === 'string') {
        try {
            data = JSON.parse(data);
        } catch (e) {
            // Se o parsing falhar, retornamos um objeto vazio para evitar erros
            data = {};
        }
    }
    return data || {};
});

// Formata o telefone para o padrão (XX) XXXX-XXXX ou (XX) XXXXX-XXXX
const formattedPhone = computed(() => {
    const phone = profileData.value.telefone?.replace(/\D/g, '');
    if (!phone) return 'Não informado';
    if (phone.length === 10) {
        return `(${phone.substring(0, 2)}) ${phone.substring(2, 6)}-${phone.substring(6, 10)}`;
    }
    if (phone.length === 11) {
        return `(${phone.substring(0, 2)}) ${phone.substring(2, 7)}-${phone.substring(7, 11)}`;
    }
    return phone;
});

// Formata o CPF para o padrão XXX.XXX.XXX-XX
const formattedCpf = computed(() => {
    const cpf = props.cidadao.cpf?.replace(/\D/g, '');
    if (!cpf) return 'Não informado';
    return `${cpf.substring(0, 3)}.${cpf.substring(3, 6)}.${cpf.substring(6, 9)}-${cpf.substring(9, 11)}`;
});

// Formata uma data para o padrão dd/mm/aaaa
const formatDate = (dateString) => {
    if (!dateString) return 'Não informado';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
};

// Formata o endereço completo
const fullAddress = computed(() => {
    const data = profileData.value;
    const parts = [
        data.endereco_logradouro && data.endereco_numero ? `${data.endereco_logradouro}, ${data.endereco_numero}` : null,
        data.endereco_bairro,
        data.endereco_cidade && data.endereco_estado ? `${data.endereco_cidade} - ${data.endereco_estado}` : null,
    ].filter(Boolean);
    return parts.join('<br>') || 'Não informado';
});

// Função para determinar a cor do texto do status com base na cor de fundo
const getStatusStyle = (cor) => {
    if (!cor) return {};
    const hex = cor.replace('#', '');
    if (hex.length < 6) return { backgroundColor: cor, color: 'white' };
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    const textColor = brightness > 125 ? 'black' : 'white';
    return { backgroundColor: cor, color: textColor };
};
</script>

<template>
    <Head :title="`Perfil de ${cidadao.name}`" />
    <TenantLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('admin.cidadaos.index')" class="table-action-btn">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Perfil do Cidadão
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Coluna de Informações do Perfil -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="content-container p-6 text-center">
                        <div class="w-24 h-24 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mx-auto mb-4">
                            <User class="w-12 h-12 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ cidadao.name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ formattedCpf }}</p>
                        <span class="mt-4 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                              :class="cidadao.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                            {{ cidadao.is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>

                    <div class="content-container p-6">
                        <h4 class="header-title-sm mb-4">Dados de Contato</h4>
                        <ul class="info-list">
                            <li>
                                <Mail class="icon" />
                                <a :href="`mailto:${cidadao.email}`" class="hover:underline text-emerald-600 dark:text-emerald-400">
                                    {{ cidadao.email }}
                                </a>
                            </li>
                            <li>
                                <Phone class="icon" />
                                <a :href="`tel:${profileData.telefone}`" class="hover:underline text-emerald-600 dark:text-emerald-400">
                                    {{ formattedPhone }}
                                </a>
                            </li>
                            <li v-if="fullAddress && fullAddress !== 'Não informado'">
                                <Home class="icon" />
                                <span v-html="fullAddress"></span>
                            </li>
                            <li v-else>
                                <Home class="icon" />
                                <span>Não informado</span>
                            </li>
                        </ul>
                    </div>

                    <div class="content-container p-6">
                        <h4 class="header-title-sm mb-4">Informações Pessoais</h4>
                        <ul class="info-list">
                            <li><Calendar class="icon" /><span><strong>Data Nasc.:</strong> {{ formatDate(profileData.data_nascimento) }}</span></li>
                            <li><User class="icon" /><span><strong>Gênero:</strong> {{ profileData.genero || 'Não informado' }}</span></li>
                            <li><span><strong>Mãe:</strong> {{ profileData.nome_mae || 'Não informado' }}</span></li>
                            <li><span><strong>Pai:</strong> {{ profileData.nome_pai || 'Não informado' }}</span></li>
                        </ul>
                    </div>

                    <!-- Campos Personalizados -->
                    <div v-if="customFields?.length > 0 && Object.keys(profileData).some(key => customFields.map(f => f.name).includes(key))" class="content-container p-6">
                        <h4 class="header-title-sm mb-4">Campos Personalizados</h4>
                        <ul class="info-list">
                            <li v-for="field in customFields" :key="field.id">
                                <ClipboardList class="icon" />
                                <span><strong>{{ field.label }}:</strong> {{ profileData[field.name] || 'Não informado' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Coluna do Histórico de Atendimentos -->
                <div class="lg:col-span-2 content-container p-6">
                    <h3 class="header-title mb-4">Histórico de Atendimentos</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Protocolo</th>
                                    <th scope="col" class="px-4 py-3">Serviço</th>
                                    <th scope="col" class="px-4 py-3">Data</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="px-4 py-3">Atendente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- CORREÇÃO: Usamos `v-if` para renderizar o loop ou a mensagem de "nenhum atendimento" -->
                                <template v-if="cidadao.solicitacoes?.length > 0">
                                    <tr v-for="solicitacao in cidadao.solicitacoes" :key="solicitacao.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                        <td class="px-4 py-3">
                                            <Link :href="route('admin.solicitacoes.show', solicitacao.id)" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline">
                                                #{{ solicitacao.id }}
                                            </Link>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ solicitacao.servico.nome }}</td>
                                        <td class="px-4 py-3">{{ formatDate(solicitacao.created_at) }}</td>
                                        <td class="px-4 py-3">
                                            <span v-if="solicitacao.status" class="badge-base" :style="getStatusStyle(solicitacao.status.cor)">
                                                {{ solicitacao.status.nome }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">{{ solicitacao.atendente?.name || 'N/A' }}</td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Nenhum atendimento registrado.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
.content-container { @apply w-full rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.header-title { @apply text-xl font-bold text-gray-900 dark:text-white; }
.header-title-sm { @apply text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.info-list { @apply space-y-3 text-sm text-gray-700 dark:text-gray-300; }
.info-list li { @apply flex items-start gap-3; }
.info-list .icon { @apply w-4 h-4 mt-0.5 text-gray-400 flex-shrink-0; }
.badge-base { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium; }
</style>
