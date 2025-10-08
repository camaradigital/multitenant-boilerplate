<script setup>
import InputError from '@/Components/InputError.vue';
import { MapPin } from 'lucide-vue-next';

defineProps({
    form: Object,
    realtimeErrors: Object,
    // ADICIONADO: Prop para receber a lista de bairros do componente pai.
    bairros: Array,
});

const emit = defineEmits(['buscar-cep']);
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

        <!-- MODIFICADO: O campo de texto foi substituído por um campo de seleção (select) -->
        <div class="input-container md:col-span-4">
            <label for="bairro" class="form-label">Bairro/Córrego</label>
            <select
                id="bairro"
                v-model="form.bairro_id"
                class="form-input !pl-5"
                required
            >
                <option value="" disabled>Selecione um bairro</option>
                <option v-for="bairro in bairros" :key="bairro.id" :value="bairro.id">
                    {{ bairro.nome }}
                </option>
            </select>
            <InputError class="form-error" :message="form.errors.bairro_id" />
        </div>

        <div class="input-container md:col-span-4">
            <label for="cidade" class="form-label">Cidade</label>
            <input id="cidade" v-model="form.profile_data.endereco_cidade" type="text" class="form-input !pl-5" placeholder="Sua cidade" required/>
            <InputError class="form-error" :message="form.errors['profile_data.endereco_cidade']" />
        </div>
        <div class="input-container md:col-span-2">
            <label for="estado" class="form-label">Estado</label>
            <input id="estado" v-model="form.profile_data.endereco_estado" type="text" class="form-input !pl-5" placeholder="UF"/>
        </div>
    </div>
</template>
