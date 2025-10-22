<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';

// Ícones Lucide para o novo tema
import { Mail, KeyRound, LogIn, Eye, EyeOff, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
});

const page = usePage(); // ✅ ADICIONAR ESTA LINHA

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// ✅ MERGE AUTOMÁTICO DOS ERRORBAGS PARA form.errors
watch(
    () => page.props.jetstream?.errorBags?.default,
    (errorBags) => {
        if (errorBags) {
            // Copia os erros do errorBags para form.errors
            Object.entries(errorBags).forEach(([field, errors]) => {
                form.setError(field, errors[0]); // Pega o primeiro erro
            });
        }
    },
    { deep: true }
);

const showPassword = ref(false);

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onError: (errors) => {
            console.log('Erros do form:', errors); // ✅ DEBUG
            form.password = '';
        },
    });
};

const statusClass = computed(() => {
    return props.status ? 'text-emerald-600 dark:text-emerald-400' : '';
});
</script>

<template>
    <Head title="Entrar" />

    <div class="page-container font-sans">
        <div class="flex flex-col items-center justify-center min-h-screen p-6">

            <div class="form-container w-full max-w-md">

                <div class="form-icon">
                    <AuthenticationCardLogo class="h-full w-full p-1" />
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
                        </div>
                        <div class="relative">
                            <span class="input-icon"><KeyRound :size="16" /></span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="form-input pr-12"
                                required
                                autocomplete="current-password"
                                placeholder="Sua senha"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 focus:outline-none"
                                aria-label="Alternar visibilidade da senha"
                            >
                                <component :is="showPassword ? EyeOff : Eye" :size="16" class="text-gray-400 dark:text-gray-500 transition-colors duration-300" />
                            </button>
                        </div>
                        <InputError class="form-error" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" type="checkbox" v-model="form.remember" class="form-checkbox">
                            <label for="remember" class="form-checkbox-label">Lembrar-me</label>
                        </div>
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-emerald-600 hover:underline dark:text-green-400">
                            Esqueceu a senha?
                        </Link>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-full !text-base !font-bold flex items-center justify-center" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                            <component :is="form.processing ? Loader2 : LogIn" :size="18" class="mr-2" :class="{ 'animate-spin': form.processing }" />
                            {{ form.processing ? 'Entrando...' : 'Entrar' }}
                        </button>
                    </div>

                    <hr class="my-4 border-gray-200 dark:border-gray-700" />

                    <div class="text-center">
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
    @apply bg-gray-50 dark:bg-[#102C26]/100 min-h-screen transition-colors duration-500;
}
.dark .page-container {
    background: radial-gradient(ellipse at top left, #0D2C2A, #0A1E1C);
}

.form-container {
    @apply relative p-10 pt-16 rounded-3xl shadow-2xl transition-colors duration-500 animate-fade-in;
    @apply bg-white/80 border border-gray-200;
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    @apply dark:bg-[#102C26]/90 dark:border-2 dark:border-green-400/30;
}

.form-icon {
    @apply absolute -top-10 left-1/2 -translate-x-1/2 w-24 h-24 rounded-full flex justify-center items-center shadow-xl transition-all duration-500;
    @apply bg-emerald-600 shadow-emerald-500/50;
    @apply dark:bg-[#43DB9E]/30 dark:border-2 dark:border-green-400/40 dark:shadow-green-400/20;
}

.form-title {
    @apply text-4xl font-bold text-center mt-6 mb-2 transition-colors duration-500;
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
    @apply block w-full text-sm rounded-xl transition-all duration-300 shadow-sm;
    @apply h-12 py-3.5 pl-11 pr-5;
    @apply bg-white border border-gray-300 text-gray-900 placeholder-gray-400;
    @apply focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:shadow-md;
    @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500;
    @apply dark:focus:ring-green-500 dark:focus:border-green-500;
}

.form-checkbox {
    @apply w-5 h-5 cursor-pointer appearance-none rounded-md transition-all duration-300;
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

.btn { @apply px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-4 shadow-md; }

.btn-primary {
    @apply bg-gradient-to-r from-emerald-600 to-emerald-700 text-white hover:from-emerald-700 hover:to-emerald-800 focus:ring-emerald-300;
    @apply dark:from-[#43DB9E] dark:to-green-500 dark:text-[#0A1E1C] dark:hover:from-green-500 dark:hover:to-green-600 dark:focus:ring-green-400/50;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<style>
html {
    /*
      O valor padrão da fonte na maioria dos navegadores é 16px.
      Reduzir este valor faz com que todos os elementos que usam a unidade 'rem'

      - 14px: Reduz o tamanho geral em cerca de 12.5% (bom para um layout mais compacto).
      - 15px: Redução mais sutil.
    */
    font-size: 14px;
}
</style>
