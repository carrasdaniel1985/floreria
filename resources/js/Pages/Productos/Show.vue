<template>
    <Head :title="producto.nombre" />
    <AuthenticatedLayout>
        <PageHeader :title="producto.nombre" :subtitle="producto.categoria?.nombre">
            <template #actions>
                <Link :href="route('productos.index')" class="btn-outline">← Volver</Link>
                <Link v-if="isAdmin" :href="route('productos.edit', producto.id)" class="btn-primary">Editar</Link>
            </template>
        </PageHeader>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Info principal -->
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">{{ tipoLabel(producto.tipo) }}</span>
                        <span :class="producto.activo ? 'badge-green' : 'badge-red'">{{ producto.activo ? 'Activo' : 'Inactivo' }}</span>
                        <span v-if="producto.es_destacado" class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded-full">⭐ Destacado</span>
                    </div>

                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-gray-400">SKU</dt><dd class="font-medium">{{ producto.sku ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Categoría</dt><dd class="font-medium">{{ producto.categoria?.nombre ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Tamaño</dt><dd class="font-medium">{{ producto.tamanio ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Tonos</dt><dd class="font-medium">{{ producto.tonos ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Temporada</dt><dd class="font-medium">{{ producto.temporada ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Vida útil</dt><dd class="font-medium">{{ producto.vida_util_dias ? producto.vida_util_dias + ' días' : '—' }}</dd></div>
                        <div><dt class="text-gray-400">Maneja stock</dt><dd class="font-medium">{{ producto.maneja_stock ? 'Sí' : 'No' }}</dd></div>
                        <div v-if="producto.maneja_stock"><dt class="text-gray-400">Stock actual</dt>
                            <dd :class="['font-bold text-lg', producto.stock_actual <= producto.stock_minimo ? 'text-red-600' : 'text-green-600']">
                                {{ producto.stock_actual }}
                            </dd>
                        </div>
                    </dl>

                    <div v-if="producto.descripcion" class="mt-4 pt-4 border-t border-gray-50">
                        <dt class="text-gray-400 text-sm mb-1">Descripción</dt>
                        <p class="text-sm text-gray-700">{{ producto.descripcion }}</p>
                    </div>
                </div>

                <!-- Precios -->
                <div v-if="isAdmin" class="bg-white rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-700">Precios</h3>
                        <button @click="showPrecioForm = !showPrecioForm" class="btn-sm">+ Agregar precio</button>
                    </div>

                    <!-- Margen -->
                    <div class="flex items-center gap-4 mb-4 bg-amber-50 rounded-lg px-4 py-3">
                        <span class="text-sm text-amber-700">Margen mínimo esperado:</span>
                        <span class="font-bold text-amber-800">{{ producto.margen?.margen_minimo_pct ?? 0 }}%</span>
                        <button @click="showMargenForm = true" class="text-xs text-amber-600 underline">Cambiar</button>
                    </div>

                    <!-- Form nuevo precio -->
                    <form v-if="showPrecioForm" @submit.prevent="agregarPrecio" class="bg-gray-50 rounded-lg p-4 mb-4 space-y-3">
                        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="label">Tipo</label>
                                <select v-model="precioForm.tipo_lista" class="input w-full">
                                    <option value="normal">Normal</option>
                                    <option value="temporada">Temporada</option>
                                    <option value="cliente_frecuente">Cliente frecuente</option>
                                </select>
                            </div>
                            <div>
                                <label class="label">Precio (CLP)</label>
                                <input v-model="precioForm.precio" type="number" min="0" required class="input w-full" />
                            </div>
                            <div>
                                <label class="label">Válido desde</label>
                                <input v-model="precioForm.fecha_desde" type="date" required class="input w-full" />
                            </div>
                            <div>
                                <label class="label">Válido hasta</label>
                                <input v-model="precioForm.fecha_hasta" type="date" class="input w-full" />
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-1.5 bg-rose-600 text-white text-sm rounded-lg">Guardar</button>
                            <button type="button" @click="showPrecioForm=false" class="px-4 py-1.5 text-sm text-gray-600">Cancelar</button>
                        </div>
                    </form>

                    <!-- Historial precios -->
                    <table class="w-full text-sm">
                        <thead class="text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="text-left pb-2">Tipo</th>
                                <th class="text-right pb-2">Precio</th>
                                <th class="text-center pb-2 hidden sm:table-cell">Desde</th>
                                <th class="text-center pb-2 hidden sm:table-cell">Hasta</th>
                                <th class="text-center pb-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="p in producto.precios" :key="p.id">
                                <td class="py-2 capitalize">{{ p.tipo_lista.replace('_', ' ') }}</td>
                                <td class="py-2 text-right font-medium">{{ formatCLP(p.precio) }}</td>
                                <td class="py-2 text-center text-gray-400 hidden sm:table-cell">{{ p.fecha_desde }}</td>
                                <td class="py-2 text-center text-gray-400 hidden sm:table-cell">{{ p.fecha_hasta ?? '—' }}</td>
                                <td class="py-2 text-center">
                                    <span :class="p.activo ? 'badge-green' : 'badge-gray'">{{ p.activo ? 'Vigente' : 'Histórico' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel derecho: fotos y acciones -->
            <div class="space-y-5">
                <!-- Precio principal -->
                <div class="bg-rose-50 rounded-xl border border-rose-100 p-5">
                    <div class="text-xs text-rose-400 uppercase tracking-wide mb-1">Precio de venta</div>
                    <div class="text-3xl font-bold text-rose-700">
                        {{ precioVigente ? formatCLP(precioVigente.precio) : '—' }}
                    </div>
                    <div class="text-xs text-rose-400 mt-1">IVA incluido</div>
                    <div v-if="margenEstimado !== null" class="mt-3 text-sm">
                        <span class="text-gray-500">Margen estimado: </span>
                        <span :class="margenBajo ? 'text-red-600 font-bold' : 'text-green-600 font-medium'">{{ margenEstimado.toFixed(1) }}%</span>
                        <span v-if="margenBajo" class="ml-2 text-xs text-red-500">⚠️ Bajo mínimo</span>
                    </div>
                </div>

                <!-- Fotos -->
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-700 mb-3">Fotos</h3>
                    <div v-if="producto.fotos?.length" class="grid grid-cols-3 gap-2">
                        <img v-for="f in producto.fotos" :key="f.id" :src="`/storage/${f.path}`" class="w-full aspect-square object-cover rounded-lg" />
                    </div>
                    <p v-else class="text-sm text-gray-400">Sin fotos</p>
                </div>

                <!-- Acciones rápidas -->
                <div v-if="isAdmin" class="bg-white rounded-xl border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-700 mb-3">Acciones</h3>
                    <button @click="toggleActivo" class="w-full py-2 text-sm rounded-lg border transition-colors"
                        :class="producto.activo ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-green-200 text-green-600 hover:bg-green-50'">
                        {{ producto.activo ? 'Desactivar producto' : 'Activar producto' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal margen -->
        <div v-if="showMargenForm" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4" @click.self="showMargenForm=false">
            <form @submit.prevent="actualizarMargen" class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl space-y-4">
                <h3 class="font-semibold">Margen mínimo esperado</h3>
                <div>
                    <label class="label">Porcentaje (%)</label>
                    <input v-model="margenForm.margen_minimo_pct" type="number" min="0" max="100" step="0.1" class="input w-full" />
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white text-sm rounded-lg">Guardar</button>
                    <button type="button" @click="showMargenForm=false" class="px-4 py-2 text-sm text-gray-600">Cancelar</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ producto: Object });

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.roles?.some(r => r.name === 'administrador'));

const showPrecioForm = ref(false);
const showMargenForm = ref(false);

const precioForm = ref({ tipo_lista: 'normal', precio: '', fecha_desde: new Date().toISOString().slice(0,10), fecha_hasta: '' });
const margenForm = ref({ margen_minimo_pct: props.producto.margen?.margen_minimo_pct ?? 0 });

const precioVigente = computed(() => props.producto.precios?.find(p => p.activo && p.tipo_lista === 'normal'));
const margenEstimado = computed(() => {
    // Calculado cuando hay costo disponible; aquí mostramos por referencia
    return null;
});
const margenBajo = computed(() => false);

function agregarPrecio() {
    router.post(route('productos.precios.store', props.producto.id), precioForm.value, {
        preserveScroll: true,
        onSuccess: () => { showPrecioForm.value = false; precioForm.value.precio = ''; }
    });
}

function actualizarMargen() {
    router.put(route('productos.margen.update', props.producto.id), margenForm.value, {
        preserveScroll: true,
        onSuccess: () => { showMargenForm.value = false; }
    });
}

function toggleActivo() {
    if (!confirm(`¿${props.producto.activo ? 'Desactivar' : 'Activar'} este producto?`)) return;
    router.patch(route('productos.toggle-activo', props.producto.id), {}, { preserveScroll: true });
}

function formatCLP(n) { return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(n); }
function tipoLabel(t) { return { simple: 'Simple', servicio: 'Servicio', compuesto: 'Compuesto' }[t] ?? t; }
</script>

<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.btn-primary { @apply inline-flex items-center gap-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
.btn-sm { @apply px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-50 text-gray-600 transition-colors; }
.badge-green { @apply inline-block px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full; }
.badge-red { @apply inline-block px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full; }
.badge-gray { @apply inline-block px-2 py-0.5 text-xs bg-gray-100 text-gray-500 rounded-full; }
</style>
