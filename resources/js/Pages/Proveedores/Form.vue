<template>
    <Head :title="proveedor ? 'Editar proveedor' : 'Nuevo proveedor'" />
    <AuthenticatedLayout>
        <PageHeader :title="proveedor ? 'Editar proveedor' : 'Nuevo proveedor'">
            <template #actions>
                <Link :href="route('proveedores.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>
        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="label">Nombre <span class="text-red-500">*</span></label>
                        <input v-model="form.nombre" required class="input w-full" />
                        <p v-if="form.errors.nombre" class="error">{{ form.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="label">RUT <span class="text-red-500">*</span></label>
                        <input v-model="form.rut" required class="input w-full" placeholder="12.345.678-9" />
                        <p v-if="form.errors.rut" class="error">{{ form.errors.rut }}</p>
                    </div>
                    <div>
                        <label class="label">Teléfono</label>
                        <input v-model="form.telefono" class="input w-full" placeholder="+56 9 1234 5678" />
                    </div>
                    <div>
                        <label class="label">Correo</label>
                        <input v-model="form.email" type="email" class="input w-full" />
                        <p v-if="form.errors.email" class="error">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="label">Dirección</label>
                        <input v-model="form.direccion" class="input w-full" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Tipos de productos que vende</label>
                        <input v-model="form.tipos_productos" class="input w-full" placeholder="Ej: Flores frescas, envases, accesorios" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.activo" id="activo" class="rounded border-gray-300 text-rose-600" />
                    <label for="activo" class="text-sm text-gray-700">Proveedor activo</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
                        {{ form.processing ? 'Guardando...' : (proveedor ? 'Actualizar' : 'Crear proveedor') }}
                    </button>
                    <Link :href="route('proveedores.index')" class="px-4 py-2 text-sm text-gray-600">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ proveedor: Object });
const form = useForm({
    nombre: props.proveedor?.nombre ?? '',
    rut: props.proveedor?.rut ?? '',
    telefono: props.proveedor?.telefono ?? '',
    email: props.proveedor?.email ?? '',
    direccion: props.proveedor?.direccion ?? '',
    tipos_productos: props.proveedor?.tipos_productos ?? '',
    activo: props.proveedor?.activo ?? true,
});
function submit() {
    if (props.proveedor) form.put(route('proveedores.update', props.proveedor.id));
    else form.post(route('proveedores.store'));
}
</script>
<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.error { @apply text-xs text-red-600 mt-1; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
