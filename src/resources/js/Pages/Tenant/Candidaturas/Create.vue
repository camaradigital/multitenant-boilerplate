<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Briefcase, UserCheck, FileText, Send, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    vaga: Object,
});

const form = useForm({
    curriculo: null,
    mensagem_apresentacao: '',
});

const submit = () => {
    form.post(route('candidaturas.store', { vaga: props.vaga.id }), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head :title="`Candidatar-se para ${vaga.titulo}`" />

    <TenantLayout>
        <div class="flex justify-center items-start py-12 px-4 font-sans">
            <div class="content-container w-full max-w-2xl">
                <div class="form-icon"><UserCheck :size="32" class="icon-in-badge" /></div>

                <div class="p-6 border-b-dynamic text-center">
                    <h2 class="header-title">Candidatura para Vaga</h2>
                    <p class="form-subtitle">{{ vaga.titulo }}</p>
                </div>

                <form @submit.prevent="submit" class="p-6">
                    <div class="mb-6 p-4 rounded-lg bg-emerald-50 dark:bg-green-500/10 border border-emerald-200 dark:border-green-500/20">
                        <p class="text-sm font-semibold text-emerald-800 dark:text-green-300">Empresa: {{ vaga.empresa.nome_fantasia }}</p>
                        <p class="text-xs text-emerald-600 dark:text-green-400 mt-1">Local: {{ vaga.localizacao }}</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="curriculo" class="form-label flex items-center">
                                <FileText class="w-4 h-4 mr-2"/>
                                Seu Currículo (PDF, DOC, DOCX - Máx 2MB)
                            </label>
                            <input
                                type="file"
                                @input="form.curriculo = $event.target.files[0]"
                                id="curriculo"
                                class="form-input-file"
                                required
                            />
                            <div v-if="form.progress" class="w-full bg-gray-200 rounded-full h-1.5 mt-2 dark:bg-gray-700">
                                <div class="bg-emerald-600 h-1.5 rounded-full" :style="{ width: form.progress.percentage + '%' }"></div>
                            </div>
                            <div v-if="form.errors.curriculo" class="form-error">{{ form.errors.curriculo }}</div>
                        </div>

                        <div>
                            <label for="mensagem_apresentacao" class="form-label">Mensagem de Apresentação (Opcional)</label>
                            <textarea
                                v-model="form.mensagem_apresentacao"
                                id="mensagem_apresentacao"
                                rows="5"
                                class="form-input"
                                placeholder="Fale um pouco sobre você e por que se interessa por esta vaga..."
                            ></textarea>
                            <div v-if="form.errors.mensagem_apresentacao" class="form-error">{{ form.errors.mensagem_apresentacao }}</div>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-between">
                         <!-- CORREÇÃO APLICADA AQUI -->
                         <Link :href="route('portal.vagas.index')" class="btn-secondary !py-3">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Voltar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="btn-primary !py-3">
                            <Send class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Enviando...' : 'Enviar Candidatura' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes com o seu design */
.font-sans { font-family: 'Poppins', sans-serif; }
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-300; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-input-file { @apply block w-full text-sm text-gray-900 border border-gray-300 rounded-xl cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700/50 dark:border-gray-600 dark:placeholder-gray-400; @apply file:mr-4 file:py-3 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100; @apply dark:file:bg-green-500/10 dark:file:text-green-300 dark:hover:file:bg-green-500/20; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
</style>

