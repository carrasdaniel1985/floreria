<template>
    <Head title="Compras" />
    <AuthenticatedLayout>
        <PageHeader title="Compras" subtitle="Registro y seguimiento de compras a proveedores">
            <template #actions>
                <Link :href="route('compras.create')" class="btn-primary">+ Nueva compra</Link>
            </template>
        </PageHeader>

        <!-- Filtros -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <select v-model="filtros.estado_operacional" @change="buscar" class="input">
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="confirmada">Confirmada</option>
                <option value="anulada">Anulada</option>
            </select>
            <select v-model="filtros.estado_pago" @change="buscar" class="input">
                <option value="">Todo el pago</option>
                <option value="pendiente">Pendiente</option>
                <option value="pagada">Pagada</option>
            </select>
            <select v-model="filtros.proveedor_id" @change="buscar" class="input">
                <option value="">Todos los proveedores</option>
                <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
            </select>
            <input v-model="filtros.fecha_desde" @change="buscar" type="date" class="input" />
            <input v-model="filtros.fecha_hasta" @change="buscar" type="date" class="input" />
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" v-model="filtros.sin_documento" @change="buscar" class="rounded text-rose-600" />
                Sin documento
            </label>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Proveedor</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Fecha</th>
                            <th class="px-4 py-3 text-right hidden sm:table-cell">Total</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-center hidden md:table-cell">Pago</th>
                            <th class="px-4 py-3 text-center hidden lg:table-cell">Doc.</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="c in compras.data" :key="c.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">#{{ c.id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ c.proveedor?.nombre ?? 'Sin proveedor' }}</div>
                                <div class="text-xs text-gray-400 sm:hidden">{{ c.fecha_compra }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ c.fecha_compra }}</td>
                            <td class="px-4 py-3 text-right font-medium hidden sm:table-cell">{{ formatCLP(c.total) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="estadoBadge(c.estado_operacional)">{{ estadoLabel(c.estado_operacional) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden md:table-cell">
                                <span :class="c.estado_pago === 'pagada' ? 'badge-green' : 'badge-yellow'">
                                    {{ c.estado_pago === 'pagada' ? 'Pagada' : 'Pendiente' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <span :class="c.documentos_count > 0 ? 'badge-green' : 'badge-gray'">
                                    {{ c.documentos_count > 0 ? '✓' : '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('compras.show', c.id)" class="btn-sm">Ver</Link>
                            </td>
                        </tr>
                        <tr v-if="!compras.data.length">
                            <td colspan="8" class="text-center py-10 text-gray-400">No hay compras que mostrar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="compras.last_page > 1" class="px-4 py-3 border-t border-gray-50 flex gap-2 flex-wrap">
                <Link v-for="link in compras.links" :key="link.label"
                    :href="link.url ?? '#'" v-html="link.label"
                    :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-rose-600 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-40 pointer-events-none' : '']"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ compras: Object, proveedores: Array, filtros: Object });
const filtros = ref({ ...props.filtros });

function buscar() { router.get(route('compras.index'), filtros.value, { preserveState: true, replace: true }); }
function formatCLP(n) { return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(n ?? 0); }
function estadoLabel(e) { return { borrador:'Borrador', confirmada:'Confirmada', anulada:'Anulada' }[e] ?? e; }
function estadoBadge(e) {
    return { borrador:'badge-gray', confirmada:'badge-green', anulada:'badge-red' }[e] ?? 'badge-gray';
}
</script>
<style scoped>
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.btn-primary { @apply inline-flex items-center gap-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors; }
.btn-sm { @apply px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-50 text-gray-600 transition-colors; }
.badge-green { @apply inline-block px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full; }
.badge-red { @apply inline-block px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full; }
.badge-yellow { @apply inline-block px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded-full; }
.badge-gray { @apply inline-block px-2 py-0.5 text-xs bg-gray-100 text-gray-500 rounded-full; }
</style>
