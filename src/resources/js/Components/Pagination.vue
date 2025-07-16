<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1 justify-center">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    class="pagination-link is-disabled"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="pagination-link"
                    :class="{ 'is-active': link.active }"
                    :href="link.url"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>

<style scoped>
.pagination-link {
    @apply flex items-center justify-center text-sm py-2 px-4 leading-tight border transition-colors duration-200;
    @apply text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700;
    @apply dark:text-gray-400 dark:bg-transparent dark:border-gray-700 dark:hover:bg-gray-800/50 dark:hover:text-white;
    @apply first:rounded-l-lg last:rounded-r-lg;
    @apply mb-1 mx-0.5;
}

.pagination-link.is-active {
    @apply z-10 font-bold;
    @apply text-emerald-600 bg-emerald-50 border-emerald-400;
    @apply dark:text-white dark:bg-green-500/20 dark:border-green-500/50;
}

.pagination-link.is-disabled {
    @apply cursor-not-allowed opacity-50;
}
</style>
