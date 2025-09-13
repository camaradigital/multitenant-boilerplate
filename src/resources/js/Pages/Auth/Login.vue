<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';

// Ícones Lucide para o novo tema
import { Mail, KeyRound, LogIn } from 'lucide-vue-next';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const statusClass = computed(() => {
    // Adiciona uma classe para mensagens de sucesso (como redefinição de senha)
    return props.status ? 'text-emerald-600 dark:text-emerald-400' : '';
});
</script>

<template>
    <Head title="Entrar" />

    <div class="page-container font-sans">
        <div class="flex flex-col items-center justify-center min-h-screen p-6">

            <div class="form-container w-full max-w-md">

                <div class="form-icon">
                    <AuthenticationCardLogo class="h-12 w-auto" />
                </div>

                <h1 class="form-title">Acessar sua Conta</h1>
                <p class="form-subtitle">Bem-vindo de volta! Faça login para continuar.</p>

                <div v-if="status" class="mb-4 font-medium text-sm text-center" :class="statusClass">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />

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

                    <div class="input-container">
                        <div class="flex justify-between items-center mb-1.5">
                             <label for="password" class="form-label !mb-0">Senha</label>
                             <!-- TESTE DE DIAGNÓSTICO: Removido v-if="canResetPassword" para forçar a exibição do link -->
                             <Link :href="route('password.request')" class="text-sm text-emerald-600 hover:underline dark:text-green-400">
                                 Esqueceu a senha?
                             </Link>
                        </div>
                        <div class="relative">
                            <span class="input-icon"><KeyRound :size="16" /></span>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="form-input"
                                required
                                autocomplete="current-password"
                                placeholder="Sua senha"
                            />
                        </div>
                        <InputError class="form-error" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center">
                        <input id="remember" type="checkbox" v-model="form.remember" class="form-checkbox">
                        <label for="remember" class="form-checkbox-label">Lembrar-me</label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-full !text-base !font-bold flex items-center justify-center" :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            <LogIn :size="18" class="mr-2" />
                            Entrar
                        </button>
                    </div>

                    <div class="text-center pt-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Não tem uma conta?
                            <Link :href="route('register')" class="font-semibold text-emerald-600 hover:underline dark:text-green-400">
                                Registre-se
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
    @apply text-center mb-10 transition-colors duration-500;
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

.form-checkbox {
    @apply w-5 h-5 cursor-pointer appearance-none rounded-md transition-all;
    @apply bg-gray-100 border border-gray-400 checked:bg-emerald-600 checked:border-transparent;
    @apply dark:bg-[#102523] dark:border-[#2a413d] dark:checked:bg-[#43DB9E];
    @apply focus:ring-emerald-500 dark:focus:ring-green-500 focus:ring-offset-0 focus:ring-offset-gray-100 dark:focus:ring-offset-gray-900;
}
.form-checkbox:checked::after {
    content: '✔';
    @apply block text-center text-sm font-bold leading-tight transition-colors duration-300;
    @apply text-white;
    @apply dark:text-[#0A1E1C];
}
.form-checkbox-label {
    @apply ml-3 text-sm font-medium cursor-pointer transition-colors duration-500;
    @apply text-gray-800 dark:text-gray-300;
}

.form-error {
    @apply text-xs mt-1.5 transition-colors duration-500;
    @apply text-red-600 dark:text-red-400;
}

.btn { @apply px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-4; }

.btn-primary {
    @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-300;
    @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400/50;
}
</style>
