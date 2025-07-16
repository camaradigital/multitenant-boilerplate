<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';

defineProps({ tiposServico: Array });

const possuiLimite = ref(false);

const form = useForm({
    nome: '',
    descricao: '',
    tipo_servico_id: '',
    regras_limite: null,
});

const periodos = [
    { value: 'diario', label: 'Diário' },
    { value: 'semanal', label: 'Semanal' },
    { value: 'mensal', label: 'Mensal' },
    { value: 'anual', label: 'Anual' },
];

function toggleLimite() {
    if (possuiLimite.value) {
        form.regras_limite = { limite: 1, periodo: 'semanal' };
    } else {
        form.regras_limite = null;
    }
}

const submit = () => {
    form.post(route('servicos.store'));
};
</script>
<template>
    <Head title="Novo Serviço" />
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo Serviço</h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="nome" value="Nome do Serviço" />
                            <TextInput id="nome" v-model="form.nome" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.nome" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="tipo_servico_id" value="Tipo de Serviço" />
                            <select v-model="form.tipo_servico_id" id="tipo_servico_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled>Selecione um tipo</option>
                                <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                            </select>
                            <InputError :message="form.errors.tipo_servico_id" class="mt-2" />
                        </div>

                        <div class="mt-6 border-t pt-4">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="possuiLimite" @change="toggleLimite" />
                                <span class="ms-2 text-sm text-gray-600">Possui Limite de Uso? </span>
                            </label>

                            <div v-if="possuiLimite" class="mt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="limite" value="Quantidade" />
                                    <TextInput id="limite" v-model="form.regras_limite.limite" type="number" min="1" class="mt-1 block w-full" required />
                                    <InputError :message="form.errors['regras_limite.limite']" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="periodo" value="Período" />
                                    <select v-model="form.regras_limite.periodo" id="periodo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option v-for="p in periodos" :key="p.value" :value="p.value">{{ p.label }}</option>
                                    </select>
                                    <InputError :message="form.errors['regras_limite.periodo']" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salvar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
