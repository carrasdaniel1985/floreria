<template>
    <Head title="Mermas" />
    <AuthenticatedLayout>
        <PageHeader title="Mermas y Pérdidas" subtitle="Registro de productos vencidos, dañados o descartados">
            <template #actions>
                <Link :href="route('mermas.create')" class="btn-primary">+ Registrar merma</Link>
            </template>
        </PageHeader>

        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <select v-model="filtros.motivo" @change="buscar" class="input">
                <option value="">Todos los motivos</option>
                <option v-for="m in motivos" :key="m" :value="m" class="capitalize">{{ m }}</option>
            </select>
            <input v-model="filtros.fecha_desde" @change="buscar" type="date" class="input" />
            <input v-model="filtros.fecha_hasta" @change="buscar" type="date" class="input" />
        </div>

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Fecha</th>
                            <th class="px-4 py-3 text-left">Producto</th>
                            <th class="px-4 py-3 text-center">Cantidad</th>
                            <th class="px-4 py-3 text-center">Motivo</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Observaciones</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="m in mermas.data" :key="m.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ formatDate(m.created_at) }}</td>
                            <td class="px-4 py-3 font-medium">{{ m.producto?.nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ m.cantidad }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="motivoBadge(m.motivo)" class="capitalize">{{ m.motivo }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ m.observaciones ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-400 hidden lg:table-cell">{{ m.creador?.nombre }}</td>
                        </tr>
                        <tr v-if="!mermas.data.length">
                            <td colspan="6" class="text-center py-10 text-gray-400">No hay mermas registradas.</td>
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

const props = defineProps({ mermas: Object, filtros: Object });
const filtros = ref({ ...props.filtros });
const motivos = ['merma', 'vencido', 'dañado', 'descartado', 'otro'];

function buscar() { router.get(route('mermas.index'), filtros.value, { preserveState: true, replace: true }); }
function formatDate(d) { return d ? new Date(d).toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' }) : ''; }
function motivoBadge(m) {
    return {
        merma: 'inline-block px-2 py-0.5 text-xs bg-orange-100 text-orange-700 rounded-full',
        vencido: 'inline-block px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded-full',
        dañado: 'inline-block px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full',
        descartado: 'inline-block px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full',
        otro: 'inline-block px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded-full',
    }[m] ?? 'inline-block px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full';
}
</script>
<style scoped>
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.btn-primary { @apply inline-flex items-center gap-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors; }
</style>
