<script setup>
defineProps({
    rating: {
        type: Number,
        default: 0
    }
});
</script>

<template>
    <div class="flex space-x-1">
        <template v-for="star in 5" :key="star">
            <i class="fa-solid fa-star" :class="star <= rating ? 'text-yellow-400' : 'text-gray-300'"></i>
        </template>
    </div>
</template>
