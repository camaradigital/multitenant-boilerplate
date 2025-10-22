<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Head, Link, useForm, usePage, watch } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';

// Ícones Lucide para o novo tema
import GlobalErrorHandler from '@/Components/GlobalErrorHandler.vue';
import { Mail, ArrowLeft } from 'lucide-vue-next';

defineProps({
    status: String,
});

const page = usePage();
const form = useForm({
    email: '',
});

watch(() => page.props.errorBags?.default, (errorBags) => {
    if (errorBags) {
        Object.entries(errorBags).forEach(([field, errors]) => {
            form.setError(field, errors[0]);
        });
    }
}, { deep: true, immediate: true });

const submit = () => {
    form.post(route('password.email'));
};
@@ -23,6 +31,11 @@
    <Head title="Esqueceu a Senha" />

    <div class="page-container font-sans">
        <!-- ✅ GLOBAL ERROR HANDLER -->
        <GlobalErrorHandler :fields-map="{
            email: 'E-mail'
        }" />

        <div class="flex flex-col items-center justify-center min-h-screen p-6">

            <div class="form-container w-full max-w-md">
@@ -62,7 +75,7 @@

                    <div class="pt-4 space-y-4">
                        <button type="submit" class="btn btn-primary w-full !text-base !font-bold flex items-center justify-center" :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            Enviar Link de Redefinição
                            {{ form.processing ? 'Enviando...' : 'Enviar Link de Redefinição' }}
                        </button>

                        <div class="text-center">
@@ -138,10 +151,14 @@
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
