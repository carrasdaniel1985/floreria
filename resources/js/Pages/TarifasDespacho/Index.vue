<template>
    <Head title="Tarifas de despacho" />
    <AuthenticatedLayout>
        <PageHeader title="Tarifas de Despacho" subtitle="Precios de despacho por comuna y sucursal">
            <template #actions>
                <Link :href="route('tarifas-despacho.create')" class="btn-primary">+ Nueva tarifa</Link>
            </template>
        </PageHeader>

        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <select v-model="filtros.sucursal_id" @change="buscar" class="input">
                <option value="">Todas las sucursales</option>
                <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
            </select>
            <select v-model="filtros.activa" @change="buscar" class="input">
                <option value="">Todas</option>
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Sucursal</th>
                            <th class="px-4 py-3 text-left">Comuna</th>
                            <th class="px-4 py-3 text-right">Precio</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Desde</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="t in tarifas.data" :key="t.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ t.sucursal?.nombre }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ t.comuna?.nombre }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ formatCLP(t.precio) }}</td>
                            <td class="px-4 py-3 text-center text-gray-400 hidden sm:table-cell">{{ t.fecha_desde }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="t.activa ? 'badge-green' : 'badge-red'">{{ t.activa ? 'Vigente' : 'Inactiva' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('tarifas-despacho.edit', t.id)" class="btn-sm">Editar</Link>
                            </td>
                        </tr>
                        <tr v-if="!tarifas.data.length">
                            <td colspan="6" class="text-center py-10 text-gray-400">No hay tarifas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ tarifas: Object, sucursales: Array, comunas: Array, filtros: Object });
const filtros = ref({ ...props.filtros });
function buscar() { router.get(route('tarifas-despacho.index'), filtros.value, { preserveState: true, replace: true }); }
function formatCLP(n) { return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(n ?? 0); }
</script>
<style scoped>
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.btn-primary { @apply inline-flex items-center gap-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors; }
.badge-green { @apply inline-block px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full; }
.badge-red { @apply inline-block px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full; }
.btn-sm { @apply px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
