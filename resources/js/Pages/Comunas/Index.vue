<template>
    <Head title="Comunas" />
    <AuthenticatedLayout>
        <PageHeader title="Comunas" subtitle="Comunas disponibles para tarifas de despacho">
            <template #actions>
                <Link :href="route('tarifas-despacho.index')" class="btn-outline">Ver tarifas</Link>
                <Link :href="route('comunas.create')" class="btn-primary">+ Nueva comuna</Link>
            </template>
        </PageHeader>

        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <input v-model="filtros.buscar" @input="buscar" type="text" placeholder="Buscar..." class="input flex-1 min-w-[150px]" />
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
                            <th class="px-4 py-3 text-left">Comuna</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Región</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="c in comunas.data" :key="c.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ c.nombre }}</td>
                            <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ c.region ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="c.activa ? 'badge-green' : 'badge-red'">{{ c.activa ? 'Activa' : 'Inactiva' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right flex justify-end gap-2">
                                <Link :href="route('comunas.edit', c.id)" class="btn-sm">Editar</Link>
                                <button @click="toggle(c)" class="btn-sm" :class="c.activa ? 'btn-sm-danger' : 'btn-sm-success'">
                                    {{ c.activa ? 'Desactivar' : 'Activar' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!comunas.data.length">
                            <td colspan="4" class="text-center py-10 text-gray-400">No hay comunas.</td>
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

const props = defineProps({ comunas: Object, filtros: Object });
const filtros = ref({ ...props.filtros });
function buscar() { router.get(route('comunas.index'), filtros.value, { preserveState: true, replace: true }); }
function toggle(c) {
    if (!confirm(`¿${c.activa ? 'Desactivar' : 'Activar'} "${c.nombre}"?`)) return;
    router.patch(route('comunas.toggle-activa', c.id), {}, { preserveScroll: true });
}
</script>
<style scoped>
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.btn-primary { @apply inline-flex items-center gap-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
.badge-green { @apply inline-block px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full; }
.badge-red { @apply inline-block px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full; }
.btn-sm { @apply px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-50 text-gray-600 transition-colors; }
.btn-sm-danger { @apply border-red-200 text-red-600 hover:bg-red-50; }
.btn-sm-success { @apply border-green-200 text-green-600 hover:bg-green-50; }
</style>
