<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Font Poppins para consistência com os layouts da aplicação -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <!-- Script para Prevenção de Flash de Tema (FOUWT) -->
        <script>
            // Este script é executado antes da renderização para aplicar o tema correto
            // e evitar que o tema claro "pisque" rapidamente ao carregar em modo escuro.
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Estilos para o Loader de Carregamento Inicial -->
        <style>
            .loader-container {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
                background-color: #f9fafb; /* Cor para modo claro (bg-gray-50) */
                opacity: 1;
                transition: opacity 0.4s ease-out, visibility 0.4s ease-out;
                visibility: visible;
            }
            /* Suporte para modo escuro com base na classe 'dark' no <html> */
            html.dark .loader-container {
                background-color: #0A1E1C; /* Cor de fundo do seu novo layout */
            }
            /* Esconde o loader quando o body não tiver mais a classe 'is-loading' */
            body:not(.is-loading) .loader-container {
                opacity: 0;
                visibility: hidden;
            }
        </style>
    </head>

    <!-- Adiciona a classe 'is-loading' para controlar a visibilidade do loader -->
    <body class="font-sans antialiased is-loading">

        <!-- O loader fica FORA do @inertia para uma transição suave de saída -->
        <div class="loader-container">
            <div role="status">
                <!-- Spinner com tamanho ajustado -->
                <svg aria-hidden="true" class="w-10 h-10 text-gray-200 animate-spin dark:text-gray-600 fill-green-500" viewBox="0 0 100 101" fill="none" xmlns="http://www.w.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        @inertia

        <script>
            // Este script remove a classe 'is-loading' do body quando a página estiver totalmente carregada,
            // acionando a transição de fade-out do loader definida no CSS.
            window.addEventListener('load', () => {
                document.body.classList.remove('is-loading');
            });
        </script>
    </body>
</html>


