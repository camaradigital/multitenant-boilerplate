<script setup>
import InputError from '@/Components/InputError.vue';

// A sua definição de props está perfeita.
defineProps({
    form: Object,
    canReviewTerms: Boolean,
});

const emit = defineEmits(['open-modal']);
</script>

<template>
    <div class="form-section-title">Termos e Privacidade</div>
    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-4 mb-4">Após preencher seus dados, revise e aceite os termos para continuar.</p>
    <div class="space-y-4">
        <div class="flex items-center p-3 rounded-xl transition-colors duration-300" :class="form.terms ? 'bg-emerald-50 dark:bg-green-500/10 border border-emerald-200 dark:border-green-500/20' : 'bg-gray-100 dark:bg-gray-800/50'">
            <svg v-if="form.terms" class="w-6 h-6 text-emerald-500 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <svg v-else class="w-6 h-6 text-gray-400 dark:text-gray-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>

            <p class="text-sm flex-1" :class="form.terms ? 'text-emerald-800 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'">
                <template v-if="form.terms">
                    <span class="font-semibold">Você aceitou os Termos de Serviço.</span>
                </template>
                <template v-else>
                    Para continuar, você deve ler e aceitar os
                    <a href="#" @click.prevent="emit('open-modal', 'terms')"
                       class="font-semibold underline"
                       :class="canReviewTerms ? 'text-emerald-600 dark:text-green-400 hover:text-emerald-500 cursor-pointer' : 'text-gray-400 dark:text-gray-600 cursor-not-allowed'"
                       title="Preencha e valide todos os dados obrigatórios primeiro">
                        Termos de Serviço
                    </a>.
                </template>
            </p>
        </div>
        <InputError class="form-error !ml-4" :message="form.errors.terms" />

        <div class="flex items-center p-3 rounded-xl transition-colors duration-300" :class="form.privacy ? 'bg-emerald-50 dark:bg-green-500/10 border border-emerald-200 dark:border-green-500/20' : 'bg-gray-100 dark:bg-gray-800/50'">
             <svg v-if="form.privacy" class="w-6 h-6 text-emerald-500 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <svg v-else class="w-6 h-6 text-gray-400 dark:text-gray-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>

            <p class="text-sm flex-1" :class="form.privacy ? 'text-emerald-800 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'">
                 <template v-if="form.privacy">
                    <span class="font-semibold">Você aceitou a Política de Privacidade.</span>
                </template>
                <template v-else>
                    E também a nossa
                    <a href="#" @click.prevent="emit('open-modal', 'privacy')"
                       class="font-semibold underline"
                       :class="canReviewTerms ? 'text-emerald-600 dark:text-green-400 hover:text-emerald-500 cursor-pointer' : 'text-gray-400 dark:text-gray-600 cursor-not-allowed'"
                       title="Preencha e valide todos os dados obrigatórios primeiro">
                        Política de Privacidade
                    </a>.
                </template>
            </p>
        </div>
        <InputError class="form-error !ml-4" :message="form.errors.privacy" />
    </div>
</template>
