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
});

const emit = defineEmits(['buscar-cep']);

// --- Autocomplete Logic ---
const bairroOptions = ref([]); // Options for v-select
const isLoading = ref(false);
// Removed selectedBairro ref, directly bind v-model to form.bairro_id with :reduce

// Function to fetch bairros from the API
const fetchBairros = debounce(async (search, loading) => {
    if (search.length < 3) {
        bairroOptions.value = []; // Clear options if search term is too short
        return;
    }
    loading(true);
    isLoading.value = true;
    try {
        const response = await axios.get(route('bairros.search', { term: search }));
        const foundBairros = response.data;

        // Add a "suggest" option ONLY if no results are found AND search is long enough
        if (foundBairros.length === 0 && search.length >= 3) {
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

// Function to handle changes (optional, useful for clearing errors)
const onBairroChange = (value) => {
    // value here is already the reduced one (id or string) due to :reduce
    if (value) {
        props.form.clearErrors('bairro_id');
    }
};

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
                @update:modelValue="onBairroChange" {{-- Call clearErrors on selection --}}
                :filterable="false"
                :loading="isLoading"
                placeholder="Digite para buscar ou sugerir..."
                :class="[
                    'form-input p-0 border-0', // Base wrapper reset styles
                    { '!border-red-500 dark:!border-red-400': form.errors.bairro_id } // Conditional error class for the wrapper
                ]"
            >
                 {{-- Custom appearance for options --}}
                <template #option="{ nome, isSuggestion }">
                    <span :class="{ 'italic text-emerald-600 dark:text-green-400': isSuggestion }">{{ nome }}</span>
                </template>
                 {{-- Message when no options match --}}
                <template #no-options="{ search, searching, loading }">
                    <span v-if="loading || searching">Buscando...</span>
                     <span v-else-if="search.length >= 3 && bairroOptions.length === 0">Nenhum bairro encontrado.</span>
                    <span v-else>Digite ao menos 3 caracteres.</span>
                </template>
                 {{-- Display selected option label --}}
                 <template #selected-option="option">
                     {{ option.nome || form.bairro_id }} {{-- Show label or the raw value if needed --}}
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
/* Global or :deep() styles for v-select */
.vs__dropdown-toggle {
  @apply !min-h-[3rem] !rounded-xl !border-gray-300 dark:!border-[#2a413d] dark:!bg-[#102523] dark:!text-white;
}
.vs__search {
   @apply !text-sm !py-0 !px-0 dark:!text-white;
}
.vs__selected {
  @apply !text-sm !py-0 !pl-0 !m-0 !text-gray-900 dark:!text-white;
}
.vs__selected-options {
    @apply !p-0 !pl-5 !flex !items-center; /* Add flex items-center */
    padding-top: 0.875rem; /* ~ py-3.5 */
    padding-bottom: 0.875rem; /* ~ py-3.5 */
    min-height: 3rem; /* Match form-input */
}

/* Ensure placeholder is visible and styled */
input.vs__search::placeholder {
    @apply text-gray-400 dark:text-gray-500 text-sm;
}


.vs__clear, .vs__open-indicator {
    @apply dark:fill-gray-400;
}
.vs__dropdown-menu {
  @apply !rounded-xl !border-gray-300 dark:!border-[#2a413d] dark:!bg-[#102523];
}
.vs__dropdown-option {
  @apply !text-sm !py-2.5 dark:!text-gray-300;
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

/* Base border style applied via the wrapper div's class logic */
.form-input.p-0.border-0 .vs__dropdown-toggle {
    /* Apply base border style from :deep(.form-input) */
     @apply !border bg-white !border-gray-300 dark:!bg-[#102523] dark:!border-[#2a413d];
}

/* Error state border applied via the wrapper div's class logic */
.form-input.p-0.border-0.\\!border-red-500 .vs__dropdown-toggle {
    @apply !border-red-500 focus-within:!ring-1 focus-within:!ring-red-500 dark:!border-red-400 dark:focus-within:!ring-red-400;
}
/* Valid state border (optional, if you want green border on valid) */
/*
.form-input.p-0.border-0.input-valid .vs__dropdown-toggle {
     @apply !border-emerald-500 focus-within:!ring-1 focus-within:!ring-emerald-500 dark:!border-green-500 dark:focus-within:!ring-green-500;
}
*/
</style>
