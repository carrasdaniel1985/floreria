<template>
    <Head title="Productos" />
    <AuthenticatedLayout>
        <PageHeader title="Productos" subtitle="Catálogo de productos y servicios">
            <template #actions>
                <Link v-if="isAdmin" :href="route('productos.create')" class="btn-primary">+ Nuevo producto</Link>
            </template>
        </PageHeader>

        <!-- Filtros -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <input v-model="filtros.buscar" @input="buscar" type="text" placeholder="Buscar por nombre o SKU..." class="input flex-1 min-w-[180px]" />
            <select v-model="filtros.categoria_id" @change="buscar" class="input">
                <option value="">Todas las categorías</option>
                <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
            </select>
            <select v-model="filtros.tipo" @change="buscar" class="input">
                <option value="">Todos los tipos</option>
                <option value="simple">Simple</option>
                <option value="servicio">Servicio</option>
            </select>
            <select v-model="filtros.activo" @change="buscar" class="input">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Producto</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Categoría</th>
                            <th class="px-4 py-3 text-right hidden sm:table-cell">Precio venta</th>
                            <th class="px-4 py-3 text-center hidden lg:table-cell">Stock</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right" v-if="isAdmin">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="p in productos.data" :key="p.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <Link :href="route('productos.show', p.id)" class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center text-sm shrink-0">
                                        <img v-if="p.foto_principal?.path" :src="`/storage/${p.foto_principal.path}`" class="w-9 h-9 rounded-lg object-cover" />
                                        <span v-else>🌸</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ p.nombre }}</div>
                                        <div class="text-xs text-gray-400">{{ p.sku ?? 'Sin SKU' }} · {{ tipoLabel(p.tipo) }}</div>
                                    </div>
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ p.categoria?.nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium hidden sm:table-cell">
                                {{ p.precio_vigente ? formatCLP(p.precio_vigente.precio) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <span v-if="p.maneja_stock" :class="p.stock_actual <= p.stock_minimo ? 'badge-red' : 'badge-green'">
                                    {{ p.stock_actual }}
                                </span>
                                <span v-else class="text-gray-300 text-xs">N/A</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="p.activo ? 'badge-green' : 'badge-red'">{{ p.activo ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right" v-if="isAdmin">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('productos.edit', p.id)" class="btn-sm">Editar</Link>
                                    <button @click="toggleActivo(p)" class="btn-sm" :class="p.activo ? 'btn-sm-danger' : 'btn-sm-success'">
                                        {{ p.activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!productos.data.length">
                            <td :colspan="isAdmin ? 6 : 5" class="text-center py-10 text-gray-400">No hay productos que mostrar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="productos.last_page > 1" class="px-4 py-3 border-t border-gray-50 flex gap-2 flex-wrap">
                <Link v-for="link in productos.links" :key="link.label"
                    :href="link.url ?? '#'" v-html="link.label"
                    :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-rose-600 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-40 pointer-events-none' : '']"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ productos: Object, categorias: Array, filtros: Object });
const filtros = ref({ ...props.filtros });

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.roles?.some(r => r.name === 'administrador'));

function buscar() { router.get(route('productos.index'), filtros.value, { preserveState: true, replace: true }); }
function toggleActivo(p) {
    if (!confirm(`¿${p.activo ? 'Desactivar' : 'Activar'} "${p.nombre}"?`)) return;
    router.patch(route('productos.toggle-activo', p.id), {}, { preserveScroll: true });
}
function formatCLP(n) { return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(n); }
function tipoLabel(t) { return { simple: 'Simple', servicio: 'Servicio', compuesto: 'Compuesto' }[t] ?? t; }
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
