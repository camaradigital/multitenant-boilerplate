<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

defineProps({ tiposServico: Array });
const successMessage = ref(usePage().props.flash.success);
</script>
<template>
    <Head title="Tipos de Serviço" />
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tipos de Serviço</h2>
                <Link :href="route('tipos-servico.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Novo Tipo</Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="successMessage" class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">{{ successMessage }}</div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <ul class="divide-y divide-gray-200">
                        <li v-for="tipo in tiposServico" :key="tipo.id" class="p-4 flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-900">{{ tipo.nome }}</p>
                                <p class="text-sm text-gray-500">{{ tipo.descricao }}</p>
                            </div>
                            <Link :href="route('tipos-servico.edit', tipo.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
