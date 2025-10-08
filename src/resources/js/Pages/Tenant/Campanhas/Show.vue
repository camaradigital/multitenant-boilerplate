<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Megaphone, ArrowLeft, Users, Filter, MessageCircle, Calendar } from 'lucide-vue-next';

const props = defineProps({
    campanha: Object,
});

// Funções auxiliares que você pode reaproveitar
const getStatusInfo = (campanha) => {
    if (campanha.total_enviado > 0) {
        return { text: 'Enviado', class: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' };
    }
    return { text: 'Agendado', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' };
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString('pt-BR', {
        day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Campanha: ${campanha.data.titulo}`" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalhes da Campanha
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">

                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <Megaphone :size="32" class="text-white" />
                    </div>

                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pt-12 p-6 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ campanha.data.titulo }}</h2>
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusInfo(campanha.data).class">
                                    {{ getStatusInfo(campanha.data).text }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Criada em: {{ formatDate(campanha.data.created_at) }}
                            </p>
                        </div>
                        <Link :href="route('admin.campanhas.index')" class="flex-shrink-0 inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                            <ArrowLeft class="w-4 h-4 mr-2"/>
                            Voltar para Campanhas
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">

                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-xl border dark:border-white/10">
                                <h3 class="flex items-center gap-2 text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">
                                    <MessageCircle class="h-5 w-5" />
                                    Conteúdo da Mensagem
                                </h3>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ campanha.data.mensagem }}</p>
                            </div>

                             <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-xl border dark:border-white/10">
                                <h3 class="flex items-center gap-2 text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">
                                    <Users class="h-5 w-5" />
                                    Resultados
                                </h3>
                                <div class="flex items-center gap-4 text-emerald-800 dark:text-emerald-300">
                                    <Users class="h-10 w-10 flex-shrink-0" />
                                    <div class="text-left">
                                        <span class="font-bold text-4xl">{{ campanha.data.total_enviado || 0 }}</span>
                                        <p class="text-sm font-medium">cidadãos receberam a comunicação</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-800/50 p-6 rounded-xl border dark:border-white/10">
                            <h3 class="flex items-center gap-2 text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">
                                <Filter class="h-5 w-5" />
                                Segmentação Usada
                            </h3>
                            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                                <li class="flex justify-between"><strong>Faixa Etária:</strong> <span>{{ campanha.data.segmentacao?.idade_min || 'N/A' }} - {{ campanha.data.segmentacao?.idade_max || 'N/A' }}</span></li>
                                <li class="flex justify-between"><strong>Gênero:</strong> <span>{{ campanha.data.segmentacao?.genero || 'Todos' }}</span></li>
                                <li class="flex justify-between"><strong>Renda Mínima:</strong> <span>{{ campanha.data.segmentacao?.renda_min ? `R$ ${campanha.data.segmentacao.renda_min}` : 'N/A' }}</span></li>
                                <li class="flex justify-between"><strong>Renda Máxima:</strong> <span>{{ campanha.data.segmentacao?.renda_max ? `R$ ${campanha.data.segmentacao.renda_max}` : 'N/A' }}</span></li>
                                <div>
                                    <strong>Bairros:</strong>
                                    <div v-if="campanha.data.segmentacao?.bairros?.length > 0" class="mt-1 flex flex-wrap gap-2">
                                        <span v-for="bairro in campanha.data.segmentacao.bairros" :key="bairro" class="px-2 py-1 text-xs bg-emerald-100 text-emerald-800 rounded-full dark:bg-emerald-900/50 dark:text-emerald-200">
                                            {{ bairro }}
                                        </span>
                                    </div>
                                    <span v-else>Todos</span>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
