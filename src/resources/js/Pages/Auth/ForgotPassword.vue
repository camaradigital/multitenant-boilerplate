<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import GlobalErrorHandler from '@/Components/GlobalErrorHandler.vue';
import { Mail, ArrowLeft } from 'lucide-vue-next';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

// O 'watch' foi removido daqui para evitar que erros gerais
// (como 'passwords.user') apareçam duplicados.
// O GlobalErrorHandler cuida dos erros gerais.
// O InputError cuida dos erros de campo (form.errors).

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Esqueceu a Senha" />

    <div class="page-container font-sans">
                <GlobalErrorHandler :fields-map="{
            email: 'E-mail'
        }" />

        <div class="flex flex-col items-center justify-center min-h-screen p-6">

            <div class="form-container w-full max-w-md">

                <div class="form-icon">
                    <AuthenticationCardLogo class="h-full w-full p-1" />
                </div>

                <h1 class="form-title">Esqueceu sua Senha?</h1>
                <p class="form-subtitle">
                    Sem problemas. Informe seu e-mail e enviaremos um link para você criar uma nova senha.
                </p>

                <div v-if="status" class="mb-6 font-medium text-sm text-center p-3 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                                        <div class="input-container">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="relative">
                            <span class="input-icon"><Mail :size="16" /></span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="form-input"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="seuemail@exemplo.com"
                            />
                        </div>
                                                <InputError class="form-error" :message="form.errors.email" />
                    </div>

                    <div class="pt-4 space-y-4">
                        <button type="submit" class="btn btn-primary w-full !text-base !font-bold flex items-center justify-center" :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            {{ form.processing ? 'Enviando...' : 'Enviar Link de Redefinição' }}
                        </button>

                        <div class="text-center">
                            <Link :href="route('login')" class="text-sm font-medium text-emerald-600 hover:underline dark:text-green-400 inline-flex items-center">
                                <ArrowLeft :size="14" class="mr-1.5" />
                                Voltar para o Login
                            </Link>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Estilos Padrão do Tema (idênticos ao Login.vue para consistência) */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

.font-sans { font-family: 'Poppins', sans-serif; }

.page-container {
    @apply bg-gray-100 dark:bg-transparent min-h-screen transition-colors duration-500;
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

.form-label {
    @apply block mb-1.5 text-sm font-medium transition-colors duration-500;
    @apply text-gray-700 dark:text-gray-300;
}

.input-icon {
    @apply absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none transition-colors duration-500;
    @apply text-gray-400 dark:text-gray-500;
}

.form-input {
    @apply block w-full text-sm rounded-xl transition-all duration-300;
    @apply h-12 py-3.5 pl-11 pr-5;
    @apply bg-white border-gray-300 text-gray-900 placeholder-gray-400;
    @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500;
    @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500;
    @apply dark:focus:ring-green-500 dark:focus:border-green-500;
}

.form-error {
    @apply text-xs mt-1.5 transition-colors duration-500;
    @apply text-red-600 dark:text-red-400;
}

.input-container {
    @apply space-y-2;
}

.btn { @apply px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-4; }

.btn-primary {
    @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-300;
    @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400/50;
}
</style>
