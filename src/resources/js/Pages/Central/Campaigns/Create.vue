<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { Mail, Send, Users, ChevronDown, Check, Paperclip, XCircle } from 'lucide-vue-next';

const props = defineProps({
    leads: Array,
});

const form = useForm({
    recipients: [],
    subject: '',
    body: 'Olá, {{ nome }}.\n\nGostaríamos de apresentar nosso sistema...',
    attachment: null,
});

const dropdownOpen = ref(false);
const fileInput = ref(null);

const allSelected = computed({
    get: () => props.leads.length > 0 && form.recipients.length === props.leads.length,
    set: (value) => {
        form.recipients = value ? props.leads.map(lead => lead.id) : [];
    }
});

const handleFileChange = (event) => {
    form.attachment = event.target.files[0];
};

const removeAttachment = () => {
    form.attachment = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submitCampaign = () => {
    form.post(route('central.campaigns.send'), {
        onSuccess: () => {
            form.reset();
            removeAttachment();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Disparo de E-mail" />

    <AppLayout title="Disparo de E-mail">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Campanha de E-mail
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <form @submit.prevent="submitCampaign" class="content-container w-full max-w-4xl">
                 <div class="form-icon"><Send :size="32" class="icon-in-badge" /></div>

                <div class="p-6 md:p-8">
                    <div class="text-center mb-10">
                        <h2 class="header-title">Criar Nova Campanha</h2>
                        <p class="form-subtitle">Envie e-mails personalizados para seus leads.</p>
                    </div>

                    <div class="space-y-8">
                        <!-- Seleção de Destinatários -->
                        <div>
                            <label class="form-label">Destinatários</label>
                             <div class="relative">
                                <button @click="dropdownOpen = !dropdownOpen" type="button" class="form-input text-left flex items-center justify-between">
                                    <span>{{ form.recipients.length }} lead(s) selecionado(s)</span>
                                    <ChevronDown class="h-5 w-5 text-gray-400" />
                                </button>
                                <!-- ESTILO DO DROPDOWN CORRIGIDO -->
                                <div v-if="dropdownOpen" @click.away="dropdownOpen = false" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                                        <label class="flex items-center space-x-3 px-2 py-1.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/60 rounded-md" @click.stop>
                                            <input type="checkbox" v-model="allSelected" class="rounded text-emerald-500 focus:ring-emerald-500" />
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Selecionar Todos</span>
                                        </label>
                                    </div>
                                    <div class="p-1">
                                        <label v-for="lead in leads" :key="lead.id" class="flex items-center space-x-3 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700/60 rounded-md cursor-pointer" @click.stop>
                                            <input type="checkbox" :value="lead.id" v-model="form.recipients" class="rounded text-emerald-500 focus:ring-emerald-500" />
                                            <div class="flex flex-col">
                                                <span class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ lead.nome }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ lead.email }}</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.recipients" class="form-error">{{ form.errors.recipients }}</div>
                        </div>

                        <!-- Assunto do E-mail -->
                        <div>
                            <label for="subject" class="form-label">Assunto</label>
                            <input id="subject" v-model="form.subject" type="text" class="form-input" required>
                            <div v-if="form.errors.subject" class="form-error">{{ form.errors.subject }}</div>
                        </div>

                        <!-- Corpo do E-mail -->
                        <div>
                            <label for="body" class="form-label">Mensagem</label>
                            <textarea id="body" v-model="form.body" class="form-input !h-64" rows="10" required></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Use <code v-pre class="bg-gray-200 dark:bg-gray-700 px-1 rounded">`{{ nome }}`</code> para personalizar com o nome do lead.</p>
                            <div v-if="form.errors.body" class="form-error">{{ form.errors.body }}</div>
                        </div>

                        <!-- Upload de Anexo -->
                        <div>
                            <label for="attachment" class="form-label">Anexo (Opcional)</label>
                            <div v-if="!form.attachment" class="relative">
                                <input ref="fileInput" @change="handleFileChange" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="attachment">
                                <div class="form-input flex items-center justify-center border-dashed border-2">
                                    <Paperclip class="h-5 w-5 mr-2 text-gray-400" />
                                    <span class="text-gray-500">Clique para selecionar um arquivo</span>
                                </div>
                            </div>
                            <div v-else class="form-input flex items-center justify-between bg-emerald-50 dark:bg-emerald-900/50">
                                <span class="truncate text-gray-700 dark:text-gray-300">{{ form.attachment.name }}</span>
                                <button @click="removeAttachment" type="button" class="p-1 rounded-full hover:bg-black/10 dark:hover:bg-white/20">
                                    <XCircle class="h-5 w-5 text-red-500" />
                                </button>
                            </div>
                            <div v-if="form.errors.attachment" class="form-error">{{ form.errors.attachment }}</div>
                        </div>
                    </div>
                </div>

                 <div class="p-6 border-t-dynamic bg-gray-50 dark:bg-green-500/5 rounded-b-3xl flex justify-end">
                    <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="form.processing">
                        <span v-if="form.processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Enviando...
                        </span>
                        <span v-else class="flex items-center"><Send class="h-4 w-4 mr-2" />Enviar Campanha</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Estilos reutilizados */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.btn-primary { @apply flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-sm uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-[#0A1E1C] bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50 disabled:cursor-not-allowed; }
.form-label { @apply block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-2; }
</style>
