<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Building2, Mail, Link as LinkIcon, Pencil, FileText, MapPin, Home, Palette, Image as ImageIcon, Globe, Hash, Tag, Trash2, Calendar, Landmark } from 'lucide-vue-next';

// Recebe o tenant do controller como prop
const props = defineProps({
    tenant: Object,
});

// Função para formatar datas
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    // Adiciona verificação para datas inválidas
    if (isNaN(date)) return 'Data inválida';
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Função para deletar o tenant
const deleteTenant = () => {
    if (confirm('Tem certeza que deseja excluir esta câmara? Esta ação é irreversível e irá apagar o banco de dados associado.')) {
        useForm({}).delete(route('central.tenants.destroy', props.tenant.id));
    }
};
</script>

<template>
    <Head :title="'Detalhes de ' + tenant.name" />

    <AppLayout :title="'Detalhes de ' + tenant.name">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalhes da Câmara: {{ tenant.name }}
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-3xl">

                <div class="form-icon">
                    <Building2 :size="32" class="icon-in-badge" />
                </div>

                <div class="p-6 md:p-8">
                    <div class="text-center mb-8">
                        <h2 class="header-title">{{ tenant.name }}</h2>
                        <p class="form-subtitle">ID do Tenant: {{ tenant.id }}</p>
                    </div>

                    <!-- SEÇÃO 1: DADOS PRINCIPAIS -->
                    <div class="space-y-6">
                        <h3 class="section-title">Dados Principais</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label"><FileText class="detail-icon" />CNPJ</span>
                                <span class="detail-value">{{ tenant.cnpj }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><LinkIcon class="detail-icon" />Subdomínio</span>
                                <span class="detail-value">{{ tenant.subdomain }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><Mail class="detail-icon" />E-mail do Admin</span>
                                <span class="detail-value">{{ tenant.admin_email }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><Landmark class="detail-icon" />Nome do Banco</span>
                                <span class="detail-value">{{ tenant.database_name }}</span>
                            </div>
                             <div class="detail-item">
                                <span class="detail-label"><Calendar class="detail-icon" />Data de Criação</span>
                                <span class="detail-value">{{ formatDate(tenant.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- SEÇÃO 2: ENDEREÇO -->
                    <div class="space-y-6 mt-10" v-if="tenant.endereco_cep">
                        <h3 class="section-title">Endereço</h3>
                         <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label"><MapPin class="detail-icon" />CEP</span>
                                <span class="detail-value">{{ tenant.endereco_cep }}</span>
                            </div>
                            <div class="detail-item col-span-2">
                                <span class="detail-label"><Home class="detail-icon" />Logradouro</span>
                                <span class="detail-value">{{ tenant.endereco_logradouro }}, {{ tenant.endereco_numero }}</span>
                            </div>
                             <div class="detail-item">
                                <span class="detail-label"><Tag class="detail-icon" />Complemento</span>
                                <span class="detail-value">{{ tenant.endereco_complemento || 'N/A' }}</span>
                            </div>
                             <div class="detail-item">
                                <span class="detail-label"><MapPin class="detail-icon" />Bairro</span>
                                <span class="detail-value">{{ tenant.endereco_bairro }}</span>
                            </div>
                             <div class="detail-item col-span-2">
                                <span class="detail-label"><MapPin class="detail-icon" />Cidade / Estado</span>
                                <span class="detail-value">{{ tenant.endereco_cidade }} - {{ tenant.endereco_estado }}</span>
                            </div>
                        </div>
                    </div>

                     <!-- SEÇÃO 3: PERSONALIZAÇÃO -->
                    <div class="space-y-6 mt-10">
                        <h3 class="section-title">Personalização do Portal</h3>
                        <div class="detail-grid">
                            <div class="detail-item col-span-full">
                                <span class="detail-label"><ImageIcon class="detail-icon" />Logotipo</span>
                                <!-- CORREÇÃO: Adicionado /storage/ ao caminho da imagem -->
                                <img v-if="tenant.logotipo_url" :src="`/storage/${tenant.logotipo_url}`" alt="Logotipo" class="mt-2 max-h-20 rounded-lg bg-gray-200 dark:bg-gray-700 p-2 object-contain self-start">
                                <span v-else class="detail-value">Não informado</span>
                            </div>
                            <div class="detail-item col-span-full">
                                <span class="detail-label"><Globe class="detail-icon" />Site Oficial</span>
                                <a v-if="tenant.site_url" :href="tenant.site_url" target="_blank" class="detail-value link-hover">{{ tenant.site_url }}</a>
                                <span v-else class="detail-value">Não informado</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><Palette class="detail-icon" />Cor Primária</span>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full border border-gray-300 dark:border-gray-600" :style="{ backgroundColor: tenant.cor_primaria }"></div>
                                    <span class="detail-value ml-2">{{ tenant.cor_primaria }}</span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><Palette class="detail-icon" />Cor Secundária</span>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full border border-gray-300 dark:border-gray-600" :style="{ backgroundColor: tenant.cor_secundaria }"></div>
                                    <span class="detail-value ml-2">{{ tenant.cor_secundaria }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t-dynamic bg-gray-50 dark:bg-green-500/5 rounded-b-3xl flex flex-col sm:flex-row justify-between items-center gap-4">
                     <button type="button" @click="deleteTenant" class="btn-danger w-full sm:w-auto">
                        <Trash2 class="h-4 w-4 mr-2" />
                        Excluir Câmara
                    </button>
                    <Link :href="route('central.tenants.edit', tenant.id)" class="btn-primary w-full sm:w-auto">
                        <Pencil class="h-4 w-4 mr-2" />
                        Editar
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.content-container {
    @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300;
    @apply bg-white border border-gray-200;
    @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm;
}
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }

.form-icon {
    @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg;
    @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30;
}
.icon-in-badge { @apply text-white; }

.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

.section-title {
    @apply text-lg font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-green-400/10 pb-3 mb-4;
}

.detail-grid {
    @apply grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5;
}

.detail-item {
    @apply flex flex-col;
}

.detail-label {
    @apply flex items-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider;
}

.detail-icon {
    @apply h-4 w-4 mr-2 text-gray-400 dark:text-gray-500;
}

.detail-value {
    @apply text-base text-gray-800 dark:text-gray-200 mt-1 font-mono break-words;
}

.link-hover {
    @apply hover:underline transition-colors;
    @apply hover:text-emerald-600 dark:hover:text-green-300;
}

.btn-primary {
    @apply flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-sm uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2;
    @apply focus:ring-offset-white dark:focus:ring-offset-[#0A1E1C];
    @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500;
    @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400;
}

.btn-danger {
    @apply flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-sm uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2;
    @apply focus:ring-offset-white dark:focus:ring-offset-gray-800;
    @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
    @apply dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-400;
}
</style>
