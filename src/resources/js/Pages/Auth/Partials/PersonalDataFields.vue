<script setup>
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import { User, Phone, Calendar, Hash } from 'lucide-vue-next';

const props = defineProps({
    form: Object,
    realtimeErrors: Object,
    modelValue: String, // para v-model do formattedDataNascimento
});

const emit = defineEmits(['update:modelValue']);

// Propriedade computada local para fazer o v-model funcionar corretamente em um componente filho
const formattedDataNascimento = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});
</script>

<template>
    <div class="form-section-title">Dados Pessoais</div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
        <div class="input-container">
            <label for="cpf" class="form-label">CPF</label>
            <div class="relative">
                <span class="input-icon"><Hash :size="16" /></span>
                <input
                    id="cpf"
                    v-model="form.cpf"
                    type="text"
                    class="form-input"
                    :class="{ 'input-invalid': realtimeErrors.cpf, 'input-valid': !realtimeErrors.cpf && form.cpf }"
                    placeholder="000.000.000-00"
                    v-maska data-maska="###.###.###-##"
                />
            </div>
            <InputError class="form-error" :message="form.errors.cpf" />
            <div v-if="realtimeErrors.cpf" class="form-error">{{ realtimeErrors.cpf }}</div>
        </div>
        <div class="input-container">
            <label for="telefone" class="form-label">Telefone</label>
            <div class="relative">
                <span class="input-icon"><Phone :size="16" /></span>
                <input
                    id="telefone"
                    v-model="form.profile_data.telefone"
                    type="text"
                    class="form-input"
                    :class="{ 'input-invalid': realtimeErrors.celular, 'input-valid': !realtimeErrors.celular && form.profile_data.telefone }"
                    placeholder="(00) 90000-0000"
                    v-maska data-maska="['(##) ####-####', '(##) #####-####']"
                />
            </div>
            <InputError class="form-error" :message="form.errors['profile_data.telefone']" />
            <div v-if="realtimeErrors.celular" class="form-error">{{ realtimeErrors.celular }}</div>
        </div>
        <div class="input-container">
            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
            <div class="relative">
                <span class="input-icon"><Calendar :size="16" /></span>
                <input
                    id="data_nascimento"
                    v-model="formattedDataNascimento"
                    type="text" class="form-input"
                    :class="{
                        'input-invalid': realtimeErrors.data_nascimento || form.errors['profile_data.data_nascimento'],
                        'input-valid': !realtimeErrors.data_nascimento && formattedDataNascimento
                    }"
                    placeholder="DD/MM/AAAA"
                    v-maska data-maska="##/##/####"
                />
            </div>
            <InputError class="form-error" :message="form.errors['profile_data.data_nascimento']" />
            <div v-if="realtimeErrors.data_nascimento" class="form-error">{{ realtimeErrors.data_nascimento }}</div>
        </div>
        <div class="input-container">
            <label for="genero" class="form-label">Gênero</label>
            <select v-model="form.profile_data.genero" id="genero" class="form-input !pl-5">
                <option value="">Não informar</option>
                <option value="Masculino">Masculino</option>
                <option value="Feminino">Feminino</option>
                <option value="Outro">Outro</option>
            </select>
        </div>
        <div class="input-container md:col-span-2">
            <label for="nome_mae" class="form-label">Nome da Mãe</label>
            <div class="relative">
                <span class="input-icon"><User :size="16" /></span>
                <input id="nome_mae" v-model="form.profile_data.nome_mae" type="text" class="form-input" placeholder="Nome completo da mãe"/>
            </div>
        </div>
        <div class="input-container md:col-span-2">
            <label for="nome_pai" class="form-label">Nome do Pai (Opcional)</label>
            <div class="relative">
                <span class="input-icon"><User :size="16" /></span>
                <input id="nome_pai" v-model="form.profile_data.nome_pai" type="text" class="form-input" placeholder="Nome completo do pai"/>
            </div>
        </div>
    </div>
</template>
