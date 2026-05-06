<template>
    <Head :title="tarifa ? 'Editar tarifa' : 'Nueva tarifa de despacho'" />
    <AuthenticatedLayout>
        <PageHeader :title="tarifa ? 'Editar tarifa' : 'Nueva tarifa de despacho'">
            <template #actions>
                <Link :href="route('tarifas-despacho.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>
        <div class="max-w-lg">
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
                <div>
                    <label class="label">Sucursal <span class="text-red-500">*</span></label>
                    <select v-model="form.sucursal_id" required class="input w-full">
                        <option value="">Seleccionar...</option>
                        <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                    </select>
                    <p v-if="form.errors.sucursal_id" class="error">{{ form.errors.sucursal_id }}</p>
                </div>
                <div>
                    <label class="label">Comuna <span class="text-red-500">*</span></label>
                    <select v-model="form.comuna_id" required class="input w-full">
                        <option value="">Seleccionar...</option>
                        <option v-for="c in comunas" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                    </select>
                    <p v-if="form.errors.comuna_id" class="error">{{ form.errors.comuna_id }}</p>
                </div>
                <div>
                    <label class="label">Precio (CLP, incluye IVA) <span class="text-red-500">*</span></label>
                    <input v-model="form.precio" type="number" min="0" required class="input w-40" />
                    <p v-if="form.errors.precio" class="error">{{ form.errors.precio }}</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Válido desde <span class="text-red-500">*</span></label>
                        <input v-model="form.fecha_desde" type="date" required class="input w-full" />
                    </div>
                    <div>
                        <label class="label">Válido hasta</label>
                        <input v-model="form.fecha_hasta" type="date" class="input w-full" />
                    </div>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800">
                    Al crear una nueva tarifa para la misma sucursal y comuna, la tarifa anterior quedará inactiva automáticamente.
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg disabled:opacity-60">
                        {{ form.processing ? 'Guardando...' : (tarifa ? 'Actualizar' : 'Registrar tarifa') }}
                    </button>
                    <Link :href="route('tarifas-despacho.index')" class="px-4 py-2 text-sm text-gray-600">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ tarifa: Object, sucursales: Array, comunas: Array });
const form = useForm({
    sucursal_id: props.tarifa?.sucursal_id ?? '',
    comuna_id:   props.tarifa?.comuna_id ?? '',
    precio:      props.tarifa?.precio ?? '',
    fecha_desde: props.tarifa?.fecha_desde ?? new Date().toISOString().slice(0, 10),
    fecha_hasta: props.tarifa?.fecha_hasta ?? '',
    activa:      props.tarifa?.activa ?? true,
});
function submit() {
    if (props.tarifa) form.put(route('tarifas-despacho.update', props.tarifa.id));
    else form.post(route('tarifas-despacho.store'));
}
</script>
<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.error { @apply text-xs text-red-600 mt-1; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
