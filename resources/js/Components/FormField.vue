<template>
    <div class="mb-4">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>
        <slot />
        <p v-if="error" class="text-xs text-red-600 mt-1">{{ error }}</p>
        <p v-if="hint" class="text-xs text-gray-400 mt-1">{{ hint }}</p>
    </div>
</template>
<script setup>
defineProps({ label: String, id: String, error: String, hint: String, required: Boolean });
</script>
