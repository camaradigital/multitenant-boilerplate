<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import TenantPublicLayout from '@/Layouts/TenantPublicLayout.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import DocumentoCard from '@/Components/DocumentoCard.vue';
import { initFlowbite } from 'flowbite';

// Lucide Icons
import { Search, FolderSearch, FileText, Loader2, X } from 'lucide-vue-next';

// Props
const props = defineProps({
    documentos: Object,
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

// --- Details Modal Logic (adapted from template) ---
const selectedDocument = ref(null);
const showDetailsModal = ref(false);

const openDocumentDetailsModal = (documento) => {
    selectedDocument.value = documento;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    setTimeout(() => {
        selectedDocument.value = null;
    }, 300); // Delay for modal animation
};
// --- End of Modal Logic ---

// Initializes Flowbite (if there are dropdowns or other JS components)
onMounted(() => initFlowbite());
router.on('success', () => {
    initFlowbite();
    isLoading.value = false;
});

// Debounced search logic
let debounceTimeout = null;
watch(filterForm, (newValue) => {
    isLoading.value = true;
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('portal.achados-e-perdidos'), { busca: newValue.search }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }, 500);
}, { deep: true });

// Function to clear the search input
const clearSearch = () => {
    filterForm.value.search = '';
};

// --- ENHANCED DATE FORMATTING FUNCTION ---
const formatDate = (dateString) => {
    // Check if the string exists and is of type string
    if (!dateString || typeof dateString !== 'string') {
        return 'Data não informada';
    }

    // A common issue is a date string with microseconds (.000000) and 'Z' at the end.
    // The JavaScript Date object doesn't handle this well. We'll use a regex to clean it.
    const cleanDateString = dateString.replace(/\.\d+Z$/, '');

    const dateObject = new Date(cleanDateString);

    if (isNaN(dateObject.getTime())) {
        console.error('Error converting date. Original string:', dateString);
        return `Formato de data inválido: ${dateString}`;
    }

    return dateObject.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

</script>

<template>
    <Head title="Achados e Perdidos" />

    <component :is="currentLayout" :tenant="tenant" :can-login="canLogin" :can-register="canRegister">
        <div class="page-container font-sans">
            <div class="flex justify-center items-start py-12 px-4">
                <div class="content-container w-full max-w-7xl">

                    <div class="form-icon">
                        <FolderSearch :size="32" class="icon-in-badge" />
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Achados e Perdidos</h2>
                            <p class="form-subtitle">Verifique se seu documento foi encontrado em nossa instituição.</p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 p-4">
                        <div class="w-full md:w-2/3 lg:w-1/2 mx-auto">
                            <form class="flex items-center" @submit.prevent>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-gray-500">
                                        <Search class="w-5 h-5" />
                                    </div>
                                    <input type="text" v-model="filterForm.search" placeholder="Buscar pelo nome completo..." class="form-input !pl-11">
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
                                <p class="font-semibold text-lg text-gray-700 dark:text-gray-300">Carregando...</p>
                            </div>
                        </div>

                        <div v-else-if="documentos.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <DocumentoCard
                                v-for="doc in documentos.data"
                                :key="doc.id"
                                :documento="doc"
                                @view-details="openDocumentDetailsModal(doc)"
                            />
                        </div>

                        <div v-else class="py-16 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <FileText class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-600" />
                                <p class="font-semibold text-lg text-gray-700 dark:text-gray-300">Nenhum documento encontrado</p>
                                <p class="text-sm mt-1">Tente refinar sua busca. Se não encontrar, volte mais tarde.</p>
                            </div>
                        </div>
                    </div>

                    <nav v-if="documentos.links && documentos.links.length > 3" class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4 border-t-dynamic">
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            Mostrando <span class="font-semibold text-gray-900 dark:text-white">{{ documentos.from || 0 }}-{{ documentos.to || 0 }}</span> de <span class="font-semibold text-gray-900 dark:text-white">{{ documentos.total || 0 }}</span>
                        </span>
                        <ul class="inline-flex items-stretch -space-x-px">
                            <li v-for="(link, index) in documentos.links" :key="index">
                                <span v-if="!link.url" class="pagination-link is-disabled" v-html="link.label"></span>
                                <Link v-else :href="link.url" preserve-scroll preserve-state class="pagination-link" :class="{'is-active': link.active}" v-html="link.label"></Link>
                            </li>
                        </ul>
                    </nav>

                </div>
            </div>
        </div>
    </component>

    <div v-show="showDetailsModal" class="modal-overlay" @keydown.esc="closeDetailsModal">
        <div v-if="selectedDocument" class="modal-card">
            <div class="modal-header">
                <div class="modal-icon is-info"><FileText class="h-6 w-6"/></div>
                <h3 class="modal-title">Detalhes do Documento</h3>
            </div>
            <div class="modal-content !text-left space-y-3">
                 <p><strong class="font-semibold text-gray-900 dark:text-white">Nome:</strong> {{ selectedDocument.nome_completo }}</p>
                 <p><strong class="font-semibold text-gray-900 dark:text-white">Documento:</strong> {{ selectedDocument.tipo_documento }}</p>
                 <p><strong class="font-semibold text-gray-900 dark:text-white">Encontrado em:</strong> {{ formatDate(selectedDocument.data_encontrado) }}</p>
                 <p v-if="selectedDocument.local_encontrado"><strong class="font-semibold text-gray-900 dark:text-white">Local:</strong> {{ selectedDocument.local_encontrado }}</p>

                <div class="!mt-6 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/50">
                    <p class="text-sm text-emerald-800 dark:text-emerald-200 font-medium">
                        Para retirar, dirija-se à sede da instituição com um documento de identificação válido.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button @click="closeDetailsModal" type="button" class="btn btn-secondary w-full">Fechar</button>
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
.header-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
/* ✨ CORREÇÃO APLICADA AQUI 👇 */
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-300; }
.btn { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-[#0A1E1C]; }
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
    @apply bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-sm w-full transform transition-all duration-300;
}
.modal-header { @apply flex flex-col items-center p-6 text-center; }
.modal-icon { @apply flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full mb-4; }
.modal-icon.is-info { @apply bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-300; }
.modal-title { @apply text-xl font-bold text-gray-900 dark:text-white; }
.modal-content { @apply px-6 pb-6 text-sm text-gray-600 dark:text-gray-400 text-center; }
.modal-footer { @apply flex justify-end gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl; }
</style>
