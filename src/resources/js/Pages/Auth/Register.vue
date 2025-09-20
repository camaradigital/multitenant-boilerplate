<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import { User, Mail, KeyRound, Phone, Calendar, MapPin, Hash, X } from 'lucide-vue-next';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';

const props = defineProps({
    customFields: Array, // Nova prop para receber os campos personalizados do backend
});

// --- Lógica do Formulário ---
// Cria a estrutura do `profile_data` com campos fixos e personalizados
const generateProfileDataStructure = (fields) => {
    const profileData = {
        telefone: '', data_nascimento: '', genero: '',
        nome_mae: '', nome_pai: '', endereco_cep: '',
        endereco_logradouro: '', endereco_numero: '', endereco_bairro: '',
        endereco_cidade: '', endereco_estado: '',
    };
    if (fields) {
        fields.forEach(field => {
            profileData[field.name] = '';
        });
    }
    return profileData;
};

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
    privacy: false,
    cpf: '',
    profile_data: generateProfileDataStructure(props.customFields),
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

// --- Lógica do CEP ---
const buscarCep = async () => {
    const cep = form.profile_data.endereco_cep.replace(/\D/g, '');
    if (cep.length === 8) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            if (!data.erro) {
                form.profile_data.endereco_logradouro = data.logradouro;
                form.profile_data.endereco_bairro = data.bairro;
                form.profile_data.endereco_cidade = data.localidade;
                form.profile_data.endereco_estado = data.uf;
            }
        } catch (error) {
            console.error("Erro ao buscar CEP:", error);
        }
    }
};

// --- Lógica dos Modais de LGPD ---
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);

const acceptTerms = () => {
    form.terms = true;
    showTermsModal.value = false;
};

const acceptPrivacy = () => {
    form.privacy = true;
    showPrivacyModal.value = false;
};

const page = usePage();

const termsContent = computed(() => {
    return page.props.tenant?.terms_of_service || '<p>Termos de serviço não disponíveis.</p>';
});

const privacyContent = computed(() => {
    const baseText = page.props.tenant?.privacy_policy || '<p>Política de privacidade não disponível.</p>';
    const citizenName = form.name ? `<strong>${form.name}</strong>` : '[Nome Completo do Cidadão]';
    return baseText.replace(/\[Nome Completo do Cidadão\]/g, citizenName);
});

// --- NOVA LÓGICA ---
// Verifica se os campos essenciais foram preenchidos para habilitar a revisão dos termos.
const isDataFormComplete = computed(() => {
    return form.name && form.email && form.password && form.password_confirmation && form.profile_data.endereco_cidade;
});

// Função para tentar abrir o modal, mostrando um alerta se os dados não estiverem preenchidos.
const tryOpenModal = (modalType) => {
    if (isDataFormComplete.value) {
        if (modalType === 'terms') showTermsModal.value = true;
        if (modalType === 'privacy') showPrivacyModal.value = true;
    } else {
        // Você pode opcionalmente adicionar um feedback mais visível aqui, como um toast.
        // Por enquanto, o link desabilitado já fornece o feedback visual.
        console.log('Preencha os dados obrigatórios primeiro.');
    }
};
</script>

<template>
    <Head title="Registrar" />

    <div class="page-container font-sans">
        <div class="flex flex-col items-center justify-center min-h-screen p-6 py-12">

            <div class="form-container w-full max-w-2xl">

                <div class="form-icon">
                    <AuthenticationCardLogo class="h-full w-full p-1" />

                </div>

                <h1 class="form-title">Criar sua Conta</h1>
                <p class="form-subtitle">Junte-se a nós! É rápido e fácil.</p>

                <form @submit.prevent="submit">
                    <div class="form-section-title">Dados de Acesso</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                        <div class="input-container">
                            <label for="name" class="form-label">Nome Completo</label>
                            <div class="relative">
                                <span class="input-icon"><User :size="16" /></span>
                                <input id="name" v-model="form.name" type="text" class="form-input" required autofocus autocomplete="name" placeholder="Seu nome completo"/>
                            </div>
                            <InputError class="form-error" :message="form.errors.name" />
                        </div>
                        <div class="input-container">
                            <label for="email" class="form-label">E-mail</label>
                            <div class="relative">
                                <span class="input-icon"><Mail :size="16" /></span>
                                <input id="email" v-model="form.email" type="email" class="form-input" required autocomplete="username" placeholder="seuemail@exemplo.com"/>
                            </div>
                            <InputError class="form-error" :message="form.errors.email" />
                        </div>
                        <div class="input-container">
                            <label for="password" class="form-label">Senha</label>
                            <div class="relative">
                                <span class="input-icon"><KeyRound :size="16" /></span>
                                <input id="password" v-model="form.password" type="password" class="form-input" required autocomplete="new-password" placeholder="Crie uma senha forte"/>
                            </div>
                            <InputError class="form-error" :message="form.errors.password" />
                        </div>
                        <div class="input-container">
                            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                            <div class="relative">
                                <span class="input-icon"><KeyRound :size="16" /></span>
                                <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="form-input" required autocomplete="new-password" placeholder="Repita a senha"/>
                            </div>
                            <InputError class="form-error" :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                    <div class="form-section-title">Dados Pessoais</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                        <div class="input-container">
                            <label for="cpf" class="form-label">CPF</label>
                            <div class="relative">
                                <span class="input-icon"><Hash :size="16" /></span>
                                <input id="cpf" v-model="form.cpf" type="text" class="form-input" placeholder="000.000.000-00"/>
                            </div>
                            <InputError class="form-error" :message="form.errors.cpf" />
                        </div>
                        <div class="input-container">
                            <label for="telefone" class="form-label">Telefone</label>
                            <div class="relative">
                                <span class="input-icon"><Phone :size="16" /></span>
                                <input id="telefone" v-model="form.profile_data.telefone" type="text" class="form-input" placeholder="(00) 90000-0000"/>
                            </div>
                            <InputError class="form-error" :message="form.errors['profile_data.telefone']" />
                        </div>
                        <div class="input-container">
                            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                            <div class="relative">
                                <span class="input-icon"><Calendar :size="16" /></span>
                                <input id="data_nascimento" v-model="form.profile_data.data_nascimento" type="date" class="form-input"/>
                            </div>
                            <InputError class="form-error" :message="form.errors['profile_data.data_nascimento']" />
                        </div>
                        <div class="input-container">
                            <label for="genero" class="form-label">Gênero</label>
                            <select v-model="form.profile_data.genero" id="genero" class="form-input !pl-5">
                                <option value="">Não informar</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="input-container md:col-span-2">
                            <label for="nome_mae" class="form-label">Nome da Mãe</label>
                            <div class="relative">
                                <span class="input-icon"><User :size="16" /></span>
                                <input id="nome_mae" v-model="form.profile_data.nome_mae" type="text" class="form-input" placeholder="Nome completo da mãe"/>
                            </div>
                        </div>
                        <div class="input-container md:col-span-2">
                            <label for="nome_pai" class="form-label">Nome do Pai (Opcional)</label>
                            <div class="relative">
                                <span class="input-icon"><User :size="16" /></span>
                                <input id="nome_pai" v-model="form.profile_data.nome_pai" type="text" class="form-input" placeholder="Nome completo do pai"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-section-title">Endereço</div>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-x-6 gap-y-6">
                        <div class="input-container md:col-span-2">
                            <label for="cep" class="form-label">CEP</label>
                            <div class="relative">
                                <span class="input-icon"><MapPin :size="16" /></span>
                                <input id="cep" v-model="form.profile_data.endereco_cep" @blur="buscarCep" type="text" class="form-input" placeholder="00000-000"/>
                            </div>
                        </div>
                        <div class="input-container md:col-span-4">
                            <label for="logradouro" class="form-label">Logradouro</label>
                            <input id="logradouro" v-model="form.profile_data.endereco_logradouro" type="text" class="form-input !pl-5" placeholder="Rua, Avenida..."/>
                        </div>
                        <div class="input-container md:col-span-2">
                            <label for="numero" class="form-label">Número</label>
                            <input id="numero" v-model="form.profile_data.endereco_numero" type="text" class="form-input !pl-5" placeholder="Ex: 123"/>
                        </div>
                        <div class="input-container md:col-span-4">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input id="bairro" v-model="form.profile_data.endereco_bairro" type="text" class="form-input !pl-5" placeholder="Seu bairro"/>
                        </div>
                        <div class="input-container md:col-span-4">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input id="cidade" v-model="form.profile_data.endereco_cidade" type="text" class="form-input !pl-5" placeholder="Sua cidade" required/>
                            <InputError class="form-error" :message="form.errors['profile_data.endereco_cidade']" />
                        </div>
                        <div class="input-container md:col-span-2">
                            <label for="estado" class="form-label">Estado</label>
                            <input id="estado" v-model="form.profile_data.endereco_estado" type="text" class="form-input !pl-5" placeholder="UF"/>
                        </div>
                    </div>

                    <div v-if="customFields && customFields.length > 0">
                        <div class="form-section-title">Informações Adicionais</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                            <div v-for="field in customFields" :key="field.id" class="input-container">
                                <label :for="field.name" class="form-label">{{ field.label }}</label>
                                <div class="relative">
                                    <input v-if="['text', 'number', 'date'].includes(field.type)" :type="field.type" v-model="form.profile_data[field.name]" :id="field.name" class="form-input !pl-5" :required="field.is_required" />
                                    <select v-if="field.type === 'select'" v-model="form.profile_data[field.name]" :id="field.name" class="form-input !pl-5" :required="field.is_required">
                                        <option value="">Selecione</option>
                                        <option v-for="option in JSON.parse(field.options)" :key="option" :value="option">{{ option }}</option>
                                    </select>
                                </div>
                                <InputError v-if="form.errors[`profile_data.${field.name}`]" class="form-error" :message="form.errors[`profile_data.${field.name}`]" />
                            </div>
                        </div>
                    </div>

                    <div class="form-section-title">Termos e Privacidade</div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-4 mb-4">Após preencher seus dados, revise e aceite os termos para continuar.</p>
                    <div class="space-y-4">
                        <div class="flex items-center p-3 rounded-xl transition-colors duration-300" :class="form.terms ? 'bg-emerald-50 dark:bg-green-500/10 border border-emerald-200 dark:border-green-500/20' : 'bg-gray-100 dark:bg-gray-800/50'">
                            <svg v-if="form.terms" class="w-6 h-6 text-emerald-500 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <svg v-else class="w-6 h-6 text-gray-400 dark:text-gray-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>

                            <p class="text-sm flex-1" :class="form.terms ? 'text-emerald-800 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'">
                                <template v-if="form.terms">
                                    <span class="font-semibold">Você aceitou os Termos de Serviço.</span>
                                </template>
                                <template v-else>
                                    Para continuar, você deve ler e aceitar os
                                    <a href="#" @click.prevent="tryOpenModal('terms')"
                                       class="font-semibold underline"
                                       :class="isDataFormComplete ? 'text-emerald-600 dark:text-green-400 hover:text-emerald-500 cursor-pointer' : 'text-gray-400 dark:text-gray-600 cursor-not-allowed'"
                                       title="Preencha os dados obrigatórios do formulário primeiro">
                                       Termos de Serviço
                                    </a>.
                                </template>
                            </p>
                        </div>
                        <InputError class="form-error !ml-4" :message="form.errors.terms" />

                        <div class="flex items-center p-3 rounded-xl transition-colors duration-300" :class="form.privacy ? 'bg-emerald-50 dark:bg-green-500/10 border border-emerald-200 dark:border-green-500/20' : 'bg-gray-100 dark:bg-gray-800/50'">
                            <svg v-if="form.privacy" class="w-6 h-6 text-emerald-500 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <svg v-else class="w-6 h-6 text-gray-400 dark:text-gray-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>

                            <p class="text-sm flex-1" :class="form.privacy ? 'text-emerald-800 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'">
                                <template v-if="form.privacy">
                                    <span class="font-semibold">Você aceitou a Política de Privacidade.</span>
                                </template>
                                <template v-else>
                                    E também a nossa
                                    <a href="#" @click.prevent="tryOpenModal('privacy')"
                                       class="font-semibold underline"
                                       :class="isDataFormComplete ? 'text-emerald-600 dark:text-green-400 hover:text-emerald-500 cursor-pointer' : 'text-gray-400 dark:text-gray-600 cursor-not-allowed'"
                                       title="Preencha os dados obrigatórios do formulário primeiro">
                                       Política de Privacidade
                                    </a>.
                                </template>
                            </p>
                        </div>
                        <InputError class="form-error !ml-4" :message="form.errors.privacy" />
                    </div>

                    <div class="pt-8 space-y-4">
                        <button type="submit" class="btn btn-primary w-full !text-base !font-bold flex items-center justify-center" :class="{ 'opacity-50 cursor-not-allowed': form.processing || !form.terms || !form.privacy }" :disabled="form.processing || !form.terms || !form.privacy">
                            Finalizar Cadastro
                        </button>

                        <div class="text-center">
                            <Link :href="route('login')" class="text-sm font-medium text-emerald-600 hover:underline dark:text-green-400">
                                Já possui uma conta?
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <transition name="modal-fade">
            <div v-if="showTermsModal" class="modal-backdrop" @click.self="showTermsModal = false">
                <div class="modal-content" @click.stop>
                    <div class="modal-header">
                        <h2 class="modal-title">Termos de Serviço</h2>
                        <button @click="showTermsModal = false" class="modal-close-button"><X :size="24" /></button>
                    </div>
                    <div class="modal-body" v-html="termsContent"></div>
                    <div class="modal-footer">
                        <button @click="showTermsModal = false" class="btn btn-secondary mr-4">Não Aceito</button>
                        <button @click="acceptTerms" class="btn btn-primary">Aceito os Termos</button>
                    </div>
                </div>
            </div>
        </transition>

        <transition name="modal-fade">
            <div v-if="showPrivacyModal" class="modal-backdrop" @click.self="showPrivacyModal = false">
                <div class="modal-content" @click.stop>
                    <div class="modal-header">
                        <h2 class="modal-title">Política de Privacidade</h2>
                        <button @click="showPrivacyModal = false" class="modal-close-button"><X :size="24" /></button>
                    </div>
                    <div class="modal-body" v-html="privacyContent"></div>
                    <div class="modal-footer">
                        <button @click="showPrivacyModal = false" class="btn btn-secondary mr-4">Não Aceito</button>
                        <button @click="acceptPrivacy" class="btn btn-primary">Aceito a Política</button>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<style scoped>
/* Estilos Padrão do Tema */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
.font-sans { font-family: 'Poppins', sans-serif; }
.page-container { @apply dark:bg-[#102C26]/100 min-h-screen transition-colors duration-500; }
.dark .page-container { background: radial-gradient(ellipse at top left, #0D2C2A, #0A1E1C); }
.form-container { @apply relative p-10 pt-16 rounded-3xl shadow-2xl transition-colors duration-500; @apply bg-white/70 border border-gray-200; backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); @apply dark:bg-[#102C26]/100 dark:border-2 dark:border-green-400/25; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-20 h-20 rounded-full flex justify-center items-center shadow-lg transition-all duration-500; @apply bg-emerald-600 shadow-emerald-500/30; @apply dark:bg-[#43DB9E]/20 dark:border-2 dark:border-green-400/30 dark:shadow-green-400/10; }
.form-title { @apply text-3xl font-bold text-center mt-6 mb-2 transition-colors duration-500; @apply text-gray-900 dark:text-white; }
.form-subtitle { @apply text-center mb-10 transition-colors duration-500; @apply text-gray-600 dark:text-gray-400; }
.form-section-title { @apply font-semibold text-gray-700 dark:text-gray-300 text-lg border-b border-emerald-500/20 dark:border-green-400/20 pb-2 mb-6 mt-8; }
.form-label { @apply block mb-1.5 text-sm font-medium transition-colors duration-500; @apply text-gray-700 dark:text-gray-300; }
.input-icon { @apply absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none transition-colors duration-500; @apply text-gray-400 dark:text-gray-500; }
.form-input { @apply block w-full text-sm rounded-xl transition-all duration-300; @apply h-12 py-3.5 pl-11 pr-5; @apply bg-white border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
select.form-input { @apply appearance-none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; }
.dark select.form-input { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); }
.form-error { @apply text-xs mt-1.5 transition-colors duration-500; @apply text-red-600 dark:text-red-400; }
.btn { @apply px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-4; }
.btn-primary { @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-300; @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400/50; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-300; @apply dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-600; }

/* --- NOVOS ESTILOS PARA O MODAL --- */
.modal-backdrop { @apply fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-50; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); }
.modal-content { @apply w-full max-w-3xl max-h-[90vh] flex flex-col p-8 rounded-2xl shadow-2xl; @apply bg-white dark:bg-[#102C26] dark:border dark:border-green-400/25; }
.modal-header { @apply flex justify-between items-center pb-4 mb-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0; }
.modal-title { @apply text-2xl font-bold; @apply text-gray-900 dark:text-white; }
.modal-close-button { @apply text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white transition-colors; }
.modal-body { @apply overflow-y-auto pr-4 text-gray-700 dark:text-gray-300 text-sm; }
.modal-footer { @apply flex justify-end pt-6 mt-auto flex-shrink-0 border-t border-gray-200 dark:border-gray-700; }
.modal-body::-webkit-scrollbar { width: 8px; }
.modal-body::-webkit-scrollbar-track { @apply bg-gray-200 dark:bg-gray-800 rounded-lg; }
.modal-body::-webkit-scrollbar-thumb { @apply bg-gray-400 dark:bg-gray-600 rounded-lg; }
.modal-body::-webkit-scrollbar-thumb:hover { @apply bg-gray-500 dark:bg-gray-500; }
.modal-body :deep(h2) { @apply text-xl font-semibold mt-4 mb-2 text-emerald-700 dark:text-green-400; }
.modal-body :deep(ul) { @apply list-disc list-inside space-y-2 my-2; }
.modal-body :deep(p) { @apply mb-3 leading-relaxed; }
.modal-body :deep(strong) { @apply font-semibold text-gray-800 dark:text-gray-200; }

.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }
</style>
