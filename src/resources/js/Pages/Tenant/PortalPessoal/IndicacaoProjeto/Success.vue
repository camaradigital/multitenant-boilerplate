<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { CheckCircle, FileText, ArrowRight } from 'lucide-vue-next'; // Adicionado CheckCircle e FileText

defineProps({
    protocolo: String,
});
</script>

<template>
    <Head title="Indicação Enviada" />

    <TenantLayout>
        <template #header>
            </template>

        <div class="flex justify-center items-center py-12 px-4 sm:px-6 lg:px-8 min-h-screen-minus-header">
            <div class="content-confirmation w-full max-w-lg">
                <div class="confirmation-icon">
                    <CheckCircle :size="48" class="icon-in-badge-lg" />
                </div>

                <div class="p-8 sm:p-10 text-center">
                    <h2 class="header-title-confirmation">Indicação Enviada!</h2>

                    <p class="mt-4 text-lg text-gray-700 dark:text-gray-300 font-medium">
                        Sua ideia foi registrada com sucesso.
                    </p>
                    <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Agradecemos sua contribuição! Sua sugestão será analisada pela equipe legislativa competente.
                    </p>

                    <div class="mt-8 bg-emerald-50 dark:bg-gray-700/50 rounded-xl p-6 border border-emerald-200 dark:border-gray-700 shadow-inner">
                        <div class="flex items-center justify-center mb-3">
                            <FileText class="w-5 h-5 mr-2 text-emerald-600 dark:text-emerald-400" />
                            <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                Protocolo de Acompanhamento
                            </p>
                        </div>
                        <p class="text-3xl font-mono font-extrabold text-gray-900 dark:text-white select-all">
                            {{ protocolo }}
                        </p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Guarde este número para futuras consultas e acompanhamento.
                        </p>
                    </div>

                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                        <Link :href="route('portalcidadao.meu-painel')" class="btn-primary-lg">
                            Voltar para Meu Painel
                            <ArrowRight class="w-4 h-4 ml-2"/>
                        </Link>

                        <Link :href="route('portalcidadao.sugestao.create')" class="btn-secondary-lg">
                            Fazer Nova Sugestão
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos unificados do modelo APRIMORADO */

/* 1. Container Principal de Confirmação */
.min-h-screen-minus-header {
    min-height: calc(100vh - 64px); /* Ajuste para o tamanho do layout principal */
}

.content-confirmation {
    @apply relative w-full pt-16 rounded-2xl shadow-2xl transition-all duration-300
           bg-white border border-gray-100 dark:bg-gray-800 dark:border-gray-700
           dark:shadow-none dark:ring-2 dark:ring-emerald-400/20;
} /* Card centralizado, com shadow-2xl e um toque esmeralda no dark mode */

.confirmation-icon {
    @apply absolute -top-8 left-1/2 -translate-x-1/2 w-20 h-20 rounded-full flex justify-center items-center
           shadow-xl bg-emerald-600 shadow-emerald-500/50 dark:bg-emerald-500 dark:shadow-emerald-400/40;
} /* Ícone maior e mais proeminente */
.icon-in-badge-lg { @apply text-white h-10 w-10; }

.header-title-confirmation {
    @apply text-3xl font-extrabold text-gray-900 dark:text-gray-50;
} /* Título maior, celebrando o sucesso */

/* 2. Estilos de Botões (Novas variações para botões grandes) */
.btn-base-lg {
    @apply flex items-center justify-center w-full px-8 py-3.5 rounded-full font-bold text-base tracking-wide transition-all
           focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800
           disabled:opacity-40 disabled:cursor-not-allowed;
}
.btn-primary-lg {
    @apply btn-base-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-500/30
           focus:ring-emerald-500 dark:bg-emerald-500 dark:text-white dark:hover:bg-emerald-600 dark:shadow-emerald-400/20 dark:focus:ring-emerald-400;
}
.btn-secondary-lg {
    @apply btn-base-lg bg-gray-100 text-gray-700 hover:bg-gray-200
           focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500;
}
</style>
