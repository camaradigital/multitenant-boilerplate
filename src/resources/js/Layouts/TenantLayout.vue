<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Banner from '@/Components/Banner.vue';
import Sidebar from '@/Components/Sidebar.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Menu, Search, Bell } from 'lucide-vue-next';

// --- Props ---
defineProps({
    title: String,
});

// --- Lógica Principal ---
const page = usePage();

const logout = () => {
    router.post(route('logout'));
};

// Propriedade computada para criar as variáveis CSS a partir dos dados do tema
// que vêm do middleware HandleInertiaRequests.php
const themeStyles = computed(() => {
    const theme = page.props.theme;
    return {
        '--color-primary': theme?.primary || '#4F46E5',   // Cor primária com fallback
        '--color-secondary': theme?.secondary || '#D946EF', // Cor secundária com fallback
    };
});

// Permissões e Debug (mantidos)
const userPermissions = computed(() => page.props.auth.user?.permissions || []);
const authDebug = computed(() => page.props.auth?.debug || {});

// --- Lógica para Sidebar Responsiva ---
const isMobile = ref(window.innerWidth < 1024);
const isSidebarOpenOnMobile = ref(false);

function updateMobileStatus() {
    isMobile.value = window.innerWidth < 1024;
    if (!isMobile.value) {
        isSidebarOpenOnMobile.value = false;
    }
}

function toggleMobileSidebar() {
    isSidebarOpenOnMobile.value = !isSidebarOpenOnMobile.value;
}

onMounted(() => window.addEventListener('resize', updateMobileStatus));
onUnmounted(() => window.removeEventListener('resize', updateMobileStatus));

</script>

<template>
    <div :style="themeStyles">
        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-slate-50 dark:bg-[#0A1E1C]">
            <div class="flex h-screen">

                <div class="hidden lg:block lg:w-64 lg:flex-shrink-0">
                    <Sidebar />
                </div>

                <div v-if="isMobile">
                    <div
                        v-if="isSidebarOpenOnMobile"
                        @click="toggleMobileSidebar"
                        class="fixed inset-0 z-20 bg-black/60 backdrop-blur-sm"
                        aria-hidden="true"
                    ></div>
                    <div :class="['fixed inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out', isSidebarOpenOnMobile ? 'translate-x-0' : '-translate-x-full']">
                        <Sidebar @close="toggleMobileSidebar" />
                    </div>
                </div>

                <div class="flex flex-1 flex-col overflow-hidden">

                    <header class="main-header">
                        <div class="flex items-center gap-4">
                            <button @click="toggleMobileSidebar" class="header-icon-btn lg:hidden" aria-label="Abrir menu">
                                <Menu class="h-6 w-6"/>
                            </button>
                            <div v-if="$slots.header" class="hidden lg:block">
                                <slot name="header" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-4">
                            <div class="hidden md:block">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Search class="h-5 w-5 text-gray-400" />
                                    </span>
                                    <input type="text" placeholder="Buscar..." class="w-48 lg:w-64 rounded-md border-gray-300 bg-gray-100 py-2 pl-10 pr-4 text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)] dark:border-gray-700/50 dark:bg-gray-800/50 dark:text-gray-300">
                                </div>
                            </div>

                            <button class="header-icon-btn">
                                <Bell class="h-6 w-6" />
                            </button>

                            <ThemeToggle />

                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center rounded-full transition focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                            <span :style="{ backgroundColor: 'var(--color-primary)' }" class="inline-flex items-center justify-center h-9 w-9 rounded-full text-sm font-semibold text-white">
                                                {{ $page.props.auth.user.name.charAt(0) }}
                                            </span>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Gerenciar Conta
                                        </div>
                                        <DropdownLink :href="route('profile.show')"> Perfil </DropdownLink>
                                        <div class="border-t border-gray-200 dark:border-gray-600" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">Sair</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </header>

                    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 scrollbar-thin scrollbar-thumb-[var(--color-primary)] scrollbar-track-slate-200 hover:scrollbar-thumb-slate-500 dark:scrollbar-track-slate-800 dark:hover:scrollbar-thumb-slate-400">
                        <slot />
                    </main>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.main-header {
    @apply flex h-16 shrink-0 items-center justify-between border-b px-4 sm:px-6 lg:px-8;
    @apply bg-white/70 dark:bg-[#0D2C2A]/50 border-gray-200 dark:border-gray-800;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.header-icon-btn {
    @apply rounded-full p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800/60 dark:hover:text-white transition-colors duration-200;
}
</style>
