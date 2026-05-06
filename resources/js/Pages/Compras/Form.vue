<template>
    <Head :title="compra ? 'Editar compra' : 'Nueva compra'" />
    <AuthenticatedLayout>
        <PageHeader :title="compra ? `Editar compra #${compra.id}` : 'Nueva compra'">
            <template #actions>
                <Link :href="route('compras.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>

        <div class="max-w-4xl">
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Cabecera -->
                <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
                    <h3 class="font-semibold text-gray-700">Información de la compra</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="label">Proveedor</label>
                            <select v-model="form.proveedor_id" class="input w-full">
                                <option value="">Sin proveedor</option>
                                <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Sucursal <span class="text-red-500">*</span></label>
                            <select v-model="form.sucursal_id" required class="input w-full">
                                <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Fecha de compra <span class="text-red-500">*</span></label>
                            <input v-model="form.fecha_compra" type="date" required class="input w-full" />
                            <p v-if="form.errors.fecha_compra" class="error">{{ form.errors.fecha_compra }}</p>
                        </div>
                        <div>
                            <label class="label">N° Referencia (folio)</label>
                            <input v-model="form.numero_referencia" type="text" class="input w-full" placeholder="Nro. boleta/factura" />
                        </div>
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="label">Observaciones</label>
                            <input v-model="form.observaciones" type="text" class="input w-full" />
                        </div>
                    </div>

                    <!-- Motivo modificación (solo si compra confirmada) -->
                    <div v-if="compra?.estado_operacional === 'confirmada'">
                        <label class="label">Motivo de modificación <span class="text-red-500">*</span></label>
                        <input v-model="form.motivo_modificacion" type="text" required class="input w-full" placeholder="Explica por qué modificas esta compra confirmada" />
                        <p v-if="form.errors.motivo_modificacion" class="error">{{ form.errors.motivo_modificacion }}</p>
                    </div>
                </div>

                <!-- Detalle de productos -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-700">Productos comprados</h3>
                        <button type="button" @click="agregarLinea" class="btn-sm text-rose-600 border-rose-200 hover:bg-rose-50">+ Agregar línea</button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(det, i) in form.detalles" :key="i"
                            class="grid grid-cols-12 gap-2 items-start bg-gray-50 rounded-lg p-3">
                            <!-- Producto -->
                            <div class="col-span-12 sm:col-span-4">
                                <label class="label text-xs">Producto</label>
                                <select v-model="det.producto_id" @change="onProductoChange(i)" class="input w-full text-xs">
                                    <option value="">Seleccionar producto...</option>
                                    <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label class="label text-xs">Descripción</label>
                                <input v-model="det.descripcion_producto" class="input w-full text-xs" placeholder="o descripción libre" />
                            </div>
                            <!-- Cantidad -->
                            <div class="col-span-4 sm:col-span-2">
                                <label class="label text-xs">Cantidad</label>
                                <input v-model.number="det.cantidad" @input="calcularLinea(i)" type="number" min="0.01" step="0.01" required class="input w-full text-xs" />
                            </div>
                            <!-- Costo unitario -->
                            <div class="col-span-5 sm:col-span-2">
                                <label class="label text-xs">Costo unit. (CLP)</label>
                                <input v-model.number="det.costo_unitario" @input="calcularLinea(i)" type="number" min="0" required class="input w-full text-xs" />
                            </div>
                            <!-- Total línea + eliminar -->
                            <div class="col-span-3 sm:col-span-1 flex flex-col items-end">
                                <label class="label text-xs">Total</label>
                                <span class="text-xs font-medium text-gray-700 pt-2">{{ formatCLP(det.costo_total) }}</span>
                                <button type="button" @click="eliminarLinea(i)" class="mt-1 text-red-400 hover:text-red-600 text-xs">✕</button>
                            </div>
                        </div>
                    </div>

                    <!-- Total compra -->
                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Total estimado</div>
                            <div class="text-2xl font-bold text-gray-800">{{ formatCLP(totalCompra) }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
                        {{ form.processing ? 'Guardando...' : (compra ? 'Actualizar compra' : 'Guardar como borrador') }}
                    </button>
                    <Link :href="route('compras.index')" class="px-4 py-2 text-sm text-gray-600">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ compra: Object, proveedores: Array, sucursales: Array, productos: Array, sucursal_default_id: Number });

const form = useForm({
    proveedor_id: props.compra?.proveedor_id ?? '',
    sucursal_id: props.compra?.sucursal_id ?? props.sucursal_default_id ?? '',
    numero_referencia: props.compra?.numero_referencia ?? '',
    fecha_compra: props.compra?.fecha_compra ?? new Date().toISOString().slice(0, 10),
    observaciones: props.compra?.observaciones ?? '',
    motivo_modificacion: '',
    detalles: props.compra?.detalles?.map(d => ({
        producto_id: d.producto_id ?? '',
        descripcion_producto: d.descripcion_producto ?? '',
        cantidad: d.cantidad,
        costo_unitario: d.costo_unitario,
        costo_total: d.costo_total,
        observaciones: d.observaciones ?? '',
    })) ?? [lineaVacia()],
});

function lineaVacia() {
    return { producto_id: '', descripcion_producto: '', cantidad: 1, costo_unitario: 0, costo_total: 0, observaciones: '' };
}

function agregarLinea() { form.detalles.push(lineaVacia()); }
function eliminarLinea(i) { if (form.detalles.length > 1) form.detalles.splice(i, 1); }

function calcularLinea(i) {
    const d = form.detalles[i];
    d.costo_total = Math.round((d.cantidad ?? 0) * (d.costo_unitario ?? 0));
}

function onProductoChange(i) {
    const det = form.detalles[i];
    const producto = props.productos.find(p => p.id == det.producto_id);
    if (producto && !det.descripcion_producto) {
        det.descripcion_producto = producto.nombre;
    }
}

const totalCompra = computed(() => form.detalles.reduce((sum, d) => sum + (d.costo_total ?? 0), 0));

function submit() {
    if (props.compra) {
        form.put(route('compras.update', props.compra.id));
    } else {
        form.post(route('compras.store'));
    }
}

function formatCLP(n) { return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(n ?? 0); }
</script>
<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.error { @apply text-xs text-red-600 mt-1; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
.btn-sm { @apply px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
