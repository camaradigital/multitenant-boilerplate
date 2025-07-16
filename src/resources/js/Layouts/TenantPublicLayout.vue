<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const tenant = page.props.tenant;

// **NOVO**: Propriedade computada para criar as variáveis de estilo dinâmicas.
const computedTenantStyle = computed(() => {
    return {
        '--cor-primaria': tenant?.cor_primaria || '#FFFFFF', // Cor de fundo padrão (branco)
        '--cor-secundaria': tenant?.cor_secundaria || '#1F2937', // Cor de texto padrão (cinza escuro)
    };
});
</script>

<template>
    <div :style="computedTenantStyle">
        <Head :title="tenant?.nome || 'Portal do Cidadão'" />
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 text-gray-800 font-sans">
            <header class="shadow-md" :style="{ backgroundColor: 'var(--cor-primaria)' }">
                <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                    <div>
                        <Link :href="route('portal.home')">
                            <img v-if="tenant?.logotipo_url" :src="tenant.logotipo_url" :alt="tenant.name" class="block h-9 w-auto">
                            <h1 v-else class="text-xl font-bold" :style="{ color: 'var(--cor-secundaria)' }">
                                {{ tenant?.name || 'Portal do Cidadão' }}
                            </h1>
                        </Link>
                    </div>
                    <div v-if="!page.props.auth.user">
                        <Link :href="route('login')" class="hover:opacity-80 mr-4 font-medium transition" :style="{ color: 'var(--cor-secundaria)' }">
                            Login
                        </Link>
                        <Link :href="route('register')" class="px-4 py-2 rounded-md hover:opacity-90 font-semibold transition" :style="{ backgroundColor: 'var(--cor-secundaria)', color: 'var(--cor-primaria)' }">
                            Cadastre-se
                        </Link>
                    </div>
                </nav>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
