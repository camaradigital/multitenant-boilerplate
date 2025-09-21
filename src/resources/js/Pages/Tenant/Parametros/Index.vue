<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { SlidersHorizontal, Image as ImageIcon, Phone, MessageCircle, Mail, Instagram, Youtube } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps({
    tenant: Object,
});

// Referência para a pré-visualização do logotipo
const logoPreview = ref(props.tenant.logotipo_url ? `/storage/${props.tenant.logotipo_url}` : null);

const form = useForm({
    _method: 'PUT',
    name: props.tenant.name,
    site_url: props.tenant.site_url,
    cor_primaria: props.tenant.cor_primaria || '#4ade80',
    cor_secundaria: props.tenant.cor_secundaria || '#15803d',
    logotipo: null, // Para o upload do arquivo

    // --- INÍCIO DA MODIFICAÇÃO ---
    telefone_contato: props.tenant.telefone_contato || '',
    whatsapp: props.tenant.whatsapp || '',
    email_contato: props.tenant.email_contato || '',
    instagram: props.tenant.instagram || '',
    youtube: props.tenant.youtube || '',
    // --- FIM DA MODIFICAÇÃO ---

    permite_cadastro_cidade_externa: props.tenant.permite_cadastro_cidade_externa,
    limite_renda_juridico: props.tenant.limite_renda_juridico,
    exigir_renda_juridico: props.tenant.exigir_renda_juridico,
    publicar_achados_e_perdidos: props.tenant.publicar_achados_e_perdidos,
    publicar_pessoas_desaparecidas: props.tenant.publicar_pessoas_desaparecidas,
    publicar_memoria_legislativa: props.tenant.publicar_memoria_legislativa,
    publicar_vagas_emprego: props.tenant.publicar_vagas_emprego,
    terms_of_service: props.tenant.terms_of_service || '',
    privacy_policy: props.tenant.privacy_policy || '',
});

// Função para atualizar o preview do logotipo
const updateLogoPreview = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    form.logotipo = file;
    logoPreview.value = URL.createObjectURL(file);
};

watch(() => props.tenant, (newTenantValues) => {
    form.defaults(newTenantValues);
    form.reset();
    logoPreview.value = newTenantValues.logotipo_url ? `/storage/${newTenantValues.logotipo_url}` : null;
}, { deep: true });

const activeTab = ref('identidade');

const tabs = [
    { id: 'identidade', name: 'Identidade Visual' },
    // --- INÍCIO DA MODIFICAÇÃO ---
    { id: 'contato', name: 'Contato e Redes' },
    // --- FIM DA MODIFICAÇÃO ---
    { id: 'regras', name: 'Regras de Negócio' },
    { id: 'visibilidade', name: 'Visibilidade do Portal' },
    { id: 'legal', name: 'Documentos Legais' },
];

const submit = () => {
    form.post(route('admin.parametros.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Parâmetros Gerais" />
    <TenantLayout title="Parâmetros Gerais">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Parâmetros Gerais
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-4xl">
                <div class="form-icon"><SlidersHorizontal :size="32" class="icon-in-badge" /></div>

                <form @submit.prevent="submit">
                    <div class="p-6 md:p-8">
                        <h2 class="header-title">Configurações do Sistema</h2>
                        <p class="form-subtitle">Ajuste a identidade visual e as regras de negócio do seu portal.</p>

                        <div class="mt-6 border-b border-gray-200 dark:border-gray-700">
                            <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                                <button v-for="tab in tabs"
                                        :key="tab.id"
                                        @click.prevent="activeTab = tab.id"
                                        type="button"
                                        :class="[activeTab === tab.id ? 'tab-active' : 'tab-inactive']">
                                    {{ tab.name }}
                                </button>
                            </nav>
                        </div>

                        <div class="mt-6">
                            <div v-show="activeTab === 'identidade'" class="space-y-6">
                                <div class="section-title">Identidade Visual</div>
                                <div>
                                    <label for="name" class="form-label">Nome da Instituição</label>
                                    <input id="name" v-model="form.name" type="text" class="form-input" required />
                                    <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                                </div>

                                <div>
                                    <label for="logotipo" class="form-label">Logotipo</label>
                                    <div v-if="logoPreview" class="my-4">
                                        <img :src="logoPreview" alt="Pré-visualização do logotipo" class="h-20 w-auto rounded-lg border border-gray-300 dark:border-gray-600 p-1">
                                    </div>
                                    <label for="logotipo" class="btn-secondary h-12 px-4 cursor-pointer inline-flex items-center">
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
                                    <input id="site_url" v-model="form.site_url" type="url" class="form-input" placeholder="https://..." />
                                    <div v-if="form.errors.site_url" class="form-error">{{ form.errors.site_url }}</div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label for="cor_primaria" class="form-label">Cor Primária</label>
                                        <div class="color-input-wrapper">
                                            <input id="cor_primaria" v-model="form.cor_primaria" type="color" class="form-color-input" />
                                            <span class="text-sm font-mono text-gray-600 dark:text-gray-400">{{ form.cor_primaria }}</span>
                                        </div>
                                        <div v-if="form.errors.cor_primaria" class="form-error">{{ form.errors.cor_primaria }}</div>
                                    </div>
                                    <div>
                                        <label for="cor_secundaria" class="form-label">Cor Secundária</label>
                                        <div class="color-input-wrapper">
                                            <input id="cor_secundaria" v-model="form.cor_secundaria" type="color" class="form-color-input" />
                                            <span class="text-sm font-mono text-gray-600 dark:text-gray-400">{{ form.cor_secundaria }}</span>
                                        </div>
                                        <div v-if="form.errors.cor_secundaria" class="form-error">{{ form.errors.cor_secundaria }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- --- INÍCIO DA MODIFICAÇÃO --- -->
                            <div v-show="activeTab === 'contato'" class="space-y-6">
                                <div class="section-title">Informações de Contato e Redes Sociais</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label for="telefone_contato" class="form-label">Telefone de Contato</label>
                                        <input id="telefone_contato" v-model="form.telefone_contato" type="text" class="form-input" placeholder="(00) 0000-0000" />
                                        <div v-if="form.errors.telefone_contato" class="form-error">{{ form.errors.telefone_contato }}</div>
                                    </div>
                                    <div>
                                        <label for="whatsapp" class="form-label">WhatsApp</label>
                                        <input id="whatsapp" v-model="form.whatsapp" type="text" class="form-input" placeholder="(00) 90000-0000" />
                                        <div v-if="form.errors.whatsapp" class="form-error">{{ form.errors.whatsapp }}</div>
                                    </div>
                                </div>

                                <div>
                                    <label for="email_contato" class="form-label">E-mail de Contato Público</label>
                                    <input id="email_contato" v-model="form.email_contato" type="email" class="form-input" placeholder="contato@camara.gov.br" />
                                    <div v-if="form.errors.email_contato" class="form-error">{{ form.errors.email_contato }}</div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label for="instagram" class="form-label">URL do Instagram</label>
                                        <input id="instagram" v-model="form.instagram" type="url" class="form-input" placeholder="https://instagram.com/..." />
                                        <div v-if="form.errors.instagram" class="form-error">{{ form.errors.instagram }}</div>
                                    </div>
                                    <div>
                                        <label for="youtube" class="form-label">URL do YouTube</label>
                                        <input id="youtube" v-model="form.youtube" type="url" class="form-input" placeholder="https://youtube.com/..." />
                                        <div v-if="form.errors.youtube" class="form-error">{{ form.errors.youtube }}</div>
                                    </div>
                                </div>
                            </div>
                            <!-- --- FIM DA MODIFICAÇÃO --- -->

                            <div v-show="activeTab === 'regras'" class="space-y-6">
                                <div class="section-title">Regras de Negócio</div>
                                <div class="flex items-start">
                                    <div class="flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Cadastro de Cidadãos</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Permitir o cadastro de cidadãos que residem em cidades diferentes da cidade sede da Câmara.</p>
                                    </div>
                                    <input id="permite_cadastro" v-model="form.permite_cadastro_cidade_externa" type="checkbox" class="form-checkbox ml-4">
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Exigir Renda para Serviços Jurídicos</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Se ativado, será obrigatório informar a renda familiar ao solicitar um serviço jurídico.</p>
                                    </div>
                                    <input id="exigir_renda" v-model="form.exigir_renda_juridico" type="checkbox" class="form-checkbox ml-4">
                                </div>
                                <div>
                                    <label for="limite_renda_juridico" class="form-label">Limite de Renda para Serviços Jurídicos (R$)</label>
                                    <input id="limite_renda_juridico" v-model="form.limite_renda_juridico" type="number" step="0.01" class="form-input" placeholder="Ex: 2824.00" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Defina 0 para desativar o bloqueio por renda.</p>
                                    <div v-if="form.errors.limite_renda_juridico" class="form-error">{{ form.errors.limite_renda_juridico }}</div>
                                </div>
                            </div>

                            <div v-show="activeTab === 'visibilidade'" class="space-y-6">
                                <div class="section-title">Visibilidade de Módulos no Portal</div>
                                 <div class="flex items-start">
                                    <div class="flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Publicar Achados e Perdidos</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Se ativado, o link para "Documentos Perdidos" aparecerá no portal público.</p>
                                    </div>
                                    <input id="publicar_achados" v-model="form.publicar_achados_e_perdidos" type="checkbox" class="form-checkbox ml-4">
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Publicar Pessoas Desaparecidas</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Se ativado, o link para "Pessoas Desaparecidas" aparecerá no portal público.</p>
                                    </div>
                                    <input id="publicar_desaparecidos" v-model="form.publicar_pessoas_desaparecidas" type="checkbox" class="form-checkbox ml-4">
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Publicar Memória Legislativa</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Se ativado, o link para "Memória Legislativa" aparecerá no portal público.</p>
                                    </div>
                                    <input id="publicar_memoria" v-model="form.publicar_memoria_legislativa" type="checkbox" class="form-checkbox ml-4">
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Publicar Vagas de Emprego</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Se ativado, o link para "Vagas de Emprego" aparecerá no portal público.</p>
                                    </div>
                                    <input id="publicar_vagas" v-model="form.publicar_vagas_emprego" type="checkbox" class="form-checkbox ml-4">
                                </div>
                            </div>

                             <div v-show="activeTab === 'legal'" class="space-y-6">
                                 <div class="section-title">Documentos Legais (LGPD)</div>
                                 <div>
                                     <label for="terms_of_service" class="form-label">Termos de Serviço</label>
                                     <p class="text-xs text-gray-500 mb-2">Cole aqui o conteúdo completo dos Termos de Serviço. Pode usar HTML para formatação (ex: &lt;h2&gt;, &lt;p&gt;, &lt;strong&gt;).</p>
                                     <textarea v-model="form.terms_of_service" id="terms_of_service" rows="15" class="form-input font-mono !p-4"></textarea>
                                     <div v-if="form.errors.terms_of_service" class="form-error">{{ form.errors.terms_of_service }}</div>
                                 </div>
                                 <div>
                                     <label for="privacy_policy" class="form-label">Política de Privacidade</label>
                                      <p class="text-xs text-gray-500 mb-2">Cole aqui o conteúdo completo da Política de Privacidade. Pode usar HTML para formatação.</p>
                                     <textarea v-model="form.privacy_policy" id="privacy_policy" rows="15" class="form-input font-mono !p-4"></textarea>
                                     <div v-if="form.errors.privacy_policy" class="form-error">{{ form.errors.privacy_policy }}</div>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end rounded-b-2xl mt-8">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.btn-primary { @apply flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply flex-shrink-0 flex items-center justify-center rounded-xl font-semibold text-sm transition-all; @apply bg-gray-200 text-gray-700 hover:bg-gray-300; @apply dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600; @apply disabled:opacity-50 disabled:cursor-not-allowed; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2; }
.form-input { @apply block w-full text-sm rounded-xl transition-all; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto leading-relaxed; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.form-checkbox { @apply h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }
.section-title { @apply text-lg font-bold text-gray-800 dark:text-white mb-4; }
.color-input-wrapper { @apply flex items-center gap-4 p-2 rounded-xl border border-gray-300 bg-gray-50; @apply dark:border-gray-600 dark:bg-gray-700/50; }
.form-color-input { @apply w-10 h-10 rounded-lg cursor-pointer border-none bg-transparent; }
.form-color-input::-webkit-color-swatch-wrapper { @apply p-0; }
.form-color-input::-webkit-color-swatch { @apply rounded-md border-none; }
.form-color-input::-moz-color-swatch { @apply rounded-md border-none; }

/* Estilos para as Abas */
.tab-inactive {
    @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-500;
    @apply whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors cursor-pointer;
}
.tab-active {
    @apply border-emerald-500 text-emerald-600 dark:border-green-400 dark:text-green-400;
    @apply whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm;
}
</style>
