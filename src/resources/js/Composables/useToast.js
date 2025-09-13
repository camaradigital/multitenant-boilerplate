// resources/js/Composables/useToast.js
import { useToast } from 'vue-toastification';

export function useAppToast() {
    const toast = useToast();

    const showSuccess = (message) => {
        toast.success(message);
    };

    const showError = (message = 'Ocorreu um erro. Tente novamente.') => {
        toast.error(message);
    };

    return { showSuccess, showError };
}
