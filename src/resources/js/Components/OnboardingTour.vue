<script setup>
import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

let tourInstance = null;

// Função que encapsula toda a lógica de criação e início do tour
const createAndStartTour = () => {
    // Se um tour já estiver ativo, cancele-o para evitar sobreposições
    if (tourInstance && tourInstance.isActive()) {
        tourInstance.cancel();
    }

    // Função para navegar e avançar no tour
    const navigateAndNext = (tour, routeName) => {
        // Verifica se a rota existe antes de tentar visitá-la
        if (typeof route === 'function' && route().has(routeName)) {
             router.visit(route(routeName), {
                onSuccess: () => setTimeout(() => tour.next(), 300),
            });
        } else {
            console.error(`[OnboardingTour] A rota "${routeName}" não foi encontrada. Verifique seu arquivo de rotas e o Ziggy.`);
            // O tour não avança se a rota não existir, para evitar erros.
        }
    };

    const tour = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            classes: 'shepherd-custom',
            scrollTo: { behavior: 'smooth', block: 'center' },
            cancelIcon: { enabled: true, label: 'Fechar tour' },
        }
    });

    // --- Passos do Tour (com nomes de rotas corrigidos para o prefixo 'admin.') ---
    tour.addStep({ id: 'welcome', title: '👋 Bem-vindo(a) à Câmara Digital!', text: 'Este guia rápido irá mostrar os passos essenciais para configurar e começar a usar o seu sistema. Vamos começar!', attachTo: { element: '.main-header', on: 'bottom' }, buttons: [{ text: 'Pular', classes: 'shepherd-button-secondary', action: tour.complete }, { text: 'Começar →', action: tour.next }] });
    tour.addStep({ id: 'parametros', title: '⚙️ 1. Configure os Parâmetros', text: 'O primeiro passo é configurar as informações básicas do seu portal, como logo, endereço, contatos e redes sociais.', attachTo: { element: 'a[href*="/parametros"]', on: 'right' }, buttons: [{ text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back }, { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.parametros.index') }] });
    tour.addStep({ id: 'status', title: '📊 2. Cadastre os Status', text: 'Defina os status que suas solicitações podem ter (ex: "Em Análise", "Finalizado"). Isso é crucial para organizar o fluxo de trabalho.', attachTo: { element: 'a[href*="/status-solicitacao"]', on: 'right' }, buttons: [{ text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back }, { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.status-solicitacao.index') }] });
    tour.addStep({ id: 'tipos-servico', title: '📂 3. Crie os Tipos de Serviço', text: 'Organize seus serviços em categorias. Por exemplo: "Serviços Urbanos", "Assistência Social", "Documentos".', attachTo: { element: 'a[href*="/tipos-servico"]', on: 'right' }, buttons: [{ text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back }, { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.tipos-servico.index') }] });
    tour.addStep({ id: 'servicos', title: '📝 4. Cadastre os Serviços', text: 'Com os tipos definidos, agora você pode cadastrar os serviços que serão oferecidos aos cidadãos.', attachTo: { element: 'a[href*="/servicos"]', on: 'right' }, buttons: [{ text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back }, { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.servicos.index') }] });
    tour.addStep({ id: 'cidadaos', title: '👤 5. Cadastre os Cidadãos', text: 'Adicione os cidadãos à sua base. Ao cadastrar, um e-mail será enviado para que eles definam uma senha de acesso ao portal.', attachTo: { element: 'a[href*="/cidadaos"]', on: 'right' }, buttons: [{ text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back }, { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.cidadaos.index') }] });
    tour.addStep({ id: 'funcionarios', title: '👥 6. Gerencie os Funcionários', text: 'Cadastre os membros da sua equipe que irão operar o sistema e atender às solicitações.', attachTo: { element: 'a[href*="/funcionarios"]', on: 'right' }, buttons: [{ text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back }, { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.funcionarios.index') }] });
    tour.addStep({ id: 'solicitacoes', title: '🚀 7. Crie uma Solicitação', text: 'Tudo pronto! Agora você já pode registrar a primeira solicitação de serviço para um cidadão.', attachTo: { element: 'a[href*="/solicitacoes"]', on: 'right' }, buttons: [{ text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back }, { text: 'Avançar →', action: () => navigateAndNext(tour, 'admin.solicitacoes.index') }] });
    tour.addStep({ id: 'finish', title: '✅ Configuração Concluída!', text: 'Você completou os passos essenciais. Explore o menu para descobrir mais funcionalidades.', attachTo: { element: '.user-menu', on: 'bottom' }, buttons: [{ text: '← Voltar', classes: 'shepherd-button-secondary', action: tour.back }, { text: 'Finalizar Tour', action: tour.complete }] });

    tourInstance = tour;

    const onTourEnd = () => {
        localStorage.setItem('onboarding_tour_completed', 'true');
        // A rota do dashboard foi corrigida para 'tenant.dashboard'
        if (typeof route === 'function' && route().has('tenant.dashboard')) {
             router.visit(route('tenant.dashboard'));
        } else {
            console.error(`[OnboardingTour] A rota "tenant.dashboard" não foi encontrada para finalizar o tour.`);
        }
    };
    tour.on('complete', onTourEnd);
    tour.on('cancel', onTourEnd);

    tour.start();
};

onMounted(() => {
  if (localStorage.getItem('onboarding_tour_completed') !== 'true') {
    createAndStartTour();
  }
  window.addEventListener('restart-onboarding-tour', createAndStartTour);
});

onUnmounted(() => {
  window.removeEventListener('restart-onboarding-tour', createAndStartTour);
  if (tourInstance && tourInstance.isActive()) {
      tourInstance.cancel();
  }
});
</script>

<template>
  <div />
</template>

<style>
/* Estilização Customizada para o Shepherd.js */
.shepherd-custom {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    max-width: 400px;
    border: 1px solid #e2e8f0;
}

.dark .shepherd-custom {
    background-color: #1f2937; /* Cor de fundo para o modo escuro */
    border: 1px solid #374151;
}

.shepherd-custom .shepherd-header {
    background-color: #f8fafc;
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.dark .shepherd-custom .shepherd-header {
    background-color: #374151; /* Cor do header para o modo escuro */
    border-bottom: 1px solid #4b5563;
}

.shepherd-custom .shepherd-title {
    color: #111827;
    font-size: 1.125rem;
    font-weight: 700;
}

.dark .shepherd-custom .shepherd-title {
    color: #f9fafb; /* Cor do título para o modo escuro */
}

.shepherd-custom .shepherd-cancel-icon {
    color: #9ca3af;
    transition: color 0.2s ease-in-out;
}

.shepherd-custom .shepherd-cancel-icon:hover {
    color: #111827;
}

.dark .shepherd-custom .shepherd-cancel-icon:hover {
    color: #ffffff;
}

.shepherd-custom .shepherd-text {
    padding: 1rem;
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.6;
}

.dark .shepherd-custom .shepherd-text {
    color: #d1d5db; /* Cor do texto para o modo escuro */
}

.shepherd-custom .shepherd-footer {
    padding: 0 1rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.shepherd-custom .shepherd-button {
    background-color: var(--color-primary, #4F46E5); /* Usa a variável de cor primária do seu sistema */
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    font-weight: 600;
    transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
    border: none;
    cursor: pointer;
}

.shepherd-custom .shepherd-button:hover {
    filter: brightness(110%);
    transform: translateY(-1px);
}

.shepherd-custom .shepherd-button-secondary {
    background-color: transparent;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.dark .shepherd-custom .shepherd-button-secondary {
    color: #9ca3af;
    border-color: #4b5563;
}

.shepherd-custom .shepherd-button-secondary:hover {
    background-color: #f3f4f6;
    color: #1f2937;
}

.dark .shepherd-custom .shepherd-button-secondary:hover {
    background-color: #374151;
    color: #f9fafb;
}

.shepherd-arrow::before {
    background-color: #ffffff !important;
}

.dark .shepherd-arrow::before {
    background-color: #1f2937 !important; /* Cor da seta para o modo escuro */
}
</style>
