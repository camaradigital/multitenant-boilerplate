<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import {
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  Dialog,
  DialogPanel,
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue';
import {
    ChevronDown,
    Building,
    Eye,
    UserCircle,
    LayoutDashboard,
    LogOut,
    LogIn,
    UserPlus,
    Grid,
    Send,
    MapPin,
    Phone,
    Mail,
    ArrowUp,
    Menu as MenuIcon,
    X,
    CheckCircle2,
    AlertTriangle,
    Info,
    ChevronUp,
    Server
} from 'lucide-vue-next';

// --- PROPS & PAGE DATA ---
const props = defineProps({
    servicos: Array,
});
const page = usePage();

// --- TENANT & AUTH DATA ---
const tenant = computed(() => page.props.tenant || {});
const authUser = computed(() => page.props.auth?.user || null);
const laravelVersion = computed(() => page.props.versions?.laravel || '');
const canLogin = computed(() => page.props.canLogin);
const canRegister = computed(() => page.props.canRegister);


// --- DYNAMIC CONTENT ---
const tenantName = computed(() => tenant.value?.name || 'Portal do Cidadão');
const logoUrl = computed(() => tenant.value?.logotipo_url || '/images/logo-placeholder-dark.svg');
const siteUrl = computed(() => tenant.value?.site_url);
const transparencyUrl = computed(() => tenant.value?.transparency_url);
const endereco = computed(() => {
    if (!tenant.value?.endereco_logradouro) return null;
    return `${tenant.value.endereco_logradouro}, ${tenant.value.endereco_numero || 's/n'}`;
});
const telefone = computed(() => tenant.value?.telefone);
const emailContato = computed(() => tenant.value?.email_contato);

// --- DYNAMIC STYLING ---
const primaryColor = computed(() => tenant.value?.cor_primaria || '#1e3a8a');
const secondaryColor = computed(() => tenant.value?.cor_secundaria || '#10b981');

const darken = (hex, percent) => {
    if (!hex || typeof hex !== 'string') return '#000000';
    let f = parseInt(hex.slice(1), 16), t = percent < 0 ? 0 : 255, p = percent < 0 ? percent * -1 : percent, R = f >> 16, G = f >> 8 & 0x00FF, B = f & 0x0000FF;
    return "#" + (0x1000000 + (Math.round((t - R) * p) + R) * 0x10000 + (Math.round((t - G) * p) + G) * 0x100 + (Math.round((t - B) * p) + B)).toString(16).slice(1);
};

const heroStyle = computed(() => ({
  background: `linear-gradient(135deg, ${primaryColor.value}, ${darken(primaryColor.value, 20)})`
}));

const themeStyles = computed(() => `
    :root {
      --primary: ${primaryColor.value};
      --secondary: ${secondaryColor.value};
      --btn-primary-hover: ${darken(primaryColor.value, -10)};
    }
`);

// --- UI STATE & LOGIC ---
const mobileMenuOpen = ref(false);
const successMessage = ref(page.props.flash?.success);
const errorMessage = ref(page.props.errors?.servico);

const form = useForm({ servico_id: null });

function solicitar(servicoId) {
    if (!authUser.value) {
        router.visit(route('login'));
        return;
    }
    form.servico_id = servicoId;
    form.post(route('tenant.solicitacoes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Sua solicitação foi registrada com sucesso! Acompanhe o status no seu portal.';
            errorMessage.value = null;
        },
        onError: (errors) => {
            successMessage.value = null;
            errorMessage.value = errors.servico || 'Ocorreu um erro ao solicitar o serviço. Por favor, tente novamente.';
        }
    });
}

const showBackToTop = ref(false);
onMounted(() => {
    window.addEventListener('scroll', () => {
        showBackToTop.value = window.scrollY > 400;
    });
});
const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const faqs = [
    { question: 'Como posso solicitar um serviço?', answer: 'Primeiro, você precisa se cadastrar e fazer login na plataforma. Depois, basta escolher um dos serviços disponíveis na lista e clicar em "Solicitar".' },
    { question: 'Onde acompanho minhas solicitações?', answer: 'Após fazer login, acesse a área "Meu Painel". Lá você encontrará um histórico de todas as suas solicitações e o status atual de cada uma.' },
    { question: 'Os serviços têm algum custo?', answer: 'Todos os serviços oferecidos através desta plataforma são gratuitos para os cidadãos do município.' },
    { question: 'Meus dados estão seguros?', answer: 'Sim. Nossa plataforma segue rigorosamente a Lei Geral de Proteção de Dados (LGPD), garantindo a segurança e a privacidade de todas as suas informações.' },
];
</script>

<template>
    <Head>
        <title>{{ tenantName }} - CAC Digital</title>
        <meta name="description" :content="`Centro de Atendimento ao Cidadão - ${tenantName}`">
        <component :is="'style'">{{ themeStyles }}</component>
    </Head>

    <div class="flex flex-col min-h-screen font-sans bg-gray-50 text-gray-800">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-40" role="navigation">
            <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a :href="route('portal.home')" class="flex items-center gap-3" aria-label="Página inicial">
                        <img :src="logoUrl" :alt="`Logo ${tenantName}`" class="h-9 w-auto">
                        <span class="font-semibold text-xl text-gray-800 hidden sm:inline">{{ tenantName }}</span>
                    </a>
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-2">
                        <a v-if="siteUrl" :href="siteUrl" target="_blank" rel="noopener noreferrer" class="nav-link">
                            <Building :size="16" class="mr-1.5" /> Site da Câmara
                        </a>
                        <a v-if="transparencyUrl" :href="transparencyUrl" target="_blank" rel="noopener noreferrer" class="nav-link">
                            <Eye :size="16" class="mr-1.5" /> Transparência
                        </a>
                        <!-- User Dropdown (Headless UI) -->
                        <Menu as="div" class="relative">
                            <MenuButton class="nav-link">
                                <UserCircle :size="16" class="mr-1.5" /> Acesso <ChevronDown :size="16" class="ml-1" />
                            </MenuButton>
                            <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                <MenuItems class="absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <div class="py-1">
                                        <template v-if="authUser">
                                            <MenuItem v-slot="{ active }">
                                                <a :href="route('tenant.dashboard')" :class="[active ? 'bg-gray-100' : '', 'dropdown-item']">
                                                    <LayoutDashboard :size="16" class="mr-2" /> Meu Painel
                                                </a>
                                            </MenuItem>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <MenuItem v-slot="{ active }">
                                                <form @submit.prevent="router.post(route('logout'))" class="w-full">
                                                    <button type="submit" :class="[active ? 'bg-gray-100' : '', 'dropdown-item w-full text-left']">
                                                        <LogOut :size="16" class="mr-2" /> Sair
                                                    </button>
                                                </form>
                                            </MenuItem>
                                        </template>
                                        <template v-else>
                                            <MenuItem v-slot="{ active }">
                                                <a :href="route('login')" :class="[active ? 'bg-gray-100' : '', 'dropdown-item']">
                                                    <LogIn :size="16" class="mr-2" /> Entrar
                                                </a>
                                            </MenuItem>
                                            <MenuItem v-if="canRegister" v-slot="{ active }">
                                                <a :href="route('register')" :class="[active ? 'bg-gray-100' : '', 'dropdown-item']">
                                                    <UserPlus :size="16" class="mr-2" /> Registrar
                                                </a>
                                            </MenuItem>
                                        </template>
                                    </div>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                    <!-- Mobile Menu Button -->
                    <div class="md:hidden">
                        <button @click="mobileMenuOpen = true" class="p-2 rounded-md text-gray-600 hover:bg-gray-100">
                            <MenuIcon :size="24" />
                        </button>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Mobile Menu (Headless UI Dialog) -->
        <TransitionRoot as="template" :show="mobileMenuOpen">
            <Dialog as="div" class="relative z-50 md:hidden" @close="mobileMenuOpen = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black bg-opacity-25" />
                </TransitionChild>
                <div class="fixed inset-0 z-50 flex justify-end">
                    <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="translate-x-full">
                        <DialogPanel class="relative w-full max-w-xs bg-white p-6">
                            <div class="flex items-center justify-between mb-8">
                                <span class="font-semibold text-lg">{{ tenantName }}</span>
                                <button @click="mobileMenuOpen = false" class="p-2 rounded-md text-gray-500 hover:bg-gray-100">
                                    <X :size="24" />
                                </button>
                            </div>
                            <div class="space-y-4">
                                <a v-if="siteUrl" :href="siteUrl" target="_blank" rel="noopener noreferrer" class="mobile-nav-link">
                                    <Building :size="18" class="mr-3" /> Site da Câmara
                                </a>
                                <a v-if="transparencyUrl" :href="transparencyUrl" target="_blank" rel="noopener noreferrer" class="mobile-nav-link">
                                    <Eye :size="18" class="mr-3" /> Transparência
                                </a>
                                <div class="border-t border-gray-200 pt-4 mt-4 space-y-4">
                                    <template v-if="authUser">
                                        <a :href="route('tenant.dashboard')" class="mobile-nav-link"><LayoutDashboard :size="18" class="mr-3" /> Meu Painel</a>
                                        <form @submit.prevent="router.post(route('logout'))">
                                            <button type="submit" class="mobile-nav-link w-full text-left"><LogOut :size="18" class="mr-3" /> Sair</button>
                                        </form>
                                    </template>
                                    <template v-else>
                                        <a :href="route('login')" class="mobile-nav-link"><LogIn :size="18" class="mr-3" /> Entrar</a>
                                        <a v-if="canRegister" :href="route('register')" class="mobile-nav-link"><UserPlus :size="18" class="mr-3" /> Registrar</a>
                                    </template>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>

        <main role="main" class="flex-grow">
            <!-- Hero Section -->
            <section
                class="text-white text-center relative overflow-hidden"
                :style="heroStyle"
                aria-labelledby="hero-title"
            >
                 <div class="absolute inset-0 bg-hero-pattern opacity-10 z-0"></div>
                 <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-24 relative z-10">
                    <h1 id="hero-title" class="text-4xl md:text-5xl font-bold mb-5 leading-tight">
                        {{ tenantName }} <br> Centro de Atendimento ao Cidadão
                    </h1>
                    <p class="text-lg md:text-xl max-w-2xl mx-auto mb-8 opacity-90">
                        Acesse os serviços digitais da sua Câmara Municipal de forma simples e segura.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                        <a v-if="authUser" :href="route('tenant.dashboard')" class="btn btn-secondary">
                            <LayoutDashboard :size="20" class="mr-2" /> Meu Painel
                        </a>
                        <template v-else>
                            <a :href="route('login')" class="btn btn-secondary">
                               <LogIn :size="20" class="mr-2" /> Entrar
                            </a>
                             <a v-if="canRegister" :href="route('register')" class="btn btn-primary-outline">
                                <UserPlus :size="20" class="mr-2" /> Registrar
                            </a>
                        </template>
                         <a href="#services" class="btn btn-light-outline">
                            <Grid :size="20" class="mr-2" /> Ver Serviços
                        </a>
                    </div>
                 </div>
            </section>

             <!-- Notifications -->
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-8">
                 <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                    <div v-if="successMessage" class="notification bg-green-50 border-green-200 text-green-800" role="alert">
                        <CheckCircle2 class="text-green-500" />
                        <span class="flex-grow">{{ successMessage }}</span>
                        <button @click="successMessage = null"><X :size="18" /></button>
                    </div>
                </transition>
                <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                    <div v-if="errorMessage" class="notification bg-red-50 border-red-200 text-red-800" role="alert">
                        <AlertTriangle class="text-red-500" />
                        <span class="flex-grow">{{ errorMessage }}</span>
                        <button @click="errorMessage = null"><X :size="18" /></button>
                    </div>
                </transition>
            </div>

            <!-- How it Works Section -->
            <section id="how-it-works" class="py-20" aria-labelledby="how-it-works-title">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                     <h2 id="how-it-works-title" class="section-title">Como Funciona</h2>
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                         <div class="step-card">
                             <div class="step-icon-wrapper"><UserPlus :size="32" class="text-primary"/></div>
                             <h3 class="step-title">1. Cadastre-se</h3>
                             <p class="step-description">Crie sua conta em minutos para ter acesso a todos os serviços da plataforma.</p>
                         </div>
                         <div class="step-card">
                             <div class="step-icon-wrapper"><Send :size="32" class="text-primary"/></div>
                             <h3 class="step-title">2. Solicite</h3>
                             <p class="step-description">Navegue pelos serviços disponíveis e faça sua solicitação com apenas alguns cliques.</p>
                         </div>
                         <div class="step-card">
                             <div class="step-icon-wrapper"><LayoutDashboard :size="32" class="text-primary"/></div>
                             <h3 class="step-title">3. Acompanhe</h3>
                             <p class="step-description">Acesse seu painel pessoal para verificar o andamento de suas solicitações em tempo real.</p>
                         </div>
                     </div>
                </div>
            </section>

            <!-- Services Section -->
            <section id="services" class="py-20 bg-gray-100" aria-labelledby="services-title">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 id="services-title" class="section-title">Serviços Disponíveis</h2>
                    <div v-if="servicos && servicos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="servico in servicos" :key="servico.id" class="service-card" role="article">
                            <div class="flex-grow">
                                <div class="service-icon-wrapper">
                                    <component :is="servico.tipo_servico.icon || Server" class="text-4xl text-primary" />
                                </div>
                                <h3 class="text-xl font-semibold mt-4 mb-2 text-gray-900">{{ servico.nome }}</h3>
                                <p class="text-gray-600 text-sm">{{ servico.descricao }}</p>
                                <div v-if="servico.regras_limite" class="mt-4 text-xs text-amber-700 bg-amber-50 p-2 rounded-md flex items-center gap-2">
                                    <Info :size="14" />
                                    <span>Limite de {{ servico.regras_limite.limite }} por {{ servico.regras_limite.periodo }}.</span>
                                </div>
                            </div>
                            <button @click="solicitar(servico.id)" class="btn btn-primary w-full mt-6" :disabled="form.processing">
                                <span v-if="form.processing && form.servico_id === servico.id">Processando...</span>
                                <span v-else>Solicitar Serviço</span>
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 text-gray-500">
                        <Grid :size="48" class="mx-auto mb-4 opacity-50" />
                        <p class="text-lg">Nenhum serviço disponível no momento.</p>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="faq" class="py-20" aria-labelledby="faq-title">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-3xl">
                    <h2 id="faq-title" class="section-title">Perguntas Frequentes</h2>
                    <div class="space-y-4">
                        <Disclosure v-for="(faq, index) in faqs" :key="index" as="div" class="bg-white p-6 rounded-lg shadow-sm" v-slot="{ open }">
                            <DisclosureButton class="flex justify-between items-center w-full text-left font-medium text-lg text-gray-900">
                                <span>{{ faq.question }}</span>
                                <ChevronUp :class="open ? '' : 'rotate-180 transform'" class="h-5 w-5 text-primary transition-transform" />
                            </DisclosureButton>
                            <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-out" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                                <DisclosurePanel class="mt-4 text-gray-600">
                                    {{ faq.answer }}
                                </DisclosurePanel>
                            </transition>
                        </Disclosure>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer text-gray-300" role="contentinfo">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="text-center md:text-left">
                        <h4 class="font-bold text-white text-lg mb-2">{{ tenantName }}</h4>
                        <p v-if="endereco" class="footer-contact-item">
                            <MapPin :size="16" class="mr-2 text-secondary" /> {{ endereco }}
                        </p>
                        <p v-if="telefone" class="footer-contact-item">
                            <Phone :size="16" class="mr-2 text-secondary" /> {{ telefone }}
                        </p>
                        <p v-if="emailContato" class="footer-contact-item">
                            <Mail :size="16" class="mr-2 text-secondary" /> {{ emailContato }}
                        </p>
                    </div>
                    <div class="text-center md:text-right">
                        <div class="mb-2">
                            <a :href="page.props.tenant?.privacy_policy_url || '#'" class="footer-link mr-4">Política de Privacidade</a>
                            <a :href="page.props.tenant?.terms_url || '#'" class="footer-link">Termos de Uso</a>
                        </div>
                        <small class="block">© {{ new Date().getFullYear() }} {{ tenantName }}. Todos os direitos reservados.</small>
                        <small class="block opacity-70" v-if="laravelVersion">Desenvolvido com Laravel v{{ laravelVersion }}</small>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Back to Top Button -->
        <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
            <button v-show="showBackToTop" @click="scrollToTop" id="back-to-top" title="Voltar ao topo" aria-label="Voltar ao topo">
                <ArrowUp :size="24" />
            </button>
        </transition>
    </div>
</template>

<style>
/* Estilos Globais e Variáveis de Tema */
body {
    font-family: 'Inter', sans-serif;
}
.bg-hero-pattern {
    background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23ffffff" fill-opacity="0.05" fill-rule="evenodd"%3E%3Ccircle cx="30" cy="30" r="10"/%3E%3C/g%3E%3C/svg%3E');
}
.footer { background-color: #111827; }
.text-primary { color: var(--primary); }
.text-secondary { color: var(--secondary); }
.bg-primary { background-color: var(--primary); }

/* Component Styles */
.nav-link {
    @apply flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors;
}
.nav-link:hover {
    color: var(--primary);
}
.dropdown-item {
    @apply flex items-center w-full px-4 py-2 text-sm text-left text-gray-700;
}
.mobile-nav-link {
    @apply -ml-3 flex items-center rounded-lg px-3 py-2 text-base font-semibold leading-7 text-gray-700 hover:bg-gray-100;
}
.btn {
    @apply inline-flex items-center justify-center font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2;
}
.btn-primary {
    background-color: var(--primary);
    border-color: var(--primary);
    @apply text-white;
}
.btn-primary:hover {
    background-color: var(--btn-primary-hover);
    border-color: var(--btn-primary-hover);
}
.btn-primary:focus-visible {
    --tw-ring-color: var(--primary);
}
.btn-secondary {
    color: var(--primary);
    @apply bg-white hover:bg-gray-100;
}
.btn-secondary:focus-visible {
    --tw-ring-color: var(--primary);
}
.btn-primary-outline {
    background-color: transparent;
    border: 2px solid white;
    @apply text-white hover:bg-white;
}
.btn-primary-outline:hover {
    color: var(--primary);
}
.btn-light-outline {
    background-color: transparent;
    border: 2px solid rgba(255,255,255,0.5);
    @apply text-white hover:bg-white hover:text-gray-800;
}
.section-title {
    @apply text-3xl font-bold text-center mb-12 text-gray-800 relative;
}
.section-title::after {
    content: '';
    background-color: var(--primary);
    @apply block w-16 h-1 mx-auto mt-4 rounded-full;
}
.step-card {
    @apply p-6;
}
.step-icon-wrapper {
    @apply w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4;
}
.step-title {
    @apply text-lg font-semibold text-gray-900 mb-2;
}
.step-description {
    @apply text-gray-600;
}
.service-card {
    @apply bg-white rounded-lg p-6 text-center shadow-sm hover:shadow-xl hover:scale-105 transition-all duration-300 flex flex-col;
}
.service-icon-wrapper {
    background-color: rgba(var(--primary-rgb), 0.1);
    @apply w-16 h-16 rounded-full flex items-center justify-center mx-auto;
}
#back-to-top {
    background-color: var(--primary);
    @apply fixed bottom-5 right-5 w-12 h-12 text-white rounded-full flex items-center justify-center shadow-lg transition-opacity z-30;
}
#back-to-top:hover {
    background-color: var(--btn-primary-hover);
}
.notification {
    @apply flex items-center gap-4 p-4 rounded-lg border;
}
</style>
