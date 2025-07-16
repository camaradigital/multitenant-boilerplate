<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    parametros: Object,
});

const form = useForm({
    name: props.parametros.name,
    site_url: props.parametros.site_url,
    cor_primaria: props.parametros.cor_primaria || '#000000',
    cor_secundaria: props.parametros.cor_secundaria || '#FFFFFF',
    logotipo_url: props.parametros.logotipo_url,
});

const submit = () => {
    form.put(route('parametros.update'));
};
</script>

<template>
    <Head title="Parâmetros do Portal" />
    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Parâmetros do Portal</h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="name" value="Nome da Instituição" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="logotipo_url" value="URL do Logotipo" />
                            <TextInput id="logotipo_url" v-model="form.logotipo_url" type="text" class="mt-1 block w-full" />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="site_url" value="URL do Site Oficial" />
                            <TextInput id="site_url" v-model="form.site_url" type="text" class="mt-1 block w-full" />
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="cor_primaria" value="Cor Primária" />
                                <input id="cor_primaria" v-model="form.cor_primaria" type="color" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel for="cor_secundaria" value="Cor Secundária" />
                                <input id="cor_secundaria" v-model="form.cor_secundaria" type="color" class="mt-1 block w-full" />
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-6">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salvar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
