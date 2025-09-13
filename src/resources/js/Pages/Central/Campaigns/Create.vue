<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { onBeforeUnmount } from 'vue';
import { Send } from 'lucide-vue-next';

// Componentes customizados (agora aprimorados)
import RecipientSelector from '@/Components/RecipientSelector.vue';
import AttachmentInput from '@/Components/AttachmentInput.vue';

// Editor de Texto
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import EditorToolbar from '@/Components/EditorToolbar.vue';

// Notificações
import { useAppToast } from '@/Composables/useToast.js';

const props = defineProps({
    leads: Array,
});

const { showSuccess, showError } = useAppToast();

const form = useForm({
    recipients: [],
    subject: '',
    body: '',
    cta_text: '',
    cta_url: '',
    attachment: null,
});

const editor = useEditor({
    content: `<p>Olá, {{ nome }}.</p><p>Gostaríamos de apresentar nosso sistema...</p>`,
    extensions: [StarterKit],
    editorProps: {
        attributes: {
            class: 'prose dark:prose-invert prose-sm sm:prose-base max-w-none focus:outline-none p-4 min-h-[16rem] overflow-y-auto',
        },
    },
    onUpdate: ({ editor }) => {
        form.body = editor.getHTML();
    },
});

if (editor.value) {
    form.body = editor.value.getHTML();
}

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const submitCampaign = () => {
    if (editor.value) {
        form.body = editor.value.getHTML();
    }

    form.post(route('central.campaigns.send'), {
        onSuccess: () => {
            form.reset();
            editor.value?.commands.setContent('<p>Olá, {{ nome }}.</p><p>Gostaríamos de apresentar nosso sistema...</p>');
            showSuccess('Campanha enviada para a fila de processamento!');
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            showError(firstError || 'Houve um erro de validação.');
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nova Campanha de E-mail" />

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
                        <RecipientSelector
                            v-model="form.recipients"
                            :leads="props.leads"
                            :error="form.errors.recipients"
                        />

                        <div>
                            <label for="subject" class="form-label">Assunto</label>
                            <input id="subject" v-model="form.subject" type="text" class="form-input" required>
                            <div v-if="form.errors.subject" class="form-error">{{ form.errors.subject }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="cta_text" class="form-label">Texto do Botão (CTA)</label>
                                <input type="text" id="cta_text" v-model="form.cta_text" placeholder="Ex: Saiba Mais" class="form-input" required>
                                <div v-if="form.errors.cta_text" class="form-error">{{ form.errors.cta_text }}</div>
                            </div>
                            <div>
                                <label for="cta_url" class="form-label">URL do Botão (CTA)</label>
                                <input type="url" id="cta_url" v-model="form.cta_url" placeholder="https://..." class="form-input" required>
                                 <div v-if="form.errors.cta_url" class="form-error">{{ form.errors.cta_url }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Mensagem</label>
                            <div v-if="editor" class="bg-gray-50 dark:bg-[#102523] border border-gray-300 dark:border-[#2a413d] rounded-xl overflow-hidden transition-all focus-within:ring-1 focus-within:ring-emerald-500 dark:focus-within:ring-green-500">
                                <EditorToolbar :editor="editor" />
                                <EditorContent :editor="editor" />
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Use <code v-pre class="bg-gray-200 dark:bg-gray-700 px-1 rounded">`{{ nome }}`</code> para personalizar com o nome do lead.</p>
                            <div v-if="form.errors.body" class="form-error">{{ form.errors.body }}</div>
                        </div>

                        <AttachmentInput
                            v-model="form.attachment"
                            :error="form.errors.attachment"
                            :progress="form.progress"
                        />
                    </div>
                </div>

                <div class="p-6 border-t-dynamic bg-gray-50 dark:bg-green-500/5 rounded-b-3xl flex justify-end">
                    <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="form.processing || form.recipients.length === 0">
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
/* Estilos mantidos, pois já estão bem estruturados */
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

:deep(.prose) {
    text-align: left;
}
</style>

