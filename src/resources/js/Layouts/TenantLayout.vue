<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    title: String,
});

const page = usePage();
const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};

// **NOVO**: Propriedade computada para aplicar as cores do tenant dinamicamente
const computedTenantStyle = computed(() => {
    return {
        '--cor-primaria': page.props.tenant?.cor_primaria || '#FFFFFF',
        '--cor-secundaria': page.props.tenant?.cor_secundaria || '#1F2937', // Cor de texto padrão
    };
});
</script>

<template>
    <div :style="computedTenantStyle">
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="border-b border-gray-100 dark:border-gray-700" :style="{ backgroundColor: 'var(--cor-primaria)' }">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('portal.home')">
                                    <img v-if="page.props.tenant?.logotipo_url" :src="page.props.tenant.logotipo_url" :alt="page.props.tenant.name" class="block h-9 w-auto">
                                    <h1 v-else class="font-bold" :style="{ color: 'var(--cor-secundaria)' }">
                                        {{ page.props.tenant?.name || 'Portal do Cidadão' }}
                                    </h1>
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('portal.home')" :active="route().current('portal.home')" :style="{ color: 'var(--cor-secundaria)' }">
                                    Portal
                                </NavLink>

                                <NavLink :href="route('solicitacoes.index')" :active="route().current('solicitacoes.index') && $page.props.auth.user.permissions.includes('solicitar servicos')" :style="{ color: 'var(--cor-secundaria)' }">
                                    Minhas Solicitações
                                </NavLink>

                                <template v-if="$page.props.auth.user.permissions.includes('gerenciar servicos')">
                                    <NavLink :href="route('solicitacoes.index')" :active="route().current('solicitacoes.index')" :style="{ color: 'var(--cor-secundaria)' }">
                                        Gerenciar Solicitações
                                    </NavLink>
                                    <NavLink :href="route('funcionarios.index')" :active="route().current('funcionarios.index')" :style="{ color: 'var(--cor-secundaria)' }">
                                        Funcionários
                                    </NavLink>
                                    <NavLink :href="route('servicos.index')" :active="route().current('servicos.index')" :style="{ color: 'var(--cor-secundaria)' }">
                                        Serviços
                                    </NavLink>
                                    <NavLink :href="route('tipos-servico.index')" :active="route().current('tipos-servico.index')" :style="{ color: 'var(--cor-secundaria)' }">
                                        Tipos de Serviço
                                    </NavLink>
                                    <NavLink :href="route('parametros.index')" :active="route().current('parametros.index')" :style="{ color: 'var(--cor-secundaria)' }">
                                        Parâmetros
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center text-sm font-medium hover:opacity-80 transition" :style="{ color: 'var(--cor-secundaria)' }">
                                            <div>{{ $page.props.auth.user.name }}</div>
                                            <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('profile.show')"> Perfil </DropdownLink>
                                        <div class="border-t border-gray-200 dark:border-gray-600" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">Sair</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    </div>
            </nav>

            <header v-if="$slots.header" class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
