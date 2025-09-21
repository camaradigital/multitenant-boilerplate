<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Banner from '@/Components/Banner.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import {
    Dialog,
    DialogPanel,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import {
    ChevronDown, Building, Eye, LogOut, LogIn, UserPlus, MapPin, Phone, Mail, ArrowUp, X, FileBadge, UserSearch,
    Menu as MenuIcon,
    Landmark as Archive,
    Briefcase,
    Instagram,
    Youtube,
    // MessageSquare foi removido daqui
} from 'lucide-vue-next';
// --- 1. IMPORTE O NOVO ÍCONE ---
import WhatsappIcon from '@/Components/Icons/WhatsappIcon.vue';

defineProps({
    title: String,
});

// --- LÓGICA DO LAYOUT ---
const page = usePage();

// --- Logout ---
const logout = () => {
    router.post(route('logout'));
};

// --- DADOS DINÂMICOS ---
const tenant = computed(() => page.props.tenant || {});
const authUser = computed(() => page.props.auth?.user || null);
const canRegister = computed(() => page.props.canRegister);

const tenantName = computed(() => tenant.value?.name || 'Portal');
const logoUrl = computed(() => {
    if (tenant.value?.logotipo_url) {
        if (tenant.value.logotipo_url.startsWith('http')) {
            return tenant.value.logotipo_url;
        }
        return `/storage/${tenant.value.logotipo_url}`;
    }
    return '/images/logo-placeholder-dark.svg';
});
const siteUrl = computed(() => tenant.value?.site_url);
const transparencyUrl = computed(() => tenant.value?.transparency_url);

const endereco = computed(() => {
  const t = tenant.value;
  if (!t?.endereco_logradouro) return null;

  const parts = [
    `${t.endereco_logradouro}, ${t.endereco_numero || 's/n'}`,
    t.endereco_bairro,
    `${t.endereco_cidade}/${t.endereco_estado}`,
  ];

  const formattedParts = parts.filter(Boolean).join(' - ');
  const cep = t.endereco_cep ? `CEP: ${t.endereco_cep}` : '';

  return [formattedParts, cep].filter(Boolean).join(' - ');
});

const telefone = computed(() => tenant.value?.telefone_contato);
const whatsapp = computed(() => tenant.value?.whatsapp);
const emailContato = computed(() => tenant.value?.email_contato);
const instagram = computed(() => tenant.value?.instagram);
const youtube = computed(() => tenant.value?.youtube);

// --- ESTILO DINÂMICO ---
const computedTenantStyle = computed(() => {
    const tenantData = page.props.tenant;
    return {
        '--cor-primaria': tenantData?.cor_primaria || '#10b981',
        '--cor-secundaria': tenantData?.cor_secundaria || '#FFFFFF',
    };
});

// --- ESTADO DA UI ---
const mobileMenuOpen = ref(false);
const showBackToTop = ref(false);

const handleScroll = () => {
    showBackToTop.value = window.scrollY > 400;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <div :style="computedTenantStyle">
        <Head>
            <title>{{ title ? `${title} - ${tenantName}` : tenantName }}</title>
            <meta name="description" :content="`Centro de Atendimento ao Cidadão - ${tenantName}`">
        </Head>

        <Banner />

        <div class="flex flex-col min-h-screen bg-slate-50 dark:bg-[#0A1E1C]">
            <header class="main-header sticky top-0 z-40" role="navigation">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-full">
                    <div class="flex items-center gap-6">
                        <Link :href="route('portal.home')" class="flex items-center gap-3" aria-label="Página inicial">
                            <img :src="logoUrl" :alt="`Logo ${tenantName}`" class="h-14 w-auto">
                        </Link>
                        <div class="hidden lg:flex items-center space-x-1">
                            <Link v-if="tenant.publicar_vagas_emprego" :href="route('portal.vagas.index')" class="nav-link">
                                <Briefcase :size="16" class="mr-2" /> Vagas de Emprego
                            </Link>
                            <Link v-if="tenant.publicar_memoria_legislativa" :href="route('portal.memoria-legislativa')" class="nav-link">
                                <Archive :size="16" class="mr-2" /> Memória Legislativa
                            </Link>
                            <Link v-if="tenant.publicar_achados_e_perdidos" :href="route('portal.achados-e-perdidos')" class="nav-link">
                                <FileBadge :size="16" class="mr-2" /> Documentos Perdidos
                            </Link>
                            <Link v-if="tenant.publicar_pessoas_desaparecidas" :href="route('portal.pessoas-desaparecidas')" class="nav-link">
                                <UserSearch :size="16" class="mr-2" /> Pessoas Desaparecidas
                            </Link>
                            <a v-if="siteUrl" :href="siteUrl" target="_blank" rel="noopener noreferrer" class="nav-link">
                                <Building :size="16" class="mr-2" /> Site Oficial
                            </a>
                            <a v-if="transparencyUrl" :href="transparencyUrl" target="_blank" rel="noopener noreferrer" class="nav-link">
                                <Eye :size="16" class="mr-2" /> Transparência
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <ThemeToggle />

                        <div class="hidden lg:flex items-center gap-2">
                            <template v-if="authUser">
                                <Dropdown align="right" width="56">
                                    <template #trigger>
                                        <button aria-label="Abrir menu do usuário" class="flex items-center gap-3 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-full pr-3">
                                            <span class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-emerald-500 dark:bg-emerald-600 text-sm font-semibold text-white ring-2 ring-white dark:ring-gray-800">
                                                {{ authUser.name.charAt(0) }}
                                            </span>
                                            <span class="hidden sm:inline">{{ authUser.name.split(' ')[0] }}</span>
                                            <ChevronDown :size="16" class="opacity-70" />
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ authUser.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ authUser.email }}</p>
                                        </div>
                                        <DropdownLink :href="route('profile.show')"> Perfil </DropdownLink>
                                        <DropdownLink :href="route('tenant.dashboard')"> Meu Painel </DropdownLink>
                                        <div class="border-t border-gray-200 dark:border-gray-600" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button" class="w-full text-left">Sair</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </template>
                            <template v-else>
                                <Link :href="route('login')" class="nav-link">
                                    <LogIn :size="16" class="mr-2" /> Entrar
                                </Link>
                                <Link v-if="canRegister" :href="route('register')" class="btn-primary-outline">
                                    <UserPlus :size="16" class="mr-2" /> Registrar
                                </Link>
                            </template>
                        </div>

                        <div class="lg:hidden">
                            <button @click="mobileMenuOpen = true" class="header-icon-btn">
                                <MenuIcon :size="28" />
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <TransitionRoot as="template" :show="mobileMenuOpen">
                <Dialog as="div" class="relative z-50 lg:hidden" @close="mobileMenuOpen = false">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
                    </TransitionChild>
                    <div class="fixed inset-0 z-50 flex justify-end">
                        <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="translate-x-full">
                            <DialogPanel class="relative w-full max-w-sm bg-slate-50 dark:bg-gray-900 p-6 shadow-2xl">
                                <div class="flex items-center justify-between mb-8">
                                    <span class="font-bold text-xl text-gray-800 dark:text-gray-200">{{ tenantName }}</span>
                                    <button @click="mobileMenuOpen = false" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors">
                                        <X :size="28" />
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    <Link v-if="tenant.publicar_vagas_emprego" :href="route('portal.vagas.index')" class="mobile-nav-link"><Briefcase :size="20" class="mr-4" /> Vagas de Emprego</Link>
                                    <Link v-if="tenant.publicar_memoria_legislativa" :href="route('portal.memoria-legislativa')" class="mobile-nav-link"><Archive :size="20" class="mr-4" /> Memória Legislativa</Link>
                                    <Link v-if="tenant.publicar_achados_e_perdidos" :href="route('portal.achados-e-perdidos')" class="mobile-nav-link"><FileBadge :size="20" class="mr-4" /> Documentos Perdidos</Link>
                                    <Link v-if="tenant.publicar_pessoas_desaparecidas" :href="route('portal.pessoas-desaparecidas')" class="mobile-nav-link"><UserSearch :size="20" class="mr-4" /> Pessoas Desaparecidas</Link>
                                    <a v-if="siteUrl" :href="siteUrl" target="_blank" rel="noopener noreferrer" class="mobile-nav-link"><Building :size="20" class="mr-4" /> Site Oficial</a>
                                    <a v-if="transparencyUrl" :href="transparencyUrl" target="_blank" rel="noopener noreferrer" class="mobile-nav-link"><Eye :size="20" class="mr-4" /> Transparência</a>

                                    <div class="border-t border-gray-200 dark:border-gray-700 pt-5 mt-5 space-y-4">
                                        <template v-if="authUser">
                                            <a :href="route('profile.show')" class="mobile-nav-link"><UserPlus :size="20" class="mr-4" /> Perfil</a>
                                            <a :href="route('tenant.dashboard')" class="mobile-nav-link"><LogIn :size="20" class="mr-4" /> Meu Painel</a>
                                            <form @submit.prevent="logout">
                                                <button type="submit" class="mobile-nav-link w-full text-left"><LogOut :size="20" class="mr-4" /> Sair</button>
                                            </form>
                                        </template>
                                        <template v-else>
                                            <a :href="route('login')" class="mobile-nav-link"><LogIn :size="20" class="mr-4" /> Entrar</a>
                                            <a v-if="canRegister" :href="route('register')" class="mobile-nav-link"><UserPlus :size="20" class="mr-4" /> Registrar</a>
                                        </template>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </Dialog>
            </TransitionRoot>

            <main role="main" class="flex-grow scrollbar-thin scrollbar-thumb-emerald-500 scrollbar-track-emerald-900/50 hover:scrollbar-thumb-emerald-400">
                <slot />
            </main>

            <footer class="bg-slate-800 text-slate-300">
                <div class="container mx-auto px-6 py-12">
                   <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                       <div class="sm:col-span-2">
                           <div class="flex items-center mb-4">
                               <img v-if="logoUrl" :src="logoUrl" :alt="`Logotipo ${tenant.name}`" class="h-10 mr-3 bg-white p-1 rounded">
                               <h2 class="text-lg font-bold text-white">{{ tenant.name }}</h2>
                           </div>
                           <p v-if="endereco" class="footer-contact-item max-w-sm"><MapPin :size="16" class="mr-2 mt-1 flex-shrink-0" /> {{ endereco }}</p>
                       </div>

                       <div>
                           <h3 class="text-base font-semibold text-white tracking-wider uppercase mb-4">Contato</h3>
                           <ul class="space-y-2 text-sm">
                               <li v-if="telefone" class="footer-contact-item">
                                   <Phone :size="16" />
                                   <a :href="`tel:${telefone.replace(/\D/g, '')}`">{{ telefone }}</a>
                               </li>
                               <li v-if="emailContato" class="footer-contact-item">
                                   <Mail :size="16" />
                                   <a :href="`mailto:${emailContato}`">{{ emailContato }}</a>
                               </li>
                           </ul>
                       </div>

                       <div>
                           <h3 class="text-base font-semibold text-white tracking-wider uppercase mb-4">Redes Sociais</h3>
                           <ul class="space-y-2 text-sm">
                               <li v-if="instagram" class="footer-contact-item">
                                   <Instagram :size="16" />
                                   <a :href="instagram" target="_blank" rel="noopener noreferrer">Instagram</a>
                               </li>
                               <li v-if="youtube" class="footer-contact-item">
                                   <Youtube :size="16" />
                                   <a :href="youtube" target="_blank" rel="noopener noreferrer">YouTube</a>
                               </li>
                           </ul>
                       </div>
                   </div>
                   <div class="mt-10 pt-8 border-t border-slate-700 text-center text-xs text-slate-500">
                       <p>&copy; {{ new Date().getFullYear() }} {{ tenant.name }}. Todos os direitos reservados.</p>
                       <p class="mt-1">Uma plataforma desenvolvida por Câmara Digital</p>
                   </div>
                </div>
            </footer>

            <div class="fixed bottom-6 right-6 z-30 flex flex-col items-center gap-3">
                <a v-if="whatsapp"
                    :href="`https://wa.me/55${whatsapp.replace(/\D/g, '')}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="fab-btn bg-green-500 hover:bg-green-600 focus:ring-green-500"
                    aria-label="Fale conosco no WhatsApp">
                    <WhatsappIcon class="w-7 h-7" />
                </a>

                <transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-4">
                    <button v-show="showBackToTop" @click="scrollToTop"
                        class="fab-btn text-white"
                        :style="{ backgroundColor: 'var(--cor-primaria)' }"
                        title="Voltar ao topo"
                        aria-label="Voltar ao topo">
                        <ArrowUp :size="24" />
                    </button>
                </transition>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Estilos Globais e Variáveis de Tema */
body {
    font-family: 'Inter', sans-serif;
}

/* ===== ESTILOS COM DARK MODE ===== */
.main-header {
    @apply h-16 shrink-0 border-b;
    @apply bg-white/75 dark:bg-[#0D2C2A]/70 border-gray-200 dark:border-green-400/10;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.header-icon-btn {
    @apply rounded-full p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800/60 dark:hover:text-white transition-colors duration-200;
}

.nav-link {
    @apply flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/60 transition-all duration-200;
}
.nav-link:hover {
    color: var(--cor-primaria);
}
.dark .nav-link:hover {
    color: var(--cor-primaria);
}

.mobile-nav-link {
    @apply flex items-center rounded-lg px-3 py-3 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-800/60 transition-all duration-200;
}
.mobile-nav-link:hover {
    color: var(--cor-primaria);
}
.dark .mobile-nav-link:hover {
    color: var(--cor-primaria);
}

.footer-contact-item {
    @apply flex items-center gap-2 text-slate-400 hover:text-white transition-colors;
}

.footer-link {
    @apply text-sm text-gray-400 hover:text-white transition-colors duration-300;
}

/* NOVOS ESTILOS REATORADOS */
.btn-primary-outline {
    @apply inline-flex items-center justify-center px-4 py-2 border rounded-md font-semibold text-sm transition ease-in-out duration-150;
    color: var(--cor-primaria);
    border-color: var(--cor-primaria);
}
.btn-primary-outline:hover {
    background-color: hsla(var(--cor-primaria-hsl), 0.1); /* Usa HSL para opacidade */
}
/* Fallback para cor_primaria em formato não-HSL. Ajuste a opacidade como preferir */
[style*="--cor-primaria:#"] .btn-primary-outline:hover {
    background-color: rgba(16, 185, 129, 0.1);
}

.fab-btn {
    @apply w-12 h-12 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.fab-btn:hover {
    filter: brightness(110%);
}
</style>
