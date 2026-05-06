<template>
    <Head :title="categoria ? 'Editar categoría' : 'Nueva categoría'" />
    <AuthenticatedLayout>
        <PageHeader :title="categoria ? 'Editar categoría' : 'Nueva categoría'">
            <template #actions>
                <Link :href="route('categorias.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>

        <div class="max-w-lg">
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-100 p-6 space-y-4" enctype="multipart/form-data">
                <div>
                    <label class="label">Nombre <span class="text-red-500">*</span></label>
                    <input v-model="form.nombre" type="text" required class="input w-full" />
                    <p v-if="form.errors.nombre" class="error">{{ form.errors.nombre }}</p>
                </div>
                <div>
                    <label class="label">Orden de visualización</label>
                    <input v-model="form.orden" type="number" min="0" class="input w-32" />
                </div>
                <div>
                    <label class="label">Imagen</label>
                    <input type="file" @change="e => form.imagen = e.target.files[0]" accept="image/*" class="text-sm text-gray-600" />
                    <img v-if="categoria?.imagen" :src="`/storage/${categoria.imagen}`" class="mt-2 w-20 h-20 object-cover rounded-lg" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.activa" id="activa" class="rounded border-gray-300 text-rose-600 focus:ring-rose-400" />
                    <label for="activa" class="text-sm text-gray-700">Categoría activa</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
                        {{ form.processing ? 'Guardando...' : (categoria ? 'Actualizar' : 'Crear') }}
                    </button>
                    <Link :href="route('categorias.index')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ categoria: Object });

const form = useForm({
    nombre: props.categoria?.nombre ?? '',
    orden:  props.categoria?.orden ?? 0,
    activa: props.categoria?.activa ?? true,
    imagen: null,
});

function submit() {
    if (props.categoria) {
        form.post(route('categorias.update', props.categoria.id), { _method: 'put' });
    } else {
        form.post(route('categorias.store'));
    }
}
</script>
<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.error { @apply text-xs text-red-600 mt-1; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
