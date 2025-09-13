<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

// Ícone Lucide para o novo tema
import { Send } from 'lucide-vue-next';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const statusClass = computed(() => {
    return verificationLinkSent.value ? 'text-emerald-600 dark:text-emerald-400' : '';
});
</script>

<template>
    <Head title="Verificação de E-mail" />

    <div class="page-container font-sans">
        <div class="flex flex-col items-center justify-center min-h-screen p-6">

            <div class="form-container w-full max-w-md">

                <div class="form-icon">
                    <AuthenticationCardLogo class="h-12 w-auto" />
                </div>

                <h1 class="form-title">Confirme seu E-mail</h1>
                <p class="form-subtitle">Um link de verificação foi enviado para o seu e-mail. Por favor, clique no link para ativar sua conta.</p>

                <div v-if="verificationLinkSent" class="mb-6 font-medium text-sm text-center" :class="statusClass">
                    Um novo link de verificação foi enviado para o endereço de e-mail que você forneceu.
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-full !text-base !font-bold flex items-center justify-center" :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            <Send :size="18" class="mr-2" />
                            Reenviar E-mail
                        </button>
                    </div>

                    <div class="text-center pt-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Deseja tentar com outra conta?
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="font-semibold text-emerald-600 hover:underline dark:text-green-400"
                            >
                                Sair
                            </Link>
                        </p>
                    </div>
                </form>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Estilos Padrão do Tema */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

.font-sans { font-family: 'Poppins', sans-serif; }

.page-container {
    @apply dark:bg-[#102C26]/100 min-h-screen transition-colors duration-500;
}
.dark .page-container {
    background: radial-gradient(ellipse at top left, #0D2C2A, #0A1E1C);
}

.form-container {
    @apply relative p-10 pt-16 rounded-3xl shadow-2xl transition-colors duration-500;
    @apply bg-white/70 border border-gray-200;
    backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
    @apply dark:bg-[#102C26]/80 dark:border-2 dark:border-green-400/25;
}

.form-icon {
    @apply absolute -top-8 left-1/2 -translate-x-1/2 w-20 h-20 rounded-full flex justify-center items-center shadow-lg transition-all duration-500;
    @apply bg-emerald-600 shadow-emerald-500/30;
    @apply dark:bg-[#43DB9E]/20 dark:border-2 dark:border-green-400/30 dark:shadow-green-400/10;
}

.form-title {
    @apply text-3xl font-bold text-center mt-6 mb-2 transition-colors duration-500;
    @apply text-gray-900 dark:text-white;
}
.form-subtitle {
    @apply text-center mb-10 transition-colors duration-500 text-sm;
    @apply text-gray-600 dark:text-gray-400;
}

.btn { @apply px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-4; }

.btn-primary {
    @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-300;
    @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400/50;
}
</style>

