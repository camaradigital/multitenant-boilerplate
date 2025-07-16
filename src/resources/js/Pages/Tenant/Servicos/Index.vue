<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ servicos: Array });
</script>
<template>
    <Head title="Serviços" />
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gerenciar Serviços</h2>
                <Link :href="route('servicos.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Novo Serviço</Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <ul class="divide-y divide-gray-200">
                        <li v-for="servico in servicos" :key="servico.id" class="p-4 flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-900">{{ servico.nome }}</p>
                                <p class="text-sm text-gray-500">{{ servico.tipo_servico.nome }}</p>
                            </div>
                            <Link :href="route('servicos.edit', servico.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
