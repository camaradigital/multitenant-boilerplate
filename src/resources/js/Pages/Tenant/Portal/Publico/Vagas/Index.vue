<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import TenantPublicLayout from '@/Layouts/TenantPublicLayout.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { initFlowbite } from 'flowbite';
import DOMPurify from 'dompurify';

// Lucide Icons
import { Search, Briefcase, ClipboardX, Loader2, X, MapPin, Clock, UserCheck, CircleDollarSign } from 'lucide-vue-next';

// Props
const props = defineProps({
    vagas: Object,
    filtros: Object,
    tenant: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});

// Inertia global props access
const page = usePage();

// Computed property to check if the user is logged in
const isLoggedIn = computed(() => !!page.props.auth.user);

// Computed property to determine which layout to use
const currentLayout = computed(() => {
    return isLoggedIn.value ? TenantLayout : TenantPublicLayout;
});

// Reactivity for filters
const filterForm = ref({
    search: props.filtros?.busca || '',
});

// Loading state for search
const isLoading = ref(false);

// --- Details Modal Logic ---
const selectedVaga = ref(null);
const showDetailsModal = ref(false);

// CORREÇÃO DE SEGURANÇA: Higieniza o HTML dos campos de texto rico
const sanitizeHtml = (html) => {
    if (!html) return '';
    return DOMPurify.sanitize(html);
};

const sanitizedDescription = computed(() => sanitizeHtml(selectedVaga.value?.descricao));
const sanitizedResponsabilidades = computed(() => sanitizeHtml(selectedVaga.value?.responsabilidades));
const sanitizedRequisitos = computed(() => sanitizeHtml(selectedVaga.value?.requisitos));

// Formata o salário para exibição
const formattedSalary = computed(() => {
    if (selectedVaga.value && selectedVaga.value.salario) {
        return parseFloat(selectedVaga.value.salario).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        });
    }
    return '';
});

const openVagaDetailsModal = (vaga) => {
    selectedVaga.value = vaga;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    setTimeout(() => {
        selectedVaga.value = null;
    }, 300); // Delay for modal animation
};
// --- End of Modal Logic ---

// Initializes Flowbite
onMounted(() => initFlowbite());
router.on('success', () => {
    initFlowbite();
    isLoading.value = false;
});

// Debounced search logic
let debounceTimeout = null;
watch(() => filterForm.value.search, (newSearch) => {
    isLoading.value = true;
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('portal.vagas.index'), { busca: newSearch }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }, 500);
});

// Function to clear the search input
const clearSearch = () => {
    filterForm.value.search = '';
};

// Date formatting function
const formatDate = (dateString) => {
    if (!dateString) return 'Data não informada';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', { timeZone: 'UTC' });
    } catch (e) {
        return 'Data inválida';
    }
};
</script>

<template>
    <Head title="Vagas de Emprego" />

    <component :is="currentLayout" :tenant="tenant" :can-login="canLogin" :can-register="canRegister">
        <div class="page-container font-sans">
            <div class="flex justify-center items-start py-12 px-4">
                <div class="content-container w-full max-w-7xl">

                    <div class="form-icon">
                        <Briefcase :size="32" class="icon-in-badge" />
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Vagas de Emprego</h2>
                            <p class="form-subtitle">Encontre oportunidades e venha fazer parte da nossa equipe.</p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 p-4">
                        <div class="w-full md:w-2/3 lg:w-1/2 mx-auto">
                            <form class="flex items-center" @submit.prevent>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-gray-500">
                                        <Search class="w-5 h-5" />
                                    </div>
                                    <input type="text" v-model="filterForm.search" placeholder="Buscar por cargo, palavra-chave..." class="form-input !pl-11">
                                    <button
                                        v-if="filterForm.search"
                                        type="button"
                                        @click="clearSearch"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white"
                                        aria-label="Limpar busca"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="isLoading" class="py-16 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <Loader2 class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-600 animate-spin" />
                                <p class="font-semibold text-lg text-gray-700 dark:text-gray-300">Carregando vagas...</p>
                            </div>
                        </div>

                        <div v-else-if="props.vagas.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6">
                            <div
                                v-for="vaga in props.vagas.data"
                                :key="vaga.id"
                                @click="openVagaDetailsModal(vaga)"
                                class="bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col"
                            >
                                <div class="p-5 flex flex-col flex-grow">
                                    <div class="flex justify-between items-start">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ vaga.titulo }}</h3>
                                    </div>
                                    <p v-if="vaga.empresa" class="text-sm font-medium text-emerald-600 dark:text-green-400 mb-3">{{ vaga.empresa.nome_fantasia }}</p>

                                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400 mt-auto">
                                        <div class="flex items-center">
                                            <MapPin class="w-4 h-4 mr-2 text-gray-400" />
                                            <span>{{ vaga.localizacao }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <Clock class="w-4 h-4 mr-2 text-gray-400" />
                                            <span>{{ vaga.tipo_contratacao }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/70 px-5 py-3 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700/50 rounded-b-xl">
                                    Publicada em: {{ formatDate(vaga.created_at) }}
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-16 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <ClipboardX class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-600" />
                                <p class="font-semibold text-lg text-gray-700 dark:text-gray-300">Nenhuma vaga encontrada</p>
                                <p class="text-sm mt-1">Tente refinar sua busca ou volte mais tarde para novas oportunidades.</p>
                            </div>
                        </div>
                    </div>

                       <nav v-if="props.vagas.links && props.vagas.links.length > 3" class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4 border-t-dynamic">
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                Mostrando <span class="font-semibold text-gray-900 dark:text-white">{{ props.vagas.from || 0 }}-{{ props.vagas.to || 0 }}</span> de <span class="font-semibold text-gray-900 dark:text-white">{{ props.vagas.total || 0 }}</span>
                            </span>
                            <ul class="inline-flex items-stretch -space-x-px">
                                <li v-for="(link, index) in props.vagas.links" :key="index">
                                    <span v-if="!link.url" class="pagination-link is-disabled" v-html="link.label"></span>
                                    <Link v-else :href="link.url" preserve-scroll preserve-state class="pagination-link" :class="{'is-active': link.active}" v-html="link.label"></Link>
                                </li>
                            </ul>
                        </nav>
                </div>
            </div>
        </div>
    </component>

    <!-- Modal de Detalhes da Vaga -->
    <div v-if="showDetailsModal" class="modal-overlay" @keydown.esc="closeDetailsModal">
         <div class="fixed inset-0" @click="closeDetailsModal"></div>
        <div v-if="selectedVaga" class="modal-card !max-w-2xl">
            <div class="modal-header !items-start !text-left">
                 <div class="flex w-full items-start justify-between">
                    <div>
                        <h3 class="modal-title">{{ selectedVaga.titulo }}</h3>
                        <p v-if="selectedVaga.empresa" class="text-base font-medium text-emerald-600 dark:text-green-400 mt-1">{{ selectedVaga.empresa.nome_fantasia }}</p>
                         <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mt-3 flex-wrap gap-x-4 gap-y-2">
                            <div class="flex items-center"><MapPin class="w-4 h-4 mr-1.5" /> {{ selectedVaga.localizacao }}</div>
                            <div class="flex items-center"><Clock class="w-4 h-4 mr-1.5" /> {{ selectedVaga.tipo_contratacao }}</div>
                            <div v-if="selectedVaga.salario" class="flex items-center"><CircleDollarSign class="w-4 h-4 mr-1.5" /> {{ formattedSalary }}</div>
                        </div>
                    </div>
                    <button @click="closeDetailsModal" class="p-2 -mt-4 -mr-4 text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                        <X class="w-6 h-6"/>
                    </button>
                 </div>
            </div>

            <div class="modal-content !text-left space-y-6 max-h-[60vh] overflow-y-auto pr-2">
                <!-- Descrição Principal -->
                <div>
                    <h4 class="modal-section-title">Descrição da Vaga</h4>
                    <div class="prose prose-sm dark:prose-invert max-w-none mt-2" v-html="sanitizedDescription"></div>
                </div>

                <!-- Responsabilidades -->
                <div v-if="selectedVaga.responsabilidades">
                    <h4 class="modal-section-title">Responsabilidades</h4>
                    <div class="prose prose-sm dark:prose-invert max-w-none mt-2" v-html="sanitizedResponsabilidades"></div>
                </div>

                <!-- Requisitos -->
                <div v-if="selectedVaga.requisitos">
                    <h4 class="modal-section-title">Requisitos</h4>
                    <div class="prose prose-sm dark:prose-invert max-w-none mt-2" v-html="sanitizedRequisitos"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button @click="closeDetailsModal" type="button" class="btn btn-secondary">Fechar</button>

                <div v-if="isLoggedIn">
                     <Link :href="route('candidaturas.create', { vaga: selectedVaga.id })" class="btn btn-primary">
                        <UserCheck class="w-4 h-4 mr-2" />
                        Candidatar-se
                    </Link>
                </div>
                <div v-else>
                     <Link :href="route('login')" class="btn btn-primary">
                        <UserCheck class="w-4 h-4 mr-2" />
                        Fazer login para se candidatar
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Bloco de estilo completo do template para garantir consistência visual */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
.font-sans { font-family: 'Poppins', sans-serif; }
.page-container { @apply bg-gray-100 dark:bg-transparent min-h-screen; }
.dark .page-container { background: radial-gradient(circle, #0D2C2A, #0A1E1C); }
.content-container {
    @apply relative w-full pt-16 rounded-3xl shadow-xl bg-white border border-gray-200;
    @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm;
}
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon {
    @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg;
    @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30;
}
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-300; }
.btn { @apply inline-flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-[#0A1E1C]; }
.btn-primary { @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; }
.btn-secondary { @apply bg-transparent ring-1 ring-inset text-gray-700 ring-gray-300 hover:bg-gray-100 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700/50 dark:hover:text-white; }
.form-input {
    @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5;
    @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400;
    @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500;
    @apply dark:bg-[#102523] dark:border-[#2a413d] dark:text-white dark:placeholder-gray-500;
    @apply dark:focus:ring-green-500 dark:focus:border-green-500;
}
.pagination-link {
    @apply flex items-center justify-center text-sm py-2 px-3 leading-tight border transition-colors duration-200;
    @apply text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700;
    @apply dark:text-gray-400 dark:bg-transparent dark:border-gray-700 dark:hover:bg-gray-800/50 dark:hover:text-white;
}
.pagination-link.is-active {
    @apply z-10 text-emerald-600 bg-emerald-50 border-emerald-300;
    @apply dark:text-white dark:bg-green-500/10 dark:border-green-500/50;
}
.pagination-link.is-disabled { @apply cursor-not-allowed opacity-50; }
ul li:first-child .pagination-link { @apply rounded-l-lg; }
ul li:last-child .pagination-link { @apply rounded-r-lg; }

/* Modal Styles */
.modal-overlay { @apply fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 flex justify-center items-center p-4 transition-opacity duration-300; }
.modal-card {
    @apply bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full transform transition-all duration-300 flex flex-col relative;
}
.modal-header { @apply flex flex-col items-center p-6; }
.modal-title { @apply text-xl font-bold text-gray-900 dark:text-white; }
.modal-section-title { @apply text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2; }
.modal-content { @apply px-6 pb-6 text-sm text-gray-700 dark:text-gray-400; }
.modal-footer { @apply flex justify-end gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl mt-auto; }

/* Adicionado para rolagem de conteúdo longo no modal */
.modal-content::-webkit-scrollbar { width: 6px; }
.modal-content::-webkit-scrollbar-track { background: transparent; }
.modal-content::-webkit-scrollbar-thumb { background-color: #a0aec0; border-radius: 20px; border: 3px solid transparent; }
.dark .modal-content::-webkit-scrollbar-thumb { background-color: #4a5568; }
</style>

