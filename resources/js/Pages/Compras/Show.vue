<template>
    <Head :title="`Compra #${compra.id}`" />
    <AuthenticatedLayout>
        <PageHeader :title="`Compra #${compra.id}`" :subtitle="compra.proveedor?.nombre ?? 'Sin proveedor'">
            <template #actions>
                <Link :href="route('compras.index')" class="btn-outline">← Volver</Link>
                <Link v-if="!compra.estado_operacional.includes('anulada')" :href="route('compras.edit', compra.id)" class="btn-outline">Editar</Link>
            </template>
        </PageHeader>

        <!-- Badges de estado -->
        <div class="flex flex-wrap gap-2 mb-5">
            <span :class="estadoBadge(compra.estado_operacional)" class="text-sm px-3 py-1 rounded-full font-medium">
                {{ estadoLabel(compra.estado_operacional) }}
            </span>
            <span :class="compra.estado_pago === 'pagada' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'" class="text-sm px-3 py-1 rounded-full font-medium">
                {{ compra.estado_pago === 'pagada' ? '✓ Pagada' : '⏳ Pago pendiente' }}
            </span>
            <span :class="compra.documentos?.length ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'" class="text-sm px-3 py-1 rounded-full font-medium">
                {{ compra.documentos?.length ? `📎 ${compra.documentos.length} doc.` : '📄 Sin documentos' }}
            </span>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Detalle principal -->
            <div class="lg:col-span-2 space-y-5">
                <!-- Info compra -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Información</h3>
                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-gray-400">Proveedor</dt><dd class="font-medium">{{ compra.proveedor?.nombre ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Sucursal</dt><dd class="font-medium">{{ compra.sucursal?.nombre }}</dd></div>
                        <div><dt class="text-gray-400">Fecha compra</dt><dd class="font-medium">{{ compra.fecha_compra }}</dd></div>
                        <div><dt class="text-gray-400">N° Referencia</dt><dd class="font-medium">{{ compra.numero_referencia ?? '—' }}</dd></div>
                        <div><dt class="text-gray-400">Creada por</dt><dd class="font-medium">{{ compra.creador?.nombre }} {{ compra.creador?.apellido }}</dd></div>
                        <div v-if="compra.confirmada_at"><dt class="text-gray-400">Confirmada por</dt><dd class="font-medium">{{ compra.confirmada_por?.nombre }} - {{ compra.confirmada_at }}</dd></div>
                        <div v-if="compra.observaciones" class="sm:col-span-2"><dt class="text-gray-400">Observaciones</dt><dd>{{ compra.observaciones }}</dd></div>
                    </dl>
                </div>

                <!-- Líneas de compra -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Productos comprados</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-xs text-gray-400 uppercase border-b border-gray-100">
                                <tr>
                                    <th class="pb-2 text-left">Producto</th>
                                    <th class="pb-2 text-right">Cant.</th>
                                    <th class="pb-2 text-right hidden sm:table-cell">Costo unit.</th>
                                    <th class="pb-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="d in compra.detalles" :key="d.id">
                                    <td class="py-2">
                                        <div class="font-medium">{{ d.descripcion_producto ?? d.producto?.nombre ?? '—' }}</div>
                                        <div v-if="d.observaciones" class="text-xs text-gray-400">{{ d.observaciones }}</div>
                                    </td>
                                    <td class="py-2 text-right">{{ d.cantidad }}</td>
                                    <td class="py-2 text-right hidden sm:table-cell">{{ formatCLP(d.costo_unitario) }}</td>
                                    <td class="py-2 text-right font-medium">{{ formatCLP(d.costo_total) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="pt-3 text-right font-semibold text-gray-600">Total:</td>
                                    <td class="pt-3 text-right font-bold text-lg">{{ formatCLP(compra.total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Documentos -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Documentos adjuntos</h3>
                    <div v-if="compra.documentos?.length" class="space-y-2 mb-4">
                        <div v-for="doc in compra.documentos" :key="doc.id" class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ doc.tipo_mime?.includes('pdf') ? '📄' : '🖼️' }}</span>
                                <div>
                                    <div class="text-sm font-medium">{{ doc.nombre_archivo }}</div>
                                    <div class="text-xs text-gray-400">{{ formatBytes(doc.tamanio_bytes) }} · {{ doc.subido_por?.nombre }}</div>
                                </div>
                            </div>
                            <a :href="route('compras.documentos.download', [compra.id, doc.id])" class="btn-sm">Descargar</a>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-400 mb-4">Sin documentos adjuntos.</div>

                    <!-- Subir documento -->
                    <form @submit.prevent="subirDoc" class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="label">Adjuntar documento (PDF/JPG, máx. 10MB)</label>
                            <input type="file" @change="e => docFile = e.target.files[0]" accept=".pdf,.jpg,.jpeg,.png" class="text-sm text-gray-600" />
                        </div>
                        <button type="submit" :disabled="!docFile" class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm rounded-lg disabled:opacity-40">
                            Subir
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel derecho: acciones -->
            <div class="space-y-4">
                <!-- Anulación info -->
                <div v-if="compra.estado_operacional === 'anulada'" class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="font-semibold text-red-700 mb-2">Compra anulada</div>
                    <dl class="text-sm space-y-1">
                        <div><dt class="text-red-400 text-xs">Motivo</dt><dd class="text-red-700">{{ compra.motivo_anulacion }}</dd></div>
                        <div><dt class="text-red-400 text-xs">Anulada el</dt><dd>{{ compra.anulada_at }}</dd></div>
                    </dl>
                </div>

                <!-- Confirmar (solo borrador) -->
                <div v-if="compra.estado_operacional === 'borrador'" class="bg-white rounded-xl border border-gray-100 p-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Confirmar compra</h4>
                    <p class="text-xs text-gray-500 mb-3">Al confirmar, el stock aumentará automáticamente en Isla de Maipo.</p>
                    <button @click="confirmar" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors">
                        ✓ Confirmar y actualizar stock
                    </button>
                </div>

                <!-- Anular (solo admin, confirmada) -->
                <div v-if="compra.estado_operacional === 'confirmada' && isAdmin" class="bg-white rounded-xl border border-gray-100 p-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Anular compra</h4>
                    <textarea v-model="motivoAnulacion" placeholder="Motivo de anulación (mínimo 10 caracteres)..." rows="2"
                        class="input w-full text-xs resize-none mb-2"></textarea>
                    <button @click="anular" :disabled="motivoAnulacion.length < 10"
                        class="w-full py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition-colors disabled:opacity-40">
                        Anular compra
                    </button>
                </div>

                <!-- Estado de pago -->
                <div v-if="compra.estado_operacional === 'confirmada'" class="bg-white rounded-xl border border-gray-100 p-4">
                    <h4 class="font-semibold text-gray-700 mb-3">Estado de pago</h4>
                    <form @submit.prevent="actualizarPago" class="space-y-3">
                        <select v-model="pagoForm.estado_pago" class="input w-full">
                            <option value="pendiente">Pendiente</option>
                            <option value="pagada">Pagada</option>
                        </select>
                        <input v-if="pagoForm.estado_pago === 'pagada'" v-model="pagoForm.fecha_pago" type="date" class="input w-full" />
                        <input v-model="pagoForm.observaciones_pago" type="text" placeholder="Observaciones de pago..." class="input w-full" />
                        <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">
                            Actualizar pago
                        </button>
                    </form>
                </div>

                <!-- Resumen -->
                <div class="bg-gray-50 rounded-xl p-4 text-sm">
                    <div class="font-semibold text-gray-700 mb-2">Resumen</div>
                    <div class="flex justify-between"><span class="text-gray-500">Líneas</span><span>{{ compra.detalles?.length }}</span></div>
                    <div class="flex justify-between font-bold text-lg mt-2"><span>Total</span><span>{{ formatCLP(compra.total) }}</span></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ compra: Object });

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.roles?.some(r => r.name === 'administrador'));

const motivoAnulacion = ref('');
const docFile = ref(null);
const pagoForm = ref({
    estado_pago: props.compra.estado_pago,
    fecha_pago: props.compra.fecha_pago ?? '',
    observaciones_pago: props.compra.observaciones_pago ?? '',
});

function confirmar() {
    if (!confirm('¿Confirmar esta compra? El stock se actualizará automáticamente.')) return;
    router.post(route('compras.confirmar', props.compra.id), {}, { preserveScroll: true });
}

function anular() {
    if (!confirm('¿Anular esta compra? Se revertirá el stock si es posible.')) return;
    router.post(route('compras.anular', props.compra.id), { motivo_anulacion: motivoAnulacion.value }, { preserveScroll: true });
}

function subirDoc() {
    if (!docFile.value) return;
    const data = new FormData();
    data.append('documento', docFile.value);
    router.post(route('compras.documentos.store', props.compra.id), data, { preserveScroll: true });
}

function actualizarPago() {
    router.patch(route('compras.pago', props.compra.id), pagoForm.value, { preserveScroll: true });
}

function formatCLP(n) { return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(n ?? 0); }
function formatBytes(b) { if (!b) return ''; if (b < 1024) return `${b} B`; if (b < 1048576) return `${(b/1024).toFixed(1)} KB`; return `${(b/1048576).toFixed(1)} MB`; }
function estadoLabel(e) { return { borrador:'Borrador', confirmada:'Confirmada', anulada:'Anulada' }[e] ?? e; }
function estadoBadge(e) { return { borrador:'bg-gray-100 text-gray-600', confirmada:'bg-green-100 text-green-700', anulada:'bg-red-100 text-red-700' }[e] ?? 'bg-gray-100'; }
</script>

<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
.btn-sm { @apply px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
