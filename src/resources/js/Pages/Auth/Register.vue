<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

// Lógica (Composables)
import { useRealtimeValidation } from '@/Composables/useRealtimeValidation';
import { useCepLookup } from '@/Composables/useCepLookup';

// UI (Componentes)
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import LgpdModal from '@/Components/LgpdModal.vue';
import AccessDataFields from './Partials/AccessDataFields.vue';
import PersonalDataFields from './Partials/PersonalDataFields.vue';
import AddressFields from './Partials/AddressFields.vue';
import CustomFieldsSection from './Partials/CustomFieldsSection.vue';
import TermsAndPrivacy from './Partials/TermsAndPrivacy.vue';

const props = defineProps({
    customFields: Array,
    // ADICIONADO: Recebe a lista de bairros do backend.
    // Cada bairro deve ser um objeto com 'id' e 'nome'.
    bairros: Array,
});

// --- LÓGICA DE ETAPAS ---
const currentStep = ref(1);
const steps = [
    { id: 1, name: 'Acesso' },
    { id: 2, name: 'Pessoal' },
    { id: 3, name: 'Endereço' },
    { id: 4, name: 'Finalização' }
];

// --- Lógica do Formulário ---
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
    // ADICIONADO: Este campo irá guardar o ID do bairro selecionado.
    bairro_id: '',
    profile_data: generateProfileDataStructure(props.customFields),
});

const formattedDataNascimento = computed({
    get() {
        if (!form.profile_data.data_nascimento) return '';
        const parts = form.profile_data.data_nascimento.split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : form.profile_data.data_nascimento;
    },
    set(value) {
        if (value && value.length === 10) {
            const parts = value.split('/');
            if (parts.length === 3) {
                form.profile_data.data_nascimento = `${parts[2]}-${parts[1]}-${parts[0]}`;
                return;
            }
        }
        form.profile_data.data_nascimento = '';
    }
});

// --- Invocando Composables ---
const { realtimeErrors } = useRealtimeValidation(form, formattedDataNascimento);
const { buscarCep } = useCepLookup(form, realtimeErrors);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

// --- FUNÇÕES DE NAVEGAÇÃO E VALIDAÇÃO POR ETAPA ---
const validateStep1 = () => {
    return form.name && form.email && form.password && form.password_confirmation && !realtimeErrors.value.email && form.password === form.password_confirmation;
};

const validateStep2 = () => {
    return form.cpf && !realtimeErrors.value.cpf &&
           form.profile_data.telefone && !realtimeErrors.value.celular &&
           form.profile_data.data_nascimento && !realtimeErrors.value.data_nascimento;
};

// ATUALIZADO: Agora valida se um bairro_id foi selecionado.
const validateStep3 = () => {
    return form.profile_data.endereco_cep && !realtimeErrors.value.cep &&
           form.profile_data.endereco_cidade && form.bairro_id;
};

const nextStep = () => {
    if (currentStep.value === 1 && validateStep1()) {
        currentStep.value++;
    } else if (currentStep.value === 2 && validateStep2()) {
        currentStep.value++;
    } else if (currentStep.value === 3 && validateStep3()) {
        currentStep.value++;
    } else {
        // Força a validação de todos os campos da etapa atual para exibir os erros
        form.validate();
        console.error("Validação da etapa falhou.");
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

// --- Lógica dos Modais ---
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);
const acceptTerms = () => { form.terms = true; showTermsModal.value = false; };
const acceptPrivacy = () => { form.privacy = true; showPrivacyModal.value = false; };

const page = usePage();
const termsContent = computed(() => page.props.tenant?.terms_of_service || '<p>Termos de serviço não disponíveis.</p>');
const privacyContent = computed(() => {
    const baseText = page.props.tenant?.privacy_policy || '<p>Política de privacidade não disponível.</p>';
    const citizenName = form.name ? `<strong>${form.name}</strong>` : '[Nome Completo do Cidadão]';
    return baseText.replace(/\[Nome Completo do Cidadão\]/g, citizenName);
});

const canReviewTerms = computed(() => currentStep.value === 4);

const tryOpenModal = (modalType) => {
    if (canReviewTerms.value) {
        if (modalType === 'terms') showTermsModal.value = true;
        if (modalType === 'privacy') showPrivacyModal.value = true;
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
                <p class="form-subtitle">Siga as etapas para completar seu cadastro.</p>

                <div class="w-full px-4 py-2">
                    <div class="flex items-center">
                        <template v-for="(step, index) in steps" :key="step.id">
                            <div class="flex flex-col items-center text-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300"
                                    :class="currentStep >= step.id ? 'bg-emerald-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
                                >
                                    <svg v-if="currentStep > step.id" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span v-else>{{ step.id }}</span>
                                </div>
                                <span class="text-xs mt-2" :class="currentStep >= step.id ? 'text-emerald-800 dark:text-green-400 font-semibold' : 'text-gray-500 dark:text-gray-400'">{{ step.name }}</span>
                            </div>
                            <div v-if="index < steps.length - 1" class="flex-1 h-0.5 mx-4 transition-colors duration-300" :class="currentStep > step.id ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700'"></div>
                        </template>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <div v-show="currentStep === 1" class="mt-4">
                        <AccessDataFields :form="form" :realtime-errors="realtimeErrors" />
                    </div>

                    <div v-show="currentStep === 2" class="mt-4">
                        <PersonalDataFields
                            :form="form"
                            :realtime-errors="realtimeErrors"
                            v-model="formattedDataNascimento"
                        />
                    </div>

                    <div v-show="currentStep === 3" class="mt-4">
                        <AddressFields
                            :form="form"
                            :realtime-errors="realtimeErrors"
                            :bairros="props.bairros"
                            @buscar-cep="buscarCep"
                        />
                         <CustomFieldsSection :custom-fields="customFields" :form="form" />
                    </div>

                    <div v-show="currentStep === 4" class="mt-4">
                        <TermsAndPrivacy
                            :form="form"
                            :can-review-terms="canReviewTerms"
                            @open-modal="tryOpenModal"
                        />
                    </div>

                    <div class="pt-8 flex items-center" :class="currentStep > 1 ? 'justify-between' : 'justify-end'">
                        <button
                            type="button"
                            v-if="currentStep > 1"
                            @click="prevStep"
                            class="btn btn-secondary"
                        >
                            Voltar
                        </button>

                        <button
                            type="button"
                            v-if="currentStep < 4"
                            @click="nextStep"
                            class="btn btn-primary"
                        >
                            Avançar
                        </button>

                        <button
                            type="submit"
                            v-if="currentStep === 4"
                            class="btn btn-primary w-full !text-base !font-bold"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing || !form.terms || !form.privacy }"
                            :disabled="form.processing || !form.terms || !form.privacy"
                        >
                            Finalizar Cadastro
                        </button>
                    </div>

                    <div class="text-center mt-4" v-if="currentStep === 4">
                        <Link :href="route('login')" class="text-sm font-medium text-emerald-600 hover:underline dark:text-green-400">
                            Já possui uma conta?
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <LgpdModal title="Termos de Serviço" :show="showTermsModal" @accept="acceptTerms" @close="showTermsModal = false">
        <div v-html="termsContent"></div>
    </LgpdModal>
    <LgpdModal title="Política de Privacidade" :show="showPrivacyModal" @accept="acceptPrivacy" @close="showPrivacyModal = false">
        <div v-html="privacyContent"></div>
    </LgpdModal>
</template>

<style scoped>
/* Estilos existentes */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
.font-sans { font-family: 'Poppins', sans-serif; }
.page-container { @apply dark:bg-[#102C26]/100 min-h-screen transition-colors duration-500; }
.dark .page-container { background: radial-gradient(ellipse at top left, #0D2C2A, #0A1E1C); }
.form-container { @apply relative p-10 pt-16 rounded-3xl shadow-2xl transition-colors duration-500; @apply bg-white/70 border border-gray-200; backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); @apply dark:bg-[#102C26]/100 dark:border-2 dark:border-green-400/25; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-20 h-20 rounded-full flex justify-center items-center shadow-lg transition-all duration-500; @apply bg-emerald-600 shadow-emerald-500/30; @apply dark:bg-[#43DB9E]/20 dark:border-2 dark:border-green-400/30 dark:shadow-green-400/10; }
.form-title { @apply text-3xl font-bold text-center mt-6 mb-2 transition-colors duration-500; @apply text-gray-900 dark:text-white; }
.form-subtitle { @apply text-center mb-6 transition-colors duration-500; @apply text-gray-600 dark:text-gray-400; }

/* Estilos de formulário (usando :deep) */
:deep(.form-section-title) { @apply font-semibold text-gray-700 dark:text-gray-300 text-lg border-b border-emerald-500/20 dark:border-green-400/20 pb-2 mb-6 mt-8; }
:deep(.form-label) { @apply block mb-1.5 text-sm font-medium transition-colors duration-500; @apply text-gray-700 dark:text-gray-300; }
:deep(.input-icon) { @apply absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none transition-colors duration-500; @apply text-gray-400 dark:text-gray-500; }
:deep(.form-input) { @apply block w-full text-sm rounded-xl transition-all duration-300; @apply h-12 py-3.5 pl-11 pr-5; @apply bg-white border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
:deep(select.form-input) { @apply appearance-none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; }
:deep(.dark select.form-input) { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); }
:deep(.form-error) { @apply text-xs mt-1.5 transition-colors duration-500; @apply text-red-600 dark:text-red-400; }
:deep(.input-valid) { @apply border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500; @apply dark:border-green-500 dark:focus:border-green-500 dark:focus:ring-green-500; }
:deep(.input-invalid) { @apply border-red-500 focus:border-red-500 focus:ring-red-500; @apply dark:border-red-400 dark:focus:border-red-400 dark:focus:ring-red-400; }

/* Estilos dos botões */
.btn { @apply px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-4; }
.btn-primary { @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-300; @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400/50; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-300; @apply dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-600; }
</style>

<style>
html {
    /*
      O valor padrão da fonte na maioria dos navegadores é 16px.
      Reduzir este valor faz com que todos os elementos que usam a unidade 'rem'

      - 14px: Reduz o tamanho geral em cerca de 12.5% (bom para um layout mais compacto).
      - 15px: Redução mais sutil.
    */
    font-size: 14px;
}
</style>
