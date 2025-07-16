<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, AlertTriangle, Info, X } from 'lucide-vue-next';

const page = usePage();
const show = ref(false);
const style = ref('success');
const message = ref('');

const flash = computed(() => page.props.flash);

watch(flash, (newFlash) => {
    if (newFlash && (newFlash.success || newFlash.error || newFlash.info)) {
        if (newFlash.success) {
            style.value = 'success';
            message.value = newFlash.success;
        } else if (newFlash.error) {
            style.value = 'error';
            message.value = newFlash.error;
        } else {
            style.value = 'info';
            message.value = newFlash.info;
        }
        show.value = true;
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (show.value) {
                show.value = false;
            }
        }, 5000);
    }
}, { deep: true });

const containerClasses = computed(() => ({
    'success': 'bg-emerald-100 border-emerald-500 text-emerald-800 dark:bg-emerald-900/50 dark:border-emerald-500/50 dark:text-emerald-200',
    'error': 'bg-red-100 border-red-500 text-red-800 dark:bg-red-900/50 dark:border-red-500/50 dark:text-red-200',
    'info': 'bg-blue-100 border-blue-500 text-blue-800 dark:bg-blue-900/50 dark:border-blue-500/50 dark:text-blue-200',
}[style.value]));

const iconComponent = computed(() => ({
    'success': CheckCircle,
    'error': AlertTriangle,
    'info': Info,
}[style.value]));

const close = () => {
    show.value = false;
};
</script>

<template>
    <transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div
            v-if="show && message"
            :class="containerClasses"
            class="fixed top-20 right-5 z-50 max-w-sm w-full rounded-lg border-l-4 p-4 shadow-lg"
            role="alert"
        >
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <component :is="iconComponent" class="h-6 w-6" aria-hidden="true" />
                </div>
                <div class="ms-3 flex-1">
                    <p class="text-sm font-medium">
                        {{ message }}
                    </p>
                </div>
                <div class="ms-4 flex-shrink-0">
                    <button @click="close" type="button" class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2" :class="`hover:bg-black/10 dark:hover:bg-white/20 focus:ring-offset-gray-50 dark:focus:ring-offset-gray-900`">
                        <span class="sr-only">Dismiss</span>
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>
