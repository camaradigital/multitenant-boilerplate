<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { MapPin, ArrowLeft, Save } from 'lucide-vue-next';

const props = defineProps({
    bairro: {
        type: Object,
        default: () => ({}),
    },
});

const isEdit = !!props.bairro.id;

const form = useForm({
    nome: props.bairro.nome || '',
    tipo_logradouro: props.bairro.tipo_logradouro || 'zona urbana',
});

const submit = () => {
    if (isEdit) {
        form.put(route('admin.bairros.update', props.bairro.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.bairros.store'), {
            preserveScroll: true,
        });
    }
};

const tipoLogradouroOptions = [
    { value: 'zona urbana', label: 'Zona Urbana' },
    { value: 'zona rural', label: 'Zona Rural' },
];
</script>

<template>
    <Head :title="isEdit ? 'Editar Bairro' : 'Novo Bairro'" />

    <TenantLayout :title="isEdit ? 'Editar Bairro' : 'Novo Bairro'">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ isEdit ? 'Editar Bairro' : 'Novo Bairro' }}
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-4xl">
                <div class="form-icon"><MapPin :size="32" class="icon-in-badge" /></div>

                <form @submit.prevent="submit">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">{{ isEdit ? 'Editar Bairro' : 'Novo Bairro' }}</h2>
                            <p class="form-subtitle">Preencha os detalhes do bairro. O "Tipo Logradouro" define se é área urbana ou rural.</p>
                        </div>
                        <Link :href="route('admin.bairros.index')" class="btn-secondary flex-shrink-0">
                            <ArrowLeft class="w-4 h-4 mr-2"/>
                            Voltar
                        </Link>
                    </div>

                    <div class="p-4 md:p-6">
                        <div class="max-w-xl space-y-6">
                            <div>
                                <label for="nome" class="input-label">Nome</label>
                                <input
                                    id="nome"
                                    v-model="form.nome"
                                    type="text"
                                    class="mt-1 input-form"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.nome" class="mt-2" />
                            </div>

                            <div>
                                <label for="tipo_logradouro" class="input-label">Tipo Logradouro</label>
                                <select
                                    id="tipo_logradouro"
                                    v-model="form.tipo_logradouro"
                                    class="mt-1 input-form"
                                    required
                                >
                                    <option v-for="option in tipoLogradouroOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.tipo_logradouro" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 border-t-dynamic flex items-center justify-end">
                        <button type="submit" class="btn-primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <Save class="w-4 h-4 mr-2"/>
                            {{ isEdit ? 'Salvar Alterações' : 'Criar Bairro' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos unificados do modelo */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.input-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300; }

/* Estilos de botões */
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }

/* Estilo para inputs e selects do formulário */
.input-form { @apply block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500; }
</style>
