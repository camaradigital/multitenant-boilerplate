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
    Globe, Building2,
} from 'lucide-vue-next';

// --- PROPS & PAGE DATA ---
const props = defineProps({
    tiposDeServico: Array,
});
const page = usePage();

// --- DADOS ESTÁTICOS ---
const testimonials = ref([
    { quote: "Consegui resolver minha pendência com a prefeitura sem sair de casa. O sistema é muito fácil de usar e o acompanhamento foi transparente.", author: "Maria S., Bairro Centro" },
    { quote: "A assistência jurídica que solicitei pelo portal foi essencial. Fui atendido rapidamente por um profissional qualificado. Recomendo!", author: "João P., Morador local" },
    { quote: "Finalmente um canal digital que funciona. Emiti os documentos que precisava em minutos. Parabéns à equipe da Câmara!", author: "Ana L., Cidadã" },
]);

const faqs = [
    { question: 'Como posso solicitar um serviço?', answer: 'Primeiro, você precisa se cadastrar e fazer login. Depois, navegue pelas abas de serviço ou use a busca para encontrar o que precisa e clique em "Solicitar".' },
    { question: 'Onde acompanho minhas solicitações?', answer: 'Após fazer login, acesse a área "Meu Painel". Lá você encontrará um histórico de todas as suas solicitações e o status atual de cada uma.' },
    { question: 'Meus dados estão seguros?', answer: 'Sim. Nossa plataforma segue rigorosamente a Lei Geral de Proteção de Dados (LGPD), garantindo a segurança e a privacidade de todas as suas informações.' },
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

// --- ALTERAÇÃO PRINCIPAL AQUI ---
// Substituímos o gradiente pela imagem de fundo com uma sobreposição escura.
const heroStyle = computed(() => ({
  backgroundImage: `
    linear-gradient(${hexToRgba(primaryColor.value, 0.9)}), /* Cor principal com 90% de opacidade */
    linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.1)), /* Camada para opacidade da imagem */
    url('/background_home.jpg')
  `,
  backgroundSize: 'cover',
  backgroundPosition: 'center',
}));

const themeStyles = computed(() => `
    :root {
        --primary: ${primaryColor.value};
        --primary-dark: ${darken(primaryColor.value, -0.1)};
        --primary-light: ${hexToRgba(primaryColor.value, 0.1)};
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

// FUNÇÃO DE SOLICITAÇÃO CORRIGIDA
function solicitar(servicoId) {
    if (!authUser.value) {
        router.visit(route('login'));
        return;
    }
    // CORREÇÃO: Usando o nome da rota definido no seu arquivo de rotas
    router.visit(route('portal.solicitacoes.create', { servico: servicoId }));
}

const showBackToTop = ref(false);
onMounted(() => { window.addEventListener('scroll', () => { showBackToTop.value = window.scrollY > 400; }); });
const scrollToTop = () => { window.scrollTo({ top: 0, behavior: 'smooth' }); };
</script>

<template>
    <Head>
        <title>{{ tenantName }} - CAC Digital</title>
        <meta name="description" :content="`Centro de Atendimento ao Cidadão - ${tenantName}`">
        <component :is="'style'">{{ themeStyles }}</component>
    </Head>

    <TenantPublicLayout>
        <section class="relative text-white text-center overflow-hidden pt-32 pb-24 md:pt-40 md:pb-32" :style="heroStyle">
             <div class="relative container mx-auto px-6 z-10">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4 animate-fade-in-down">{{ tenantName }}</h1>
                <p class="text-lg md:text-xl max-w-2xl mx-auto mb-8 opacity-90 text-shadow animate-fade-in-up">Qual serviço você procura hoje?</p>

                <div class="max-w-2xl mx-auto animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="relative">
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Ex: Impressão de Documento, Consulta Jurídica..."
                            class="w-full py-4 pl-12 pr-6 text-lg text-gray-800 rounded-full border-2 border-transparent focus:ring-4 focus:ring-white/50 focus:border-white/80 transition-shadow duration-300 shadow-lg"
                            @keyup.enter="() => $refs.servicesSection.scrollIntoView({ behavior: 'smooth' })"
                        />
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-6 w-6 text-gray-400"/>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mx-auto px-6 -mt-8 z-20 relative">
             <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                <div v-if="successMessage" class="flex items-center gap-4 p-4 rounded-lg border bg-green-50/80 backdrop-blur-sm border-green-200 text-green-800 shadow-lg" role="alert">
                    <CheckCircle2 class="text-green-500 flex-shrink-0" :size="24" />
                    <span class="flex-grow font-medium">{{ successMessage }}</span>
                    <button @click="successMessage = null" class="p-1 rounded-full hover:bg-green-100 transition-colors"><X :size="20" class="text-green-600"/></button>
                </div>
            </transition>
             <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                <div v-if="errorMessage" class="flex items-center gap-4 p-4 mt-4 rounded-lg border bg-red-50/80 backdrop-blur-sm border-red-200 text-red-800 shadow-lg" role="alert">
                    <AlertTriangle class="text-red-500 flex-shrink-0" :size="24" />
                    <span class="flex-grow font-medium">{{ errorMessage }}</span>
                    <button @click="errorMessage = null" class="p-1 rounded-full hover:bg-red-100 transition-colors"><X :size="20" class="text-red-600"/></button>
                </div>
            </transition>
        </div>

        <section id="how-it-works" class="py-20 md:py-28 bg-white" aria-labelledby="how-it-works-title">
             <div class="container mx-auto px-6">
                 <div class="text-center max-w-2xl mx-auto">
                    <h2 class="section-title">Tudo em 3 Passos Simples</h2>
                    <p class="mb-16 text-lg text-gray-600">Projetamos uma experiência direta para que você resolva suas necessidades sem complicação.</p>
                </div>
                <div class="relative max-w-2xl mx-auto">
                    <div class="absolute left-8 top-8 bottom-8 w-0.5 bg-slate-200 hidden md:block"></div>
                    <div class="space-y-12">
                        <div class="step-item">
                             <div class="step-icon">1</div>
                             <div class="step-content">
                                <h3>Cadastre-se na Plataforma</h3>
                                <p>Crie sua conta segura em poucos minutos fornecendo suas informações básicas.</p>
                             </div>
                        </div>
                        <div class="step-item" style="animation-delay: 200ms;">
                             <div class="step-icon">2</div>
                             <div class="step-content">
                                <h3>Escolha e Solicite o Serviço</h3>
                                <p>Navegue em nosso catálogo digital e faça sua solicitação com apenas alguns cliques.</p>
                             </div>
                        </div>
                        <div class="step-item" style="animation-delay: 400ms;">
                             <div class="step-icon">3</div>
                             <div class="step-content">
                                <h3>Acompanhe em Tempo Real</h3>
                                <p>Acesse seu painel para ver o andamento de suas solicitações a qualquer momento.</p>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="services" ref="servicesSection" class="py-20 md:py-28 bg-slate-50" aria-labelledby="services-title">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 id="services-title" class="section-title">Navegador de Serviços</h2>
                    <p class="mb-12 text-lg text-gray-600">Filtre por categoria ou use a busca para encontrar o serviço que você precisa de forma rápida e fácil.</p>
                </div>

                <div class="flex justify-center flex-wrap gap-2 mb-10">
                    <button @click="activeTab = 'Todos'" :class="[activeTab === 'Todos' ? 'tab-active' : 'tab-inactive']">Todos</button>
                    <button v-for="tipo in tiposDeServico" :key="tipo.id" @click="activeTab = tipo.nome" :class="[activeTab === tipo.nome ? 'tab-active' : 'tab-inactive']">
                        {{ tipo.nome }}
                    </button>
                </div>

                <div class="space-y-4 max-w-4xl mx-auto">
                    <transition-group name="list">
                        <div v-for="servico in filteredServices" :key="servico.id" class="group bg-white p-4 sm:p-6 rounded-lg border border-slate-200 flex items-center gap-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:border-[rgba(var(--primary-rgb),0.5)]">
                            <div class="service-card-icon-wrapper">
                                <component :is="getIconForService(servico.nome)" class="h-7 w-7 text-[var(--primary)]" />
                            </div>
                            <div class="flex-grow">
                                <h3 class="service-card-title">{{ servico.nome }}</h3>
                                <p class="service-card-description">{{ servico.descricao || 'Descrição não disponível.' }}</p>
                                <div class="mt-3">
                                    <div v-if="servico.permite_solicitacao_online" class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-100 px-2 py-1 rounded-full">
                                        <Globe :size="14" />
                                        Online e Presencial
                                    </div>
                                    <div v-else class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded-full">
                                        <Building2 :size="14" />
                                        Somente Presencial
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="servico.permite_solicitacao_online && solicitar(servico.id)"
                                class="service-card-button"
                                :disabled="!servico.permite_solicitacao_online"
                                :title="!servico.permite_solicitacao_online ? 'Este serviço só pode ser solicitado presencialmente' : 'Iniciar solicitação'">
                                <span v-if="servico.permite_solicitacao_online">Solicitar</span>
                                <span v-else>Presencial</span>
                            </button>
                        </div>
                    </transition-group>
                    <p v-if="filteredServices.length === 0" class="text-center py-12 text-gray-500">
                        Nenhum serviço encontrado com os filtros atuais.
                    </p>
                </div>
            </div>
        </section>

        <section class="py-20 md:py-28 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="section-title">A Voz do Cidadão</h2>
                    <p class="mb-16 text-lg text-gray-600">Veja o que as pessoas estão dizendo sobre nossa plataforma de atendimento digital.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="(testimonial, index) in testimonials" :key="index" class="p-8 bg-slate-50 border border-slate-100 rounded-lg animate-fade-in-up" :style="{'animation-delay': `${index * 150}ms`}">
                        <div class="flex items-center mb-4">
                            <Star v-for="i in 5" :key="i" class="h-5 w-5 text-amber-400 fill-current" />
                        </div>
                        <p class="text-gray-600 italic mb-6">"{{ testimonial.quote }}"</p>
                        <p class="font-bold text-gray-800">{{ testimonial.author }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="py-20 md:py-28 bg-slate-50" aria-labelledby="faq-title">
            <div class="container mx-auto px-6 max-w-4xl">
                 <div class="text-center max-w-2xl mx-auto">
                    <h2 id="faq-title" class="section-title">Dúvidas Frequentes</h2>
                     <p class="mb-16 text-lg text-gray-600">Respostas rápidas para as perguntas mais comuns sobre nossa plataforma.</p>
                </div>
                <div class="space-y-4 bg-white p-4 sm:p-8 rounded-xl shadow-sm border border-slate-200">
                    <Disclosure v-for="faq in faqs" :key="faq.question" as="div" v-slot="{ open }">
                        <DisclosureButton class="faq-button">
                            <span>{{ faq.question }}</span>
                            <ChevronUp :class="open ? 'rotate-180' : ''" class="faq-chevron" />
                        </DisclosureButton>
                        <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-96 opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="max-h-96 opacity-100" leave-to-class="max-h-0 opacity-0">
                            <DisclosurePanel class="px-5 pb-5 text-gray-600 leading-relaxed overflow-hidden">
                                {{ faq.answer }}
                            </DisclosurePanel>
                        </transition>
                    </Disclosure>
                </div>
            </div>
        </section>

    </TenantPublicLayout>

    <button v-show="showBackToTop" @click="scrollToTop" class="btn-back-to-top" aria-label="Voltar ao topo">
        <ArrowUp :size="24" />
    </button>
</template>

<style>
body {
    font-family: 'Inter', sans-serif;
    @apply bg-slate-50 antialiased text-gray-700;
}
/* REMOVI O .bg-grid-pattern pois não está mais sendo usado, mas você pode manter se quiser */
.text-shadow-lg { text-shadow: 0 2px 5px rgba(0,0,0,0.3); } /* Aumentei um pouco a sombra para melhor contraste */

.section-title { @apply text-3xl md:text-4xl font-extrabold text-slate-800 relative pb-4; }
.section-title::after { content: ''; @apply block w-20 h-1 bg-[var(--primary)] mx-auto mt-4 rounded-full; }

/* Component Styles */
.step-item { @apply relative flex items-start md:pl-20 animate-fade-in-right; }
.step-icon { @apply flex-shrink-0 w-16 h-16 rounded-full bg-white border-2 border-slate-200 flex items-center justify-center z-10 text-2xl font-bold text-[var(--primary)]; }
.step-content { @apply ml-6; }
.step-content h3 { @apply text-xl font-bold text-slate-900 mb-2; }
.step-content p { @apply text-slate-600; }

.tab-active { @apply bg-[var(--primary)] text-white font-semibold px-4 py-2 rounded-full text-sm transition-all shadow; }
.tab-inactive { @apply bg-white text-slate-600 font-semibold px-4 py-2 rounded-full text-sm transition-all border border-slate-200 hover:bg-slate-100; }

.service-card-icon-wrapper { @apply flex-shrink-0 w-14 h-14 rounded-lg flex items-center justify-center bg-[var(--primary-light)] transition-all duration-300 group-hover:scale-110 group-hover:rotate-6; }
.service-card-title { @apply font-bold text-slate-800 transition-colors duration-300 group-hover:text-[var(--primary)]; }
.service-card-description { @apply text-sm text-slate-500; }
.service-card-button { @apply ml-auto flex-shrink-0 h-10 w-24 flex items-center justify-center font-bold text-sm rounded-md shadow-sm transition-all duration-300 transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary)] bg-slate-100 text-slate-700 hover:bg-slate-200 group-hover:bg-[var(--primary)] group-hover:text-white disabled:bg-slate-200 disabled:text-slate-500 disabled:cursor-not-allowed disabled:transform-none; }

.faq-button { @apply flex justify-between items-center w-full p-5 text-left font-semibold text-lg text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-2 rounded-lg; }
.faq-chevron { @apply h-5 w-5 text-slate-500 transform transition-transform duration-300; }

.btn-back-to-top { @apply fixed bottom-6 right-6 w-12 h-12 text-white rounded-full flex items-center justify-center shadow-lg transition-all duration-300 z-50 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary)] bg-[var(--primary)] hover:bg-[var(--primary-dark)]; }

/* Animations */
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInRight { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
.animate-fade-in-up { animation: fadeInUp 0.7s ease-out forwards; }
.animate-fade-in-down { animation: fadeInDown 0.7s ease-out forwards; }
.animate-fade-in-right { animation: fadeInRight 0.7s ease-out forwards; }

.list-enter-active, .list-leave-active { transition: all 0.5s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(30px); }
</style>
