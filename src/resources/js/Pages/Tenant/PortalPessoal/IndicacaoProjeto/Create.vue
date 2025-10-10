<script setup>
import { onBeforeUnmount } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InputError from '@/Components/InputError.vue';
import EditorToolbar from '@/Components/EditorToolbar.vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { FileSignature, ArrowLeft, Send, UploadCloud } from 'lucide-vue-next'; // Adicionado UploadCloud

defineProps({
    comissoes: Array,
});

const form = useForm({
    titulo: '',
    descricao: '',
    area_tematica_id: null,
    anexo: null,
});

// Configuração do Editor Tiptap
const editor = useEditor({
    content: form.descricao,
    extensions: [
        StarterKit.configure({
            heading: { levels: [1, 2, 3] },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose dark:prose-invert max-w-none p-4 min-h-[250px] focus:outline-none', // Aumentei o min-h para um campo de texto mais imponente.
        },
    },
    onUpdate: ({ editor }) => {
        form.descricao = editor.getHTML();
    },
});

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const onFileChange = (e) => {
    form.anexo = e.target.files[0];
};

const submit = () => {
    form.post(route('portalcidadao.sugestao.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            editor.value?.commands.clearContent();
        },
    });
};
</script>

<template>
    <Head title="Indicação de Projeto de Lei" />

    <TenantLayout title="Indicação de Projeto de Lei">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Indicação de Projeto de Lei
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4 sm:px-6 lg:px-8">
            <div class="content-container w-full max-w-4xl">
                <div class="form-icon"><FileSignature :size="32" class="icon-in-badge" /></div>

                <form @submit.prevent="submit">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 sm:p-8 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Sua Ideia pode virar Lei 💡</h2>
                            <p class="form-subtitle">Use este espaço para enviar suas ideias e sugestões de projetos de lei. Preencha os campos com detalhes e clareza.</p>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 sm:p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                            <div class="lg:col-span-2 space-y-6">
                                <div>
                                    <label for="titulo" class="input-label">Título da Sugestão <span class="text-red-500">*</span></label>
                                    <input id="titulo" v-model="form.titulo" type="text" class="mt-2 input-form" placeholder="Ex: Sugestão para o Transporte Público" required />
                                    <InputError class="mt-2" :message="form.errors.titulo" />
                                </div>

                                <div>
                                    <label for="area_tematica_id" class="input-label">Área Temática (Selecione a comissão relacionada)</label>
                                    <select v-model="form.area_tematica_id" id="area_tematica_id" class="mt-2 input-form">
                                        <option :value="null" disabled>Selecione uma área temática...</option>
                                        <option :value="null">Nenhuma / Outros</option>
                                        <option v-for="comissao in comissoes" :key="comissao.id" :value="comissao.id">
                                            {{ comissao.nome }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.area_tematica_id" />
                                </div>
                            </div>

                            <div class="lg:col-span-2">
                                <label for="descricao" class="input-label">Descreva sua Sugestão <span class="text-red-500">*</span></label>
                                <div class="mt-2 border-editor rounded-xl shadow-input bg-white dark:bg-gray-700/50">
                                    <EditorToolbar v-if="editor" :editor="editor" class="border-b border-gray-200 dark:border-gray-600 p-2"/>
                                    <EditorContent :editor="editor" />
                                </div>
                                <InputError class="mt-2" :message="form.errors.descricao" />
                            </div>

                            <div class="lg:col-span-2">
                                <label for="anexo" class="input-label mb-2">Anexar Documento (Opcional - PDF, DOC, JPG, PNG)</label>

                                <div class="file-input-container">
                                    <input
                                        type="file"
                                        id="anexo"
                                        @change="onFileChange"
                                        class="file-input-hidden"
                                    >
                                    <label for="anexo" class="file-input-label">
                                        <UploadCloud class="w-5 h-5 mr-3 text-emerald-500 dark:text-emerald-300"/>
                                        <span v-if="form.anexo" class="truncate font-medium text-emerald-700 dark:text-emerald-300">
                                            Arquivo selecionado: {{ form.anexo.name }}
                                        </span>
                                        <span v-else class="text-gray-500 dark:text-gray-400">
                                            Clique para selecionar um arquivo ou arraste e solte aqui.
                                        </span>
                                    </label>
                                </div>

                                <InputError class="mt-2" :message="form.errors.anexo" />
                            </div>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 sm:p-8 border-t-dynamic flex items-center justify-end">
                        <button type="submit" class="btn-primary" :class="{ 'opacity-50': form.processing }" :disabled="form.processing || !form.titulo || !form.descricao">
                            <Send class="w-4 h-4 mr-2"/>
                            {{ form.processing ? 'Enviando...' : 'Enviar Indicação' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos unificados do modelo APRIMORADO */

/* 1. Container Principal */
.content-container {
    @apply relative w-full pt-16 rounded-2xl shadow-2xl transition-all duration-300
           bg-white border-none dark:bg-[#1f2937] dark:shadow-none dark:ring-1 dark:ring-green-400/20;
} /* Removida a borda, substituída por um anel/sombra mais suave e moderno. Fundo escuro mais neutro (gray-800). */
.border-b-dynamic { @apply border-b border-gray-200 dark:border-gray-700; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-gray-700; }
.form-icon {
    @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center
           shadow-xl bg-emerald-600 shadow-emerald-500/50 dark:bg-emerald-500 dark:shadow-emerald-400/40;
} /* Ícone mais proeminente e cor de destaque no dark mode. */
.icon-in-badge { @apply text-white; }
.header-title { @apply text-3xl font-extrabold text-gray-900 dark:text-gray-50; } /* Título maior e mais impactante. */
.form-subtitle { @apply text-base mt-2 text-gray-600 dark:text-gray-400; } /* Subtítulo mais legível. */
.input-label { @apply block text-sm font-semibold text-gray-800 dark:text-gray-200; } /* Label mais forte e legível. */

/* 2. Estilos de Botões */
.btn-base {
    @apply flex items-center justify-center px-6 py-3 rounded-full font-bold text-sm tracking-wide transition-all
           focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-[#1f2937]
           disabled:opacity-40 disabled:cursor-not-allowed;
} /* Botões com cantos mais arredondados (full) e padding maior. */
.btn-primary {
    @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-500/30
           focus:ring-emerald-500 dark:bg-emerald-500 dark:text-white dark:hover:bg-emerald-600 dark:shadow-emerald-400/20 dark:focus:ring-emerald-400;
} /* Sombra para o botão primário. */
.btn-secondary-icon {
    @apply flex items-center px-4 py-2 rounded-full font-semibold text-sm text-gray-600 dark:text-gray-300
           hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors;
} /* Novo estilo para o botão de 'Voltar'. */

/* 3. Estilo para Inputs e Selects */
.input-form {
    @apply block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
           shadow-sm sm:text-base p-3 placeholder:text-gray-400 dark:placeholder:text-gray-500
           focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors;
} /* Inputs maiores, mais arredondados (lg) e com fundo escuro mais neutro no dark mode. */

/* 4. Estilo para o Tiptap Editor */
.border-editor {
    @apply border border-gray-300 dark:border-gray-600 rounded-xl overflow-hidden;
}
.shadow-input {
    @apply shadow-lg shadow-gray-100/50 dark:shadow-none;
}

/* 5. Estilo para o Input de Arquivo (File Upload) */
.file-input-container {
    @apply relative flex justify-center items-center border-2 border-dashed border-gray-300 dark:border-gray-600
           rounded-lg p-6 cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-400 transition-colors duration-200 bg-gray-50 dark:bg-gray-800;
}
.file-input-hidden {
    @apply absolute inset-0 w-full h-full opacity-0 cursor-pointer;
}
.file-input-label {
    @apply flex items-center justify-center w-full text-center text-sm font-medium text-gray-500 dark:text-gray-400;
}

/* Estilo para o Tiptap Editor (manter para tipografia interna) */
:deep(.prose h1) { @apply text-2xl font-bold; }
:deep(.prose h2) { @apply text-xl font-semibold; }
:deep(.prose h3) { @apply text-lg font-medium; }
</style>
