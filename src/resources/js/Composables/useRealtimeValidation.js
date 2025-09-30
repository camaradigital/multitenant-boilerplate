import { ref, watch } from 'vue';
import axios from 'axios';

// Função de Debounce
const debounce = (func, delay = 500) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
};

export function useRealtimeValidation(form, formattedDataNascimento) {
    const realtimeErrors = ref({});

    const validateField = async (fieldName, value) => {
        // Limpa o erro anterior para este campo
        delete realtimeErrors.value[fieldName];

        // Se o campo estiver vazio, não faz nada
        if (!value) {
            return;
        }

        try {
            await axios.post(route('realtime.validate'), {
                field: fieldName,
                value: value,
            });
        } catch (error) {
            if (error.response && error.response.status === 422) {
                realtimeErrors.value[fieldName] = error.response.data[fieldName][0];
            } else {
                console.error(`Erro ao validar o campo ${fieldName}:`, error);
            }
        }
    };

    const debouncedValidate = debounce(validateField);

    // Observadores para cada campo
    watch(() => form.cpf, (newValue) => debouncedValidate('cpf', newValue));
    watch(() => form.profile_data.telefone, (newValue) => debouncedValidate('celular', newValue)); // Backend espera 'celular'
    watch(() => form.profile_data.endereco_cep, (newValue) => debouncedValidate('cep', newValue));
    watch(() => form.email, (newValue) => debouncedValidate('email', newValue));

    // Observa o valor formatado (DD/MM/YYYY)
    watch(formattedDataNascimento, (newValue) => {
        if (newValue && newValue.length === 10) {
            debouncedValidate('data_nascimento', newValue);
        }
    });

    return { realtimeErrors };
}
