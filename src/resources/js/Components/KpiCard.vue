<script setup lang="ts">
import { defineProps } from 'vue';

defineProps({
    title: String,
    value: [String, Number],
    // CORREÇÃO: A propriedade 'icon' agora aceita Object ou Function,
    // o que é compatível com os ícones da biblioteca lucide-vue-next.
    icon: [Object, Function],
    colorClass: {
        type: String,
        default: 'bg-gray-500 text-white',
    },
});
</script>

<template>
    <div class="p-5 rounded-xl shadow-sm flex items-center space-x-4 border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50">
        <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-lg" :class="colorClass">
            <component :is="icon" class="w-6 h-6" />
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ title }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ value }}</p>
        </div>
    </div>
</template>
