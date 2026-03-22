<script setup>

defineProps({
    valor: {
        type: String,
    },
    data: {
        type: String,
    },
    descricao: {
        type: String,
    },
    tipo: {
        type: String, // credito ou debito
    },
    de_user: {
        type: String,
    },
    para_user: {
        type: String,
    },
});
</script>

<template>
    <div class="p-4 border rounded-lg bg-white shadow-sm w-full">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-semibold">
                {{ tipo === 'credito' ? 'Crédito' : 'Débito' }}
            </h3>
            <span
                :class="[
                    'px-2 py-1 text-sm font-medium rounded',
                    tipo === 'credito' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                ]"
            >
                {{ valor ? Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor) : '' }} - {{ tipo === 'credito' ? 'Recebido de ' + de_user : 'Enviado para ' + para_user }}
            </span>
        </div>
        <p class="text-gray-700 mb-1">
            {{ descricao ? descricao + ' - ' : '' }} {{ new Date(data).toLocaleDateString() }}
        </p>
        <slot></slot>
    </div>
</template>
