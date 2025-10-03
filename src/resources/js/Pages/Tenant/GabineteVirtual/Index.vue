<script setup>
import { ref } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import { Send, Plus, X, ArrowRight, Mailbox } from 'lucide-vue-next';

const props = defineProps({
    mensagens: Object,
});

const isModalOpen = ref(false);

const form = useForm({
    assunto: '',
    mensagem: '',
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    form.post(route('gabinete-virtual.store'), {
        onSuccess: () => {
            closeModal();
        },
        preserveScroll: true,
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Pendente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
        case 'Resolvido':
            return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
        case 'Sem Solução':
            return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-300';
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit', month: 'short', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Gabinete Virtual" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Fale com o Presidente
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Card Principal -->
                <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">

                    <!-- Ícone no Topo -->
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <Send :size="32" class="text-white" />
                    </div>

                    <!-- Cabeçalho do Card -->
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pt-12 p-6 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Fale com o Presidente</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Envie sua mensagem, sugestão ou solicitação diretamente.</p>
                        </div>
                        <button @click="openModal" class="flex-shrink-0 inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 dark:focus:ring-offset-gray-900">
                            <Plus class="w-4 h-4 mr-2"/>
                            Nova Mensagem
                        </button>
                    </div>

                    <!-- Lista de Mensagens e Estado Vazio -->
                    <div class="p-4 md:p-6">
                        <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">Suas Mensagens</h3>

                        <!-- Estado Vazio -->
                        <div v-if="mensagens.data.length === 0" class="text-center py-16">
                            <Mailbox class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Nenhuma mensagem enviada</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Clique em "Nova Mensagem" para enviar sua primeira solicitação.</p>
                        </div>

                        <!-- Lista de Cards de Mensagens -->
                        <div v-else class="space-y-3">
                            <Link v-for="mensagem in mensagens.data" :key="mensagem.id" :href="route('gabinete-virtual.show', mensagem.id)" class="block p-4 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 hover:border-emerald-500 dark:hover:border-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all duration-200 group">
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div class="flex-grow">
                                        <div class="flex items-center gap-3">
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(mensagem.status)">
                                                {{ mensagem.status }}
                                            </span>
                                             <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(mensagem.created_at) }}</p>
                                        </div>
                                        <p class="mt-2 font-semibold text-gray-900 dark:text-white group-hover:text-emerald-800 dark:group-hover:text-emerald-300">{{ mensagem.assunto }}</p>
                                    </div>
                                    <div class="flex-shrink-0 flex items-center gap-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                        Ver Detalhes
                                        <ArrowRight class="h-4 w-4" />
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <Pagination class="pt-6" :links="mensagens.links" v-if="mensagens.data.length > 0"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Nova Mensagem -->
        <Transition name="modal-fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div @click.self="closeModal" class="absolute inset-0"></div>
                <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-xl transform transition-all">
                    <form @submit.prevent="submit">
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-white/10">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Enviar Nova Mensagem</h3>
                            <button @click="closeModal" type="button" class="p-1 rounded-full text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 hover:text-gray-600 dark:hover:text-gray-200">
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div>
                                <label for="assunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assunto</label>
                                <input id="assunto" v-model="form.assunto" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" required />
                                <InputError class="mt-2" :message="form.errors.assunto" />
                            </div>

                            <div>
                                <label for="mensagem" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem</label>
                                <textarea id="mensagem" v-model="form.mensagem" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500" rows="6" required></textarea>
                                <InputError class="mt-2" :message="form.errors.mensagem" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 p-5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-white/10 rounded-b-2xl">
                            <button @click="closeModal" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">
                                Cancelar
                            </button>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 disabled:opacity-50" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Enviar Mensagem
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

    </TenantLayout>
</template>

<style>
/* Estilos para a transição do modal */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}
.modal-fade-enter-active .transform,
.modal-fade-leave-active .transform {
    transition: transform 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.modal-fade-enter-from .transform,
.modal-fade-leave-to .transform {
    transform: scale(0.95) translateY(20px);
}
</style>
