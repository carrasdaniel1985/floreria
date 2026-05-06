<template>
    <Head title="Auditoría" />
    <AuthenticatedLayout>
        <PageHeader title="Auditoría" subtitle="Registro de acciones críticas del sistema" />

        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <select v-model="filtros.modulo" @change="buscar" class="input">
                <option value="">Todos los módulos</option>
                <option v-for="m in modulos" :key="m" :value="m">{{ m }}</option>
            </select>
            <select v-model="filtros.user_id" @change="buscar" class="input">
                <option value="">Todos los usuarios</option>
                <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.nombre }} {{ u.apellido }}</option>
            </select>
            <input v-model="filtros.fecha_desde" @change="buscar" type="date" class="input" />
            <input v-model="filtros.fecha_hasta" @change="buscar" type="date" class="input" />
        </div>

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Fecha/Hora</th>
                            <th class="px-4 py-3 text-left">Usuario</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Módulo</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Acción</th>
                            <th class="px-4 py-3 text-left">Descripción</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="e in eventos.data" :key="e.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-400 font-mono whitespace-nowrap">{{ formatDate(e.created_at) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ e.usuario?.nombre ?? 'Sistema' }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">{{ e.modulo }}</span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ e.accion }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate">{{ e.descripcion }}</td>
                            <td class="px-4 py-3">
                                <Link :href="route('auditoria.show', e.id)" class="btn-sm">Ver</Link>
                            </td>
                        </tr>
                        <tr v-if="!eventos.data.length">
                            <td colspan="6" class="text-center py-10 text-gray-400">No hay eventos de auditoría.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="eventos.last_page > 1" class="px-4 py-3 border-t border-gray-50 flex gap-2 flex-wrap">
                <Link v-for="link in eventos.links" :key="link.label"
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

const props = defineProps({ eventos: Object, usuarios: Array, modulos: Array, filtros: Object });
const filtros = ref({ ...props.filtros });

function buscar() { router.get(route('auditoria.index'), filtros.value, { preserveState: true, replace: true }); }
function formatDate(d) {
    if (!d) return '';
    return new Date(d).toLocaleString('es-CL', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
}
</script>
<style scoped>
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.btn-sm { @apply px-2 py-1 text-xs border border-gray-200 rounded hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
