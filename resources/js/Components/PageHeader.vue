<template>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ title }}</h1>
            <p v-if="subtitle" class="text-sm text-gray-500 mt-0.5">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.actions" class="flex gap-2 shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup>
defineProps({ title: String, subtitle: String });
</script>
