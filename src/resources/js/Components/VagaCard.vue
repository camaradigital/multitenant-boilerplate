<script setup>
import { Link } from '@inertiajs/vue3';
import { MapPin, ChevronRight } from 'lucide-vue-next';

// CORRETO: O componente VagaCard recebe um ÚNICO objeto 'vaga'.
defineProps({
    vaga: {
        type: Object,
        required: true,
    },
});

const getContratacaoClass = (tipo) => {
    if (!tipo) {
        return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }

    switch (tipo.toLowerCase()) {
        case 'clt':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'pj':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'estágio':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};
</script>

<template>
    <div v-if="vaga && vaga.id" class="vaga-card-container">
        </div>
</template>


<style scoped>
.vaga-card-container {
    @apply flex flex-col bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden;
}
.vaga-title {
    @apply text-lg font-bold text-gray-900 dark:text-white;
}
.vaga-company {
    @apply text-sm text-emerald-600 dark:text-emerald-400 font-medium mt-1;
}
.vaga-badge {
    @apply px-2.5 py-1 text-xs font-semibold leading-tight rounded-full;
}
.vaga-footer-link {
    @apply flex items-center justify-center gap-2 p-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-sm font-semibold text-emerald-700 dark:text-emerald-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors duration-200;
}
</style>

