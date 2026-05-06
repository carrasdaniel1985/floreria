<template>
    <Head title="Usuarios" />
    <AuthenticatedLayout>
        <PageHeader title="Usuarios" subtitle="Gestión de usuarios internos del sistema">
            <template #actions>
                <Link :href="route('usuarios.create')" class="btn-primary">+ Nuevo usuario</Link>
            </template>
        </PageHeader>

        <!-- Filtros -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
            <input v-model="filtros.buscar" @input="buscar" type="text" placeholder="Buscar por nombre o correo..."
                class="input flex-1 min-w-[180px]" />
            <select v-model="filtros.rol" @change="buscar" class="input">
                <option value="">Todos los roles</option>
                <option v-for="rol in roles" :key="rol.id" :value="rol.name">{{ capitalize(rol.name) }}</option>
            </select>
            <select v-model="filtros.activo" @change="buscar" class="input">
                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Nombre</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Correo</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Roles</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Último acceso</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="u in usuarios.data" :key="u.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ u.nombre }} {{ u.apellido }}</div>
                                <div class="text-xs text-gray-400 sm:hidden">{{ u.email }}</div>
                                <div class="text-xs text-gray-400 md:hidden">{{ u.roles.map(r=>capitalize(r.name)).join(', ') }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ u.email }}</td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span v-for="r in u.roles" :key="r.id" class="inline-block mr-1 px-2 py-0.5 text-xs bg-rose-100 text-rose-700 rounded-full">
                                    {{ capitalize(r.name) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">
                                {{ u.last_login_at ? formatDate(u.last_login_at) : 'Nunca' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="u.activo ? 'badge-green' : 'badge-red'">
                                    {{ u.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('usuarios.edit', u.id)" class="btn-sm">Editar</Link>
                                    <button @click="toggleActivo(u)" class="btn-sm" :class="u.activo ? 'btn-sm-danger' : 'btn-sm-success'">
                                        {{ u.activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                    <button @click="resetPassword(u)" class="btn-sm">Reset pwd</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!usuarios.data.length">
                            <td colspan="6" class="text-center py-10 text-gray-400">No hay usuarios que mostrar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="usuarios.last_page > 1" class="px-4 py-3 border-t border-gray-50 flex gap-2 flex-wrap">
                <Link v-for="link in usuarios.links" :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-rose-600 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-40 pointer-events-none' : '']"
                />
            </div>
        </div>

        <!-- Modal reset password -->
        <div v-if="resetResult" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4" @click.self="resetResult=null">
            <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl">
                <h3 class="font-semibold mb-3">Contraseña temporal</h3>
                <p class="text-sm text-gray-600 mb-3">La contraseña temporal para <strong>{{ resetResult.nombre }}</strong> es:</p>
                <div class="bg-gray-100 rounded-lg px-4 py-3 font-mono text-center text-lg font-bold tracking-widest">{{ resetResult.password }}</div>
                <p class="text-xs text-gray-400 mt-3">El usuario deberá cambiarla al próximo ingreso.</p>
                <button @click="resetResult=null" class="mt-4 w-full btn-primary">Cerrar</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ usuarios: Object, roles: Array, sucursales: Array, filtros: Object });

const filtros = ref({ ...props.filtros });
const resetResult = ref(null);

function buscar() {
    router.get(route('usuarios.index'), filtros.value, { preserveState: true, replace: true });
}

function toggleActivo(u) {
    if (!confirm(`¿${u.activo ? 'Desactivar' : 'Activar'} a ${u.nombre} ${u.apellido}?`)) return;
    router.patch(route('usuarios.toggle-activo', u.id), {}, { preserveScroll: true });
}

async function resetPassword(u) {
    if (!confirm(`¿Resetear contraseña de ${u.nombre} ${u.apellido}?`)) return;
    router.post(route('usuarios.reset-password', u.id), {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            const msg = page.props.flash?.success ?? '';
            const match = msg.match(/Contraseña temporal: (.+)/);
            if (match) resetResult.value = { nombre: u.nombre, password: match[1] };
        }
    });
}

function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : s; }
function formatDate(d) { return d ? new Date(d).toLocaleDateString('es-CL', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' }) : ''; }
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
