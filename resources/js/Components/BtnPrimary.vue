<template>
    <button type="submit" v-bind="$attrs" :disabled="disabled" :class="['inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors', $attrs.class]">
        <slot />
    </button>
</template>
<script setup>
defineProps({ disabled: Boolean });
</script>
