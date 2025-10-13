<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    ShieldCheck, Users, Server, FileText, Smartphone, MessageSquare, Building2,
    LoaderCircle, Star, Quote, CheckCircle, XCircle,
    AlertCircle, ThumbsUp, TrendingUp, BarChart4
} from 'lucide-vue-next';
import { useIntersectionObserver } from '@vueuse/core';

import heroBg from '@/assets/hero-background.jpg';

// -----------------------------------------------------------------------------
// ANIMAÇÃO DE SCROLL E HEADER
// -----------------------------------------------------------------------------
const headerHasShadow = ref(false);

const vAnimateOnScroll = {
    mounted: (el) => {
        el.classList.add('animate-on-scroll');
        useIntersectionObserver(el, ([{ isIntersecting }]) => {
            if (isIntersecting) {
                el.classList.add('is-visible');
            }
        });
    },
};

onMounted(() => {
    window.addEventListener('scroll', () => {
        headerHasShadow.value = window.scrollY > 20;
    });
});


// -----------------------------------------------------------------------------
// DADOS DA PÁGINA
// -----------------------------------------------------------------------------
const problems = ref([
    { text: 'Comunicação fragmentada e processos manuais.', icon: AlertCircle },
    { text: 'Falta de dados para tomada de decisão estratégica.', icon: BarChart4 },
    { text: 'Dificuldade em cumprir as exigências da LGPD.', icon: ShieldCheck },
]);

const solutions = ref([
    { text: 'Plataforma centralizada que automatiza e organiza o fluxo de atendimento.', icon: CheckCircle },
    { text: 'Relatórios em tempo real que transformam dados em insights valiosos.', icon: TrendingUp },
    { text: 'Segurança e conformidade como pilares, garantindo tranquilidade jurídica.', icon: ThumbsUp },
]);

const features = ref([
    { name: 'Gestão de Atendimento Unificada', description: 'Cadastre, acompanhe e notifique os cidadãos sobre serviços de forma centralizada e eficiente.', icon: FileText },
    { name: 'Arquitetura Multi-Tenant Segura', description: 'Cada Câmara opera em um subdomínio próprio, com total isolamento de dados, garantindo privacidade e integridade.', icon: Server },
    { name: 'Conformidade Rigorosa com a LGPD', description: 'Garanta a proteção de dados com consentimento explícito, auditoria completa e políticas de privacidade robustas.', icon: ShieldCheck },
    { name: 'Portal Intuitivo para o Cidadão', description: 'Interface amigável para cidadãos solicitarem serviços, consultarem informações e acompanharem suas demandas online, 24/7.', icon: Smartphone },
    { name: 'Controle de Acesso Granular', description: 'Defina permissões específicas para cada tipo de usuário, garantindo que o acesso aos dados seja restrito e auditorável.', icon: Users },
    { name: 'Relatórios Abrangentes e Auditoria', description: 'Gere relatórios detalhados de uso e demandas, além de manter um log completo de todas as ações para total transparência.', icon: MessageSquare },
]);

const testimonials = ref([
    { quote: "A implementação do Câmara Digital transformou nosso atendimento. A eficiência aumentou em 50% e os relatórios nos dão uma visão clara da demanda dos cidadãos.", author: "Ana Pereira", title: "Diretora de Atendimento, Câmara de Porto Feliz", avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29329?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80' },
    { quote: "Finalmente um sistema que entende as necessidades do setor público. É robusto, seguro e, o mais importante, fácil de usar tanto para nossa equipe quanto para os munícipes.", author: "Carlos Martins", title: "Chefe de Gabinete, Câmara de Vista Alegre", avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80' },
    { quote: "O Câmara Digital otimizou nossos processos e a comunicação com a população. A transparência e agilidade no atendimento são notáveis, superando nossas expectativas.", author: "Fernanda Costa", title: "Assessora de Comunicação, Câmara de Pouso Alto", avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80' }
]);

// -----------------------------------------------------------------------------
// LÓGICA DO FORMULÁRIO
// -----------------------------------------------------------------------------
const form = useForm({
    cityCouncilName: '',
    contactPerson: '',
    email: '',
    message: ''
});

const formStatus = ref({ type: '', message: '' });

const submitForm = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            // Define a mensagem de sucesso diretamente no front-end
            formStatus.value = { type: 'success', message: 'Contato enviado com sucesso! Nossa equipe retornará em breve.' };
        },
        onError: () => {
            formStatus.value = { type: 'error', message: 'Por favor, corrija os erros no formulário.' };
        },
        onFinish: () => {
            // Limpa a mensagem de status após 5 segundos
            setTimeout(() => {
                formStatus.value = { type: '', message: '' };
            }, 5000);
        }
    });
};
</script>

<template>
    <Head title="Câmara Digital - Solução para Câmaras Municipais" />

    <div class="bg-slate-50 text-slate-800 antialiased font-sans">
        <header :class="{ 'shadow-md': headerHasShadow }" class="bg-white/90 backdrop-blur-lg sticky top-0 z-50 transition-shadow duration-300">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <a href="/" class="flex items-center space-x-3 group">
                    <Building2 class="h-8 w-8 text-green-600" />
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Câmara <span class="text-green-600">Digital</span></h1>
                </a>
                <nav class="hidden md:flex items-center space-x-2">
                    <a href="#features" class="text-slate-700 hover:text-green-600 px-3 py-2 rounded-md font-medium transition-colors relative group">
                        Funcionalidades
                        <span class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-green-600 group-hover:w-full group-hover:left-0 transition-all duration-300"></span>
                    </a>
                    <a href="#testimonials" class="text-slate-700 hover:text-green-600 px-3 py-2 rounded-md font-medium transition-colors relative group">
                        Depoimentos
                        <span class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-green-600 group-hover:w-full group-hover:left-0 transition-all duration-300"></span>
                    </a>
                    <a href="#contact" class="ml-4 inline-flex items-center bg-green-600 text-white font-semibold shadow-sm hover:shadow-md hover:bg-green-700 transition-colors duration-300 px-5 py-2 rounded-full">
                        Fale Conosco
                    </a>
                </nav>
            </div>
        </header>

        <main>
            <section class="relative bg-slate-800 text-white pt-24 pb-32 md:pt-32 md:pb-40 text-center overflow-hidden">
                <div class="absolute inset-0">
                    <img :src="heroBg" alt="Atendimento ao cidadão em um ambiente moderno" class="w-full h-full object-cover opacity-20">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/80 to-transparent"></div>
                </div>
                <div class="container mx-auto px-6 relative z-10">
                    <h2 v-animate-on-scroll class="text-4xl font-extrabold tracking-tight sm:text-5xl md:text-6xl leading-tight text-shadow">
                        <span class="block">Modernize a Gestão Pública.</span>
                        <span class="block text-green-400 mt-2">Conecte-se com o Cidadão.</span>
                    </h2>
                    <p v-animate-on-scroll class="mt-8 max-w-3xl mx-auto text-xl text-slate-300 leading-relaxed text-shadow-sm" style="animation-delay: 0.2s;">
                        A plataforma líder para gestão de atendimentos no setor público, unindo eficiência, segurança e total conformidade com a LGPD.
                    </p>
                    <div v-animate-on-scroll class="mt-12" style="animation-delay: 0.4s;">
                        <a href="#contact" class="inline-flex items-center bg-green-600 text-white font-bold py-3.5 px-9 rounded-full shadow-lg hover:shadow-xl hover:bg-green-700 transition-colors duration-300">
                            <Star class="h-5 w-5 mr-3" />
                            Solicite uma Demonstração TESTE
                        </a>
                    </div>
                </div>
            </section>

            <section class="py-20 md:py-28 bg-white">
                <div class="container mx-auto px-6">
                     <div v-animate-on-scroll class="text-center mb-16 max-w-3xl mx-auto">
                        <h3 class="text-4xl font-bold tracking-tight text-slate-900">Da Burocracia à Eficiência Digital</h3>
                        <p class="mt-4 text-xl text-slate-600">Entendemos os desafios do setor público e criamos a solução definitiva para modernizar o atendimento ao cidadão.</p>
                    </div>
                    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-start">
                        <div v-animate-on-scroll class="bg-slate-50 p-8 rounded-lg border border-slate-200">
                             <h4 class="text-2xl font-semibold text-slate-700 mb-6 flex items-center">
                                <AlertCircle class="h-7 w-7 mr-3 text-amber-500"/>
                                Desafios Comuns
                            </h4>
                            <ul class="space-y-4">
                                <li v-for="item in problems" :key="item.text" class="flex items-start">
                                    <component :is="item.icon" class="h-6 w-6 text-amber-600 mr-3 mt-1 flex-shrink-0" />
                                    <span class="text-lg text-slate-600">{{ item.text }}</span>
                                </li>
                            </ul>
                        </div>
                        <div v-animate-on-scroll class="bg-slate-50 p-8 rounded-lg border border-slate-200" style="animation-delay: 0.2s;">
                            <h4 class="text-2xl font-semibold text-slate-700 mb-6 flex items-center">
                                <ThumbsUp class="h-7 w-7 mr-3 text-green-600"/>
                                Nossa Solução
                            </h4>
                             <ul class="space-y-4">
                                <li v-for="item in solutions" :key="item.text" class="flex items-start">
                                    <component :is="item.icon" class="h-6 w-6 text-green-600 mr-3 mt-1 flex-shrink-0" />
                                    <span class="text-lg text-slate-600">{{ item.text }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="py-20 md:py-28 bg-slate-50">
                <div class="container mx-auto px-6">
                    <div v-animate-on-scroll class="text-center mb-16 max-w-3xl mx-auto">
                        <h3 class="text-4xl font-bold tracking-tight text-slate-900">Plataforma Completa</h3>
                        <p class="mt-4 text-xl text-slate-600">Funcionalidades pensadas para a realidade do serviço público municipal.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="(feature, index) in features" :key="feature.name" v-animate-on-scroll class="bg-white p-8 rounded-lg shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-slate-100 group" :style="{'animation-delay': `${index * 100}ms`}">
                            <div class="flex items-center justify-center h-14 w-14 rounded-full bg-green-100 text-green-600 mb-6 transition-all duration-300">
                                <component :is="feature.icon" class="h-7 w-7" aria-hidden="true" />
                            </div>
                            <h4 class="text-xl font-semibold text-slate-900 mb-3">{{ feature.name }}</h4>
                            <p class="text-slate-600 text-base leading-relaxed">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="testimonials" class="py-20 md:py-28 bg-white">
                 <div class="container mx-auto px-6">
                    <div v-animate-on-scroll class="text-center mb-16 max-w-3xl mx-auto">
                        <h3 class="text-4xl font-bold tracking-tight text-slate-900">Aprovado por quem usa</h3>
                        <p class="mt-4 text-xl text-slate-600">Veja o que gestores públicos estão dizendo sobre o Câmara Digital.</p>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                        <div v-for="(t, index) in testimonials" :key="t.author" v-animate-on-scroll class="bg-slate-50 p-8 rounded-lg border border-slate-200" :style="{'animation-delay': `${index * 100}ms`}">
                            <Quote class="h-10 w-10 text-green-100 mb-4" />
                            <p class="text-slate-700 text-base my-4 leading-relaxed italic">"{{ t.quote }}"</p>
                            <div class="flex items-center mt-6 pt-6 border-t border-slate-200">
                                <img :src="t.avatar" :alt="`Foto de ${t.author}`" class="h-12 w-12 rounded-full object-cover bg-slate-200" />
                                <div class="ml-4">
                                    <p class="font-semibold text-slate-900">{{ t.author }}</p>
                                    <p class="text-slate-500 text-sm">{{ t.title }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contact" class="py-20 md:py-28 bg-slate-50">
                 <div class="container mx-auto px-6">
                    <div v-animate-on-scroll class="text-center mb-16 max-w-3xl mx-auto">
                        <h3 class="text-4xl font-bold tracking-tight text-slate-900">Vamos conversar?</h3>
                        <p class="mt-4 text-xl text-slate-600">Preencha o formulário e agende uma apresentação sem compromisso.</p>
                    </div>
                    <div v-animate-on-scroll class="max-w-2xl mx-auto bg-white p-8 sm:p-10 rounded-xl shadow-lg border border-slate-200" style="animation-delay: 0.2s;">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div>
                                <label for="city-council-name" class="block text-sm font-medium text-slate-700 mb-1">Nome da Câmara Municipal <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.cityCouncilName" id="city-council-name" :class="{'border-red-500': form.errors.cityCouncilName}" class="form-input" />
                                <p v-if="form.errors.cityCouncilName" class="form-error"><XCircle class="h-4 w-4 mr-1"/>{{ form.errors.cityCouncilName }}</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="contact-person" class="block text-sm font-medium text-slate-700 mb-1">Seu Nome <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.contactPerson" id="contact-person" :class="{'border-red-500': form.errors.contactPerson}" class="form-input" />
                                    <p v-if="form.errors.contactPerson" class="form-error"><XCircle class="h-4 w-4 mr-1"/>{{ form.errors.contactPerson }}</p>
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Seu E-mail <span class="text-red-500">*</span></label>
                                    <input type="email" v-model="form.email" id="email" :class="{'border-red-500': form.errors.email}" class="form-input" />
                                    <p v-if="form.errors.email" class="form-error"><XCircle class="h-4 w-4 mr-1"/>{{ form.errors.email }}</p>
                                </div>
                            </div>
                            <div>
                                <label for="message" class="block text-sm font-medium text-slate-700 mb-1">Mensagem <span class="text-red-500">*</span></label>
                                <textarea v-model="form.message" id="message" rows="5" :class="{'border-red-500': form.errors.message}" class="form-input"></textarea>
                                <p v-if="form.errors.message" class="form-error"><XCircle class="h-4 w-4 mr-1"/>{{ form.errors.message }}</p>
                            </div>
                            <div class="pt-2 text-right">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center py-3 px-8 border border-transparent shadow-sm text-base font-medium rounded-full text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-300 disabled:bg-green-400 disabled:cursor-not-allowed">
                                    <LoaderCircle v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5" />
                                    <span>{{ form.processing ? 'Enviando...' : 'Enviar Mensagem' }}</span>
                                </button>
                            </div>
                             <div v-if="formStatus.message" class="text-center p-4 rounded-md font-medium flex items-center justify-center" :class="{ 'bg-green-100 text-green-800': formStatus.type === 'success', 'bg-red-100 text-red-800': formStatus.type === 'error' }">
                                <CheckCircle v-if="formStatus.type === 'success'" class="h-5 w-5 mr-2 text-green-600" />
                                <XCircle v-if="formStatus.type === 'error'" class="h-5 w-5 mr-2 text-red-600" />
                                <p>{{ formStatus.message }}</p>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-slate-800 text-white">
             <div class="container mx-auto px-6 py-12 text-center">
                <p>&copy; {{ new Date().getFullYear() }} Câmara Digital. Todos os direitos reservados.</p>
                <p class="text-sm mt-2 text-slate-400">Uma solução moderna e transparente para a gestão pública.</p>
                <div class="mt-6 flex justify-center space-x-4">
                    <a href="/login" class="text-sm text-slate-400 hover:text-white transition-colors">Login</a>
                    <span class="text-slate-600">|</span>
                    <a href="/superadmin/dashboard" class="text-sm text-slate-400 hover:text-white transition-colors">Painel Admin</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
body { font-family: 'Inter', sans-serif; }

/* CLASSES DE UTILIDADE PARA FORMULÁRIO */
.form-input {
    @apply block w-full border-slate-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 py-2.5 px-3 transition;
}
.form-error {
    @apply text-sm text-red-600 mt-1 flex items-center;
}

/* ANIMAÇÃO DE SCROLL */
.animate-on-scroll {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
.animate-on-scroll.is-visible {
    opacity: 1;
    transform: translateY(0);
}
</style>
