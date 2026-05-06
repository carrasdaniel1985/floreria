<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-3 py-1 rounded-md text-sm font-medium text-rose-700 bg-rose-50 transition'
        : 'inline-flex items-center px-3 py-1 rounded-md text-sm font-medium text-gray-600 hover:text-rose-600 hover:bg-rose-50 transition',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
