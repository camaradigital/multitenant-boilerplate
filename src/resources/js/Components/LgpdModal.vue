<script setup>
import { X } from 'lucide-vue-next';

defineProps({
    show: { type: Boolean, default: false },
    title: String,
});

const emit = defineEmits(['accept', 'close']);
</script>

<template>
    <Teleport to="body">
        <transition name="modal-fade">
            <div v-if="show" class="modal-backdrop" @click.self="emit('close')">
                <div class="modal-content" @click.stop>
                    <div class="modal-header">
                        <h2 class="modal-title">{{ title }}</h2>
                        <button @click="emit('close')" class="modal-close-button"><X :size="24" /></button>
                    </div>
                    <div class="modal-body">
                        <slot />
                    </div>
                    <div class="modal-footer">
                        <button @click="emit('close')" class="btn btn-secondary mr-4">Não Aceito</button>
                        <button @click="emit('accept')" class="btn btn-primary">Aceito</button>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<style scoped>
.modal-backdrop { @apply fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-50; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); }
.modal-content { @apply w-full max-w-3xl max-h-[90vh] flex flex-col p-8 rounded-2xl shadow-2xl; @apply bg-white dark:bg-[#102C26] dark:border dark:border-green-400/25; }
.modal-header { @apply flex justify-between items-center pb-4 mb-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0; }
.modal-title { @apply text-2xl font-bold; @apply text-gray-900 dark:text-white; }
.modal-close-button { @apply text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white transition-colors; }
.modal-body { @apply overflow-y-auto pr-4 text-gray-700 dark:text-gray-300 text-sm; }
.modal-footer { @apply flex justify-end pt-6 mt-auto flex-shrink-0 border-t border-gray-200 dark:border-gray-700; }
.modal-body::-webkit-scrollbar { width: 8px; }
.modal-body::-webkit-scrollbar-track { @apply bg-gray-200 dark:bg-gray-800 rounded-lg; }
.modal-body::-webkit-scrollbar-thumb { @apply bg-gray-400 dark:bg-gray-600 rounded-lg; }
.modal-body::-webkit-scrollbar-thumb:hover { @apply bg-gray-500 dark:bg-gray-500; }
.modal-body :deep(h2) { @apply text-xl font-semibold mt-4 mb-2 text-emerald-700 dark:text-green-400; }
.modal-body :deep(ul) { @apply list-disc list-inside space-y-2 my-2; }
.modal-body :deep(p) { @apply mb-3 leading-relaxed; }
.modal-body :deep(strong) { @apply font-semibold text-gray-800 dark:text-gray-200; }

.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }

/* Importações de estilos globais de botões, se necessário */
.btn { @apply px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-4; }
.btn-primary { @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-300; @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400/50; }
.btn-secondary { @apply bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-300; @apply dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-600; }
</style>
