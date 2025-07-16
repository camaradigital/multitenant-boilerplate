<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    title: String,
});
</script>

<template>
    <Head :title="title" />
    <div class="bg-gray-100 text-gray-800">
        <header class="bg-white shadow-md">
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div>
                    <Link :href="route('marketing.home')" class="text-xl font-bold">
                        CACSystem
                    </Link>
                </div>
                <div>
                    <Link :href="route('login')" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                        Acessar Painel
                    </Link>
                </div>
            </nav>
        </header>

        <main>
            <slot />
        </main>

        <footer class="bg-gray-800 text-white mt-12 py-6">
            <div class="container mx-auto px-6 text-center">
                <p>&copy; 2025 CACSystem. Todos os direitos reservados.</p>
            </div>
        </footer>
    </div>
</template>
