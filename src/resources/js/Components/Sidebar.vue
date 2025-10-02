<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { navigation as rawNavigation } from '@/menu.js';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
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

// ####################################################################
// #           INÍCIO DA SEÇÃO CORRIGIDA E ATUALIZADA                 #
// ####################################################################
const navigation = computed(() => {
    // A lista de permissões já vem correta do back-end
    const userPermissions = page.props.auth.user?.permissions || [];

    // Função de verificação SIMPLIFICADA E CORRIGIDA
    const hasPermission = (permission) => {
        // Se o item de menu não exige permissão, sempre mostra.
        if (!permission) {
            return true;
        }
        // A verificação agora é única e vale para TODOS os casos.
        return userPermissions.includes(permission);
    };

    // Lógica de filtragem recursiva para garantir que pais sem filhos visíveis não apareçam
    const filterItems = (items) => {
        return items
            .map(item => {
                // Primeiro, verifica se o item PAI tem permissão para ser exibido
                if (!hasPermission(item.permission)) {
                    return null;
                }

                // Se o item tem filhos, filtra os filhos recursivamente
                if (item.children) {
                    const filteredChildren = filterItems(item.children);
                    // Só retorna o item pai se ele ainda tiver filhos visíveis após o filtro
                    if (filteredChildren.length > 0) {
                        return { ...item, children: filteredChildren };
                    }
                    // Se não sobraram filhos, não mostra o item pai
                    return null;
                }

                // Se não tem filhos e passou na verificação de permissão, retorna o item
                return item;
            })
            // Remove todos os itens que se tornaram nulos (sem permissão ou sem filhos)
            .filter(Boolean);
    };

    return filterItems(rawNavigation);
});
// ####################################################################
// #             FIM DA SEÇÃO CORRIGIDA E ATUALIZADA                  #
// ####################################################################

onMounted(() => {
    // Mantém o submenu aberto se uma de suas rotas filhas estiver ativa
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

/* Estilo para Itens Ativos */
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

/* Estilo para Grupos de Menu Ativos */
.nav-link-active-parent {
    @apply bg-gray-100 dark:bg-gray-800/60;
}

/* Estilo para Itens Filhos (submenus) */
.nav-link-child {
    @apply h-9 text-gray-500 dark:text-gray-400;
}
.nav-link-child-active {
    color: var(--color-primary);
    @apply font-semibold;
}
.nav-link-child:hover {
    @apply text-gray-900 dark:text-white;
}

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
