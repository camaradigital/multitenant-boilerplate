<script setup>
import { ref } from 'vue';
import axios from 'axios'; // Import axios for API calls
import { debounce } from 'lodash-es'; // Import debounce
import vSelect from 'vue-select'; // Import vue-select
import 'vue-select/dist/vue-select.css'; // Import vue-select CSS
import InputError from '@/Components/InputError.vue';
import { MapPin } from 'lucide-vue-next';

const props = defineProps({
    form: Object,
    realtimeErrors: Object,
    // REMOVED: Bairros are fetched dynamically now
    // bairros: Array,
});

const emit = defineEmits(['buscar-cep']);

// --- Autocomplete Logic ---
const bairroOptions = ref([]); // Options for v-select
const isLoading = ref(false);
const selectedBairro = ref(null); // Local ref to manage v-select's object selection

// Function to fetch bairros from the API
const fetchBairros = debounce(async (search, loading) => {
    if (search.length < 3) {
        bairroOptions.value = []; // Clear options if search term is too short
        return;
    }
    loading(true);
    isLoading.value = true; // Also manage our own loading state if needed elsewhere
    try {
        const response = await axios.get(route('bairros.search', { term: search }));
        const foundBairros = response.data;

        // If exact match found, select it (helps with CEP lookup pre-fill)
        const exactMatch = foundBairros.find(b => b.nome.toLowerCase() === search.toLowerCase());
        if (exactMatch) {
            selectedBairro.value = exactMatch; // Pre-select if found
             props.form.bairro_id = exactMatch.id; // Update form immediately
        }

        // Add a "suggest" option if no results are found
        if (foundBairros.length === 0) {
             bairroOptions.value = [{
                 id: search, // Use the search term itself as the ID for suggestion
                 nome: `Sugerir "${search}" como novo bairro`,
                 isSuggestion: true // Flag to identify this option
             }];
        } else {
             bairroOptions.value = foundBairros;
        }

    } catch (error) {
        console.error("Erro ao buscar bairros:", error);
        bairroOptions.value = []; // Clear options on error
    } finally {
        loading(false);
        isLoading.value = false;
    }
}, 350); // Debounce time in ms

// Function called when a bairro is selected or deselected
const onBairroSelected = (selectedOption) => {
    if (selectedOption) {
        // If it's a suggestion, form.bairro_id gets the string name
        // Otherwise, it gets the numeric id
        props.form.bairro_id = selectedOption.id;
        selectedBairro.value = selectedOption; // Keep v-select model updated
         // Clear any previous bairro_id error when a selection is made
        props.form.clearErrors('bairro_id');
    } else {
        // When cleared, reset form.bairro_id and the local ref
        props.form.bairro_id = null;
        selectedBairro.value = null;
    }
};

// --- Watch for CEP lookup potentially setting the bairro name ---
// This is tricky because useCepLookup sets form.profile_data.endereco_bairro,
// but we now use form.bairro_id which expects an ID or a suggestion string.
// A potential solution is to modify useCepLookup OR trigger a search here.
// Let's try triggering a search if form.profile_data.endereco_bairro is set by CEP
/*
watch(() => props.form.profile_data.endereco_bairro, (newBairroName) => {
    if (newBairroName && !props.form.bairro_id) {
        // If CEP lookup provided a name, but we don't have an ID yet,
        // trigger a search for that name to try and find/select it.
        // We pass a dummy loading function as fetchBairros expects it.
        fetchBairros(newBairroName, () => {});
    }
});
*/
// Simpler approach: If CEP returns a bairro ID (if API supports it), useCepLookup should set form.bairro_id directly.
// If CEP only returns name, let the user confirm via autocomplete.


</script>

<template>
    <div class="form-section-title">Endereço</div>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-x-6 gap-y-6">
        <div class="input-container md:col-span-2">
            <label for="cep" class="form-label">CEP</label>
            <div class="relative">
                <span class="input-icon"><MapPin :size="16" /></span>
                <input
                    id="cep"
                    v-model="form.profile_data.endereco_cep"
                    @blur="emit('buscar-cep')"
                    type="text"
                    class="form-input"
                    :class="{ 'input-invalid': realtimeErrors.cep || form.errors['profile_data.endereco_cep'], 'input-valid': !realtimeErrors.cep && !form.errors['profile_data.endereco_cep'] && form.profile_data.endereco_cep }"
                    placeholder="00000-000"
                    v-maska data-maska="#####-###"
                />
            </div>
            <InputError class="form-error" :message="form.errors['profile_data.endereco_cep']" />
            <div v-if="realtimeErrors.cep" class="form-error">{{ realtimeErrors.cep }}</div>
        </div>
        <div class="input-container md:col-span-4">
            <label for="logradouro" class="form-label">Logradouro</label>
            <input id="logradouro" v-model="form.profile_data.endereco_logradouro" type="text" class="form-input !pl-5" :class="{'input-invalid': form.errors['profile_data.endereco_logradouro']}" placeholder="Rua, Avenida..."/>
             <InputError class="form-error" :message="form.errors['profile_data.endereco_logradouro']" />
        </div>
        <div class="input-container md:col-span-2">
            <label for="numero" class="form-label">Número</label>
            <input id="numero" v-model="form.profile_data.endereco_numero" type="text" class="form-input !pl-5" :class="{'input-invalid': form.errors['profile_data.endereco_numero']}" placeholder="Ex: 123"/>
             <InputError class="form-error" :message="form.errors['profile_data.endereco_numero']" />
        </div>

        <div class="input-container md:col-span-4">
            <label for="bairro" class="form-label">Bairro/Córrego</label>
            <v-select
                id="bairro"
                :options="bairroOptions"
                label="nome"
                :reduce="bairro => bairro.id"
                v-model="form.bairro_id"
                @search="fetchBairros"
                :filterable="false"
                :loading="isLoading"
                placeholder="Digite para buscar ou sugerir..."
                class="form-input p-0 border-0"
                 :class="{ '!border-red-500 dark:!border-red-400': form.errors.bairro_id }"
            >
                <template #option="{ nome, isSuggestion }">
                    <span :class="{ 'italic text-emerald-600 dark:text-green-400': isSuggestion }">{{ nome }}</span>
                </template>
                <template #no-options="{ search, searching, loading }">
                    <span v-if="loading || searching">Buscando...</span>
                    <span v-else-if="search.length >= 3">Nenhum bairro encontrado.</span>
                    <span v-else>Digite ao menos 3 caracteres.</span>
                </template>
                 <template #selected-option-container="{ option }">
                     <span class="vs__selected">{{ option.label || option.nome }}</span>
                 </template>


            </v-select>
            <InputError class="form-error" :message="form.errors.bairro_id" />
        </div>

        <div class="input-container md:col-span-4">
            <label for="cidade" class="form-label">Cidade</label>
            <input id="cidade" v-model="form.profile_data.endereco_cidade" type="text" class="form-input !pl-5" :class="{'input-invalid': form.errors['profile_data.endereco_cidade']}" placeholder="Sua cidade" required/>
            <InputError class="form-error" :message="form.errors['profile_data.endereco_cidade']" />
        </div>
        <div class="input-container md:col-span-2">
            <label for="estado" class="form-label">Estado</label>
            <input id="estado" v-model="form.profile_data.endereco_estado" type="text" class="form-input !pl-5" :class="{'input-invalid': form.errors['profile_data.endereco_estado']}" placeholder="UF"/>
             <InputError class="form-error" :message="form.errors['profile_data.endereco_estado']" />
        </div>
    </div>
</template>

<style>
/* Scoped styles might not reach vue-select, define globally or use :deep() in parent */
.vs__dropdown-toggle {
  @apply !min-h-[3rem] !rounded-xl !border-gray-300 dark:!border-[#2a413d] dark:!bg-[#102523] dark:!text-white;
  /* Match your form-input height and border */
}
.vs__search {
   @apply !text-sm !py-0 !px-0 dark:!text-white; /* Adjust padding if needed */
}
.vs__selected {
  @apply !text-sm !py-0 !pl-0 !m-0 !text-gray-900 dark:!text-white; /* Adjust padding */
}
.vs__selected-options {
    @apply !p-0 !pl-5; /* Match form-input left padding */
}
.vs__clear, .vs__open-indicator {
    @apply dark:fill-gray-400;
}
.vs__dropdown-menu {
  @apply !rounded-xl !border-gray-300 dark:!border-[#2a413d] dark:!bg-[#102523];
}
.vs__dropdown-option {
  @apply !text-sm !py-2.5 dark:!text-gray-300; /* Match form-input vertical padding roughly */
}
.vs__dropdown-option--highlight {
   @apply !bg-emerald-600/20 dark:!bg-[#43DB9E]/20 !text-emerald-800 dark:!text-green-300;
}
.vs__no-options {
  @apply !text-sm !text-gray-500 dark:!text-gray-400 !py-2.5;
}
.vs--loading .vs__spinner {
    @apply !border-emerald-600 dark:!border-green-400;
}

/* Add red border on error */
.form-input.p-0.border-0.vs--open, /* Keep border consistent when dropdown is open */
.form-input.p-0.border-0 .vs__dropdown-toggle {
    /* Apply base border style from :deep(.form-input) */
    @apply border bg-white border-gray-300 dark:bg-[#102523] dark:border-[#2a413d];
}

/* Error state border */
.form-input.p-0.border-0.\\!border-red-500 .vs__dropdown-toggle {
    @apply !border-red-500 focus:!border-red-500 focus:!ring-red-500 dark:!border-red-400 dark:focus:!border-red-400 dark:focus:!ring-red-400;
}


</style>
