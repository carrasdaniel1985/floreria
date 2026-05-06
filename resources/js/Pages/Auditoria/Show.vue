<template>
    <Head title="Evento de auditoría" />
    <AuthenticatedLayout>
        <PageHeader title="Evento de auditoría">
            <template #actions>
                <Link :href="route('auditoria.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>

        <div class="max-w-3xl space-y-5">
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-400">Fecha/Hora</dt><dd class="font-medium">{{ formatDate(evento.created_at) }}</dd></div>
                    <div><dt class="text-gray-400">Usuario</dt><dd class="font-medium">{{ evento.usuario?.nombre ?? 'Sistema' }} {{ evento.usuario?.apellido }}</dd></div>
                    <div><dt class="text-gray-400">Módulo</dt><dd><span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">{{ evento.modulo }}</span></dd></div>
                    <div><dt class="text-gray-400">Acción</dt><dd class="font-medium">{{ evento.accion }}</dd></div>
                    <div v-if="evento.entidad_tipo"><dt class="text-gray-400">Entidad</dt><dd class="font-medium">{{ evento.entidad_tipo }} #{{ evento.entidad_id }}</dd></div>
                    <div v-if="evento.ip_address"><dt class="text-gray-400">IP</dt><dd class="font-mono text-xs">{{ evento.ip_address }}</dd></div>
                    <div v-if="evento.descripcion" class="sm:col-span-2"><dt class="text-gray-400">Descripción</dt><dd>{{ evento.descripcion }}</dd></div>
                    <div v-if="evento.motivo" class="sm:col-span-2"><dt class="text-gray-400">Motivo</dt><dd>{{ evento.motivo }}</dd></div>
                </dl>
            </div>

            <div v-if="evento.cambios?.length" class="bg-white rounded-xl border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Cambios registrados</h3>
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-400 uppercase border-b border-gray-100">
                        <tr>
                            <th class="pb-2 text-left">Campo</th>
                            <th class="pb-2 text-left">Valor anterior</th>
                            <th class="pb-2 text-left">Valor nuevo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="c in evento.cambios" :key="c.id">
                            <td class="py-2 font-mono text-xs text-gray-600">{{ c.campo }}</td>
                            <td class="py-2 text-red-600 text-xs">{{ c.valor_anterior ?? '—' }}</td>
                            <td class="py-2 text-green-600 text-xs font-medium">{{ c.valor_nuevo ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

defineProps({ evento: Object });

function formatDate(d) {
    return d ? new Date(d).toLocaleString('es-CL', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' }) : '';
}
</script>
<style scoped>
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
