<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { ChevronDown, Search, Users } from 'lucide-vue-next';

const props = defineProps({
    leads: {
        type: Array,
        required: true
    },
    modelValue: {
        type: Array,
        required: true
    },
    error: String
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const selector = ref(null);

const filteredLeads = computed(() => {
    if (!searchQuery.value) {
        return props.leads;
    }
    return props.leads.filter(lead =>
        lead.nome.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        lead.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const selectionText = computed(() => {
    const count = props.modelValue.length;
    if (count === 0) return 'Selecione os destinatários';
    if (count === 1) return '1 lead selecionado';
    return `${count} leads selecionados`;
});

const isAllSelected = computed({
    get: () => props.leads.length > 0 && props.modelValue.length === props.leads.length,
    set: (value) => {
        const allLeadIds = props.leads.map(lead => lead.id);
        emit('update:modelValue', value ? allLeadIds : []);
    }
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (selector.value && !selector.value.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="selector" class="relative">
        <label class="form-label">Destinatários</label>
        <button
            type="button"
            @click.prevent="toggleDropdown"
            class="form-input flex items-center justify-between w-full text-left"
            :class="{ 'border-red-500 dark:border-red-500': error }"
        >
            <span class="flex items-center">
                <Users class="h-5 w-5 mr-3 text-gray-400 dark:text-gray-500" />
                <span>{{ selectionText }}</span>
            </span>
            <ChevronDown class="h-5 w-5 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" />
        </button>

        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div v-if="isOpen" class="absolute z-10 mt-2 w-full bg-white dark:bg-[#102523] border border-gray-300 dark:border-[#2a413d] rounded-xl shadow-lg">
                <div class="p-2">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Buscar por nome ou e-mail..."
                            class="w-full text-sm rounded-lg h-10 pl-9 pr-4 bg-gray-100 dark:bg-[#0A1E1C] border-gray-200 dark:border-[#2a413d] focus:ring-1 focus:ring-emerald-500 dark:focus:ring-green-500 focus:border-emerald-500 dark:focus:border-green-500"
                        />
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-[#2a413d] max-h-60 overflow-y-auto">
                    <label class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer">
                        <input type="checkbox" v-model="isAllSelected" class="h-4 w-4 rounded border-gray-300 dark:bg-gray-700 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-3">Selecionar Todos</span>
                    </label>

                    <label v-for="lead in filteredLeads" :key="lead.id" class="flex items-center w-full px-4 py-3 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer">
                        <input type="checkbox" :value="lead.id" :checked="modelValue.includes(lead.id)" @change="emit('update:modelValue', $event.target.checked ? [...modelValue, lead.id] : modelValue.filter(id => id !== lead.id))" class="h-4 w-4 rounded border-gray-300 dark:bg-gray-700 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-3 flex flex-col">
                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ lead.nome }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ lead.email }}</span>
                        </span>
                    </label>

                     <div v-if="filteredLeads.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                        Nenhum lead encontrado.
                    </div>
                </div>
            </div>
        </transition>

        <div v-if="error" class="form-error">{{ error }}</div>
    </div>
</template>

