<template>
    <Head :title="comuna ? 'Editar comuna' : 'Nueva comuna'" />
    <AuthenticatedLayout>
        <PageHeader :title="comuna ? 'Editar comuna' : 'Nueva comuna'">
            <template #actions>
                <Link :href="route('comunas.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>
        <div class="max-w-md">
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
                <div>
                    <label class="label">Nombre <span class="text-red-500">*</span></label>
                    <input v-model="form.nombre" required class="input w-full" />
                    <p v-if="form.errors.nombre" class="error">{{ form.errors.nombre }}</p>
                </div>
                <div>
                    <label class="label">Región</label>
                    <input v-model="form.region" class="input w-full" placeholder="Ej: Región Metropolitana" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.activa" id="activa" class="rounded border-gray-300 text-rose-600" />
                    <label for="activa" class="text-sm text-gray-700">Activa</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg disabled:opacity-60">
                        {{ form.processing ? 'Guardando...' : (comuna ? 'Actualizar' : 'Crear') }}
                    </button>
                    <Link :href="route('comunas.index')" class="px-4 py-2 text-sm text-gray-600">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
const props = defineProps({ comuna: Object });
const form = useForm({ nombre: props.comuna?.nombre ?? '', region: props.comuna?.region ?? '', activa: props.comuna?.activa ?? true });
function submit() {
    if (props.comuna) form.put(route('comunas.update', props.comuna.id));
    else form.post(route('comunas.store'));
}
</script>
<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.error { @apply text-xs text-red-600 mt-1; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
