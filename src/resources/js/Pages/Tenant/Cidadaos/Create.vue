<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { UserCheck, ArrowLeft } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    customFields: Array,
    // ADICIONADO: Recebe a lista de bairros do controller
    bairros: Array,
});

// Gera a estrutura do profile_data com campos fixos e personalizados
const generateProfileDataStructure = (fields) => {
    const profileData = {
        telefone: '',
        data_nascimento: '',
        genero: '',
        nome_mae: '',
        nome_pai: '',
        endereco_cep: '',
        endereco_logradouro: '',
        endereco_numero: '',
        // endereco_bairro: '', // REMOVIDO: Este campo não é mais usado diretamente no form
        endereco_cidade: '',
        endereco_estado: '',
    };
    if (fields) {
        fields.forEach(field => {
            profileData[field.name] = '';
        });
    }
    return profileData;
};

const form = useForm({
    name: '',
    email: '',
    cpf: '',
    // ADICIONADO: O ID do bairro agora faz parte do formulário principal
    bairro_id: null,
    profile_data: generateProfileDataStructure(props.customFields),
});

const formattedDataNascimento = computed({
    get() {
        if (!form.profile_data.data_nascimento) return '';
        const parts = form.profile_data.data_nascimento.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return form.profile_data.data_nascimento;
    },
    set(value) {
        if (value && value.length === 10) {
            const parts = value.split('/');
            if (parts.length === 3) {
                form.profile_data.data_nascimento = `${parts[2]}-${parts[1]}-${parts[0]}`;
                return;
            }
        }
        form.profile_data.data_nascimento = '';
    }
});

const submit = () => {
    form.post(route('admin.cidadaos.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

// ========================================================================
// --- LÓGICA DE VALIDAÇÃO EM TEMPO REAL ---
// ========================================================================
const realtimeErrors = ref({});

const debounce = (func, delay = 500) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
};

const validateField = async (fieldName, value) => {
    delete realtimeErrors.value[fieldName];
    if (!value) return;

    try {
        await axios.post(route('realtime.validate'), { field: fieldName, value: value });
    } catch (error) {
        if (error.response && error.response.status === 422) {
            realtimeErrors.value[fieldName] = error.response.data[fieldName][0];
        } else {
            console.error('Erro ao validar o campo:', error);
        }
    }
};

const debouncedValidate = debounce(validateField);

watch(() => form.cpf, (newValue) => { debouncedValidate('cpf', newValue) });
watch(() => form.email, (newValue) => { debouncedValidate('email', newValue) });
watch(() => form.profile_data.telefone, (newValue) => { debouncedValidate('celular', newValue) });
watch(() => form.profile_data.endereco_cep, (newValue) => { debouncedValidate('cep', newValue) });

watch(() => formattedDataNascimento.value, (newValue) => {
    if (newValue && newValue.length === 10) {
        debouncedValidate('data_nascimento', newValue);
    }
});

const buscarCep = async () => {
    const fieldName = 'cep';
    const cep = form.profile_data.endereco_cep.replace(/\D/g, '');
    delete realtimeErrors.value[fieldName];

    if (cep.length !== 8) return;

    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();

        if (data.erro) {
            realtimeErrors.value[fieldName] = 'CEP não encontrado ou inválido.';
            form.profile_data.endereco_logradouro = '';
            form.bairro_id = null; // Limpa o bairro
            form.profile_data.endereco_cidade = '';
            form.profile_data.endereco_estado = '';
            return;
        }

        form.profile_data.endereco_logradouro = data.logradouro;
        form.profile_data.endereco_cidade = data.localidade;
        form.profile_data.endereco_estado = data.uf;

        // ALTERADO: Tenta encontrar e pré-selecionar o bairro
        if (data.bairro && props.bairros) {
            const bairroEncontrado = props.bairros.find(
                b => b.nome.toLowerCase() === data.bairro.toLowerCase()
            );
            if (bairroEncontrado) {
                form.bairro_id = bairroEncontrado.id;
            } else {
                form.bairro_id = null;
            }
        }

    } catch (error) {
        console.error("Erro ao buscar CEP:", error);
        realtimeErrors.value[fieldName] = 'Não foi possível consultar o CEP.';
    }
};
</script>

<template>
    <Head title="Novo Cidadão" />
    <TenantLayout title="Novo Cidadão">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Adicionar Novo Cidadão</h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-4xl">
                <div class="form-icon"><UserCheck :size="32" class="icon-in-badge" /></div>
                <form @submit.prevent="submit">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b-dynamic">
                            <div>
                                <h2 class="header-title">Cadastro de Cidadão</h2>
                                <p class="form-subtitle">Preencha os dados para adicionar um novo cidadão.</p>
                            </div>
                            <Link :href="route('admin.cidadaos.index')" class="btn-secondary flex-shrink-0"><ArrowLeft class="h-4 w-4 mr-2" />Voltar para a Lista</Link>
                        </div>

                        <div class="mt-6 space-y-6">
                            <div class="section-title">Dados de Acesso</div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="form-label">Nome Completo</label>
                                    <input type="text" v-model="form.name" id="name" class="form-input" required>
                                    <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                                </div>
                                <div>
                                    <label for="email" class="form-label">Email</label>
                                    <input id="email" v-model="form.email" type="email" class="form-input" :class="{ 'input-invalid': realtimeErrors.email, 'input-valid': !realtimeErrors.email && form.email }" required />
                                    <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                                    <div v-if="realtimeErrors.email" class="form-error">{{ realtimeErrors.email }}</div>
                                </div>
                                <div class="md:col-span-2 text-sm text-gray-500 bg-gray-100 dark:bg-gray-700/50 p-3 rounded-lg">
                                    A senha será definida pelo cidadão através de um link enviado para o e-mail cadastrado.
                                </div>
                            </div>
                            <div class="section-title pt-4">Dados Pessoais</div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="cpf" class="form-label">CPF</label>
                                    <input id="cpf" v-model="form.cpf" type="text" class="form-input" :class="{ 'input-invalid': realtimeErrors.cpf, 'input-valid': !realtimeErrors.cpf && form.cpf }" v-maska data-maska="###.###.###-##" placeholder="000.000.000-00" />
                                    <div v-if="form.errors.cpf" class="form-error">{{ form.errors.cpf }}</div>
                                    <div v-if="realtimeErrors.cpf" class="form-error">{{ realtimeErrors.cpf }}</div>
                                </div>
                                <div>
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input id="telefone" v-model="form.profile_data.telefone" type="text" class="form-input" :class="{ 'input-invalid': realtimeErrors.celular, 'input-valid': !realtimeErrors.celular && form.profile_data.telefone }" v-maska data-maska="['(##) ####-####', '(##) #####-####']" placeholder="(00) 90000-0000" />
                                    <div v-if="form.errors['profile_data.telefone']" class="form-error">{{ form.errors['profile_data.telefone'] }}</div>
                                    <div v-if="realtimeErrors.celular" class="form-error">{{ realtimeErrors.celular }}</div>
                                </div>
                                <div>
                                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                    <input id="data_nascimento" v-model="formattedDataNascimento" type="text" class="form-input" :class="{ 'input-invalid': realtimeErrors.data_nascimento || form.errors['profile_data.data_nascimento'], 'input-valid': !realtimeErrors.data_nascimento && formattedDataNascimento }" v-maska data-maska="##/##/####" placeholder="DD/MM/AAAA" />
                                    <div v-if="form.errors['profile_data.data_nascimento']" class="form-error">{{ form.errors['profile_data.data_nascimento'] }}</div>
                                    <div v-if="realtimeErrors.data_nascimento" class="form-error">{{ realtimeErrors.data_nascimento }}</div>
                                </div>
                                <div>
                                    <label for="genero" class="form-label">Gênero</label>
                                    <select v-model="form.profile_data.genero" id="genero" class="form-input">
                                        <option value="">Não informar</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Feminino">Feminino</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="nome_mae" class="form-label">Nome da Mãe</label>
                                    <input type="text" v-model="form.profile_data.nome_mae" id="nome_mae" class="form-input">
                                </div>
                                <div>
                                    <label for="nome_pai" class="form-label">Nome do Pai</label>
                                    <input type="text" v-model="form.profile_data.nome_pai" id="nome_pai" class="form-input">
                                </div>
                            </div>
                            <div class="section-title pt-4">Endereço</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-1">
                                    <label for="cep" class="form-label">CEP</label>
                                    <input id="cep" v-model="form.profile_data.endereco_cep" type="text" class="form-input" :class="{ 'input-invalid': realtimeErrors.cep, 'input-valid': !realtimeErrors.cep && form.profile_data.endereco_cep }" v-maska data-maska="#####-###" @blur="buscarCep" placeholder="00000-000" />
                                    <div v-if="form.errors['profile_data.endereco_cep']" class="form-error">{{ form.errors['profile_data.endereco_cep'] }}</div>
                                    <div v-if="realtimeErrors.cep" class="form-error">{{ realtimeErrors.cep }}</div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="logradouro" class="form-label">Logradouro</label>
                                    <input type="text" v-model="form.profile_data.endereco_logradouro" id="logradouro" class="form-input">
                                </div>
                                <div>
                                    <label for="numero" class="form-label">Número</label>
                                    <input type="text" v-model="form.profile_data.endereco_numero" id="numero" class="form-input">
                                </div>

                                <!-- ALTERADO: Campo de Bairro agora é um select -->
                                <div class="md:col-span-2">
                                    <label for="bairro_id" class="form-label">Bairro</label>
                                    <select id="bairro_id" v-model="form.bairro_id" class="form-input" required>
                                        <option :value="null">Selecione um bairro</option>
                                        <option v-for="bairro in props.bairros" :key="bairro.id" :value="bairro.id">
                                            {{ bairro.nome }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.bairro_id" class="form-error">{{ form.errors.bairro_id }}</div>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="cidade" class="form-label">Cidade</label>
                                    <input type="text" v-model="form.profile_data.endereco_cidade" id="cidade" class="form-input" required>
                                    <div v-if="form.errors['profile_data.endereco_cidade']" class="form-error">{{ form.errors['profile_data.endereco_cidade'] }}</div>
                                </div>
                                <div>
                                    <label for="estado" class="form-label">Estado</label>
                                    <input type="text" v-model="form.profile_data.endereco_estado" id="estado" class="form-input">
                                </div>
                            </div>
                            <div v-if="customFields && customFields.length > 0" class="section-title pt-4">Informações Adicionais</div>
                            <div v-if="customFields && customFields.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="field in customFields" :key="field.id">
                                    <label :for="field.name" class="form-label">{{ field.label }}</label>
                                    <input v-if="['text', 'number', 'date'].includes(field.type)" :type="field.type" v-model="form.profile_data[field.name]" :id="field.name" class="form-input" :required="field.is_required" />
                                    <select v-if="field.type === 'select'" v-model="form.profile_data[field.name]" :id="field.name" class="form-input" :required="field.is_required">
                                        <option value="">Selecione</option>
                                        <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                                    </select>
                                    <div v-if="form.errors[`profile_data.${field.name}`]" class="form-error">{{ form.errors[`profile_data.${field.name}`] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl mt-6">
                        <Link :href="route('admin.cidadaos.index')" class="btn-secondary">Cancelar</Link>
                        <button type="submit" :disabled="form.processing" class="btn-primary">Salvar Cidadão</button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos copiados do Index.vue para manter a consistência */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.section-title { @apply font-semibold text-gray-700 dark:text-gray-300 text-sm border-b border-gray-200 dark:border-gray-700 pb-2; }

/* Estilos para feedback de validação em tempo real */
.input-valid {
    @apply border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500;
    @apply dark:border-green-500 dark:focus:border-green-500 dark:focus:ring-green-500;
}

.input-invalid {
    @apply border-red-500 focus:border-red-500 focus:ring-red-500;
    @apply dark:border-red-400 dark:focus:border-red-400 dark:focus:ring-red-400;
}
</style>
