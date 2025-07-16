<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
const props = defineProps({ solicitacao: Object });
const form = useForm({ status: props.solicitacao.status, observacoes: props.solicitacao.observacoes });
const submit = () => {
    form.put(route('solicitacoes.update', props.solicitacao.id));
};
</script>
<template>
    <Head :title="`Solicitação #${solicitacao.id}`" />
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalhes da Solicitação #{{ solicitacao.id }}</h2>
        </template>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="font-bold">Serviço: {{ solicitacao.servico.nome }}</h3>
                    <p class="mt-2"><strong>Cidadão:</strong> {{ solicitacao.cidadao.name }}</p>
                    <p><strong>Email:</strong> {{ solicitacao.cidadao.email }}</p>
                    <p class="mt-4"><strong>Status Atual:</strong> <span class="font-semibold">{{ solicitacao.status }}</span></p>
                    <p v-if="solicitacao.atendente"><strong>Atendido por:</strong> {{ solicitacao.atendente.name }}</p>
                    <p v-if="solicitacao.observacoes"><strong>Última Observação:</strong> {{ solicitacao.observacoes }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <h3 class="font-bold mb-2">Alterar Status</h3>
                        <div>
                            <label for="status" class="block font-medium text-sm text-gray-700">Novo Status</label>
                            <select v-model="form.status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option>Em Atendimento</option>
                                <option>Concluído</option>
                                <option>Cancelado</option>
                            </select>
                        </div>
                        <div class="mt-4">
                            <label for="observacoes" class="block font-medium text-sm text-gray-700">Observações</label>
                            <textarea v-model="form.observacoes" id="observacoes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        <div class="mt-4">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salvar Alterações</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
