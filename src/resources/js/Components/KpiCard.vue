<script setup lang="ts">
import { defineProps } from 'vue';

const props = defineProps({
  title: String,
  value: [String, Number],
  icon: Object, // Espera um componente de ícone (ex: lucide-vue-next)
  colorClass: String, // Ex: 'bg-blue-500 text-white'
});
</script>

<template>
    <div class="p-5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm flex items-center space-x-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" :class="colorClass">
            <component :is="icon" class="w-6 h-6" />
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ title }}</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ value }}</p>
        </div>
    </div>
</template>
