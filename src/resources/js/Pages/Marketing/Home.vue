<script setup>
import { ref, defineComponent, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
  ShieldCheck, Users, Server, FileText, Smartphone, MessageSquare, Building2,
  LoaderCircle, Star, Quote, CheckCircle, XCircle // Adicionado CheckCircle e XCircle para feedback do formulário
} from 'lucide-vue-next';
import { useIntersectionObserver } from '@vueuse/core';

// Importe a imagem da pasta de assets
import heroIllustration from '@/assets/hero-ilustracao.png'; // Garanta que esta imagem seja de alta qualidade e otimizada

// -----------------------------------------------------------------------------
// DIRETIVA DE ANIMAÇÃO CUSTOMIZADA
// -----------------------------------------------------------------------------
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

// -----------------------------------------------------------------------------
// DADOS DA PÁGINA
// -----------------------------------------------------------------------------
const features = [
    {
        name: 'Gestão de Atendimento Unificada',
        description: 'Cadastre, acompanhe e notifique os cidadãos sobre serviços de forma centralizada e eficiente, simplificando o fluxo de trabalho.',
        icon: FileText
    },
    {
        name: 'Arquitetura Multi-Tenant Segura',
        description: 'Cada Câmara Municipal opera em um subdomínio próprio, com total isolamento de dados e configurações, garantindo privacidade e integridade.',
        icon: Server
    },
    {
        name: 'Conformidade Rigorosa com a LGPD',
        description: 'Garanta a proteção de dados com consentimento explícito, auditoria completa e políticas de privacidade robustas, evitando multas e fortalecendo a confiança.',
        icon: ShieldCheck
    },
    {
        name: 'Portal Intuitivo para o Cidadão',
        description: 'Interface amigável e acessível para cidadãos solicitarem serviços, consultarem informações e acompanharem o status de suas demandas online, 24/7.',
        icon: Smartphone
    },
    {
        name: 'Controle de Acesso Granular',
        description: 'Defina permissões específicas para cada tipo de usuário e função, garantindo que o acesso aos dados seja restrito ao necessário e auditorável.',
        icon: Users
    },
    {
        name: 'Relatórios Abrangentes e Auditoria',
        description: 'Gere relatórios detalhados de uso, desempenho e demandas, além de manter um log completo de todas as ações importantes realizadas no sistema para transparência.',
        icon: MessageSquare // Poderia ser um icon de ChartBar ou similar, mas MessageSquare ainda funciona para 'log de comunicação'
    },
];

const testimonials = [
  {
    quote: "A implementação do CAC System transformou nosso atendimento. A eficiência aumentou em 50% e os relatórios nos dão uma visão clara da demanda dos cidadãos, permitindo tomadas de decisão mais assertivas.",
    author: "Ana Pereira",
    title: "Diretora de Atendimento, Câmara de Porto Feliz",
    avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29329?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80' // Imagem mais profissional
  },
  {
    quote: "Finalmente um sistema que entende as necessidades do setor público. É robusto, seguro e, o mais importante, fácil de usar tanto para nossa equipe quanto para os munícipes. Um salto de qualidade no serviço.",
    author: "Carlos Martins",
    title: "Chefe de Gabinete, Câmara de Vista Alegre",
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80' // Imagem mais profissional
  },
  // Adicionar mais depoimentos se houver
  {
    quote: "O CAC System otimizou nossos processos internos e a comunicação com a população. A transparência e agilidade no atendimento são notáveis, superando nossas expectativas iniciais.",
    author: "Fernanda Costa",
    title: "Assessora de Comunicação, Câmara de Pouso Alto",
    avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80'
  }
];


// -----------------------------------------------------------------------------
// LÓGICA DO FORMULÁRIO
// -----------------------------------------------------------------------------
const form = ref({ cityCouncilName: '', contactPerson: '', email: '', message: '' });
const errors = ref({});
const isSubmitting = ref(false);
const formStatus = ref({ type: '', message: '' });

const validateForm = () => {
  errors.value = {};
  if (!form.value.contactPerson) errors.value.contactPerson = 'Seu nome é obrigatório.';
  if (!form.value.cityCouncilName) errors.value.cityCouncilName = 'O nome da câmara é obrigatório.';
  if (!form.value.email) {
    errors.value.email = 'Seu e-mail é obrigatório.';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Por favor, insira um e-mail válido.';
  }
  if (!form.value.message) errors.value.message = 'A mensagem não pode estar em branco.';
  return Object.keys(errors.value).length === 0;
};

const submitForm = async () => {
  if (!validateForm() || isSubmitting.value) return;

  isSubmitting.value = true;
  formStatus.value = { type: '', message: '' };
  // Simula um atraso na requisição de rede
  await new Promise(resolve => setTimeout(resolve, 1500));

  // Simula um erro de servidor para testar
  if (form.value.email.includes('fail')) {
    formStatus.value = { type: 'error', message: 'Houve um problema no servidor. Tente novamente mais tarde.' };
  } else {
    formStatus.value = { type: 'success', message: 'Contato enviado com sucesso! Nossa equipe retornará em breve.' };
    // Limpa o formulário após o sucesso
    form.value = { cityCouncilName: '', contactPerson: '', email: '', message: '' };
  }
  isSubmitting.value = false;
};

</script>

<template>
  <Head title="CAC System - Solução para Câmaras Municipais" />

  <div class="bg-slate-50 text-slate-800 antialiased has-bg-pattern font-sans">

    <header class="bg-white/95 backdrop-blur-lg shadow-sm sticky top-0 z-50 transition-all duration-300">
      <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="/" class="flex items-center space-x-3 group">
          <Building2 class="h-9 w-9 text-indigo-600 group-hover:scale-105 transition-transform" />
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sistema <span class="text-indigo-600">CAC</span></h1>
        </a>
        <nav class="hidden md:flex items-center space-x-4">
          <a href="#features" class="text-slate-700 hover:text-indigo-700 px-4 py-2 rounded-lg font-medium transition-colors relative group">
            Funcionalidades
            <span class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-indigo-600 group-hover:w-full group-hover:left-0 transition-all duration-300"></span>
          </a>
          <a href="#testimonials" class="text-slate-700 hover:text-indigo-700 px-4 py-2 rounded-lg font-medium transition-colors relative group">
            Depoimentos
            <span class="absolute bottom-0 left-1/2 w-0 h-0.5 bg-indigo-600 group-hover:w-full group-hover:left-0 transition-all duration-300"></span>
          </a>
          <a href="#contact" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 px-6 py-2.5 rounded-full transform hover:scale-105">
            Fale Conosco
          </a>
        </nav>
        </div>
    </header>

    <main>

      <section class="relative min-h-screen flex items-center justify-center text-center overflow-hidden bg-cover bg-center"
        :style="{ backgroundImage: `url(${heroIllustration})` }">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/70 to-white z-0"></div>
        <div class="container mx-auto px-6 relative z-10">
          <h2 v-animate-on-scroll class="text-5xl font-extrabold tracking-tight text-slate-900 sm:text-6xl md:text-7xl leading-tight">
            <span class="block">Otimize o Relacionamento</span>
            <span class="block bg-gradient-to-r from-indigo-600 to-purple-500 bg-clip-text text-transparent mt-2">com o Cidadão da Sua Câmara</span>
          </h2>
          <p v-animate-on-scroll class="mt-8 max-w-3xl mx-auto text-xl text-slate-700 leading-relaxed">
            A plataforma líder para <strong>gestão de atendimentos</strong> no setor público, unindo <strong>eficiência</strong>, <strong>segurança</strong> e <strong>total conformidade com a LGPD</strong>.
        </p>
          <div v-animate-on-scroll class="mt-12 flex justify-center gap-4">
            <a href="#contact" class="inline-flex items-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3.5 px-9 rounded-full shadow-lg hover:shadow-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 focus:outline-none focus-visible:ring-3 focus-visible:ring-offset-2 focus-visible:ring-indigo-500">
              <Star class="h-5 w-5 mr-3" />
              Solicite uma Demonstração Gratuita
            </a>
          </div>
        </div>
      </section>

      <section id="features" class="py-24 bg-slate-50/70">
        <div class="container mx-auto px-6">
          <div v-animate-on-scroll class="text-center mb-16">
            <h3 class="text-4xl font-bold tracking-tight text-slate-900">Tudo que você precisa em um só lugar</h3>
            <p class="mt-4 text-xl text-slate-600 max-w-2xl mx-auto">Funcionalidades pensadas para a realidade do serviço público municipal, com foco em resultados.</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="feature in features" :key="feature.name" v-animate-on-scroll class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-slate-100 group">
              <div class="flex items-center justify-center h-14 w-14 rounded-full bg-indigo-100 text-indigo-600 mb-5 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                <component :is="feature.icon" class="h-7 w-7" aria-hidden="true" />
              </div>
              <h4 class="text-2xl font-semibold text-slate-900 mb-3">{{ feature.name }}</h4>
              <p class="text-slate-700 text-base leading-relaxed">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </section>

      <section id="testimonials" class="py-24 bg-white">
        <div class="container mx-auto px-6">
          <div v-animate-on-scroll class="text-center mb-16">
            <h3 class="text-4xl font-bold tracking-tight text-slate-900">Aprovado por quem usa</h3>
            <p class="mt-4 text-xl text-slate-600 max-w-2xl mx-auto">Veja o que gestores públicos estão dizendo sobre o CAC System e os resultados que alcançaram.</p>
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <div v-for="t in testimonials" :key="t.author" v-animate-on-scroll class="bg-slate-50 p-8 rounded-xl border border-slate-200 shadow-md relative overflow-hidden">
                <Quote class="absolute top-4 left-4 h-14 w-14 text-indigo-100/70 -z-10 transform -rotate-12" />
                <p class="text-slate-800 text-lg my-4 leading-relaxed italic">"{{ t.quote }}"</p>
                <div class="flex items-center mt-6">
                    <img :src="t.avatar" :alt="`Foto de ${t.author}`" class="h-14 w-14 rounded-full object-cover bg-slate-200 border-2 border-indigo-500" />
                    <div class="ml-4">
                        <p class="font-semibold text-slate-900 text-lg">{{ t.author }}</p>
                        <p class="text-slate-600 text-sm">{{ t.title }}</p>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </section>

      <section id="contact" class="py-24 bg-slate-50/70">
        <div class="container mx-auto px-6">
          <div v-animate-on-scroll class="text-center mb-16">
            <h3 class="text-4xl font-bold tracking-tight text-slate-900">Vamos conversar?</h3>
            <p class="mt-4 text-xl text-slate-600">Preencha o formulário e agende uma apresentação sem compromisso. Estamos prontos para otimizar sua gestão!</p>
          </div>
          <div v-animate-on-scroll class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-2xl border border-slate-200">
            <form @submit.prevent="submitForm" class="space-y-6">
              <div>
                <label for="city-council-name" class="block text-sm font-medium text-slate-700 mb-1">Nome da Câmara Municipal <span class="text-red-500">*</span></label>
                <input type="text" v-model="form.cityCouncilName" id="city-council-name" :class="{'border-red-500': errors.cityCouncilName}" class="block w-full border-slate-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 transition" />
                <p v-if="errors.cityCouncilName" class="text-sm text-red-600 mt-1 flex items-center"><XCircle class="h-4 w-4 mr-1"/>{{ errors.cityCouncilName }}</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                  <label for="contact-person" class="block text-sm font-medium text-slate-700 mb-1">Seu Nome <span class="text-red-500">*</span></label>
                  <input type="text" v-model="form.contactPerson" id="contact-person" :class="{'border-red-500': errors.contactPerson}" class="block w-full border-slate-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 transition" />
                   <p v-if="errors.contactPerson" class="text-sm text-red-600 mt-1 flex items-center"><XCircle class="h-4 w-4 mr-1"/>{{ errors.contactPerson }}</p>
                </div>
                <div>
                  <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Seu E-mail <span class="text-red-500">*</span></label>
                  <input type="email" v-model="form.email" id="email" :class="{'border-red-500': errors.email}" class="block w-full border-slate-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 transition" />
                   <p v-if="errors.email" class="text-sm text-red-600 mt-1 flex items-center"><XCircle class="h-4 w-4 mr-1"/>{{ errors.email }}</p>
                </div>
              </div>
              <div>
                <label for="message" class="block text-sm font-medium text-slate-700 mb-1">Mensagem <span class="text-red-500">*</span></label>
                <textarea v-model="form.message" id="message" rows="5" :class="{'border-red-500': errors.message}" class="block w-full border-slate-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 transition"></textarea>
                   <p v-if="errors.message" class="text-sm text-red-600 mt-1 flex items-center"><XCircle class="h-4 w-4 mr-1"/>{{ errors.message }}</p>
              </div>
              <div class="pt-2 text-right">
                <button type="submit" :disabled="isSubmitting" class="inline-flex items-center justify-center py-3.5 px-9 border border-transparent shadow-lg text-base font-medium rounded-full text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 transition-all duration-300 disabled:from-indigo-400 disabled:to-purple-400 disabled:cursor-not-allowed disabled:shadow-md transform hover:scale-105">
                  <LoaderCircle v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5" />
                  <span>{{ isSubmitting ? 'Enviando...' : 'Enviar Mensagem' }}</span>
                </button>
              </div>
              <div v-if="formStatus.message" class="text-center p-4 rounded-lg font-medium flex items-center justify-center" :class="{ 'bg-green-100 text-green-800': formStatus.type === 'success', 'bg-red-100 text-red-800': formStatus.type === 'error' }">
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
        <p>&copy; {{ new Date().getFullYear() }} CAC System. Todos os direitos reservados.</p>
        <p class="text-sm mt-2 text-slate-400">Uma solução moderna e transparente para a gestão pública.</p>
        <div class="mt-6 flex justify-center space-x-6">
          <a href="/login" class="text-slate-400 hover:text-indigo-400 text-sm underline-offset-2 hover:underline transition-colors">
            Login
          </a>
          <span class="text-slate-600">|</span>
          <a href="/dashboard" class="text-slate-400 hover:text-indigo-400 text-sm underline-offset-2 hover:underline transition-colors">
            Meu Painel
          </a>
          <span class="text-slate-600">|</span>
          <a href="/politica-privacidade" class="text-slate-400 hover:text-indigo-400 text-sm underline-offset-2 hover:underline transition-colors">
            Política de Privacidade
          </a>
        </div>
      </div>
    </footer>
  </div>
</template>

<style>
/* Importação de fontes (exemplo com Google Fonts - adicione ao seu index.html ou main.css) */
/*
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
body {
  font-family: 'Inter', sans-serif;
}
*/

/* Estilos para a diretiva de animação */
.animate-on-scroll {
  opacity: 0;
  transform: translateY(25px); /* Aumentado um pouco o translateY */
  transition: opacity 0.8s ease-out, transform 0.8s ease-out; /* Transição mais suave e longa */
}
.animate-on-scroll.is-visible {
  opacity: 1;
  transform: translateY(0);
}

/* Adicionando pequenos atrasos para animações encadeadas (exemplo) */
.animate-on-scroll:nth-child(1) { transition-delay: 0s; }
.animate-on-scroll:nth-child(2) { transition-delay: 0.1s; }
.animate-on-scroll:nth-child(3) { transition-delay: 0.2s; }
.animate-on-scroll:nth-child(4) { transition-delay: 0.3s; }
.animate-on-scroll:nth-child(5) { transition-delay: 0.4s; }
.animate-on-scroll:nth-child(6) { transition-delay: 0.5s; }


/* Classe para o padrão de fundo */
.has-bg-pattern {
  background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23e2e8f0" fill-opacity="0.3"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
}
</style>
