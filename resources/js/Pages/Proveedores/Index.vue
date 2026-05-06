<template>
    <Head title="Proveedores" />
    <AuthenticatedLayout>
        <PageHeader title="Proveedores" subtitle="Registro de proveedores de la florería">
            <template #actions>
                <Link :href="route('proveedores.create')" class="btn-primary">+ Nuevo proveedor</Link>
            </template>
        </PageHeader>

        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <input v-model="filtros.buscar" @input="buscar" type="text" placeholder="Buscar por nombre o RUT..." class="input flex-1 min-w-[180px]" />
            <select v-model="filtros.activo" @change="buscar" class="input">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Proveedor</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">RUT</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Contacto</th>
                            <th class="px-4 py-3 text-center hidden lg:table-cell">Compras</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="p in proveedores.data" :key="p.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ p.nombre }}</div>
                                <div class="text-xs text-gray-400 sm:hidden">{{ p.rut }}</div>
                                <div v-if="p.tipos_productos" class="text-xs text-gray-400">{{ p.tipos_productos }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ p.rut }}</td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div class="text-gray-600">{{ p.telefono ?? '—' }}</div>
                                <div class="text-xs text-gray-400">{{ p.email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 hidden lg:table-cell">{{ p.compras_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="p.activo ? 'badge-green' : 'badge-red'">{{ p.activo ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('proveedores.edit', p.id)" class="btn-sm">Editar</Link>
                                    <button @click="toggleActivo(p)" class="btn-sm" :class="p.activo ? 'btn-sm-danger' : 'btn-sm-success'">
                                        {{ p.activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!proveedores.data.length">
                            <td colspan="6" class="text-center py-10 text-gray-400">No hay proveedores.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="proveedores.last_page > 1" class="px-4 py-3 border-t border-gray-50 flex gap-2 flex-wrap">
                <Link v-for="link in proveedores.links" :key="link.label"
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

const props = defineProps({ proveedores: Object, filtros: Object });
const filtros = ref({ ...props.filtros });

function buscar() { router.get(route('proveedores.index'), filtros.value, { preserveState: true, replace: true }); }
function toggleActivo(p) {
    if (!confirm(`¿${p.activo ? 'Desactivar' : 'Activar'} a "${p.nombre}"?`)) return;
    router.patch(route('proveedores.toggle-activo', p.id), {}, { preserveScroll: true });
}
</script>
<style scoped>
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.btn-primary { @apply inline-flex items-center gap-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors; }
.badge-green { @apply inline-block px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full; }
.badge-red { @apply inline-block px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full; }
.btn-sm { @apply px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-50 text-gray-600 transition-colors; }
.btn-sm-danger { @apply border-red-200 text-red-600 hover:bg-red-50; }
.btn-sm-success { @apply border-green-200 text-green-600 hover:bg-green-50; }
</style>
