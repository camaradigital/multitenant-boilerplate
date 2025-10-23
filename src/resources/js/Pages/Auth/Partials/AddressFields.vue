<script setup>
import InputError from '@/Components/InputError.vue';
// ADICIONADO: Ícones para o novo slot
import { MapPin, Search, Loader2 } from 'lucide-vue-next'; 
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

defineProps({
    form: Object,
    realtimeErrors: Object,
    bairrosOptions: Array, // <-- Recebe as opções da busca (vem do Register.vue)
});

const emit = defineEmits(['buscar-cep', 'search-bairros']);

// ATUALIZADO: Lógica de busca melhorada
const handleBairroSearch = (search, loading) => {
    if (search.length > 2) {
        // Ativa o spinner do v-select
        loading(true); 
        // Emite para o componente pai (Register.vue) fazer a chamada API
        emit('search-bairros', search, loading);
    } else {
        // Garante que o spinner pare se o usuário apagar o texto
        loading(false); 
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
                    :class="{ 'input-invalid': realtimeErrors.cep, 'input-valid': !realtimeErrors.cep && form.profile_data.endereco_cep }"
                    placeholder="00000-000"
                    v-maska data-maska="#####-###"
                />
            </div>
            <InputError class="form-error" :message="form.errors['profile_data.endereco_cep']" />
            <div v-if="realtimeErrors.cep" class="form-error">{{ realtimeErrors.cep }}</div>
        </div>

        <div class="input-container md:col-span-4">
            <label for="logradouro" class="form-label">Logradouro</label>
            <input id="logradouro" v-model="form.profile_data.endereco_logradouro" type="text" class="form-input !pl-5" placeholder="Rua, Avenida..."/>
        </div>

        <div class="input-container md:col-span-2">
            <label for="numero" class="form-label">Número</label>
            <input id="numero" v-model="form.profile_data.endereco_numero" type="text" class="form-input !pl-5" placeholder="Ex: 123"/>
        </div>

        <div class="input-container md:col-span-4">
            <label for="bairro" class="form-label">Bairro/Córrego</label>
            
            <div class="relative">
                <span class="input-icon"><MapPin :size="16" /></span>
                
                <v-select
                    id="bairro"
                    v-model="form.bairro_id"
                    :options="bairrosOptions"
                    label="nome"
                    :reduce="bairro => bairro.id"
                    @search="handleBairroSearch"
                    :filterable="false"
                    taggable
                    placeholder="Digite para buscar ou criar um novo bairro"
                >
                    <template #no-options="{ search, loading }">
                        <div v-if="loading" class="flex items-center justify-center p-3 text-sm text-gray-500">
                            <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                            Buscando...
                        </div>
                        
                        <div v-else-if="search.length <= 2" class="flex items-center justify-center p-3 text-sm text-gray-500">
                            <Search class="w-4 h-4 mr-2" />
                            Digite ao menos 3 letras para buscar
                        </div>

                        <div v-else class="text-center p-3">
                            <div class="font-semibold text-gray-800 dark:text-gray-200">
                                Nenhum bairro encontrado para: <span class="text-emerald-600 dark:text-emerald-400">"{{ search }}"</span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                Pressione <Kbd class="key-badge">Enter</Kbd> para sugerir este novo bairro.
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                (Sua sugestão será enviada para aprovação)
                            </div>
                        </div>
                    </template>
                </v-select>
            </div>
            <InputError class="form-error" :message="form.errors.bairro_id" />
        </div>

        <div class="input-container md:col-span-4">
            <label for="cidade" class="form-label">Cidade</label>
            <input id="cidade" v-model="form.profile_data.endereco_cidade" type="text" class="form-input !pl-5" placeholder="Sua cidade" disabled/>
            <InputError class="form-error" :message="form.errors['profile_data.endereco_cidade']" />
        </div>
        
        <div class="input-container md:col-span-2">
            <label for="estado" class="form-label">Estado</label>
            <input id="estado" v-model="form.profile_data.endereco_estado" type="text" class="form-input !pl-5" placeholder="UF" disabled/>
        </div>
    </div>
</template>

<style scoped>
/* Tecla "Enter" customizada */
.key-badge {
    @apply inline-block px-2 py-0.5 mx-1 rounded-md font-mono text-xs font-semibold
           bg-gray-200 text-gray-700
           dark:bg-gray-700 dark:text-gray-200;
}

/* --- ESTILIZAÇÃO DO V-SELECT ---
Isso sobreescreve o CSS padrão do vue-select para que ele 
se pareça com a classe 'form-input' do seu projeto.
*/

/* Remove o estilo padrão e aplica o seu */
:deep(.vs__dropdown-toggle) {
    @apply form-input; /* <-- Puxa seu estilo de input! */
    @apply p-0; /* Reseta o padding interno do v-select */
    height: 3.125rem; /* 50px - Ajuste se seu form-input for diferente */
}

/* Caixa de seleção (quando há um item selecionado) */
:deep(.vs__selected-options) {
    @apply p-0 flex-nowrap;
    padding-left: 2.75rem; /* 44px - Espaço para o ícone */
}

:deep(.vs__selected) {
     @apply font-medium text-gray-800 dark:text-gray-200 m-0 py-0 pl-0 pr-2;
     line-height: 3.125rem; /* 50px - Centraliza verticalmente */
}

/* Campo de busca (quando está digitando) */
:deep(.vs__search) {
    @apply font-medium text-gray-800 dark:text-gray-200 m-0 py-0;
    padding-left: 2.75rem !important; /* 44px - Espaço para o ícone */
    line-height: 3.125rem; /* 50px - Centraliza verticalmente */
}
:deep(.vs__search::placeholder) {
    @apply text-gray-400 dark:text-gray-500;
}

/* Remove a borda padrão do v-select no foco, pois 'form-input' já tem */
:deep(.vs--open .vs__dropdown-toggle) {
    border-color: inherit;
    box-shadow: none;
}

/* Ícones (seta para baixo e 'x' para limpar) */
:deep(.vs__actions) {
    @apply text-gray-500;
    padding: 0 0.75rem 0 0.25rem; /* Posição à direita */
    scale: 1.2;
}

/* --- ESTILOS DO DROPDOWN (LISTA DE OPÇÕES) --- */

:deep(.vs__dropdown-menu) {
    @apply mt-2 p-0 rounded-lg shadow-lg border overflow-hidden;
    @apply border-gray-200 bg-white;
    @apply dark:border-gray-700 dark:bg-gray-800;
}

:deep(.vs__option) {
     @apply p-3 text-base font-medium;
     @apply text-gray-700 dark:text-gray-300;
}

/* Opção com mouse em cima (highlight) */
:deep(.vs__option--highlight) {
    @apply bg-emerald-100 text-emerald-800;
    @apply dark:bg-emerald-800/50 dark:text-emerald-200;
}

/* Opção já selecionada */
:deep(.vs__option--selected) {
     @apply bg-gray-100 font-bold;
     @apply dark:bg-gray-700;
}
</style>
