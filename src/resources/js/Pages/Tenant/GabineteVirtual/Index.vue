<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import { Send, Plus, X } from 'lucide-vue-next';

const props = defineProps({
    mensagens: Object,
});

const showForm = ref(false);

const form = useForm({
    assunto: '',
    mensagem: '',
});

const submit = () => {
    form.post(route('gabinete-virtual.store'), {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
        preserveScroll: true,
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Pendente':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'Resolvido':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'Sem Solução':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};
</script>

<template>
    <Head title="Gabinete Virtual" />

    <TenantLayout title="Gabinete Virtual">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Fale com o Presidente
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><Send :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Fale com o Presidente</h2>
                        <p class="form-subtitle">Envie sua mensagem, sugestão ou solicitação diretamente.</p>
                    </div>
                    <button @click="showForm = !showForm" :class="showForm ? 'btn-secondary' : 'btn-primary'" class="flex-shrink-0">
                        <X v-if="showForm" class="w-4 h-4 mr-2"/>
                        <Plus v-else class="w-4 h-4 mr-2"/>
                        {{ showForm ? 'Cancelar' : 'Nova Mensagem' }}
                    </button>
                </div>

                <div v-if="showForm" class="p-4 md:p-6">
                    <form @submit.prevent="submit">
                        <h3 class="role-name mb-4">Enviar Nova Mensagem</h3>
                        <div>
                            <label for="assunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assunto</label>
                            <input id="assunto" v-model="form.assunto" type="text" class="mt-1 input-form" required />
                            <InputError class="mt-2" :message="form.errors.assunto" />
                        </div>

                        <div class="mt-4">
                            <label for="mensagem" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem</label>
                            <textarea id="mensagem" v-model="form.mensagem" class="mt-1 input-form" rows="6" required></textarea>
                            <InputError class="mt-2" :message="form.errors.mensagem" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                             <button type="submit" class="btn-primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Enviar Mensagem
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-4 md:p-6" :class="{ 'border-t-dynamic' : showForm }">
                    <h3 class="role-name mb-4">Suas Mensagens</h3>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Assunto</th>
                                    <th scope="col" class="px-6 py-3">Data de Envio</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="mensagens.data.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center">Você ainda não enviou nenhuma mensagem.</td>
                                </tr>
                                <tr v-for="mensagem in mensagens.data" :key="mensagem.id">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ mensagem.assunto }}</td>
                                    <td class="px-6 py-4">{{ new Date(mensagem.created_at).toLocaleDateString('pt-BR') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(mensagem.status)">{{ mensagem.status }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('gabinete-virtual.show', mensagem.id)" class="font-medium text-emerald-600 dark:text-emerald-400 hover:underline">Ver Detalhes</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="pt-6" :links="mensagens.links" v-if="mensagens.data.length > 0"/>
                </div>
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
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }

/* Estilos de botões */
.btn-base { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 disabled:opacity-50; }
.btn-primary { @apply btn-base bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply btn-base bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-500; }

/* Estilo para inputs e selects do formulário */
.input-form { @apply block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-emerald-500 focus:border-emerald-500; }
</style>
