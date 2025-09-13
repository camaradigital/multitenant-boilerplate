<script setup>
// O SCRIPT PERMANECE IDÊNTICO
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
    show: { type: Boolean, default: false },
    lead: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const form = useForm({
    nome: '',
    telefone: '',
    email: '',
    site: '',
    cidade: '',
    endereco: '',
});

const isEditing = computed(() => !!props.lead);

watch(() => props.lead, (newLead) => {
    if (newLead) {
        form.defaults(newLead).reset();
    } else {
        form.defaults({}).reset();
    }
});

const submitLead = () => {
    const routeName = isEditing.value ? 'central.leads.update' : 'central.leads.store';
    const options = {
        onSuccess: () => emit('close'),
        preserveScroll: true,
    };

    if (isEditing.value) {
        form.put(route(routeName, { lead: props.lead.id }), options);
    } else {
        form.post(route(routeName), options);
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 overflow-y-auto p-4" @click.self="$emit('close')">

        <form @submit.prevent="submitLead" class="bg-[#252A36] rounded-lg shadow-2xl w-full max-w-lg flex flex-col max-h-[90vh]">

            <div class="p-6 relative flex-shrink-0">
                <h3 class="text-lg font-semibold text-white">{{ isEditing ? 'Editar Lead' : 'Adicionar Novo Lead' }}</h3>
                <button type="button" @click="$emit('close')" class="absolute top-4 right-4 text-gray-500 hover:text-gray-400 transition-colors">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <div class="px-6 pb-6 space-y-5 overflow-y-auto">
                <div>
                    <label for="nome" class="form-label">Nome</label>
                    <input id="nome" v-model="form.nome" type="text" class="form-input">
                    <div v-if="form.errors.nome" class="form-error">{{ form.errors.nome }}</div>
                </div>
                <div>
                    <label for="email" class="form-label">E-mail</label>
                    <input id="email" v-model="form.email" type="email" class="form-input">
                    <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                </div>
                <div>
                    <label for="telefone" class="form-label">Telefone</label>
                    <input id="telefone" v-model="form.telefone" type="text" class="form-input">
                    <div v-if="form.errors.telefone" class="form-error">{{ form.errors.telefone }}</div>
                </div>
                <div>
                    <label for="site" class="form-label">Site</label>
                    <input id="site" v-model="form.site" type="url" class="form-input" placeholder="https://exemplo.com">
                    <div v-if="form.errors.site" class="form-error">{{ form.errors.site }}</div>
                </div>
                <div>
                    <label for="cidade" class="form-label">Cidade</label>
                    <input id="cidade" v-model="form.cidade" type="text" class="form-input">
                    <div v-if="form.errors.cidade" class="form-error">{{ form.errors.cidade }}</div>
                </div>
                <div>
                    <label for="endereco" class="form-label">Endereço</label>
                    <textarea id="endereco" v-model="form.endereco" class="form-input !h-24" rows="3"></textarea>
                    <div v-if="form.errors.endereco" class="form-error">{{ form.errors.endereco }}</div>
                </div>
            </div>

            <div class="px-6 py-4 bg-[#252A36] flex justify-end gap-3 rounded-b-lg flex-shrink-0">
                <button type="button" @click="$emit('close')" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ isEditing ? 'Salvar Alterações' : 'Adicionar Lead' }}</button>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* OS ESTILOS CSS PERMANECEM IDÊNTICOS */
.form-label { @apply block mb-2 text-sm text-gray-400; }

.form-input {
    @apply block w-full text-base rounded-md transition-all h-11 py-2 px-3;
    @apply bg-[#313849] border border-[#4A5568] text-gray-200 placeholder-gray-500;
    @apply focus:ring-2 focus:ring-[#38F8D4]/50 focus:border-[#38F8D4];
}

.form-error { @apply text-sm text-red-500 mt-1; }

.btn {
    @apply inline-flex items-center justify-center rounded-md font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#252A36] transition-all duration-150;
    padding: 0.625rem 1.25rem; /* 10px 20px */
}

.btn-primary {
    @apply bg-[#38F8D4] text-[#1A202C] hover:bg-opacity-90 focus:ring-[#38F8D4];
}

.btn-secondary {
    @apply bg-[#4A5568] text-gray-200 hover:bg-opacity-90 focus:ring-gray-500;
}
</style>
