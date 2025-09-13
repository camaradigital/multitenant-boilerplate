<script setup>
import { ref, computed } from 'vue';
import { FileUp, X, Paperclip } from 'lucide-vue-next';

const props = defineProps({
    modelValue: File,
    error: String,
    progress: Object,
});

const emit = defineEmits(['update:modelValue']);

const isDragging = ref(false);
const fileInput = ref(null);

const hasFile = computed(() => !!props.modelValue);
const fileName = computed(() => props.modelValue?.name);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        emit('update:modelValue', file);
    }
};

const removeFile = () => {
    emit('update:modelValue', null);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        emit('update:modelValue', file);
    }
};
</script>

<template>
    <div>
        <label class="form-label">Anexo (Opcional, máx 5MB)</label>
        <div class="mt-2">
            <!-- Dropzone Area -->
            <label
                v-if="!hasFile"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                class="relative block w-full rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 p-8 text-center hover:border-gray-400 dark:hover:border-gray-500 transition-colors cursor-pointer"
                :class="{ 'border-emerald-500 dark:border-green-500 bg-emerald-50 dark:bg-green-500/10': isDragging }"
            >
                <FileUp class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500" />
                <span class="mt-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Arraste e solte um arquivo ou <span class="text-emerald-600 dark:text-green-400">clique para selecionar</span>
                </span>
                <input ref="fileInput" @change="handleFileChange" type="file" class="sr-only">
            </label>

            <!-- File Info and Progress Bar -->
            <div v-else class="relative bg-gray-50 dark:bg-[#102523] border border-gray-300 dark:border-[#2a413d] rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                         <Paperclip class="h-5 w-5 text-gray-500 dark:text-gray-400 flex-shrink-0 mr-3" />
                         <span class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ fileName }}</span>
                    </div>
                    <button @click="removeFile" type="button" class="p-1 rounded-full text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                 <!-- Progress Bar -->
                <div v-if="progress" class="mt-3 h-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-600 dark:bg-green-500 rounded-full transition-all duration-300" :style="{ width: progress.percentage + '%' }"></div>
                </div>
            </div>

            <div v-if="error" class="form-error">{{ error }}</div>
        </div>
    </div>
</template>

