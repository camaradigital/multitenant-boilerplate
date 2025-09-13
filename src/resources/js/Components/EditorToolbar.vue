<script setup>
import { Bold, Italic, Strikethrough, Pilcrow, List, ListOrdered } from 'lucide-vue-next';

defineProps({
    editor: Object,
});

const buttons = [
    { command: 'toggleBold', icon: Bold, name: 'Negrito' },
    { command: 'toggleItalic', icon: Italic, name: 'Itálico' },
    { command: 'toggleStrike', icon: Strikethrough, name: 'Riscado' },
    { command: 'setParagraph', icon: Pilcrow, name: 'Parágrafo' },
    { command: 'toggleBulletList', icon: List, name: 'Lista' },
    { command: 'toggleOrderedList', icon: ListOrdered, name: 'Lista Numerada' },
];
</script>

<template>
    <div class="p-2 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-600 flex items-center flex-wrap gap-1">
        <button
            v-for="btn in buttons"
            :key="btn.name"
            @click="editor.chain().focus()[btn.command]().run()"
            type="button"
            class="p-2 rounded-md transition-colors"
            :class="{
                'bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-100': editor.isActive(btn.command.replace('toggle', '').replace('set', '').toLowerCase()),
                'hover:bg-gray-200 dark:hover:bg-gray-700': !editor.isActive(btn.command.replace('toggle', '').replace('set', '').toLowerCase())
            }"
            :title="btn.name"
        >
            <component :is="btn.icon" :size="18" />
        </button>
    </div>
</template>
