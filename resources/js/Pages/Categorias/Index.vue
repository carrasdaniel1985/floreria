<template>
    <Head title="Categorías" />
    <AuthenticatedLayout>
        <PageHeader title="Categorías" subtitle="Organización del catálogo de productos">
            <template #actions>
                <Link :href="route('categorias.create')" class="btn-primary">+ Nueva categoría</Link>
            </template>
        </PageHeader>

        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <input v-model="filtros.buscar" @input="buscar" type="text" placeholder="Buscar..." class="input flex-1 min-w-[180px]" />
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
                            <th class="px-4 py-3 text-left">Nombre</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Orden</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Productos</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="c in categorias.data" :key="c.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img v-if="c.imagen" :src="`/storage/${c.imagen}`" class="w-8 h-8 rounded object-cover" />
                                    <div v-else class="w-8 h-8 bg-rose-100 rounded flex items-center justify-center text-sm">🌸</div>
                                    <span class="font-medium text-gray-900">{{ c.nombre }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 hidden sm:table-cell">{{ c.orden }}</td>
                            <td class="px-4 py-3 text-center text-gray-500 hidden sm:table-cell">{{ c.productos_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="c.activa ? 'badge-green' : 'badge-red'">{{ c.activa ? 'Activa' : 'Inactiva' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('categorias.edit', c.id)" class="btn-sm">Editar</Link>
                                    <button @click="toggleActiva(c)" class="btn-sm" :class="c.activa ? 'btn-sm-danger' : 'btn-sm-success'">
                                        {{ c.activa ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!categorias.data.length">
                            <td colspan="5" class="text-center py-10 text-gray-400">No hay categorías.</td>
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

const props = defineProps({ categorias: Object, filtros: Object });
const filtros = ref({ ...props.filtros });

function buscar() { router.get(route('categorias.index'), filtros.value, { preserveState: true, replace: true }); }
function toggleActiva(c) {
    if (!confirm(`¿${c.activa ? 'Desactivar' : 'Activar'} categoría "${c.nombre}"?`)) return;
    router.patch(route('categorias.toggle-activa', c.id), {}, { preserveScroll: true });
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
