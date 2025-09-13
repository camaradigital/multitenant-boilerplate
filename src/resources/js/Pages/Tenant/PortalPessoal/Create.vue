<script setup>
// SUGESTÃO: Importar o 'router' do Inertia para fazer a navegação manual.
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { ArrowLeft, Send, Check, FileUp, X, Sparkles, ShieldAlert } from 'lucide-vue-next';

const props = defineProps({
    servicos: Array,
});

const etapa = ref(1);
const servicoSelecionado = ref(null);

const form = useForm({
    servico_id: null,
    observacoes: '',
    documentos: [],
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
        form.reset();
    }
};

const prepararConfirmacao = () => {
    etapa.value = 3;
};

const handleFileUpload = (event) => {
    form.documentos = Array.from(event.target.files);
};

const removeFile = (indexToRemove) => {
    form.documentos = form.documentos.filter((_, index) => index !== indexToRemove);
};

const submit = () => {
    form.post(route('portal.solicitacoes.store'), {
        // SUGESTÃO: Adicionar a navegação manual no callback onSuccess.
        onSuccess: () => {
            // Como o backend não está redirecionando, nós navegamos
            // para o painel manualmente após o sucesso.
            router.visit(route('portal.meu-painel'));
        },
        onError: () => {
            // A mensagem de erro será exibida no novo bloco de alerta.
        }
    });
};
</script>

<template>
    <Head title="Nova Solicitação" />
    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Nova Solicitação de Serviço
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800/50 dark:border dark:border-gray-700 overflow-hidden shadow-xl sm:rounded-2xl p-6 md:p-8">

                    <!-- Indicador de Etapas -->
                    <div class="flex items-center justify-center mb-8">
                        <div class="flex items-center" :class="{'opacity-50': etapa !== 1}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full" :class="etapa >= 1 ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-gray-700'">
                                <Check v-if="etapa > 1" class="w-6 h-6" />
                                <span v-else class="text-lg font-bold">1</span>
                            </div>
                            <span class="ml-3 font-medium" :class="etapa >= 1 ? 'text-gray-800 dark:text-gray-200' : 'text-gray-500'">Serviço</span>
                        </div>
                        <div class="flex-auto border-t-2 mx-4" :class="etapa >= 2 ? 'border-emerald-500' : 'border-gray-200 dark:border-gray-600'"></div>
                        <div class="flex items-center" :class="{'opacity-50': etapa < 2}">
                             <div class="flex items-center justify-center w-10 h-10 rounded-full" :class="etapa >= 2 ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-gray-700'">
                                <Check v-if="etapa > 2" class="w-6 h-6" />
                                <span v-else class="text-lg font-bold">2</span>
                            </div>
                            <span class="ml-3 font-medium" :class="etapa >= 2 ? 'text-gray-800 dark:text-gray-200' : 'text-gray-500'">Detalhes</span>
                        </div>
                        <div class="flex-auto border-t-2 mx-4" :class="etapa >= 3 ? 'border-emerald-500' : 'border-gray-200 dark:border-gray-600'"></div>
                        <div class="flex items-center" :class="{'opacity-50': etapa < 3}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full" :class="etapa >= 3 ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-gray-700'">
                                <Send class="w-5 h-5" />
                            </div>
                            <span class="ml-3 font-medium" :class="etapa >= 3 ? 'text-gray-800 dark:text-gray-200' : 'text-gray-500'">Confirmar</span>
                        </div>
                    </div>

                    <!-- Etapa 1: Selecionar Serviço -->
                    <div v-if="etapa === 1">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-center">Qual serviço você precisa?</h3>
                        <p class="text-center text-gray-500 mt-2">Escolha uma das opções abaixo para começar.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                            <div v-for="servico in servicos" :key="servico.id" @click="selecionarServico(servico)" class="p-6 border dark:border-gray-700 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/50 hover:border-emerald-500 dark:hover:border-emerald-400 cursor-pointer transition-all duration-300 transform hover:scale-105">
                                <p class="font-semibold text-lg text-emerald-700 dark:text-emerald-400">{{ servico.tipo_servico?.nome }}</p>
                                <h4 class="font-bold text-xl text-gray-800 dark:text-white mt-1">{{ servico.nome }}</h4>
                                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">{{ servico.descricao || 'Serviço padrão oferecido pela câmara.' }}</p>
                            </div>
                        </div>
                         <div class="mt-8 text-center">
                            <Link :href="route('portal.meu-painel')" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Voltar ao Painel
                            </Link>
                        </div>
                    </div>

                    <!-- Etapa 2: Detalhes e Documentos -->
                    <div v-if="etapa === 2">
                         <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-center">Detalhes da sua Solicitação</h3>
                         <p class="text-center text-gray-500 mt-2">Serviço selecionado: <strong class="text-emerald-600 dark:text-emerald-400">{{ servicoSelecionado.nome }}</strong></p>

                         <div class="mt-8">
                             <label for="observacoes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
                             <textarea id="observacoes" v-model="form.observacoes" rows="4" class="form-input mt-1" placeholder="Descreva aqui sua necessidade. Quanto mais detalhes, melhor."></textarea>
                             <div v-if="form.errors.observacoes" class="text-sm text-red-500 mt-1">{{ form.errors.observacoes }}</div>
                         </div>

                         <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anexar Documentos (Opcional)</label>
                             <div class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 dark:border-gray-600 px-6 py-10">
                                 <div class="text-center">
                                    <FileUp class="mx-auto h-12 w-12 text-gray-400" />
                                    <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400">
                                        <label for="file-upload" class="relative cursor-pointer rounded-md bg-white dark:bg-gray-800 font-semibold text-emerald-600 dark:text-emerald-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-emerald-600 focus-within:ring-offset-2 dark:focus-within:ring-offset-gray-900 hover:text-emerald-500">
                                            <span>Selecione os arquivos</span>
                                            <input id="file-upload" name="file-upload" type="file" @change="handleFileUpload" multiple class="sr-only">
                                        </label>
                                        <p class="pl-1">ou arraste e solte aqui</p>
                                    </div>
                                    <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">PNG, JPG, PDF até 10MB</p>
                                </div>
                             </div>
                        </div>

                        <div v-if="form.documentos.length > 0" class="mt-4">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Arquivos selecionados:</p>
                            <ul class="mt-2 space-y-2">
                                <li v-for="(file, index) in form.documentos" :key="index" class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 p-2 rounded-md">
                                    <span class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ file.name }}</span>
                                    <button @click="removeFile(index)" class="text-red-500 hover:text-red-700">
                                        <X class="w-4 h-4"/>
                                    </button>
                                </li>
                            </ul>
                        </div>


                         <div class="mt-8 flex justify-between items-center">
                            <button @click="voltarEtapa(1)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Trocar Serviço
                            </button>
                             <button @click="prepararConfirmacao" class="action-button">
                                Ir para Confirmação
                                <Check class="w-4 h-4 ml-2" />
                            </button>
                         </div>
                    </div>

                    <!-- Etapa 3: Confirmar e Enviar -->
                    <div v-if="etapa === 3">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-center">Revise sua Solicitação</h3>
                        <p class="text-center text-gray-500 mt-2">Confira os dados abaixo antes de enviar.</p>

                        <div class="mt-8 p-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border dark:border-gray-700 space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Serviço Solicitado</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ servicoSelecionado.nome }}</p>
                            </div>
                            <div v-if="form.observacoes">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Observações</p>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ form.observacoes }}</p>
                            </div>
                             <div v-if="form.documentos.length > 0">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Documentos Anexados</p>
                                <ul class="list-disc list-inside text-gray-700 dark:text-gray-300">
                                    <li v-for="(file, index) in form.documentos" :key="`confirm-${index}`">{{ file.name }}</li>
                                </ul>
                            </div>
                            <div v-else>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Documentos Anexados</p>
                                <p class="text-gray-700 dark:text-gray-300">Nenhum documento anexado.</p>
                            </div>
                        </div>

                        <!-- Bloco de Alerta de Erro -->
                        <div v-if="form.errors.servico_id || form.errors.servico" class="mt-6 flex items-start space-x-3 p-4 bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded-lg">
                            <ShieldAlert class="w-6 h-6 flex-shrink-0" />
                            <div>
                                <h4 class="font-bold">Não foi possível enviar a solicitação</h4>
                                <p class="text-sm">{{ form.errors.servico_id || form.errors.servico }}</p>
                            </div>
                        </div>


                         <div class="mt-8 flex justify-between items-center">
                            <button @click="voltarEtapa(2)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Editar Detalhes
                            </button>
                             <button @click="submit" class="action-button bg-green-600 hover:bg-green-500" :disabled="form.processing">
                                <Sparkles v-if="!form.processing" class="w-5 h-5 mr-2" />
                                {{ form.processing ? 'Enviando...' : 'Confirmar e Enviar Solicitação' }}
                            </button>
                         </div>
                    </div>

                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
.form-input {
    @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4;
    @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400;
    @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500;
    @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400;
    @apply dark:focus:ring-green-500 dark:focus:border-green-500;
}
textarea.form-input {
    @apply h-auto;
}
.action-button {
    @apply inline-flex items-center justify-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50;
}
</style>

