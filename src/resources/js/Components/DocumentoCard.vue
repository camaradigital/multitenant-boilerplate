<script setup>
import { FileText, ArrowRight } from 'lucide-vue-next';

defineProps({
    documento: {
        type: Object,
        required: true,
    },
});

defineEmits(['view-details']);
</script>

<template>
    <div
        @click="$emit('view-details')"
        class="group flex flex-col justify-between bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:border-emerald-500/50 cursor-pointer"
    >
        <div class="p-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 h-12 w-12 flex items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50">
                    <FileText class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400 truncate">
                        {{ documento.tipo_documento }}
                    </p>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate">
                        {{ documento.nome_completo }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 group-hover:bg-emerald-50 dark:group-hover:bg-emerald-900/30 transition-colors duration-300">
            <div class="flex items-center justify-between text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                <span>Ver Detalhes</span>
                <ArrowRight class="h-4 w-4 transform transition-transform duration-300 group-hover:translate-x-1" />
            </div>
        </div>
    </div>
</template>
