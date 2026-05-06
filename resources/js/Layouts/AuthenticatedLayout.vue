<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-4">
                        <Link :href="route('dashboard')" class="flex items-center gap-2 shrink-0">
                            <span class="text-lg font-bold text-rose-600">🌸 Florería</span>
                        </Link>
                        <div class="hidden md:flex gap-0.5">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Inicio</NavLink>
                            <NavLink v-if="hasRole('administrador')" :href="route('usuarios.index')" :active="route().current('usuarios.*')">Usuarios</NavLink>
                            <NavLink v-if="hasRole('administrador')" :href="route('categorias.index')" :active="route().current('categorias.*')">Categorías</NavLink>
                            <NavLink :href="route('productos.index')" :active="route().current('productos.*')">Productos</NavLink>
                            <NavLink v-if="hasRole('administrador')" :href="route('comunas.index')" :active="route().current('comunas.*') || route().current('tarifas-despacho.*')">Despacho</NavLink>
                            <NavLink v-if="hasRole(['administrador','comprador'])" :href="route('proveedores.index')" :active="route().current('proveedores.*')">Proveedores</NavLink>
                            <NavLink v-if="hasRole(['administrador','comprador'])" :href="route('compras.index')" :active="route().current('compras.*')">Compras</NavLink>
                            <NavLink v-if="hasRole(['administrador','comprador'])" :href="route('mermas.index')" :active="route().current('mermas.*')">Mermas</NavLink>
                            <NavLink v-if="hasRole('administrador')" :href="route('auditoria.index')" :active="route().current('auditoria.*')">Auditoría</NavLink>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden sm:block text-sm text-gray-500">{{ $page.props.auth.user.nombre }}</span>
                        <Link :href="route('logout')" method="post" as="button" class="text-sm text-gray-400 hover:text-rose-600 transition-colors px-2 py-1">
                            Salir
                        </Link>
                        <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Mobile menu -->
            <div v-show="mobileOpen" class="md:hidden border-t border-gray-100">
                <div class="px-4 py-3 space-y-1 bg-white">
                    <a :href="route('dashboard')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Inicio</a>
                    <a v-if="hasRole('administrador')" :href="route('usuarios.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Usuarios</a>
                    <a v-if="hasRole('administrador')" :href="route('categorias.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Categorías</a>
                    <a :href="route('productos.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Productos</a>
                    <a v-if="hasRole('administrador')" :href="route('comunas.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Comunas</a>
                    <a v-if="hasRole('administrador')" :href="route('tarifas-despacho.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Tarifas Despacho</a>
                    <a v-if="hasRole(['administrador','comprador'])" :href="route('proveedores.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Proveedores</a>
                    <a v-if="hasRole(['administrador','comprador'])" :href="route('compras.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Compras</a>
                    <a v-if="hasRole(['administrador','comprador'])" :href="route('mermas.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Mermas</a>
                    <a v-if="hasRole('administrador')" :href="route('auditoria.index')" class="block py-2 px-3 text-sm rounded hover:bg-gray-50">Auditoría</a>
                </div>
            </div>
        </nav>

        <!-- Flash messages -->
        <div v-if="flash?.success || flash?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div v-if="flash?.success" class="flex items-center justify-between bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                <span>{{ flash.success }}</span>
                <button @click="$page.props.flash.success = null" class="ml-4 text-green-500 hover:text-green-700">✕</button>
            </div>
            <div v-if="flash?.error" class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                {{ flash.error }}
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

const mobileOpen = ref(false);
const page = usePage();

const flash = computed(() => page.props.flash);

function hasRole(roles) {
    const userRoles = page.props.auth?.user?.roles?.map(r => r.name) ?? [];
    if (Array.isArray(roles)) return roles.some(r => userRoles.includes(r));
    return userRoles.includes(roles);
}
</script>
