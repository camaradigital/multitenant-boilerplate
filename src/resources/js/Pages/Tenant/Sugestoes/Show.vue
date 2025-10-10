<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { FileText, ArrowLeft, Trash2, Download } from 'lucide-vue-next';

const props = defineProps({
    sugestao: Object,
});

const statusForm = useForm({
    status: props.sugestao.status,
});

const updateStatus = () => {
    statusForm.post(route('admin.sugestoes.updateStatus', props.sugestao.id), {
        preserveScroll: true,
    });
};

// Lógica para o modal de exclusão
const confirmingDeletion = ref(false);
const confirmDeleteSugestao = () => {
    confirmingDeletion.value = true;
};
const deleteSugestao = () => {
    router.delete(route('admin.sugestoes.destroy', props.sugestao.id), {
        onSuccess: () => {
            confirmingDeletion.value = false;
        }
    });
};

const getStatusClass = (status) => {
    const classes = {
        'Recebida': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'Em Análise': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'Arquivada': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'Aprovada': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Sugestão #${sugestao.protocolo}`" />

    <TenantLayout title="Detalhes da Sugestão">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalhes da Sugestão
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><FileText :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">{{ sugestao.titulo }}</h2>
                        <p class="form-subtitle">Protocolo #{{ sugestao.protocolo }} • Enviado por {{ sugestao.cidadao_nome }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <Link :href="route('admin.sugestoes.index')" class="btn-secondary">
                            <ArrowLeft class="w-4 h-4 mr-2"/>
                            Voltar
                        </Link>
                        <button @click="confirmDeleteSugestao" class="btn-danger">
                            <Trash2 class="w-4 h-4 mr-2"/>
                            Apagar
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-6">
                            <div>
                                <h3 class="role-name mb-4">Descrição da Sugestão</h3>
                                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300" v-html="sugestao.descricao"></div>
                            </div>

                            <div v-if="sugestao.anexo_path" class="border-t border-gray-200 dark:border-green-400/10 pt-6">
                                <h3 class="role-name mb-4">Anexo</h3>
                                <a :href="`/storage/${sugestao.anexo_path}`" target="_blank" class="inline-flex items-center gap-2 text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">
                                    <Download class="w-5 h-5" />
                                    <span>Baixar Anexo</span>
                                </a>
                            </div>
                        </div>

                        <div class="space-y-6">
                             <div class="widget">
                                <h4 class="widget-title">Informações do Cidadão</h4>
                                <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <li><strong>Nome:</strong> {{ sugestao.cidadao_nome }}</li>
                                    <li><strong>Email:</strong> {{ sugestao.cidadao_email }}</li>
                                    <li><strong>Telefone:</strong> {{ sugestao.cidadao_telefone || 'Não informado' }}</li>
                                </ul>
                            </div>
                            <div class="widget">
                                <h4 class="widget-title">Status da Sugestão</h4>
                                <div class="mt-4">
                                    <p class="mb-4 text-sm">Status Atual: <span :class="getStatusClass(sugestao.status)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">{{ sugestao.status }}</span></p>

                                    <form @submit.prevent="updateStatus">
                                        <select v-model="statusForm.status" class="input-form">
                                            <option>Recebida</option>
                                            <option>Em Análise</option>
                                            <option>Arquivada</option>
                                            <option>Aprovada</option>
                                        </select>
                                        <button type="submit" class="btn-primary mt-3 w-full justify-center" :class="{ 'opacity-25': statusForm.processing }" :disabled="statusForm.processing">
                                            Atualizar Status
                                        </button>
                                    </form>
                                </div>
                            </div>
                             <div class="widget">
                                <h4 class="widget-title">Área Temática</h4>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ sugestao.area_tematica ? sugestao.area_tematica.nome : 'Nenhuma / Outros' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="confirmingDeletion"
            title="Apagar Sugestão"
            message="Tem certeza que deseja apagar esta sugestão? Esta ação não pode ser desfeita."
            @close="confirmingDeletion = false"
            @confirm="deleteSugestao"
        />
    </TenantLayout>
</template>

<style scoped>
/* Estilos unificados do modelo */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }

/* Estilos de botões */
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }
.btn-danger { @apply btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500; }

/* Estilo para inputs e selects do formulário */
.input-form { @apply block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500; }

/* Estilo para os 'widgets' da barra lateral */
.widget { @apply bg-white/50 dark:bg-black/20 p-4 rounded-xl border border-gray-200 dark:border-white/10; }
.widget-title { @apply font-semibold text-gray-800 dark:text-gray-200; }
</style>
