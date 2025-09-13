<script setup>
import { ref, onMounted } from 'vue';
import { Sun, Moon } from 'lucide-vue-next';

const isDarkMode = ref(false);

const applyTheme = () => {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
};

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    applyTheme();
};

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        isDarkMode.value = savedTheme === 'dark';
    } else {
        isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyTheme();
});
</script>

<template>
    <button
        @click="toggleTheme"
        class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors duration-300"
        aria-label="Alternar tema"
    >
        <transition name="fade-rotate" mode="out-in">
            <Sun class="h-5 w-5" v-if="isDarkMode" key="sun" />
            <Moon class="h-5 w-5" v-else key="moon" />
        </transition>
    </button>
</template>

<style scoped>
.fade-rotate-enter-active,
.fade-rotate-leave-active {
    transition: all 0.3s ease;
}

.fade-rotate-enter-from,
.fade-rotate-leave-to {
    opacity: 0;
    transform: rotate(-90deg) scale(0.8);
}
</style>
