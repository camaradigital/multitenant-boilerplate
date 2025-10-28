<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import TenantPublicLayout from '@/Layouts/TenantPublicLayout.vue';
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
} from '@headlessui/vue';
import {
    LayoutDashboard, Grid, Send, ArrowUp, CheckCircle2, AlertTriangle, Info, ChevronUp, Server, LogIn, UserPlus, X, LoaderCircle, Search, Star, MessageCircleQuestion,
    Gavel, Printer, FileText, BookUser, Home, Wrench,
    Globe, Building2, Sparkles, Shield, Clock, Users
} from 'lucide-vue-next';

// --- PROPS & PAGE DATA ---
const props = defineProps({
    tiposDeServico: Array,
});
const page = usePage();

// --- DADOS ESTÁTICOS ---
const testimonials = ref([
    { quote: "Consegui resolver minha pendência com a prefeitura sem sair de casa. O sistema é muito fácil de usar e o acompanhamento foi transparente.", author: "Maria S.", role: "Bairro Centro" },
    { quote: "A assistência jurídica que solicitei pelo portal foi essencial. Fui atendido rapidamente por um profissional qualificado. Recomendo!", author: "João P.", role: "Morador local" },
    { quote: "Finalmente um canal digital que funciona. Emiti os documentos que precisava em minutos. Parabéns à equipe da Câmara!", author: "Ana L.", role: "Cidadã" },
]);

const features = ref([
    { icon: Shield, title: 'Seguro e Protegido', description: 'Seus dados protegidos conforme a LGPD' },
    { icon: Clock, title: 'Disponível 24/7', description: 'Acesse serviços a qualquer momento' },
    { icon: Users, title: 'Suporte Dedicado', description: 'Equipe pronta para ajudar você' },
    { icon: Sparkles, title: 'Interface Intuitiva', description: 'Fácil de usar para todos' }
]);

const faqs = [
    { question: 'Como posso solicitar um serviço?', answer: 'Primeiro, você precisa se cadastrar e fazer login. Depois, navegue pelas abas de serviço ou use a busca para encontrar o que precisa e clique em "Solicitar".' },
    { question: 'Onde acompanho minhas solicitações?', answer: 'Após fazer login, acesse a área "Meu Painel". Lá você encontrará um histórico de todas as suas solicitações e o status atual de cada uma.' },
    { question: 'Meus dados estão seguros?', answer: 'Sim. Nossa plataforma segue rigorosamente a Lei Geral de Proteção de Dados (LGPD), garantindo a segurança e a privacidade de todas as suas informações.' },
    { question: 'Posso solicitar serviços pelo celular?', answer: 'Sim! Nossa plataforma é totalmente responsiva e funciona perfeitamente em smartphones e tablets.' },
];

// --- TENANT & AUTH DATA ---
const tenant = computed(() => page.props.tenant || {});
const authUser = computed(() => page.props.auth?.user || null);
const canRegister = computed(() => page.props.canRegister);
const tenantName = computed(() => tenant.value?.name || 'Portal do Cidadão');
const primaryColor = computed(() => tenant.value?.cor_primaria || '#1e3a8a');

// --- LÓGICA DE ESTILO DINÂMICO ---
const darken = (hex, percent) => {
    if (!hex || typeof hex !== 'string') return '#000000';
    let f=parseInt(hex.slice(1),16),t=percent<0?0:255,p=percent<0?percent*-1:percent,R=f>>16,G=f>>8&0x00FF,B=f&0x0000FF;
    return "#"+(0x1000000+(Math.round((t-R)*p)+R)*0x10000+(Math.round((t-G)*p)+G)*0x100+(Math.round((t-B)*p)+B)).toString(16).slice(1);
};
const hexToRgba = (hex, alpha) => {
    if (!hex || typeof hex !== 'string') return `rgba(0,0,0,${alpha})`;
    hex = hex.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16); const g = parseInt(hex.substring(2, 4), 16); const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r},${g},${b},${alpha})`;
};
const primaryRGB = computed(() => {
    const hex = primaryColor.value.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `${r},${g},${b}`;
});

const heroStyle = computed(() => ({
  backgroundImage: `
    linear-gradient(${hexToRgba(primaryColor.value, 0.92)}, ${hexToRgba(darken(primaryColor.value, -0.2), 0.95)}),
    linear-gradient(rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0.15)),
    url('/background_home.jpg')
  `,
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  backgroundAttachment: 'fixed',
}));

const themeStyles = computed(() => `
    :root {
        --primary: ${primaryColor.value};
        --primary-dark: ${darken(primaryColor.value, -0.15)};
        --primary-light: ${hexToRgba(primaryColor.value, 0.08)};
        --primary-focus-ring: ${hexToRgba(primaryColor.value, 0.4)};
        --primary-rgb: ${primaryRGB.value};
    }
`);

// --- LÓGICA DE ÍCONES DE SERVIÇO ---
const serviceIcons = {
    'consulta': Gavel, 'jurídico': Gavel, 'advocacia': Gavel, 'processual': Gavel,
    'impressão': Printer, 'imprimir': Printer,
    'documento': FileText, 'certidão': FileText,
    'currículo': BookUser, 'cadastro': BookUser,
    'obras': Wrench, 'manutenção': Wrench,
    'iptu': Home, 'habitação': Home,
};
function getIconForService(serviceName) {
    if (!serviceName) return Server;
    const nameLower = serviceName.toLowerCase();
    for (const key in serviceIcons) { if (nameLower.includes(key)) return serviceIcons[key]; }
    return Server;
}

// --- LÓGICA DE FILTRO E UI ---
const searchQuery = ref('');
const activeTab = ref('Todos');
const successMessage = ref(page.props.flash?.success);
const errorMessage = ref(page.props.errors?.servico);

const allServices = computed(() => props.tiposDeServico.flatMap(tipo =>
    tipo.servicos.map(servico => ({ ...servico, tipoNome: tipo.nome }))
));

const filteredServices = computed(() => {
    let services = allServices.value;
    if (activeTab.value !== 'Todos') {
        services = services.filter(s => s.tipoNome === activeTab.value);
    }
    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.trim().toLowerCase();
        services = services.filter(s =>
            s.nome.toLowerCase().includes(query) ||
            (s.descricao && s.descricao.toLowerCase().includes(query))
        );
    }
    return services;
});

function solicitar(servicoId) {
    if (!authUser.value) {
        router.visit(route('login'));
        return;
    }
    router.visit(route('portalcidadao.solicitacoes.create', { servico: servicoId }));
}

const showBackToTop = ref(false);
onMounted(() => { 
    window.addEventListener('scroll', () => { 
        showBackToTop.value = window.scrollY > 400; 
    }); 
});
const scrollToTop = () => { window.scrollTo({ top: 0, behavior: 'smooth' }); };
</script>

<template>
    <Head>
        <title>{{ tenantName }} - CAC Digital</title>
        <meta name="description" :content="`Centro de Atendimento ao Cidadão - ${tenantName}`">
        <component :is="'style'">{{ themeStyles }}</component>
    </Head>

    <TenantPublicLayout>
        <!-- Hero Section -->
        <section class="hero-section" :style="heroStyle">
            <div class="hero-overlay"></div>
            <div class="relative container mx-auto px-6 z-10">
                <div class="max-w-4xl mx-auto text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6 animate-fade-in">
                        <Sparkles class="w-4 h-4 text-white" />
                        <span class="text-sm font-medium text-white">Centro de Atendimento ao Cidadão</span>
                    </div>
                    
                    <h1 class="hero-title">{{ tenantName }}</h1>
                    <p class="hero-subtitle">
                        Serviços públicos digitais de forma simples, rápida e segura. 
                        Tudo que você precisa em um só lugar.
                    </p>

                    <div class="search-wrapper">
                        <div class="search-container">
                            <Search class="search-icon" />
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Busque por serviços: impressão, jurídico, documentos..."
                                class="search-input"
                                @keyup.enter="() => $refs.servicesSection.scrollIntoView({ behavior: 'smooth' })"
                            />
                            <button 
                                class="search-button"
                                @click="() => $refs.servicesSection.scrollIntoView({ behavior: 'smooth' })"
                            >
                                Buscar
                            </button>
                        </div>
                        <p class="search-hint">
                            Pressione Enter ou clique em Buscar para encontrar serviços
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Floating Elements -->
            <div class="hero-decoration hero-decoration-1"></div>
            <div class="hero-decoration hero-decoration-2"></div>
            <div class="hero-decoration hero-decoration-3"></div>
        </section>

        <!-- Alert Messages -->
        <div class="container mx-auto px-6 -mt-8 z-20 relative">
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                <div v-if="successMessage" class="alert alert-success">
                    <CheckCircle2 class="alert-icon" :size="24" />
                    <span class="flex-grow font-medium">{{ successMessage }}</span>
                    <button @click="successMessage = null" class="alert-close">
                        <X :size="20" />
                    </button>
                </div>
            </transition>
            
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                <div v-if="errorMessage" class="alert alert-error">
                    <AlertTriangle class="alert-icon" :size="24" />
                    <span class="flex-grow font-medium">{{ errorMessage }}</span>
                    <button @click="errorMessage = null" class="alert-close">
                        <X :size="20" />
                    </button>
                </div>
            </transition>
        </div>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container mx-auto px-6">
                <div class="features-grid">
                    <div 
                        v-for="(feature, index) in features" 
                        :key="index" 
                        class="feature-card"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <div class="feature-icon">
                            <component :is="feature.icon" class="w-6 h-6" />
                        </div>
                        <h3 class="feature-title">{{ feature.title }}</h3>
                        <p class="feature-description">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="section-white">
            <div class="container mx-auto px-6">
                <div class="section-header">
                    <h2 class="section-title">Como Funciona</h2>
                    <p class="section-description">
                        Três passos simples para acessar todos os serviços públicos digitais
                    </p>
                </div>

                <div class="steps-container">
                    <div class="steps-line"></div>
                    
                    <div class="step-item" style="animation-delay: 0ms;">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3 class="step-title">Crie sua Conta</h3>
                            <p class="step-description">
                                Cadastro rápido e seguro com suas informações básicas. 
                                Leva apenas 2 minutos.
                            </p>
                        </div>
                    </div>

                    <div class="step-item" style="animation-delay: 200ms;">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3 class="step-title">Solicite Serviços</h3>
                            <p class="step-description">
                                Navegue pelo catálogo e faça solicitações online com poucos cliques.
                            </p>
                        </div>
                    </div>

                    <div class="step-item" style="animation-delay: 400ms;">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3 class="step-title">Acompanhe em Tempo Real</h3>
                            <p class="step-description">
                                Monitore o status de suas solicitações pelo painel pessoal.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" ref="servicesSection" class="section-gray">
            <div class="container mx-auto px-6">
                <div class="section-header">
                    <h2 class="section-title">Catálogo de Serviços</h2>
                    <p class="section-description">
                        Explore todos os serviços disponíveis e faça suas solicitações de forma digital
                    </p>
                </div>

                <div class="tabs-container">
                    <button 
                        @click="activeTab = 'Todos'" 
                        :class="['tab-button', activeTab === 'Todos' ? 'tab-active' : 'tab-inactive']"
                    >
                        Todos os Serviços
                    </button>
                    <button 
                        v-for="tipo in tiposDeServico" 
                        :key="tipo.id" 
                        @click="activeTab = tipo.nome" 
                        :class="['tab-button', activeTab === tipo.nome ? 'tab-active' : 'tab-inactive']"
                    >
                        {{ tipo.nome }}
                    </button>
                </div>

                <div class="services-list">
                    <transition-group name="service-list">
                        <div 
                            v-for="servico in filteredServices" 
                            :key="servico.id" 
                            class="service-card group"
                        >
                            <div class="service-icon-wrapper">
                                <component :is="getIconForService(servico.nome)" class="service-icon" />
                            </div>
                            
                            <div class="service-info">
                                <h3 class="service-title">{{ servico.nome }}</h3>
                                <p class="service-description">
                                    {{ servico.descricao || 'Descrição não disponível.' }}
                                </p>
                                
                                <div class="service-badge-wrapper">
                                    <div v-if="servico.permite_solicitacao_online" class="service-badge service-badge-online">
                                        <Globe :size="14" />
                                        <span>Disponível Online</span>
                                    </div>
                                    <div v-else class="service-badge service-badge-presential">
                                        <Building2 :size="14" />
                                        <span>Apenas Presencial</span>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="servico.permite_solicitacao_online && solicitar(servico.id)"
                                :class="['service-button', !servico.permite_solicitacao_online && 'service-button-disabled']"
                                :disabled="!servico.permite_solicitacao_online"
                                :title="!servico.permite_solicitacao_online ? 'Este serviço só pode ser solicitado presencialmente' : 'Iniciar solicitação'"
                            >
                                <span v-if="servico.permite_solicitacao_online">Solicitar</span>
                                <span v-else>Presencial</span>
                                <ArrowUp v-if="servico.permite_solicitacao_online" class="service-button-icon" />
                            </button>
                        </div>
                    </transition-group>

                    <div v-if="filteredServices.length === 0" class="empty-state">
                        <MessageCircleQuestion class="empty-state-icon" />
                        <p class="empty-state-text">
                            Nenhum serviço encontrado com os filtros atuais
                        </p>
                        <button @click="searchQuery = ''; activeTab = 'Todos'" class="empty-state-button">
                            Limpar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="section-white">
            <div class="container mx-auto px-6">
                <div class="section-header">
                    <h2 class="section-title">O Que Dizem os Cidadãos</h2>
                    <p class="section-description">
                        Experiências reais de quem já utilizou nossa plataforma
                    </p>
                </div>

                <div class="testimonials-grid">
                    <div 
                        v-for="(testimonial, index) in testimonials" 
                        :key="index" 
                        class="testimonial-card"
                        :style="{ animationDelay: `${index * 150}ms` }"
                    >
                        <div class="testimonial-stars">
                            <Star v-for="i in 5" :key="i" class="star-icon" />
                        </div>
                        <p class="testimonial-quote">"{{ testimonial.quote }}"</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                {{ testimonial.author.charAt(0) }}
                            </div>
                            <div>
                                <p class="author-name">{{ testimonial.author }}</p>
                                <p class="author-role">{{ testimonial.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="section-gray">
            <div class="container mx-auto px-6">
                <div class="section-header">
                    <h2 class="section-title">Perguntas Frequentes</h2>
                    <p class="section-description">
                        Respostas rápidas para as dúvidas mais comuns
                    </p>
                </div>

                <div class="faq-container">
                    <Disclosure v-for="(faq, index) in faqs" :key="index" as="div" v-slot="{ open }" class="faq-item">
                        <DisclosureButton class="faq-button">
                            <span class="faq-question">{{ faq.question }}</span>
                            <ChevronUp :class="['faq-chevron', open && 'faq-chevron-open']" />
                        </DisclosureButton>
                        
                        <transition 
                            enter-active-class="transition-all duration-300 ease-out" 
                            enter-from-class="max-h-0 opacity-0" 
                            enter-to-class="max-h-96 opacity-100" 
                            leave-active-class="transition-all duration-200 ease-in" 
                            leave-from-class="max-h-96 opacity-100" 
                            leave-to-class="max-h-0 opacity-0"
                        >
                            <DisclosurePanel class="faq-answer">
                                {{ faq.answer }}
                            </DisclosurePanel>
                        </transition>
                    </Disclosure>
                </div>
            </div>
        </section>
    </TenantPublicLayout>
</template>

<style scoped>
/* Hero Section */
.hero-section {
    @apply relative text-white overflow-hidden;
    padding: 10rem 0 8rem;
    min-height: 85vh;
    display: flex;
    align-items: center;
}

.hero-overlay {
    @apply absolute inset-0 bg-gradient-to-b from-black/5 via-transparent to-black/10;
}

.hero-title {
    @apply text-5xl md:text-6xl lg:text-7xl font-black mb-6 leading-tight;
    animation: fadeInUp 0.8s ease-out;
    text-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.hero-subtitle {
    @apply text-lg md:text-xl lg:text-2xl mb-12 opacity-95 leading-relaxed max-w-3xl mx-auto;
    animation: fadeInUp 0.8s ease-out 0.2s backwards;
    text-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.hero-decoration {
    @apply absolute rounded-full blur-3xl opacity-20;
    animation: float 20s infinite ease-in-out;
}

.hero-decoration-1 {
    @apply w-96 h-96 bg-white;
    top: -10%;
    left: -5%;
}

.hero-decoration-2 {
    @apply w-80 h-80 bg-white;
    bottom: -10%;
    right: -5%;
    animation-delay: 7s;
}

.hero-decoration-3 {
    @apply w-64 h-64 bg-white;
    top: 40%;
    right: 10%;
    animation-delay: 14s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -30px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
}

.search-wrapper {
    @apply max-w-3xl mx-auto;
    animation: fadeInUp 0.8s ease-out 0.4s backwards;
}

.search-container {
    @apply relative flex items-center gap-2 bg-white rounded-2xl shadow-2xl p-2 transition-all duration-300;
}

.search-container:focus-within {
    @apply scale-[1.02];
}

.search-icon {
    @apply absolute left-6 text-gray-400;
    width: 22px;
    height: 22px;
}

.search-input {
    @apply flex-1 py-4 pl-14 pr-4 text-lg text-gray-800 bg-transparent border-none outline-none;
    font-weight: 500;
}

.search-input::placeholder {
    @apply text-gray-400;
}

.search-button {
    @apply px-8 py-3 font-bold text-white rounded-xl transition-all duration-300;
    background: var(--primary);
}

.search-button:hover {
    @apply scale-105;
    background: var(--primary-dark);
}

.search-hint {
    @apply text-center mt-4 text-sm text-white/80;
}

.features-section {
    @apply py-16 bg-white;
    margin-top: -4rem;
    position: relative;
    z-index: 10;
}

.features-grid {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto;
}

.feature-card {
    @apply bg-gradient-to-br from-white to-gray-50 p-6 rounded-2xl border border-gray-100 text-center transition-all duration-300;
    animation: fadeInUp 0.6s ease-out backwards;
}

.feature-card:hover {
    @apply -translate-y-2 shadow-xl border-[rgba(var(--primary-rgb),0.2)];
}

.feature-icon {
    @apply w-14 h-14 mx-auto mb-4 rounded-xl flex items-center justify-center;
    background: var(--primary-light);
    color: var(--primary);
}

.feature-title {
    @apply text-lg font-bold text-gray-800 mb-2;
}

.feature-description {
    @apply text-sm text-gray-600;
}

.section-white {
    @apply py-20 md:py-28 bg-white;
}

.section-gray {
    @apply py-20 md:py-28 bg-gradient-to-b from-gray-50 to-white;
}

.section-header {
    @apply text-center max-w-3xl mx-auto mb-16;
}

.section-title {
    @apply text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 mb-4;
}

.section-description {
    @apply text-lg text-gray-600 leading-relaxed;
}

.steps-container {
    @apply relative max-w-3xl mx-auto space-y-12;
}

.steps-line {
    @apply absolute left-8 top-12 bottom-12 w-0.5 bg-gradient-to-b from-[var(--primary)] via-[var(--primary)] to-transparent hidden md:block;
}

.step-item {
    @apply relative flex items-start gap-6 md:pl-20;
    animation: fadeInRight 0.7s ease-out backwards;
}

.step-number {
    @apply flex-shrink-0 w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white z-10 shadow-xl;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}

.step-content {
    @apply flex-1 pt-2;
}

.step-title {
    @apply text-2xl font-bold text-gray-900 mb-3;
}

.step-description {
    @apply text-gray-600 leading-relaxed;
}

.tabs-container {
    @apply flex justify-center flex-wrap gap-3 mb-12;
}

.tab-button {
    @apply px-6 py-3 font-semibold text-sm rounded-xl transition-all duration-300;
}

.tab-active {
    @apply text-white shadow-lg scale-105;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}

.tab-inactive {
    @apply bg-white text-gray-700 border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 hover:scale-105;
}

.services-list {
    @apply max-w-5xl mx-auto space-y-4;
}

.service-card {
    @apply bg-white p-6 rounded-2xl border-2 border-gray-100 flex items-center gap-6 transition-all duration-300;
}

.service-card:hover {
    @apply -translate-y-1 shadow-2xl;
    border-color: rgba(var(--primary-rgb), 0.3);
}

.service-icon-wrapper {
    @apply flex-shrink-0 w-16 h-16 rounded-2xl flex items-center justify-center transition-all duration-300;
    background: var(--primary-light);
}

.service-card:hover .service-icon-wrapper {
    @apply scale-110 rotate-6;
}

.service-icon {
    @apply w-8 h-8;
    color: var(--primary);
}

.service-info {
    @apply flex-1 min-w-0;
}

.service-title {
    @apply text-xl font-bold text-gray-900 mb-2 transition-colors duration-300;
}

.service-card:hover .service-title {
    color: var(--primary);
}

.service-description {
    @apply text-sm text-gray-600 mb-3 line-clamp-2;
}

.service-badge-wrapper {
    @apply flex items-center gap-2;
}

.service-badge {
    @apply inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg;
}

.service-badge-online {
    @apply bg-emerald-50 text-emerald-700 border border-emerald-200;
}

.service-badge-presential {
    @apply bg-slate-50 text-slate-700 border border-slate-200;
}

.service-button {
    @apply flex-shrink-0 px-6 py-3 font-bold text-sm rounded-xl transition-all duration-300 flex items-center gap-2;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.service-button:hover:not(.service-button-disabled) {
    @apply scale-105 -translate-y-0.5;
}

.service-button-disabled {
    @apply bg-gray-200 text-gray-500 cursor-not-allowed;
    background: linear-gradient(135deg, #e5e7eb, #d1d5db);
}

.service-button-icon {
    @apply w-4 h-4 transition-transform duration-300;
    transform: rotate(45deg);
}

.service-button:hover .service-button-icon {
    transform: rotate(45deg) translate(2px, -2px);
}

.empty-state {
    @apply text-center py-20;
}

.empty-state-icon {
    @apply w-20 h-20 mx-auto mb-6 text-gray-300;
}

.empty-state-text {
    @apply text-xl text-gray-500 mb-6;
}

.empty-state-button {
    @apply px-6 py-3 font-bold text-white rounded-xl transition-all duration-300;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}

.empty-state-button:hover {
    @apply scale-105;
}

.testimonials-grid {
    @apply grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto;
}

.testimonial-card {
    @apply bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl border border-gray-100 transition-all duration-300;
    animation: fadeInUp 0.6s ease-out backwards;
}

.testimonial-card:hover {
    @apply -translate-y-2 shadow-2xl;
    border-color: rgba(var(--primary-rgb), 0.2);
}

.testimonial-stars {
    @apply flex gap-1 mb-6;
}

.star-icon {
    @apply w-5 h-5 text-amber-400 fill-current;
}

.testimonial-quote {
    @apply text-gray-700 italic leading-relaxed mb-6 text-base;
}

.testimonial-author {
    @apply flex items-center gap-4 pt-4 border-t border-gray-100;
}

.author-avatar {
    @apply w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}

.author-name {
    @apply font-bold text-gray-900;
}

.author-role {
    @apply text-sm text-gray-500;
}

.faq-container {
    @apply max-w-4xl mx-auto bg-white rounded-2xl border border-gray-100 overflow-hidden;
}

.faq-item {
    @apply border-b border-gray-100 last:border-b-0;
}

.faq-button {
    @apply flex justify-between items-center w-full p-6 text-left transition-all duration-300 hover:bg-gray-50;
}

.faq-question {
    @apply text-lg font-bold text-gray-900 pr-4;
}

.faq-chevron {
    @apply w-5 h-5 text-gray-400 transition-all duration-300 flex-shrink-0;
}

.faq-chevron-open {
    @apply rotate-180;
    color: var(--primary);
}

.faq-answer {
    @apply px-6 pb-6 text-gray-600 leading-relaxed overflow-hidden;
}

.alert {
    @apply flex items-center gap-4 p-5 rounded-2xl border backdrop-blur-sm shadow-lg mb-4;
}

.alert-success {
    @apply bg-emerald-50/90 border-emerald-200 text-emerald-900;
}

.alert-error {
    @apply bg-red-50/90 border-red-200 text-red-900;
}

.alert-icon {
    @apply flex-shrink-0;
}

.alert-success .alert-icon {
    @apply text-emerald-600;
}

.alert-error .alert-icon {
    @apply text-red-600;
}

.alert-close {
    @apply p-1.5 rounded-lg transition-colors duration-300;
}

.alert-success .alert-close {
    @apply hover:bg-emerald-100;
}

.alert-error .alert-close {
    @apply hover:bg-red-100;
}

.back-to-top {
    @apply fixed bottom-8 right-8 w-14 h-14 text-white rounded-2xl flex items-center justify-center shadow-2xl transition-all duration-300 z-50;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}

.back-to-top:hover {
    @apply scale-110 -translate-y-1;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(-40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fadeIn 0.6s ease-out;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.service-list-enter-active,
.service-list-leave-active {
    transition: all 0.4s ease;
}

.service-list-enter-from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
}

.service-list-leave-to {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
}

.service-list-move {
    transition: transform 0.4s ease;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 8rem 0 6rem;
        min-height: 70vh;
    }

    .hero-title {
        @apply text-4xl;
    }

    .hero-subtitle {
        @apply text-lg;
    }

    .search-input {
        @apply text-base py-3 pl-12;
    }

    .search-button {
        @apply px-6 py-2.5 text-sm;
    }

    .service-card {
        @apply flex-col items-start gap-4;
    }

    .service-button {
        @apply w-full justify-center;
    }

    .step-item {
        @apply pl-0;
    }

    .steps-line {
        @apply hidden;
    }
}
</style>
