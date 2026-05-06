<template>
    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', colorClass]">
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({ color: { type: String, default: 'gray' } });
const colorClass = computed(() => ({
    green: 'bg-green-100 text-green-800',
    red:   'bg-red-100 text-red-800',
    yellow: 'bg-yellow-100 text-yellow-800',
    blue:  'bg-blue-100 text-blue-800',
    gray:  'bg-gray-100 text-gray-700',
    rose:  'bg-rose-100 text-rose-800',
}[props.color] ?? 'bg-gray-100 text-gray-700'));
</script>
