<script setup>
import InputError from '@/Components/InputError.vue';

defineProps({
    customFields: Array,
    form: Object,
});
</script>

<template>
    <div v-if="customFields && customFields.length > 0">
        <div class="form-section-title">Informações Adicionais</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
            <div v-for="field in customFields" :key="field.id" class="input-container">
                <label :for="field.name" class="form-label">{{ field.label }}</label>
                <div class="relative">
                    <input v-if="['text', 'number', 'date'].includes(field.type)" :type="field.type" v-model="form.profile_data[field.name]" :id="field.name" class="form-input !pl-5" :required="field.is_required" />
                    <select v-if="field.type === 'select'" v-model="form.profile_data[field.name]" :id="field.name" class="form-input !pl-5" :required="field.is_required">
                        <option value="">Selecione</option>
                        <option v-for="option in JSON.parse(field.options)" :key="option" :value="option">{{ option }}</option>
                    </select>
                </div>
                <InputError v-if="form.errors[`profile_data.${field.name}`]" class="form-error" :message="form.errors[`profile_data.${field.name}`]" />
            </div>
        </div>
    </div>
</template>
