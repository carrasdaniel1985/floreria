<template>
    <Head title="Registrar merma" />
    <AuthenticatedLayout>
        <PageHeader title="Registrar merma" subtitle="Descuenta stock por producto vencido, dañado o descartado">
            <template #actions>
                <Link :href="route('mermas.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>

        <div class="max-w-lg">
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
                <div>
                    <label class="label">Producto <span class="text-red-500">*</span></label>
                    <select v-model="form.producto_id" required class="input w-full">
                        <option value="">Seleccionar producto...</option>
                        <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.nombre }} (stock: {{ p.stock_actual }})</option>
                    </select>
                    <p v-if="form.errors.producto_id" class="error">{{ form.errors.producto_id }}</p>
                </div>
                <div>
                    <label class="label">Sucursal <span class="text-red-500">*</span></label>
                    <select v-model="form.sucursal_id" required class="input w-full">
                        <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                    </select>
                </div>
                <div>
                    <label class="label">Cantidad a descontar <span class="text-red-500">*</span></label>
                    <input v-model="form.cantidad" type="number" min="0.01" step="0.01" required class="input w-40" />
                    <p v-if="form.errors.cantidad" class="error">{{ form.errors.cantidad }}</p>
                </div>
                <div>
                    <label class="label">Motivo <span class="text-red-500">*</span></label>
                    <select v-model="form.motivo" required class="input w-full">
                        <option value="">Seleccionar motivo...</option>
                        <option v-for="m in motivos" :key="m" :value="m" class="capitalize">{{ m }}</option>
                    </select>
                    <p v-if="form.errors.motivo" class="error">{{ form.errors.motivo }}</p>
                </div>
                <div>
                    <label class="label">Observaciones</label>
                    <textarea v-model="form.observaciones" rows="3" class="input w-full resize-none"></textarea>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800">
                    ⚠️ Al registrar, el stock del producto se descontará automáticamente.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
                        {{ form.processing ? 'Registrando...' : 'Registrar merma' }}
                    </button>
                    <Link :href="route('mermas.index')" class="px-4 py-2 text-sm text-gray-600">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ productos: Array, sucursales: Array, motivos: Array, sucursal_default_id: Number });

const form = useForm({
    producto_id: '',
    sucursal_id: props.sucursal_default_id ?? '',
    cantidad: 1,
    motivo: '',
    observaciones: '',
});

function submit() {
    form.post(route('mermas.store'));
}
</script>
<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.error { @apply text-xs text-red-600 mt-1; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
