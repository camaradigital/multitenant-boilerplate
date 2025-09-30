<script setup>
import InputError from '@/Components/InputError.vue';
import { User, Mail, KeyRound } from 'lucide-vue-next';

defineProps({
    form: Object,
    realtimeErrors: Object,
});
</script>

<template>
    <div class="form-section-title">Dados de Acesso</div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
        <div class="input-container">
            <label for="name" class="form-label">Nome Completo</label>
            <div class="relative">
                <span class="input-icon"><User :size="16" /></span>
                <input id="name" v-model="form.name" type="text" class="form-input" required autofocus autocomplete="name" placeholder="Seu nome completo"/>
            </div>
            <InputError class="form-error" :message="form.errors.name" />
        </div>
        <div class="input-container">
            <label for="email" class="form-label">E-mail</label>
            <div class="relative">
                <span class="input-icon"><Mail :size="16" /></span>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-input"
                    :class="{ 'input-invalid': realtimeErrors.email, 'input-valid': !realtimeErrors.email && form.email }"
                    required
                    autocomplete="username"
                    placeholder="seuemail@exemplo.com"
                />
            </div>
            <InputError class="form-error" :message="form.errors.email" />
            <div v-if="realtimeErrors.email" class="form-error">{{ realtimeErrors.email }}</div>
        </div>
        <div class="input-container">
            <label for="password" class="form-label">Senha</label>
            <div class="relative">
                <span class="input-icon"><KeyRound :size="16" /></span>
                <input id="password" v-model="form.password" type="password" class="form-input" required autocomplete="new-password" placeholder="Crie uma senha forte"/>
            </div>
            <InputError class="form-error" :message="form.errors.password" />
        </div>
        <div class="input-container">
            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
            <div class="relative">
                <span class="input-icon"><KeyRound :size="16" /></span>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="form-input" required autocomplete="new-password" placeholder="Repita a senha"/>
            </div>
            <InputError class="form-error" :message="form.errors.password_confirmation" />
        </div>
    </div>
</template>
