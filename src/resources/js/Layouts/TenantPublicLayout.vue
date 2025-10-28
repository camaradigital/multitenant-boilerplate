<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

// Componentes
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Banner from '@/Components/Banner.vue';
import WhatsappIcon from '@/Components/Icons/WhatsappIcon.vue';

// Headless UI
import {
    Dialog,
    DialogPanel,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';

// Ícones
import {
    ChevronDown, Building, Eye, LogOut, LogIn, UserPlus, MapPin, Phone,
    Mail, ArrowUp, X, FileBadge, UserSearch, Briefcase, Instagram, Youtube,
    User,
    Menu as MenuIcon,
    Landmark as Archive,
    Home as HomeIcon,
} from 'lucide-vue-next';

// --- Props ---
defineProps({
    title: String,
});

// --- Estado Central ---
const page = usePage();
const logout = () => {
    router.post(route('logout'));
};

// --- Funções Utilitárias (Mantidas no arquivo) ---
const darken = (hex, percent) => {
    if (!hex || typeof hex !== 'string') return '#000000';
    let f=parseInt(hex.slice(1),16),t=percent<0?0:255,p=percent<0?percent*-1:percent,R=f>>16,G=f>>8&0x00FF,B=f&0x0000FF;
    return "#"+(0x1000000+(Math.round((t-R)*p)+R)*0x10000+(Math.round((t-G)*p)+G)*0x100+(Math.round((t-B)*p)+B)).toString(16).slice(1);
};

const hexToRgba = (hex, alpha) => {
    if (!hex || typeof hex !== 'string') return `rgba(0,0,0,${alpha})`;
    hex = hex.replace('#', '');
    if (hex.length !== 6) return `rgba(0,0,0,${alpha})`; // Guarda contra hex inválido
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r},${g},${b},${alpha})`;
};

// --- Dados Dinâmicos ---
const tenant = computed(() => page.props.tenant || {});
const authUser = computed(() => page.props.auth?.user || null);
const canRegister = computed(() => page.props.canRegister);

// Computadas que REALMENTE têm lógica
const tenantName = computed(() => tenant.value?.name || 'Portal');
const logoUrl = computed(() => {
    const url = tenant.value?.logotipo_url;
    if (!url) {
        return '/images/logo-placeholder-dark.svg';
    }
    return url.startsWith('http') ? url : `/storage/${url}`;
});

const endereco = computed(() => {
    const t = tenant.value;
    if (!t?.endereco_logradouro) return null;

    const parts = [
        `${t.endereco_logradouro}, ${t.endereco_numero || 's/n'}`,
        t.endereco_bairro,
        `${t.endereco_cidade}/${t.endereco_estado}`,
    ].filter(Boolean).join(' - ');

    const cep = t.endereco_cep ? `CEP: ${t.endereco_cep}` : '';
    return [parts, cep].filter(Boolean).join(' - ');
});

// REMOVIDAS: siteUrl, transparencyUrl, telefone, whatsapp, emailContato, instagram, youtube
// Elas serão acessadas diretamente via `tenant.value.propriedade` no template.

// --- Estilos Dinâmicos ---
const primaryColor = computed(() => tenant.value?.cor_primaria || '#10b981');
const secondaryColor = computed(() => tenant.value?.cor_secundaria || '#FFFFFF');

const primaryRGB = computed(() => {
    const hex = primaryColor.value.replace('#', '');
    if (hex.length !== 6) return '0,0,0'; // Adiciona robustez
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `${r},${g},${b}`;
});

const computedTenantStyle = computed(() => ({
    '--cor-primaria': primaryColor.value,
    '--cor-secundaria': secondaryColor.value,
    '--cor-primaria-dark': darken(primaryColor.value, -0.15),
    '--cor-primaria-light': hexToRgba(primaryColor.value, 0.1),
    '--cor-primaria-rgb': primaryRGB.value,
}));

// --- Estado da UI ---
const mobileMenuOpen = ref(false);
const showBackToTop = ref(false);
const scrolled = ref(false);

const handleScroll = () => {
    showBackToTop.value = window.scrollY > 400;
    scrolled.value = window.scrollY > 20;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// --- Lifecycle Hooks ---
onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// --- Links de Navegação ---
const navigationLinks = computed(() => {
    const links = [];
    const t = tenant.value; // Pega o valor uma vez

    if (t.publicar_vagas_emprego) {
        links.push({ href: route('portal.vagas.index'), label: 'Vagas de Emprego', icon: Briefcase });
    }
    if (t.publicar_memoria_legislativa) {
        links.push({ href: route('portal.memoria-legislativa'), label: 'Memória Legislativa', icon: Archive });
    }
    if (t.publicar_achados_e_perdidos) {
        links.push({ href: route('portal.achados-e-perdidos'), label: 'Documentos Perdidos', icon: FileBadge });
    }
    if (t.publicar_pessoas_desaparecidas) {
        links.push({ href: route('portal.pessoas-desaparecidas'), label: 'Pessoas Desaparecidas', icon: UserSearch });
    }
    return links;
});

const externalLinks = computed(() => {
    const links = [];
    const t = tenant.value; // Pega o valor uma vez

    // MODIFICADO: Usa `t.site_url` diretamente
    if (t.site_url) {
        links.push({ href: t.site_url, label: 'Site Oficial', icon: Building });
    }
    // MODIFICADO: Usa `t.transparency_url` diretamente
    if (t.transparency_url) {
        links.push({ href: t.transparency_url, label: 'Transparência', icon: Eye });
    }
    return links;
});
</script>

<template>
    <div :style="computedTenantStyle">
        <Head>
            <title>{{ title ? `${title} - ${tenantName}` : tenantName }}</title>
            <meta name="description" :content="`Centro de Atendimento ao Cidadão - ${tenantName}`">
        </Head>

        <Banner />

        <div class="layout-wrapper">
            <header :class="['main-header', scrolled && 'header-scrolled']">
                <div class="header-container">
                    <div class="header-brand">
                        <Link :href="route('portal.home')" class="logo-link group" aria-label="Página inicial">
                            <img :src="logoUrl" :alt="`Logo ${tenantName}`" class="logo-image">
                            <div class="logo-text">
                                <span class="logo-title">{{ tenantName }}</span>
                                <span class="logo-subtitle">Portal do Cidadão</span>
                            </div>
                        </Link>
                    </div>

                    <nav class="desktop-nav">
                        <Link :href="route('portal.home')" class="nav-link">
                            <HomeIcon :size="16" />
                            <span>Início</span>
                        </Link>
                        
                        <Link 
                            v-for="link in navigationLinks" 
                            :key="link.href"
                            :href="link.href" 
                            class="nav-link"
                        >
                            <component :is="link.icon" :size="16" />
                            <span>{{ link.label }}</span>
                        </Link>
                        
                        <a 
                            v-for="link in externalLinks" 
                            :key="link.href"
                            :href="link.href" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="nav-link"
                        >
                            <component :is="link.icon" :size="16" />
                            <span>{{ link.label }}</span>
                        </a>
                    </nav>

                    <div class="header-actions">

                        <div class="desktop-user-menu">
                            <template v-if="authUser">
                                <Dropdown align="right" width="56">
                                    <template #trigger>
                                        <button class="user-menu-trigger">
                                            <div class="user-avatar">
                                                {{ authUser.name.charAt(0) }}
                                            </div>
                                            <div class="user-info">
                                                <span class="user-name">{{ authUser.name.split(' ')[0] }}</span>
                                                <span class="user-role">Minha Conta</span>
                                            </div>
                                            <ChevronDown :size="16" class="user-chevron" />
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="dropdown-header">
                                            <p class="dropdown-user-name">{{ authUser.name }}</p>
                                            <p class="dropdown-user-email">{{ authUser.email }}</p>
                                        </div>
                                        <DropdownLink :href="route('profile.show')">
                                            <User :size="16" class="mr-2" />
                                            Perfil
                                        </DropdownLink>
                                        <DropdownLink :href="route('tenant.dashboard')">
                                            <HomeIcon :size="16" class="mr-2" />
                                            Meu Painel
                                        </DropdownLink>
                                        <div class="dropdown-divider" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button" class="w-full text-left">
                                                <LogOut :size="16" class="mr-2" />
                                                Sair
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </template>
                            <template v-else>
                                <Link :href="route('login')" class="btn-login">
                                    <LogIn :size="16" />
                                    <span>Entrar</span>
                                </Link>
                                <Link v-if="canRegister" :href="route('register')" class="btn-register">
                                    <UserPlus :size="16" />
                                    <span>Registrar</span>
                                </Link>
                            </template>
                        </div>

                        <button @click="mobileMenuOpen = true" class="mobile-menu-button">
                            <MenuIcon :size="24" />
                        </button>
                    </div>
                </div>
            </header>

            <TransitionRoot as="template" :show="mobileMenuOpen">
                <Dialog as="div" class="mobile-menu-dialog" @close="mobileMenuOpen = false">
                    <TransitionChild 
                        as="template" 
                        enter="ease-out duration-300" 
                        enter-from="opacity-0" 
                        enter-to="opacity-100" 
                        leave="ease-in duration-200" 
                        leave-from="opacity-100" 
                        leave-to="opacity-0"
                    >
                        <div class="mobile-menu-overlay" />
                    </TransitionChild>
                    
                    <div class="mobile-menu-container">
                        <TransitionChild 
                            as="template" 
                            enter="transition ease-in-out duration-300 transform" 
                            enter-from="translate-x-full" 
                            enter-to="translate-x-0" 
                            leave="transition ease-in-out duration-300 transform" 
                            leave-from="translate-x-0" 
                            leave-to="translate-x-full"
                        >
                            <DialogPanel class="mobile-menu-panel">
                                <div class="mobile-menu-header">
                                    <div class="mobile-menu-brand">
                                        <img :src="logoUrl" :alt="`Logo ${tenantName}`" class="mobile-logo">
                                        <span class="mobile-brand-name">{{ tenantName }}</span>
                                    </div>
                                    <button @click="mobileMenuOpen = false" class="mobile-close-button">
                                        <X :size="24" />
                                    </button>
                                </div>

                                <div v-if="authUser" class="mobile-user-info">
                                    <div class="mobile-user-avatar">
                                        {{ authUser.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="mobile-user-name">{{ authUser.name }}</p>
                                        <p class="mobile-user-email">{{ authUser.email }}</p>
                                    </div>
                                </div>

                                <nav class="mobile-nav">
                                    <div class="mobile-nav-section">
                                        <Link :href="route('portal.home')" class="mobile-nav-link">
                                            <HomeIcon :size="20" />
                                            <span>Início</span>
                                        </Link>
                                        
                                        <Link 
                                            v-for="link in navigationLinks" 
                                            :key="link.href"
                                            :href="link.href" 
                                            class="mobile-nav-link"
                                        >
                                            <component :is="link.icon" :size="20" />
                                            <span>{{ link.label }}</span>
                                        </Link>
                                        
                                        <a 
                                            v-for="link in externalLinks" 
                                            :key="link.href"
                                            :href="link.href" 
                                            target="_blank" 
                                            rel="noopener noreferrer" 
                                            class="mobile-nav-link"
                                        >
                                            <component :is="link.icon" :size="20" />
                                            <span>{{ link.label }}</span>
                                        </a>
                                    </div>

                                    <div class="mobile-nav-divider"></div>

                                    <div class="mobile-nav-section">
                                        <template v-if="authUser">
                                            <Link :href="route('profile.show')" class="mobile-nav-link">
                                                <User :size="20" />
                                                <span>Perfil</span>
                                            </Link>
                                            <Link :href="route('tenant.dashboard')" class="mobile-nav-link">
                                                <HomeIcon :size="20" />
                                                <span>Meu Painel</span>
                                            </Link>
                                            <form @submit.prevent="logout">
                                                <button type="submit" class="mobile-nav-link w-full">
                                                    <LogOut :size="20" />
                                                    <span>Sair</span>
                                                </button>
                                            </form>
                                        </template>
                                        <template v-else>
                                            <Link :href="route('login')" class="mobile-nav-link">
                                                <LogIn :size="20" />
                                                <span>Entrar</span>
                                            </Link>
                                            <Link v-if="canRegister" :href="route('register')" class="mobile-nav-link">
                                                <UserPlus :size="20" />
                                                <span>Registrar</span>
                                            </Link>
                                        </template>
                                    </div>
                                </nav>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </Dialog>
            </TransitionRoot>

            <main class="main-content">
                <slot />
            </main>

            <footer class="main-footer">
                <div class="footer-container">
                    <div class="footer-grid">
                        <div class="footer-about">
                            <div class="footer-logo-section">
                                <img :src="logoUrl" :alt="`Logotipo ${tenantName}`" class="footer-logo">
                                <h2 class="footer-brand">{{ tenantName }}</h2>
                            </div>
                            <p v-if="endereco" class="footer-address">
                                <MapPin :size="16" class="footer-icon" />
                                <span>{{ endereco }}</span>
                            </p>
                        </div>

                        <div class="footer-section">
                            <h3 class="footer-title">Contato</h3>
                            <ul class="footer-list">
                                <li v-if="tenant.telefone_contato" class="footer-list-item">
                                    <Phone :size="16" class="footer-icon" />
                                    <a :href="`tel:${tenant.telefone_contato.replace(/\D/g, '')}`" class="footer-link">
                                        {{ tenant.telefone_contato }}
                                    </a>
                                </li>
                                <li v-if="tenant.email_contato" class="footer-list-item">
                                    <Mail :size="16" class="footer-icon" />
                                    <a :href="`mailto:${tenant.email_contato}`" class="footer-link">
                                        {{ tenant.email_contato }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="footer-section">
                            <h3 class="footer-title">Links Úteis</h3>
                            <ul class="footer-list">
                                <li v-if="tenant.site_url" class="footer-list-item">
                                    <Building :size="16" class="footer-icon" />
                                    <a :href="tenant.site_url" target="_blank" rel="noopener noreferrer" class="footer-link">
                                        Site Oficial
                                    </a>
                                </li>
                                <li v-if="tenant.transparency_url" class="footer-list-item">
                                    <Eye :size="16" class="footer-icon" />
                                    <a :href="tenant.transparency_url" target="_blank" rel="noopener noreferrer" class="footer-link">
                                        Transparência
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="footer-section">
                            <h3 class="footer-title">Redes Sociais</h3>
                            <ul class="footer-list">
                                <li v-if="tenant.instagram" class="footer-list-item">
                                    <Instagram :size="16" class="footer-icon" />
                                    <a :href="tenant.instagram" target="_blank" rel="noopener noreferrer" class="footer-link">
                                        Instagram
                                    </a>
                                </li>
                                <li v-if="tenant.youtube" class="footer-list-item">
                                    <Youtube :size="16" class="footer-icon" />
                                    <a :href="tenant.youtube" target="_blank" rel="noopener noreferrer" class="footer-link">
                                        YouTube
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="footer-bottom">
                        <p class="footer-copyright">
                            &copy; {{ new Date().getFullYear() }} {{ tenantName }}. Todos os direitos reservados.
                        </p>
                        <p class="footer-credits">
                            Desenvolvido por <span class="footer-credits-highlight">Câmara Digital</span>
                        </p>
                    </div>
                </div>
            </footer>

            <div class="fab-container">
                <transition name="fab-slide">
                    <a 
                        v-if="tenant.whatsapp"
                        :href="`https://wa.me/55${tenant.whatsapp.replace(/\D/g, '')}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="fab whatsapp-fab"
                        aria-label="Fale conosco no WhatsApp"
                    >
                        <WhatsappIcon class="fab-icon" />
                    </a>
                </transition>

                <transition name="fab-slide">
                    <button 
                        v-show="showBackToTop" 
                        @click="scrollToTop"
                        class="fab primary-fab"
                        title="Voltar ao topo"
                        aria-label="Voltar ao topo"
                    >
                        <ArrowUp class="fab-icon-small" />
                    </button>
                </transition>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Layout Base */
.layout-wrapper {
 @apply flex flex-col min-h-screen bg-white; /* MODIFICADO: Fundo branco e remoção do dark: */
}

/* Header Styles */
.main-header {
 @apply sticky top-0 z-50 h-20 transition-all duration-300;
 background: rgba(255, 255, 255, 0.8);
 backdrop-filter: blur(12px);
 -webkit-backdrop-filter: blur(12px);
 border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

/* REGRA .dark .main-header REMOVIDA */

.header-scrolled {
 @apply h-16 shadow-lg;
}

.header-container {
 @apply container mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between gap-6;
}

/* Logo and Brand */
.header-brand {
 @apply flex items-center flex-shrink-0;
}

.logo-link {
 @apply flex items-center gap-3;
}

.logo-image {
 @apply h-12 w-auto transition-transform duration-300 group-hover:scale-105;
}

.header-scrolled .logo-image {
 @apply h-10;
}

.logo-text {
 @apply hidden lg:flex flex-col;
}

.logo-title {
 @apply text-lg font-black text-gray-900 leading-tight; /* MODIFICADO: removido dark:text-white */
}

.logo-subtitle {
 @apply text-xs font-medium text-gray-500; /* MODIFICADO: removido dark:text-gray-400 */
}

/* Desktop Navigation */
.desktop-nav {
 @apply hidden lg:flex items-center gap-1 flex-1 justify-center max-w-4xl;
}

.nav-link {
 @apply flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 transition-all duration-300 whitespace-nowrap; /* MODIFICADO: removido dark:text-gray-300 */
}

.nav-link:hover {
 @apply bg-gray-100 scale-105; /* MODIFICADO: removido dark:bg-gray-800 */
 color: var(--cor-primaria);
}

/* Header Actions */
.header-actions {
 @apply flex items-center gap-3;
}

.desktop-user-menu {
 @apply hidden lg:flex items-center gap-2;
}

.user-menu-trigger {
 @apply flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-300 hover:bg-gray-100; /* MODIFICADO: removido dark:hover:bg-gray-800 */
}

.user-avatar {
 @apply flex items-center justify-center h-10 w-10 rounded-full text-white font-bold shadow-lg;
 background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-dark));
}

.user-info {
 @apply hidden xl:flex flex-col items-start;
}

.user-name {
 @apply text-sm font-bold text-gray-900; /* MODIFICADO: removido dark:text-white */
}

.user-role {
 @apply text-xs text-gray-500; /* MODIFICADO: removido dark:text-gray-400 */
}

.user-chevron {
 @apply text-gray-400 transition-transform duration-300;
}

.user-menu-trigger:hover .user-chevron {
 @apply rotate-180;
}

/* Dropdown Styles */
.dropdown-header {
 @apply px-4 py-3 border-b border-gray-200; /* MODIFICADO: removido dark:border-gray-700 */
}

.dropdown-user-name {
 @apply text-sm font-bold text-gray-900; /* MODIFICADO: removido dark:text-white */
}

.dropdown-user-email {
 @apply text-xs text-gray-500 truncate; /* MODIFICADO: removido dark:text-gray-400 */
}

.dropdown-divider {
 @apply border-t border-gray-200; /* MODIFICADO: removido dark:border-gray-700 */
}

/* Auth Buttons */
.btn-login {
 @apply inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 transition-all duration-300 hover:bg-gray-100; /* MODIFICADO: removido dark:text-gray-300 e dark:hover:bg-gray-800 */
}

.btn-register {
 @apply inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105;
 background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-dark));
}

/* Mobile Menu Button */
.mobile-menu-button {
 @apply lg:hidden p-2 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors duration-300; /* MODIFICADO: removido dark:text-gray-300 e dark:hover:bg-gray-800 */
}

/* Mobile Menu */
.mobile-menu-dialog {
 @apply relative z-50 lg:hidden;
}

.mobile-menu-overlay {
 @apply fixed inset-0 bg-black/60 backdrop-blur-sm;
}

.mobile-menu-container {
 @apply fixed inset-0 flex justify-end;
}

.mobile-menu-panel {
 @apply relative w-full max-w-sm bg-white shadow-2xl flex flex-col h-full; /* MODIFICADO: removido dark:bg-gray-900 */
}

.mobile-menu-header {
 @apply flex items-center justify-between p-6 border-b border-gray-200; /* MODIFICADO: removido dark:border-gray-800 */
}

.mobile-menu-brand {
 @apply flex items-center gap-3;
}

.mobile-logo {
 @apply h-10 w-auto;
}

.mobile-brand-name {
 @apply text-lg font-black text-gray-900; /* MODIFICADO: removido dark:text-white */
}

.mobile-close-button {
 @apply p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors; /* MODIFICADO: removido dark:text-gray-400 e dark:hover:bg-gray-800 */
}

.mobile-user-info {
 @apply flex items-center gap-4 p-6 bg-gradient-to-r;
 background: linear-gradient(135deg, var(--cor-primaria-light), rgba(var(--cor-primaria-rgb), 0.05));
}

.mobile-user-avatar {
 @apply flex items-center justify-center h-14 w-14 rounded-full text-white text-xl font-bold shadow-lg;
 background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-dark));
}

.mobile-user-name {
 @apply text-base font-bold text-gray-900; /* MODIFICADO: removido dark:text-white */
}

.mobile-user-email {
 @apply text-sm text-gray-600 truncate; /* MODIFICADO: removido dark:text-gray-400 */
}

.mobile-nav {
 @apply flex-1 overflow-y-auto p-4;
}

.mobile-nav-section {
 @apply space-y-2;
}

.mobile-nav-link {
 @apply flex items-center gap-3 px-4 py-3.5 rounded-xl text-base font-semibold text-gray-700 transition-all duration-300 hover:bg-gray-100; /* MODIFICADO: removido dark:text-gray-300 e dark:hover:bg-gray-800 */
}

.mobile-nav-link:hover {
 color: var(--cor-primaria);
 transform: translateX(4px);
}

.mobile-nav-divider {
 @apply my-4 border-t border-gray-200; /* MODIFICADO: removido dark:border-gray-800 */
}

/* Main Content */
.main-content {
 @apply flex-grow;
}

/* Footer */
.main-footer {
 @apply bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-slate-300 mt-auto;
}

.footer-container {
 @apply container mx-auto px-6 py-16;
}

.footer-grid {
 @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12;
}

.footer-about {
 @apply sm:col-span-2 lg:col-span-1;
}

.footer-logo-section {
 @apply flex items-center gap-3 mb-6;
}

.footer-logo {
 @apply h-12 w-auto bg-white p-2 rounded-lg shadow-lg;
}

.footer-brand {
 @apply text-xl font-black text-white;
}

.footer-address {
 @apply flex gap-3 text-sm text-slate-400 leading-relaxed;
}

.footer-section {
 @apply space-y-4;
}

.footer-title {
 @apply text-base font-bold text-white tracking-wider uppercase mb-6 relative inline-block;
}

.footer-title::after {
 content: '';
 @apply absolute -bottom-2 left-0 w-12 h-1 rounded-full;
 background: var(--cor-primaria);
}

.footer-list {
 @apply space-y-3;
}

.footer-list-item {
 @apply flex items-center gap-3 text-sm transition-all duration-300 hover:translate-x-1;
}

.footer-icon {
 @apply flex-shrink-0 text-slate-500;
}

.footer-link {
 @apply text-slate-400 hover:text-white transition-colors duration-300;
}

.footer-bottom {
 @apply pt-10 border-t border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4;
}

.footer-copyright {
 @apply text-xs text-slate-500;
}

.footer-credits {
 @apply text-xs text-slate-500;
}

.footer-credits-highlight {
 @apply font-bold;
 color: var(--cor-primaria);
}

/* Floating Action Buttons */
.fab-container {
 @apply fixed bottom-6 right-6 z-40 flex flex-col gap-3;
}

.fab {
 @apply w-14 h-14 rounded-full flex items-center justify-center shadow-2xl transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-offset-2;
}

.whatsapp-fab {
 @apply bg-green-500 hover:bg-green-600;
 box-shadow: 0 10px 30px rgba(34, 197, 94, 0.4);
}

.whatsapp-fab:hover {
 box-shadow: 0 15px 40px rgba(34, 197, 94, 0.6);
}

.whatsapp-fab:focus {
 @apply ring-green-400;
}

.primary-fab {
 @apply text-white;
 background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-dark));
 box-shadow: 0 10px 30px rgba(var(--cor-primaria-rgb), 0.4);
}

.primary-fab:hover {
 box-shadow: 0 15px 40px rgba(var(--cor-primaria-rgb), 0.6);
}

.primary-fab:focus {
 ring-color: var(--cor-primaria);
}

.fab-icon {
 @apply w-7 h-7 text-white;
}

.fab-icon-small {
 @apply w-6 h-6;
}

/* FAB Animations */
.fab-slide-enter-active,
.fab-slide-leave-active {
 transition: all 0.3s ease;
}

.fab-slide-enter-from {
 opacity: 0;
 transform: translateY(20px) scale(0.8);
}

.fab-slide-leave-to {
 opacity: 0;
 transform: translateY(20px) scale(0.8);
}

/* Responsive Adjustments */
@media (max-width: 1024px) {
 .header-container {
  @apply gap-4;
 }
  
 .logo-image {
  @apply h-10;
 }
}

@media (max-width: 640px) {
 .header-container {
  @apply px-4;
 }
  
 .logo-image {
  @apply h-9;
 }
  
 .fab-container {
  @apply bottom-4 right-4;
 }
  
 .fab {
  @apply w-12 h-12;
 }
  
 .fab-icon {
  @apply w-6 h-6;
 }
  
 .fab-icon-small {
  @apply w-5 h-5;
 }
  
 .footer-container {
  @apply px-4 py-12;
 }
  
 .footer-grid {
  @apply gap-8;
 }
}

/* Utility Classes */
::selection {
 background: rgba(var(--cor-primaria-rgb), 0.2);
 color: inherit;
}

/* Scrollbar Styling */
::-webkit-scrollbar {
 width: 8px;
 height: 8px;
}

::-webkit-scrollbar-track {
 @apply bg-gray-100; /* MODIFICADO: removido dark:bg-gray-900 */
}

::-webkit-scrollbar-thumb {
 @apply bg-gray-300 rounded-full; /* MODIFICADO: removido dark:bg-gray-700 */
}

::-webkit-scrollbar-thumb:hover {
 background: var(--cor-primaria);
}

/* Focus Visible Styles */
*:focus-visible {
 @apply outline-none ring-2 ring-offset-2;
 ring-color: var(--cor-primaria);
}

/* Animation Classes */
@keyframes fadeIn {
 from {
  opacity: 0;
  transform: translateY(10px);
 }
 to {
  opacity: 1;
  transform: translateY(0);
 }
}

.animate-fade-in {
 animation: fadeIn 0.4s ease-out;
}

/* Loading State */
.loading-pulse {
 animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
 0%, 100% {
  opacity: 1;
 }
 50% {
  opacity: 0.5;
 }
}

/* Smooth Transitions */
.smooth-transition {
 transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Glass Effect */
.glass-effect {
 backdrop-filter: blur(12px);
 -webkit-backdrop-filter: blur(12px);
}

/* Gradient Text */
.gradient-text {
 background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-dark));
 -webkit-background-clip: text;
 -webkit-text-fill-color: transparent;
 background-clip: text;
}

/* Dark Mode Specific Adjustments - BLOCO REMOVIDO */

/* Print Styles */
@media print {
 .main-header,
 .main-footer,
 .fab-container,
 .mobile-menu-button {
  display: none !important;
 }
  
 .main-content {
  @apply p-0;
 }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
 *,
 *::before,
 *::after {
  animation-duration: 0.01ms !important;
  animation-iteration-count: 1 !important;
  transition-duration: 0.01ms !important;
 }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
 .nav-link,
 .mobile-nav-link {
  @apply border border-current;
 }
  
 .fab {
  @apply border-2 border-current;
 }
}
</style>

<style>
/* Global Styles */
html {
    font-size: 14px;
    scroll-behavior: smooth;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Custom Scrollbar for Main Content */
.main-content {
    scrollbar-width: thin;
    scrollbar-color: var(--cor-primaria) transparent;
}

.main-content::-webkit-scrollbar {
    width: 8px;
}

.main-content::-webkit-scrollbar-track {
    background: transparent;
}

.main-content::-webkit-scrollbar-thumb {
    background: var(--cor-primaria);
    border-radius: 10px;
}

.main-content::-webkit-scrollbar-thumb:hover {
    background: var(--cor-primaria-dark);
}
</style>
