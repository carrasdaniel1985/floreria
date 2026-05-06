<template>
    <Head :title="usuario ? 'Editar usuario' : 'Nuevo usuario'" />
    <AuthenticatedLayout>
        <PageHeader :title="usuario ? 'Editar usuario' : 'Nuevo usuario'"
            :subtitle="usuario ? `${usuario.nombre} ${usuario.apellido}` : 'Completa los datos del nuevo usuario'">
            <template #actions>
                <Link :href="route('usuarios.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-100 p-6 space-y-5">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Nombre <span class="text-red-500">*</span></label>
                        <input v-model="form.nombre" type="text" required class="input w-full" />
                        <p v-if="form.errors.nombre" class="error">{{ form.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="label">Apellido <span class="text-red-500">*</span></label>
                        <input v-model="form.apellido" type="text" required class="input w-full" />
                        <p v-if="form.errors.apellido" class="error">{{ form.errors.apellido }}</p>
                    </div>
                </div>

                <div>
                    <label class="label">Correo electrónico <span class="text-red-500">*</span></label>
                    <input v-model="form.email" type="email" required class="input w-full" />
                    <p v-if="form.errors.email" class="error">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="label">Teléfono</label>
                    <input v-model="form.telefono" type="tel" class="input w-full" placeholder="+56 9 1234 5678" />
                </div>

                <div v-if="!usuario">
                    <label class="label">Contraseña inicial <span class="text-red-500">*</span></label>
                    <input v-model="form.password" type="password" required class="input w-full" />
                    <p class="text-xs text-gray-400 mt-1">El usuario deberá cambiarla en su primer ingreso.</p>
                    <p v-if="form.errors.password" class="error">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="label">Roles <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <label v-for="rol in roles" :key="rol.id" class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" :value="rol.name" v-model="form.roles"
                                class="rounded border-gray-300 text-rose-600 focus:ring-rose-400" />
                            <span class="text-sm capitalize">{{ rol.name }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.roles" class="error">{{ form.errors.roles }}</p>
                </div>

                <div>
                    <label class="label">Sucursales <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <label v-for="s in sucursales" :key="s.id" class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" :value="s.id" v-model="form.sucursales"
                                class="rounded border-gray-300 text-rose-600 focus:ring-rose-400" />
                            <span class="text-sm">{{ s.nombre }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.sucursales" class="error">{{ form.errors.sucursales }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.activo" id="activo"
                        class="rounded border-gray-300 text-rose-600 focus:ring-rose-400" />
                    <label for="activo" class="text-sm text-gray-700">Usuario activo</label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
                        {{ form.processing ? 'Guardando...' : (usuario ? 'Actualizar' : 'Crear usuario') }}
                    </button>
                    <Link :href="route('usuarios.index')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ usuario: Object, roles: Array, sucursales: Array });

const form = useForm({
    nombre:    props.usuario?.nombre ?? '',
    apellido:  props.usuario?.apellido ?? '',
    email:     props.usuario?.email ?? '',
    telefono:  props.usuario?.telefono ?? '',
    password:  '',
    roles:     props.usuario?.roles?.map(r => r.name) ?? [],
    sucursales: props.usuario?.sucursales?.map(s => s.id) ?? [],
    activo:    props.usuario?.activo ?? true,
});

function submit() {
    if (props.usuario) {
        form.put(route('usuarios.update', props.usuario.id));
    } else {
        form.post(route('usuarios.store'));
    }
}
</script>

<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.error { @apply text-xs text-red-600 mt-1; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
