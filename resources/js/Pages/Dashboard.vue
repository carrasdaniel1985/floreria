<template>
    <Head title="Inicio" />
    <AuthenticatedLayout>
        <PageHeader title="Panel de Control" :subtitle="`Bienvenido, ${$page.props.auth.user.nombre}`" />

        <div v-if="stats" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <StatCard label="Usuarios activos" :value="stats.usuarios_activos" icon="👥" color="blue" :href="route('usuarios.index')" />
            <StatCard label="Productos activos" :value="stats.productos_activos" icon="🌸" color="rose" :href="route('productos.index')" />
            <StatCard label="Proveedores" :value="stats.proveedores_activos" icon="🚚" color="gray" :href="route('proveedores.index')" />
            <StatCard label="Compras pendientes pago" :value="stats.compras_pendientes" icon="💳" color="yellow" :href="route('compras.index', { estado_pago: 'pendiente' })" />
            <StatCard label="Compras sin documento" :value="stats.compras_sin_doc" icon="📄" color="red" :href="route('compras.index', { sin_documento: 1 })" />
            <StatCard label="Stock bajo mínimo" :value="stats.productos_bajo_stock" icon="⚠️" color="orange" :href="route('productos.index')" />
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4">Accesos rápidos</h2>
                <div class="grid grid-cols-2 gap-3">
                    <QuickLink :href="route('compras.create')" icon="📦" label="Nueva compra" v-if="hasRole(['administrador','comprador'])" />
                    <QuickLink :href="route('mermas.create')" icon="🗑️" label="Registrar merma" v-if="hasRole(['administrador','comprador'])" />
                    <QuickLink :href="route('productos.create')" icon="🌸" label="Nuevo producto" v-if="hasRole('administrador')" />
                    <QuickLink :href="route('proveedores.create')" icon="🚚" label="Nuevo proveedor" v-if="hasRole(['administrador','comprador'])" />
                    <QuickLink :href="route('usuarios.create')" icon="👤" label="Nuevo usuario" v-if="hasRole('administrador')" />
                    <QuickLink :href="route('auditoria.index')" icon="📋" label="Ver auditoría" v-if="hasRole('administrador')" />
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-3">Tu perfil</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Nombre</dt>
                        <dd class="font-medium">{{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Correo</dt>
                        <dd class="font-medium">{{ $page.props.auth.user.email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Roles</dt>
                        <dd class="font-medium capitalize">{{ roles.join(', ') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

defineProps({ stats: Object });

const page = usePage();
const roles = computed(() => page.props.auth?.user?.roles?.map(r => r.name) ?? []);

function hasRole(r) {
    if (Array.isArray(r)) return r.some(x => roles.value.includes(x));
    return roles.value.includes(r);
}

// Inline sub-components
const StatCard = {
    props: ['label', 'value', 'icon', 'color', 'href'],
    template: `
        <a :href="href" class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-sm transition-shadow block">
            <div class="text-2xl mb-1">{{ icon }}</div>
            <div class="text-xl font-bold text-gray-800">{{ value ?? 0 }}</div>
            <div class="text-xs text-gray-500 leading-tight">{{ label }}</div>
        </a>
    `
};

const QuickLink = {
    props: ['href', 'icon', 'label'],
    template: `
        <a :href="href" class="flex items-center gap-3 px-4 py-3 bg-gray-50 hover:bg-rose-50 rounded-lg text-sm font-medium text-gray-700 hover:text-rose-700 transition-colors">
            <span class="text-xl">{{ icon }}</span>
            {{ label }}
        </a>
    `
};
</script>
