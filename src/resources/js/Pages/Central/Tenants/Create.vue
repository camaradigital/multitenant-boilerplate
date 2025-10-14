<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { watch, ref } from 'vue';
import {
    Building2, Mail, Link as LinkIcon, UserPlus, FileText,
    MapPin, Home, Palette, Image as ImageIcon, Globe, Hash, Tag,
    LoaderCircle, Search
} from 'lucide-vue-next';

// --- Estado e Formulário ---

// Controla o estado de "carregando" da consulta de CNPJ para feedback visual
const isFetchingCnpj = ref(false);
const logoPreview = ref(null); // <-- ADICIONE ESTA LINHA

// Formulário do Inertia com todos os campos necessários
const form = useForm({
    name: '', // CORRIGIDO
    cnpj: '',
    subdomain: '',
    admin_email: '',
    endereco_cep: '',
    endereco_logradouro: '',
    endereco_numero: '',
    endereco_complemento: '',
    endereco_bairro: '',
    endereco_cidade: '',
    endereco_estado: '',
    logotipo: null,
    site_url: '',
    cor_primaria: '#000000',
    cor_secundaria: '#FFFFFF',
});

const updateLogoPreview = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    form.logotipo = file; // Atribui o arquivo ao formulário
    logoPreview.value = URL.createObjectURL(file); // Cria a URL de preview
};

// --- Funções de Consulta ---

/**
 * Busca os dados do CNPJ no backend e preenche o formulário.
 * É acionada pelo clique no botão "Buscar".
 * @param {string} cnpjValue - O valor do CNPJ do formulário.
 */
const handleBuscaCnpj = async () => {
    const cnpj = (form.cnpj || '').replace(/\D/g, ''); // Limpa o CNPJ

    // Valida se o CNPJ está completo antes de buscar
    if (cnpj.length !== 14) {
        alert("Por favor, preencha o CNPJ completo com 14 dígitos.");
        return;
    }

    isFetchingCnpj.value = true;
    try {
        // Chama a rota do backend Laravel
        const response = await fetch(route('api.cnpj.consulta', { cnpj }));
        const data = await response.json();

        if (response.ok) {
            // Preenche os campos do formulário com os dados recebidos
            form.name = data.company?.name || ''; // CORRIGIDO
            form.admin_email = (data.emails && data.emails.length > 0) ? data.emails[0].address : '';
            form.endereco_cep = data.address?.zip || '';
            form.endereco_logradouro = data.address?.street || '';
            form.endereco_numero = data.address?.number || '';
            form.endereco_complemento = data.address?.details || '';
            form.endereco_bairro = data.address?.district || '';
            form.endereco_cidade = data.address?.city || '';
            form.endereco_estado = data.address?.state || '';
        } else {
            alert(data.error || "CNPJ não encontrado ou inválido.");
        }
    } catch (error) {
        console.error("Falha na requisição para buscar CNPJ:", error);
        alert("Ocorreu um erro de comunicação ao tentar consultar o CNPJ.");
    } finally {
        isFetchingCnpj.value = false;
    }
};

/**
 * Busca o endereço a partir do CEP usando a API ViaCEP.
 * @param {string} cepValue - O valor do CEP digitado.
 */
const buscarCep = async (cepValue) => {
    const cep = (cepValue || '').replace(/\D/g, '');
    if (cep.length === 8) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            if (!data.erro) {
                form.endereco_logradouro = data.logradouro;
                form.endereco_bairro = data.bairro;
                form.endereco_cidade = data.localidade;
                form.endereco_estado = data.uf;
            }
        } catch (error) {
            console.error("Erro ao buscar CEP:", error);
        }
    }
};


// --- Observadores (Watchers) ---

// Observa o campo CNPJ apenas para aplicar a máscara de formatação
watch(() => form.cnpj, (newValue) => {
    let v = (newValue || '').replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/, '$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
    v = v.replace(/(\d{4})(\d)/, '$1-$2');
    form.cnpj = v.substring(0, 18);
});

// Observa o campo CEP para disparar a busca de endereço
watch(() => form.endereco_cep, (newValue) => {
    buscarCep(newValue);
});


// --- Submissão do Formulário ---

const submit = () => {
    form.post(route('central.tenants.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Criar Nova Câmara" />

    <AppLayout title="Nova Câmara">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Cadastro de Nova Câmara
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <form @submit.prevent="submit" class="content-container w-full max-w-3xl">

                <div class="form-icon">
                    <UserPlus :size="32" class="icon-in-badge" />
                </div>

                <div class="p-6 md:p-8">
                    <div class="text-center mb-8">
                        <h2 class="header-title">Informações da Nova Câmara</h2>
                        <p class="form-subtitle">Preencha os dados para registrar uma nova instância.</p>
                    </div>

                    <fieldset class="space-y-6">
                        <legend class="section-title">Dados Principais</legend>

                        <div>
                            <label for="cnpj" class="form-label">CNPJ</label>
                            <div class="flex items-center gap-x-2">
                                <div class="form-input-container flex-grow">
                                    <FileText class="form-input-icon" />
                                    <input id="cnpj" v-model="form.cnpj" type="text" class="form-input" required autofocus placeholder="00.000.000/0000-00">
                                </div>
                                <button @click="handleBuscaCnpj" type="button" class="btn-secondary h-12 px-4" :disabled="isFetchingCnpj">
                                    <LoaderCircle v-if="isFetchingCnpj" class="animate-spin h-5 w-5" />
                                    <span v-else class="flex items-center gap-x-2">
                                        <Search :size="16" />
                                        Buscar
                                    </span>
                                </button>
                            </div>
                            <div v-if="form.errors.cnpj" class="form-error">{{ form.errors.cnpj }}</div>
                        </div>

                        <div>
                            <!-- CORRIGIDO AQUI (label for, id, v-model, e form.errors) -->
                            <label for="name" class="form-label">Nome da Câmara</label>
                            <div class="form-input-container">
                                <Building2 class="form-input-icon" />
                                <input id="name" v-model="form.name" type="text" class="form-input" required>
                            </div>
                            <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="subdomain" class="form-label">Subdomínio</label>
                            <div class="form-input-container">
                                <LinkIcon class="form-input-icon" />
                                <input id="subdomain" v-model="form.subdomain" type="text" class="form-input" required>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Ex: `camara-mg`. O acesso será `camara-mg.camaradigital.app`</p>
                            <div v-if="form.errors.subdomain" class="form-error">{{ form.errors.subdomain }}</div>
                        </div>

                        <div>
                            <label for="admin_email" class="form-label">E-mail do Administrador Local</label>
                            <div class="form-input-container">
                                <Mail class="form-input-icon" />
                                <input id="admin_email" v-model="form.admin_email" type="email" class="form-input" required>
                            </div>
                            <div v-if="form.errors.admin_email" class="form-error">{{ form.errors.admin_email }}</div>
                        </div>
                    </fieldset>

                    <fieldset class="space-y-6 mt-10">
                        <legend class="section-title">Endereço</legend>
                        <div>
                            <label for="endereco_cep" class="form-label">CEP</label>
                            <div class="form-input-container">
                                <MapPin class="form-input-icon" />
                                <input id="endereco_cep" v-model="form.endereco_cep" type="text" class="form-input" placeholder="00000-000">
                            </div>
                            <div v-if="form.errors.endereco_cep" class="form-error">{{ form.errors.endereco_cep }}</div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <label for="endereco_logradouro" class="form-label">Logradouro</label>
                                <div class="form-input-container">
                                    <Home class="form-input-icon" />
                                    <input id="endereco_logradouro" v-model="form.endereco_logradouro" type="text" class="form-input">
                                </div>
                                <div v-if="form.errors.endereco_logradouro" class="form-error">{{ form.errors.endereco_logradouro }}</div>
                            </div>
                            <div>
                                <label for="endereco_numero" class="form-label">Número</label>
                                <div class="form-input-container">
                                    <Hash class="form-input-icon" />
                                    <input id="endereco_numero" v-model="form.endereco_numero" type="text" class="form-input">
                                </div>
                                <div v-if="form.errors.endereco_numero" class="form-error">{{ form.errors.endereco_numero }}</div>
                            </div>
                        </div>
                        <div>
                            <label for="endereco_complemento" class="form-label">Complemento</label>
                            <div class="form-input-container">
                                <Tag class="form-input-icon" />
                                <input id="endereco_complemento" v-model="form.endereco_complemento" type="text" class="form-input">
                            </div>
                            <div v-if="form.errors.endereco_complemento" class="form-error">{{ form.errors.endereco_complemento }}</div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="endereco_bairro" class="form-label">Bairro</label>
                                <div class="form-input-container">
                                    <MapPin class="form-input-icon" />
                                    <input id="endereco_bairro" v-model="form.endereco_bairro" type="text" class="form-input">
                                </div>
                                <div v-if="form.errors.endereco_bairro" class="form-error">{{ form.errors.endereco_bairro }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <label for="endereco_cidade" class="form-label">Cidade</label>
                                <div class="form-input-container">
                                    <MapPin class="form-input-icon" />
                                    <input id="endereco_cidade" v-model="form.endereco_cidade" type="text" class="form-input">
                                </div>
                                <div v-if="form.errors.endereco_cidade" class="form-error">{{ form.errors.endereco_cidade }}</div>
                            </div>
                        </div>
                        <div>
                            <label for="endereco_estado" class="form-label">Estado (UF)</label>
                            <div class="form-input-container">
                                <MapPin class="form-input-icon" />
                                <input id="endereco_estado" v-model="form.endereco_estado" type="text" class="form-input">
                            </div>
                            <div v-if="form.errors.endereco_estado" class="form-error">{{ form.errors.endereco_estado }}</div>
                        </div>
                    </fieldset>

                    <fieldset class="space-y-6 mt-10">
                        <legend class="section-title">Personalização do Portal</legend>
                        <div>
                            <label for="logotipo" class="form-label">Logotipo</label>

                            <div v-if="logoPreview" class="my-4">
                                <img :src="logoPreview" alt="Pré-visualização do logotipo" class="h-20 w-auto rounded-lg border border-gray-300 dark:border-gray-600 p-1">
                            </div>

                            <label for="logotipo" class="btn-secondary h-12 px-4 cursor-pointer w-full sm:w-auto">
                                <ImageIcon :size="16" class="mr-2" />
                                {{ logoPreview ? 'Trocar Arquivo' : 'Selecionar Arquivo' }}
                            </label>
                            <input
                                id="logotipo"
                                type="file"
                                class="hidden"
                                @change="updateLogoPreview"
                                accept="image/png, image/jpeg, image/svg+xml"
                            >

                            <div v-if="form.errors.logotipo" class="form-error">{{ form.errors.logotipo }}</div>
                        </div>
                        <div>
                            <label for="site_url" class="form-label">URL do Site Oficial</label>
                            <div class="form-input-container">
                                <Globe class="form-input-icon" />
                                <input id="site_url" v-model="form.site_url" type="url" class="form-input" placeholder="https://exemplo.com">
                            </div>
                            <div v-if="form.errors.site_url" class="form-error">{{ form.errors.site_url }}</div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="cor_primaria" class="form-label">Cor Primária</label>
                                <div class="form-input-container">
                                    <input id="cor_primaria" v-model="form.cor_primaria" type="color" class="p-1 h-12 w-full block bg-gray-50 border border-gray-300 dark:bg-[#102523] dark:border-[#2a413d] cursor-pointer rounded-xl">
                                </div>
                                <div v-if="form.errors.cor_primaria" class="form-error">{{ form.errors.cor_primaria }}</div>
                            </div>
                            <div>
                                <label for="cor_secundaria" class="form-label">Cor Secundária</label>
                                <div class="form-input-container">
                                    <input id="cor_secundaria" v-model="form.cor_secundaria" type="color" class="p-1 h-12 w-full block bg-gray-50 border border-gray-300 dark:bg-[#102523] dark:border-[#2a413d] cursor-pointer rounded-xl">
                                </div>
                                <div v-if="form.errors.cor_secundaria" class="form-error">{{ form.errors.cor_secundaria }}</div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="p-6 border-t-dynamic bg-gray-50 dark:bg-green-500/5 rounded-b-3xl flex justify-end">
                    <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="form.processing || isFetchingCnpj">
                        <span v-if="form.processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processando...
                        </span>
                        <span v-else>Criar Câmara</span>
                    </button>
                </div>
            </form>
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

.form-label {
    @apply block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300;
}

.form-input-container {
    @apply relative;
}

.form-input {
    @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 pl-11 pr-4;
    @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400;
    @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500;
    @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500;
    @apply dark:focus:ring-green-500 dark:focus:border-green-500;
}

.form-input-icon {
    @apply absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 h-5 w-5 pointer-events-none;
}

.form-error {
    @apply text-sm text-red-600 dark:text-red-400 mt-2;
}

.btn-primary {
    @apply flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-sm uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2;
    @apply focus:ring-offset-white dark:focus:ring-offset-[#0A1E1C];
    @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500;
    @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400;
    @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

/* ESTILO ADICIONADO PARA O BOTÃO "BUSCAR" */
.btn-secondary {
    @apply flex-shrink-0 flex items-center justify-center rounded-xl font-semibold text-sm transition-all;
    @apply bg-gray-200 text-gray-700 hover:bg-gray-300;
    @apply dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600;
    @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
}
input[type="color"]::-webkit-color-swatch {
    border: none;
    border-radius: 0.65rem;
}
input[type="color"]::-moz-color-swatch {
    border: none;
    border-radius: 0.65rem;
}
</style>
