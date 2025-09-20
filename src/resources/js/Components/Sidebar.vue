<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { navigation as rawNavigation } from '@/menu.js';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue'; // <-- 1. Importado aqui
import { LogOut, UserCircle, ChevronDown, CircleDot } from 'lucide-vue-next';

const page = usePage();
const openSubmenu = ref('');

// Lógica para verificar se um item de navegação ou seu grupo está ativo
const isRouteActive = (item) => {
    const currentRouteName = route().current();
    if (!currentRouteName) return false;

    // Se o item for um grupo, verifica se algum dos filhos é a rota ativa
    if (item.children) {
        return item.children.some(child => {
            if (child.current.endsWith('.*')) {
                return currentRouteName.startsWith(child.current.slice(0, -2));
            }
            return currentRouteName === child.current;
        });
    }

    // Se for um link simples, verifica se a rota atual corresponde
    if (item.current.endsWith('.*')) {
        return currentRouteName.startsWith(item.current.slice(0, -2));
    }
    return currentRouteName === item.current;
};

// Nova lógica de ativação específica para os links filhos
const isChildRouteActive = (child) => {
    const currentRouteName = route().current();
    if (!currentRouteName) return false;

    if (child.current.endsWith('.*')) {
        return currentRouteName.startsWith(child.current.slice(0, -2));
    }
    return currentRouteName === child.current;
};

function toggleSubmenu(name) {
    openSubmenu.value = openSubmenu.value === name ? '' : name;
}

const navigation = computed(() => {
    const userPermissions = page.props.auth.user?.permissions || [];
    const userRoles = page.props.auth.user?.roles || [];

    const hasPermission = (permission) => {
        if (!permission) return true;

        if (permission === 'ver portal') {
            return userRoles.some(role => role.name === 'Cidadao');
        }

        return userPermissions.includes(permission);
    };

    const filterItems = (items) => {
        return items
            .filter(item => hasPermission(item.permission))
            .map(item => {
                if (item.children) {
                    const filteredChildren = filterItems(item.children);
                    if (filteredChildren.length > 0) {
                        return { ...item, children: filteredChildren };
                    }
                    return null;
                }
                return item;
            })
            .filter(Boolean);
    };

    return filterItems(rawNavigation);
});

onMounted(() => {
    for (const item of navigation.value) {
        if (item.children && isRouteActive(item)) {
            openSubmenu.value = item.name;
            break;
        }
    }
});

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="flex h-full flex-col bg-white px-4 py-4 dark:bg-[#0A1E1C] dark:border-r dark:border-gray-800">
        <!-- 2. Logotipo substituído e centralizado -->
        <Link :href="route('tenant.dashboard')" class="mb-6 flex items-center justify-center px-2">
            <ApplicationLogo class="h-full w-full p-1" />
        </Link>

        <div class="flex flex-1 flex-col justify-between overflow-hidden">
            <nav class="-mx-2 space-y-1 overflow-y-auto pr-1 scrollbar-hide">
                <div v-for="item in navigation" :key="item.name">
                    <Link v-if="!item.children" :href="item.href" class="nav-link" :class="{ 'nav-link-active': isRouteActive(item) }">
                        <component :is="item.icon" class="h-6 w-6 shrink-0" :stroke-width="1.5" />
                        <span class="nav-link-text">{{ item.name }}</span>
                    </Link>

                    <div v-else>
                        <button @click="toggleSubmenu(item.name)" class="nav-link w-full justify-between" :class="{ 'nav-link-active-parent': isRouteActive(item) && openSubmenu !== item.name }">
                            <div class="flex items-center gap-x-3">
                                <component :is="item.icon" class="h-6 w-6 shrink-0" :stroke-width="1.5" />
                                <span class="nav-link-text">{{ item.name }}</span>
                            </div>
                            <ChevronDown :class="['h-5 w-5 shrink-0 transition-transform duration-300', { 'rotate-180': openSubmenu === item.name }]" />
                        </button>

                        <transition
                            enter-active-class="transition-max-height duration-500 ease-in-out"
                            enter-from-class="max-h-0"
                            enter-to-class="max-h-screen"
                            leave-active-class="transition-max-height duration-300 ease-in-out"
                            leave-from-class="max-h-screen"
                            leave-to-class="max-h-0"
                        >
                            <div v-if="openSubmenu === item.name" class="overflow-hidden">
                                <div class="submenu-container">
                                    <Link v-for="child in item.children" :key="child.name" :href="child.href" class="nav-link nav-link-child" :class="{ 'nav-link-child-active': isChildRouteActive(child) }">
                                        <CircleDot class="h-4 w-4 shrink-0" :stroke-width="2" />
                                        <span class="nav-link-text">{{ child.name }}</span>
                                    </Link>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </nav>

            <div class="mt-6 shrink-0">
                 <div class="user-card">
                      <div class="flex items-center gap-x-3 overflow-hidden">
                           <UserCircle class="h-10 w-10 text-gray-400 shrink-0" />
                           <div class="flex-1 truncate">
                                <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ page.props.auth.user.name }}</p>
                                <Link :href="route('profile.show')" class="text-xs text-[var(--color-primary)] hover:underline">Ver Perfil</Link>
                           </div>
                      </div>
                      <ThemeToggle />
                 </div>
                 <form @submit.prevent="logout" class="w-full mt-2">
                      <button type="submit" class="logout-button">
                           <LogOut class="h-5 w-5" :stroke-width="1.5" />
                           <span class="nav-link-text">Sair</span>
                      </button>
                 </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Estilo Base para todos os links e botões do menu */
.nav-link {
    @apply flex items-center w-full gap-x-3 h-11 px-3 rounded-lg transition-colors duration-200 ease-in-out;
    @apply text-gray-600 dark:text-gray-400;
}
.nav-link:hover {
    @apply bg-gray-100 dark:bg-gray-800/60 text-gray-900 dark:text-white;
}

/* MUDANÇA: Estilo para Itens Ativos agora usa a variável primária */
.nav-link-active {
    background-color: var(--color-primary);
    color: white;
    box-shadow: 0 4px 6px -1px color-mix(in srgb, var(--color-primary) 40%, transparent);
    @apply font-semibold;
}
.nav-link-active:hover {
    background-color: color-mix(in srgb, var(--color-primary) 85%, black);
    color: white;
}

/* Estilo para Grupos de Menu Ativos (quando um filho está ativo mas o grupo está fechado) */
.nav-link-active-parent {
    @apply bg-gray-100 dark:bg-gray-800/60;
}

/* Estilo para Itens Filhos (submenus) */
.nav-link-child {
    @apply h-9 text-gray-500 dark:text-gray-400;
}
/* MUDANÇA: Estilo para filho ativo agora usa a variável primária */
.nav-link-child-active {
    color: var(--color-primary);
    @apply font-semibold;
}
.nav-link-child:hover {
    @apply text-gray-900 dark:text-white;
}

/* MUDANÇA: Borda do submenu agora usa a variável primária com opacidade */
.submenu-container {
    @apply mt-1 ml-4 pl-4 border-l-2 space-y-1;
    border-color: color-mix(in srgb, var(--color-primary) 20%, transparent);
}

.nav-link-text {
    @apply text-sm font-medium;
}

.user-card {
    @apply flex items-center justify-between rounded-lg bg-gray-100 p-2 dark:bg-gray-800/60;
}

/* MUDANÇA: Botão de logout agora usa a variável primária para o anel de foco */
.logout-button {
    @apply w-full flex items-center h-11 px-3 mt-1 rounded-lg transition-colors duration-200 ease-in-out;
    @apply text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60;
    @apply focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--color-primary)];
}
.logout-button:hover {
    @apply text-gray-900 dark:text-white;
}

.transition-max-height {
    transition-property: max-height;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
