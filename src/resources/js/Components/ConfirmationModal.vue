<script setup>
import { computed } from 'vue';
import { AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Confirmar Ação' },
    message: { type: String, default: 'Você tem certeza que deseja continuar?' },
});

const emit = defineEmits(['confirm', 'close']);

</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md m-4 p-6 text-center">
            <AlertTriangle class="mx-auto h-16 w-16 text-amber-500 dark:text-yellow-400 mb-4" />

            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ title }}</h3>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ message }}</p>

            <div class="mt-6 flex justify-center gap-4">
                <button type="button" @click="$emit('close')" class="btn btn-secondary w-full sm:w-auto">Cancelar</button>
                <button type="button" @click="$emit('confirm')" class="btn btn-danger w-full sm:w-auto">Confirmar Exclusão</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Adicione esta nova classe aos seus estilos ou use classes do Tailwind diretamente */
.btn-danger { @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500; }
.btn { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600; }
</style>
