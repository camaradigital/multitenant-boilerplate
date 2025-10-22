<template>
    <div v-if="hasErrors && showErrors" class="fixed top-20 right-4 z-50 max-w-sm">
        <div v-for="(errors, field) in errorBag" :key="field" class="mb-4">
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 shadow-lg animate-slide-in-right">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800">{{ fieldTitle(field) }}</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p v-for="error in errors" :key="error">{{ error }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, watch, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    fieldsMap: {
        type: Object,
        default: () => ({})
    }
});

const page = usePage();
const showErrors = ref(true);

const errorBag = computed(() => {
    // Foca APENAS nos erros do errorBags (Jetstream/Fortify)
    return page.props.errorBags?.default || {};
});

const hasErrors = computed(() => Object.keys(errorBag.value).length > 0);

const fieldTitle = (field) => {
    const map = {
        'email': 'E-mail',
        'password': 'Senha',
        'name': 'Nome',
        'password_confirmation': 'Confirmação de senha',
        'user': 'Atenção', // Chave para o 'passwords.user'
        ...props.fieldsMap
    };
    return map[field] || 'Erro Geral';
};

watch(errorBag, (newErrors) => {
    if (Object.keys(newErrors).length > 0) {
        showErrors.value = true;
        
        setTimeout(() => {
            showErrors.value = false;
        }, 8000);
    }
}, { deep: true });
</script>

<style scoped>
@keyframes slide-in-right {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
.animate-slide-in-right {
    animation: slide-in-right 0.3s ease-out;
}
</style>
