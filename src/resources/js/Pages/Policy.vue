<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantPublicLayout from '@/Layouts/TenantPublicLayout.vue';
import { ArrowLeft } from 'lucide-vue-next';

defineProps({
    title: String,
    content: String,
    tenantName: String,
    lastUpdated: String,
});
</script>

<template>
    <Head :title="title" />

    <TenantPublicLayout>
        <div class="bg-white dark:bg-gray-900 py-16 sm:py-24">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <Link :href="route('portal.home')" class="inline-flex items-center text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 transition-colors">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Voltar à Página Inicial
                    </Link>
                </div>

                <div class="text-center mb-12">
                    <p class="text-base font-semibold text-emerald-600 dark:text-emerald-400">{{ tenantName }}</p>
                    <h1 class="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">{{ title }}</h1>
                    <p class="mt-6 text-md leading-8 text-gray-600 dark:text-gray-400">Última atualização: {{ lastUpdated }}</p>
                </div>

                <!-- A classe 'prose' do Tailwind aplica automaticamente um estilo de alta legibilidade -->
                <div
                    class="prose prose-lg dark:prose-invert max-w-none prose-headings:font-bold prose-a:text-emerald-600 dark:prose-a:text-emerald-400"
                    v-html="content"
                />
            </div>
        </div>
    </TenantPublicLayout>
</template>

<style>
/* Estilos para a classe 'prose' (Tailwind Typography) */
/* Estes estilos podem ser colocados no seu app.css principal se preferir */
.prose h1, .prose h2, .prose h3 {
    @apply scroll-m-20;
}
</style>

