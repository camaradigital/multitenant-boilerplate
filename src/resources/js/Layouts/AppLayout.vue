<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

// Componentes Jetstream e Ícones
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import Spinner from '@/Components/Spinner.vue'; // Spinner para o loading de navegação
import { Sun, Moon } from 'lucide-vue-next';

defineProps({
    title: String,
});

const page = usePage();
const isNavigating = ref(false); // Estado de loading para navegação

// Verifica se o usuário tem a permissão para ver o link
const canManagePermissions = computed(() => {
    // CORREÇÃO: Adicionado '?' depois de 'user'
    return page.props.auth.user?.permissions?.includes('gerenciar permissoes centrais') ?? false;
});

const showingNavigationDropdown = ref(false);
const isDarkMode = ref(false);

// Lógica para carregar e alternar o tema
onMounted(() => {
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        isDarkMode.value = true;
    } else {
        document.documentElement.classList.remove('dark');
        isDarkMode.value = false;
    }
});

// Ouve os eventos do Inertia para mostrar/esconder o spinner durante a navegação
router.on('start', () => {
  isNavigating.value = true;
});

router.on('finish', () => {
  isNavigating.value = false;
});

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};


const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="font-sans">
        <Head :title="title" />

        <!-- Overlay de Loading para Navegação -->
        <div v-if="isNavigating" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex justify-center items-center z-[100]">
            <Spinner />
        </div>

        <FlashMessage />
        <Banner />

        <div class="page-container">
            <nav class="fixed top-0 left-0 right-0 z-40 bg-white/80 backdrop-blur-sm border-b border-gray-200 dark:bg-[#102C26]/80 dark:border-green-400/10 transition-colors duration-500">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('central.dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('central.dashboard')" :active="route().current('central.dashboard')">
                                    Dashboard
                                </NavLink>
                                <NavLink :href="route('central.tenants.index')" :active="route().current('central.tenants.index') || route().current('central.tenants.create') || route().current('central.tenants.edit') || route().current('central.tenants.show')">
                                    Gerenciar Câmaras
                                </NavLink>
                                <!-- Links Adicionados -->
                                <NavLink :href="route('central.leads.index')" :active="route().current('central.leads.index')">
                                    Prospecção
                                </NavLink>
                                <NavLink :href="route('central.campaigns.create')" :active="route().current('central.campaigns.create')">
                                    Campanhas
                                </NavLink>
                                <NavLink v-if="canManagePermissions" :href="route('central.roles_permissions.index')" :active="route().current('central.roles_permissions.index')">
                                    Permissões Globais
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <button @click="toggleTheme" class="me-4 p-2 rounded-full text-gray-500 hover:text-gray-800 hover:bg-gray-200 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/10 transition-colors duration-200">
                                <Sun v-if="isDarkMode" class="h-5 w-5" />
                                <Moon v-else class="h-5 w-5" />
                            </button>

                            <div v-if="$page.props.jetstream.hasTeamFeatures && $page.props.auth.user.current_team" class="ms-3 relative">
                                <Dropdown align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-transparent hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 dark:text-gray-400 dark:hover:text-gray-300">
                                                {{ $page.props.auth.user.current_team.name }}
                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <!-- Conteúdo do dropdown de times -->
                                    </template>
                                </Dropdown>
                            </div>

                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 dark:focus:border-green-400/50 transition">
                                            <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>
                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-transparent hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 dark:text-gray-400 dark:hover:text-gray-300">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Gerenciar Conta
                                        </div>
                                        <DropdownLink :href="route('profile.show')"> Perfil </DropdownLink>
                                        <div class="border-t border-gray-200 dark:border-green-400/10" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button"> Sair </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:hover:bg-gray-900 dark:focus:bg-gray-900 transition" @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('central.dashboard')" :active="route().current('central.dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                         <ResponsiveNavLink :href="route('central.tenants.index')" :active="route().current('central.tenants.index') || route().current('central.tenants.create') || route().current('central.tenants.edit') || route().current('central.tenants.show')">
                            Gerenciar Câmaras
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('central.leads.index')" :active="route().current('central.leads.index')">
                            Prospecção
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('central.campaigns.create')" :active="route().current('central.campaigns.create')">
                            Campanhas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canManagePermissions" :href="route('central.roles_permissions.index')" :active="route().current('central.roles_permissions.index')">
                            Permissões Globais
                        </ResponsiveNavLink>
                    </div>
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="h-10 w-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>
                            <div>
                                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Perfil
                            </ResponsiveNavLink>
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Sair
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="pt-16">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 scrollbar-thin scrollbar-thumb-emerald-500 scrollbar-track-emerald-900/50 hover:scrollbar-thumb-emerald-400">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.font-sans {
    font-family: 'Poppins', sans-serif;
}

.page-container {
    @apply bg-gray-100 dark:bg-transparent min-h-screen transition-colors duration-500;
}

.dark .page-container {
    background: radial-gradient(circle, #0D2C2A, #0A1E1C);
}

:deep(.dropdown-content) {
    @apply mt-2 rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 py-1;
    @apply bg-white dark:bg-[#102C26] dark:ring-green-400/20;
}

:deep(.nav-link-active) {
    @apply border-emerald-500 dark:border-emerald-400;
}
:deep(.nav-link-text-active) {
    @apply text-gray-900 dark:text-gray-100;
}

:deep(.responsive-nav-link-active) {
    @apply border-emerald-500 dark:border-emerald-400;
}
:deep(.responsive-nav-link-bg-active) {
    @apply bg-emerald-50/50 dark:bg-emerald-900/20;
}
:deep(.responsive-nav-link-text-active) {
    @apply text-emerald-700 dark:text-emerald-300;
}

</style>
