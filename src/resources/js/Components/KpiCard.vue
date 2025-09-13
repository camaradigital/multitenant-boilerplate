<script setup>
defineProps({
    title: String,
    value: [String, Number],
    unit: String,
    icon: Object,
    colorClass: String,
});
</script>

<template>
    <div class="kpi-card" :class="colorClass">
        <div class="flex-grow">
            <p class="kpi-title">{{ title }}</p>
            <p class="kpi-value">
                {{ value }}
                <span v-if="unit" class="kpi-unit">{{ unit }}</span>
            </p>
        </div>
        <div class="kpi-icon-wrapper">
            <component :is="icon" class="w-6 h-6" />
        </div>
    </div>
</template>
