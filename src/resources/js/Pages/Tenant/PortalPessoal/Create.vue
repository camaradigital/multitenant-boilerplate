<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
// Assumindo que TenantLayout e a rotação de ícones estão disponíveis
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { ArrowLeft, Send, Check, FileUp, X, Sparkles, ShieldAlert, FileText, Loader2 } from 'lucide-vue-next';

// Definição das props, usando sintaxe de objeto JS para máxima compatibilidade no ambiente de compilação.
const props = defineProps({
    servicos: Array,
});

const etapa = ref(1);
const servicoSelecionado = ref(null);

// Definição do formulário Inertia
const form = useForm({
    servico_id: null,
    observacoes: '',
    documentos: [],
});

// Calcula o nome completo do serviço para exibição no Stepper/Resumo
const servicoNomeCompleto = computed(() => {
    if (!servicoSelecionado.value) return 'Nenhum Serviço';
    const tipo = servicoSelecionado.value.tipo_servico?.nome || 'Serviço';
    return `${tipo}: ${servicoSelecionado.value.nome}`;
});

const selecionarServico = (servico) => {
    servicoSelecionado.value = servico;
    form.servico_id = servico.id;
    etapa.value = 2;
};

const voltarEtapa = (passo) => {
    etapa.value = passo;
    if (passo === 1) {
        servicoSelecionado.value = null;
        form.reset('servico_id', 'observacoes', 'documentos'); // Resetar campos específicos
    }
};

const prepararConfirmacao = () => {
    // Validação simples antes de ir para a confirmação
    if (!form.observacoes.trim() && form.documentos.length === 0) {
        // SUGESTÃO: Aqui você poderia exibir um toast/notificação informando
        // que é recomendado adicionar observações ou anexos.
    }
    etapa.value = 3;
};

const handleFileUpload = (event) => {
    // Adicionar novos arquivos aos existentes
    const newFiles = Array.from(event.target.files);
    form.documentos = [...form.documentos, ...newFiles];

    // Limpar o valor do input para permitir o upload dos mesmos arquivos novamente se necessário
    event.target.value = null;
};

const removeFile = (indexToRemove) => {
    form.documentos = form.documentos.filter((_, index) => index !== indexToRemove);
};

// Função de utilidade para formatar o tamanho do arquivo
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};


// Função principal de envio
const submit = () => {
    form.post(route('portal.solicitacoes.store'), {
        forceFormData: true, // Garante que arquivos sejam enviados corretamente
        onSuccess: () => {
            // Navegação manual após o sucesso
            router.visit(route('portal.meu-painel'));
        },
        onError: (errors) => {
            // Voltar para a etapa 2 se o erro for de validação de observações/documentos
            if (errors.observacoes || errors.documentos) {
                 voltarEtapa(2);
            }
            // A mensagem de erro será exibida no bloco de alerta na etapa 3
        }
    });
};
</script>

<template>
    <Head title="Nova Solicitação" />
    <TenantLayout>
        <template #header>
            <!-- Título com um visual mais robusto -->
            <div class="flex items-center">
                <FileText class="w-6 h-6 mr-3 text-emerald-600 dark:text-emerald-400" />
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                    Nova Solicitação de Serviço
                </h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700/80 overflow-hidden shadow-2xl sm:rounded-2xl p-6 md:p-10 transition-all duration-500">

                    <!-- Indicador de Etapas (Stepper) -->
                    <div class="flex items-center justify-center mb-10 relative">
                        <!-- Linha de Progresso -->
                        <div class="absolute inset-x-12 top-1/2 h-0.5 transform -translate-y-1/2 bg-gray-200 dark:bg-gray-700 z-0">
                            <div class="h-full bg-emerald-500 transition-all duration-500" :style="{ width: `${(etapa - 1) * 50}%` }"></div>
                        </div>

                        <!-- Etapa 1: Serviço -->
                        <div class="flex flex-col items-center z-10 w-1/3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300"
                                 :class="etapa >= 1 ? 'bg-emerald-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                                <Check v-if="etapa > 1" class="w-5 h-5" />
                                <span v-else class="text-lg font-bold">1</span>
                            </div>
                            <span class="mt-2 text-sm font-medium text-center"
                                  :class="etapa >= 1 ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-500'">Serviço</span>
                        </div>

                        <!-- Etapa 2: Detalhes -->
                        <div class="flex flex-col items-center z-10 w-1/3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300"
                                 :class="etapa >= 2 ? 'bg-emerald-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                                <Check v-if="etapa > 2" class="w-5 h-5" />
                                <span v-else class="text-lg font-bold">2</span>
                            </div>
                            <span class="mt-2 text-sm font-medium text-center"
                                  :class="etapa >= 2 ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-500'">Detalhes</span>
                        </div>

                        <!-- Etapa 3: Confirmar -->
                        <div class="flex flex-col items-center z-10 w-1/3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300"
                                 :class="etapa >= 3 ? 'bg-emerald-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                                <Send class="w-5 h-5" />
                            </div>
                            <span class="mt-2 text-sm font-medium text-center"
                                  :class="etapa >= 3 ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-500'">Confirmar</span>
                        </div>
                    </div>


                    <!-- Bloco de Título Dinâmico -->
                    <div class="text-center mb-8 pb-4 border-b dark:border-gray-700">
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white transition-opacity duration-300">
                            {{ etapa === 1 ? 'Qual serviço você precisa?' : (etapa === 2 ? 'Adicione os detalhes necessários' : 'Revise e Envie') }}
                        </h3>
                        <p v-if="etapa === 2" class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                             <strong class="text-emerald-600 dark:text-emerald-400">{{ servicoNomeCompleto }}</strong> selecionado.
                        </p>
                    </div>

                    <!-- Etapa 1: Selecionar Serviço -->
                    <div v-if="etapa === 1" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                            <div v-for="servico in props.servicos" :key="servico.id" @click="selecionarServico(servico)"
                                 class="p-5 border dark:border-gray-700 rounded-xl cursor-pointer transition-all duration-300
                                        hover:bg-emerald-50 dark:hover:bg-gray-700/70 hover:border-emerald-500 dark:hover:border-emerald-400
                                        shadow-sm hover:shadow-lg hover:ring-2 hover:ring-emerald-200 dark:hover:ring-emerald-500/50">

                                <div class="flex items-center mb-3">
                                     <Sparkles class="w-6 h-6 text-emerald-500 flex-shrink-0 mr-3" />
                                     <p class="font-semibold text-sm text-emerald-700 dark:text-emerald-400">{{ servico.tipo_servico?.nome || 'Serviço' }}</p>
                                </div>

                                <h4 class="font-bold text-xl text-gray-800 dark:text-white">{{ servico.nome }}</h4>
                                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm line-clamp-2">{{ servico.descricao || 'Serviço padrão oferecido. Clique para continuar.' }}</p>
                            </div>
                        </div>

                        <div class="mt-10 text-center">
                            <Link :href="route('portal.meu-painel')" class="inline-flex items-center button-secondary">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Voltar ao Painel
                            </Link>
                        </div>
                    </div>

                    <!-- Etapa 2: Detalhes e Documentos -->
                    <form v-if="etapa === 2" @submit.prevent="prepararConfirmacao" class="animate-in fade-in slide-in-from-bottom-4 duration-500">

                        <div class="space-y-6">
                            <!-- Campo Observações -->
                            <div>
                                <label for="observacoes" class="label-input">Observações <span class="text-gray-500 dark:text-gray-400 font-normal">(Opcional)</span></label>
                                <textarea id="observacoes" v-model="form.observacoes" rows="5" class="form-input" placeholder="Descreva aqui sua necessidade em detalhes (máximo 500 caracteres, por exemplo)."></textarea>
                                <div v-if="form.errors.observacoes" class="error-message">{{ form.errors.observacoes }}</div>
                            </div>

                            <!-- Bloco de Upload -->
                            <div>
                                <label class="label-input mb-2">Anexar Documentos <span class="text-gray-500 dark:text-gray-400 font-normal">(Opcional)</span></label>
                                <div class="p-6 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30 transition duration-300 hover:border-emerald-500">
                                    <div class="text-center">
                                        <FileUp class="mx-auto h-12 w-12 text-emerald-500" />
                                        <div class="mt-4 flex flex-col sm:flex-row justify-center items-center text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            <label for="file-upload" class="relative cursor-pointer rounded-md font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 dark:hover:text-emerald-300 transition-colors">
                                                <span>Clique para selecionar</span>
                                                <input id="file-upload" name="file-upload" type="file" @change="handleFileUpload" multiple class="sr-only" :disabled="form.processing">
                                            </label>
                                            <p class="sm:ml-1 mt-1 sm:mt-0">ou arraste e solte seus arquivos</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600 dark:text-gray-400 mt-1">PNG, JPG, PDF (Limite de tamanho por arquivo/total a ser definido pelo backend)</p>
                                        <div v-if="form.errors.documentos" class="error-message mt-2">{{ form.errors.documentos }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de Arquivos Selecionados -->
                            <div v-if="form.documentos.length > 0">
                                <p class="label-input mb-2">Arquivos selecionados ({{ form.documentos.length }})</p>
                                <ul class="space-y-2 max-h-48 overflow-y-auto pr-2">
                                    <li v-for="(file, index) in form.documentos" :key="index" class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 p-3 rounded-lg shadow-sm">
                                        <div class="flex items-center truncate">
                                            <FileText class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400 flex-shrink-0" />
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ file.name }}</span>
                                            <span class="text-xs ml-2 text-gray-500 dark:text-gray-400 flex-shrink-0">({{ formatFileSize(file.size) }})</span>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-100 dark:hover:bg-red-900/50 transition duration-150" aria-label="Remover arquivo">
                                            <X class="w-4 h-4"/>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Ações Etapa 2 -->
                        <div class="mt-10 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                            <button type="button" @click="voltarEtapa(1)" class="button-secondary">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Trocar Serviço
                            </button>
                            <button type="submit" class="button-primary" :disabled="form.processing">
                                Ir para Confirmação
                                <Check class="w-4 h-4 ml-2" />
                            </button>
                        </div>
                    </form>

                    <!-- Etapa 3: Confirmar e Enviar -->
                    <div v-if="etapa === 3" class="animate-in fade-in slide-in-from-bottom-4 duration-500">

                        <div class="mt-4 p-6 bg-gray-50 dark:bg-gray-900/60 rounded-xl border-t-4 border-emerald-500 space-y-6 shadow-md">
                            <div class="border-b dark:border-gray-700 pb-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serviço Solicitado</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ servicoSelecionado.nome }}</p>
                                <p class="text-sm text-emerald-600 dark:text-emerald-400">{{ servicoSelecionado.tipo_servico?.nome }}</p>
                            </div>

                            <div v-if="form.observacoes">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Observações Detalhadas</p>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap mt-1 p-3 bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700">{{ form.observacoes }}</p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Documentos Anexados ({{ form.documentos.length }})</p>
                                <ul v-if="form.documentos.length > 0" class="mt-2 space-y-2">
                                    <li v-for="(file, index) in form.documentos" :key="`confirm-${index}`" class="text-gray-700 dark:text-gray-300 flex items-center">
                                        <FileText class="w-4 h-4 mr-2 text-emerald-500 flex-shrink-0" />
                                        <span>{{ file.name }}</span>
                                        <span class="text-xs ml-2 text-gray-500 dark:text-gray-400">({{ formatFileSize(file.size) }})</span>
                                    </li>
                                </ul>
                                <p v-else class="text-gray-700 dark:text-gray-300 mt-1">Nenhum documento anexado.</p>
                            </div>
                        </div>

                        <!-- Bloco de Alerta de Erro - Mais destacado -->
                        <div v-if="form.errors.servico_id || form.errors.servico" class="mt-8 flex items-start space-x-3 p-4 bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded-lg shadow-lg" role="alert">
                            <ShieldAlert class="w-6 h-6 flex-shrink-0 text-red-500" />
                            <div>
                                <h4 class="font-bold text-lg">Erro ao Enviar</h4>
                                <p class="text-sm">{{ form.errors.servico_id || form.errors.servico || 'Ocorreu um erro inesperado no servidor. Tente novamente mais tarde.' }}</p>
                            </div>
                        </div>


                        <!-- Ações Etapa 3 -->
                        <div class="mt-10 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                            <button type="button" @click="voltarEtapa(2)" class="button-secondary">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Editar Detalhes
                            </button>
                            <button type="button" @click="submit" class="button-primary bg-emerald-600 hover:bg-emerald-700" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="w-5 h-5 mr-2 animate-spin" />
                                <Send v-else class="w-5 h-5 mr-2" />
                                {{ form.processing ? 'Enviando Solicitação...' : 'Confirmar e Enviar Solicitação' }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos globais para padronização e profissionalismo */
.label-input {
    @apply block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1;
}
.form-input {
    /* Base para inputs e textareas */
    @apply block w-full text-base rounded-xl py-3 px-4 transition-all duration-200;
    /* Tema Claro */
    @apply bg-white border-gray-300 text-gray-900 placeholder-gray-500 shadow-sm;
    @apply focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500;
    /* Tema Escuro */
    @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400;
    @apply dark:focus:ring-emerald-500 dark:focus:border-emerald-500;
}
textarea.form-input {
    /* Ajuste específico para textarea */
    @apply h-auto min-h-[120px] resize-y;
}

/* Botão Principal (Ação/Avançar) */
.button-primary {
    @apply inline-flex items-center justify-center px-6 py-3 bg-emerald-600 border border-transparent
           rounded-xl font-bold text-sm text-white uppercase tracking-wider
           shadow-md hover:bg-emerald-700 active:bg-emerald-800
           focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900
           transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed;
}

/* Botão Secundário (Voltar/Cancelar) */
.button-secondary {
    @apply inline-flex items-center justify-center px-5 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300
           bg-gray-200 dark:bg-gray-700 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600
           transition ease-in-out duration-150 shadow-sm hover:shadow-md;
}

.error-message {
    @apply text-sm text-red-500 mt-1 font-medium;
}
</style>
