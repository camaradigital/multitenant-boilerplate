import { ref } from 'vue';

export function useCepLookup(form, realtimeErrors) {
    const isCepLoading = ref(false);

    const buscarCep = async () => {
        const fieldName = 'cep';
        const cep = form.profile_data.endereco_cep.replace(/\D/g, '');

        delete realtimeErrors.value[fieldName];

        if (cep.length !== 8) {
            return;
        }

        isCepLoading.value = true;
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();

            if (data.erro) {
                realtimeErrors.value[fieldName] = 'CEP não encontrado ou inválido.';
                form.profile_data.endereco_logradouro = '';
                form.profile_data.endereco_bairro = '';
                form.profile_data.endereco_cidade = '';
                form.profile_data.endereco_estado = '';
                return;
            }

            form.profile_data.endereco_logradouro = data.logradouro;
            form.profile_data.endereco_bairro = data.bairro;
            form.profile_data.endereco_cidade = data.localidade;
            form.profile_data.endereco_estado = data.uf;
        } catch (error) {
            console.error("Erro ao buscar CEP:", error);
            realtimeErrors.value[fieldName] = 'Não foi possível consultar o CEP.';
        } finally {
            isCepLoading.value = false;
        }
    };

    return { buscarCep, isCepLoading };
}
