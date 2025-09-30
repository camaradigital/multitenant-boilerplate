<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    requiresConfirmation: Boolean,
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => ! enabling.value && page.props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (! twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
}

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <div class="shadow sm:rounded-md bg-white dark:bg-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                 <span v-if="twoFactorEnabled && ! confirming">
                    Você ativou a autenticação de dois fatores.
                </span>
                <span v-else-if="twoFactorEnabled && confirming">
                    Conclua a ativação da autenticação de dois fatores.
                </span>
                <span v-else>
                    Autenticação de Dois Fatores (2FA)
                </span>
            </h3>

            <div class="mt-2 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                <p>
                    Adicione uma camada extra de segurança à sua conta. Quando a autenticação de dois fatores está ativada, ser-lhe-á solicitado um token seguro e aleatório durante o login. Pode obter este token a partir da aplicação de autenticação do seu telemóvel (ex: Google Authenticator).
                </p>
            </div>

             <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p v-if="confirming" class="font-semibold">
                            Para concluir a ativação, leia o código QR seguinte utilizando a aplicação de autenticação do seu telemóvel ou introduza a chave de configuração e forneça o código OTP gerado.
                        </p>

                        <p v-else>
                            A autenticação de dois fatores está agora ativada. Leia o código QR seguinte utilizando a aplicação de autenticação do seu telemóvel ou introduza a chave de configuração.
                        </p>
                    </div>

                    <div class="mt-4 p-2 inline-block bg-white" v-html="qrCode" />

                    <div v-if="setupKey" class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            Chave de Configuração: <span v-html="setupKey" class="font-mono text-xs"></span>
                        </p>
                    </div>

                    <div v-if="confirming" class="mt-4">
                        <InputLabel for="code" value="Código" />

                        <TextInput
                            id="code"
                            v-model="confirmationForm.code"
                            type="text"
                            name="code"
                            class="block mt-1 w-1/2"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            @keyup.enter="confirmTwoFactorAuthentication"
                        />

                        <InputError :message="confirmationForm.errors.code" class="mt-2" />
                    </div>
                </div>

                <div v-if="recoveryCodes.length > 0 && ! confirming">
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold">
                            Guarde estes códigos de recuperação num gestor de senhas seguro. Podem ser utilizados para recuperar o acesso à sua conta se o seu dispositivo de autenticação de dois fatores for perdido.
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-right sm:px-6 flex items-center justify-end space-x-3">
             <div v-if="! twoFactorEnabled">
                <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
                    <PrimaryButton type="button" class="bg-[#059669] hover:bg-emerald-700" :class="{ 'opacity-25': enabling }" :disabled="enabling">
                        Ativar 2FA
                    </PrimaryButton>
                </ConfirmsPassword>
            </div>

            <div v-else class="flex items-center space-x-3">
                <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
                    <PrimaryButton
                        v-if="confirming"
                        type="button"
                        class="bg-[#059669] hover:bg-emerald-700"
                        :class="{ 'opacity-25': enabling || confirmationForm.processing }"
                        :disabled="enabling || confirmationForm.processing"
                    >
                        Confirmar
                    </PrimaryButton>
                </ConfirmsPassword>

                <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
                    <SecondaryButton
                        v-if="recoveryCodes.length > 0 && ! confirming"
                    >
                        Gerar Códigos Novamente
                    </SecondaryButton>
                </ConfirmsPassword>

                <ConfirmsPassword @confirmed="showRecoveryCodes">
                    <SecondaryButton
                        v-if="recoveryCodes.length === 0 && ! confirming"
                    >
                        Mostrar Códigos de Recuperação
                    </SecondaryButton>
                </ConfirmsPassword>

                <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                    <SecondaryButton
                        v-if="confirming"
                        :class="{ 'opacity-25': disabling }"
                        :disabled="disabling"
                    >
                        Cancelar
                    </SecondaryButton>
                </ConfirmsPassword>

                <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                    <DangerButton
                        v-if="! confirming"
                        :class="{ 'opacity-25': disabling }"
                        :disabled="disabling"
                    >
                        Desativar
                    </DangerButton>
                </ConfirmsPassword>
            </div>
        </div>
    </div>
</template>
