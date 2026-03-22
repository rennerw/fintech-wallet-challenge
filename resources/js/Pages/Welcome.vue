<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome" />
    <div v-if="canLogin || canRegister">
        <div class="bg-gray-50 text-black flex items-center justify-center" style="min-height: 100vh; min-width: 100vw;">
            <a href="login" class="text-2xl font-bold text-gray-800 hover:text-gray-600 transition duration-300">Login</a>
            <a href="register" class="text-2xl ml-10 font-bold text-gray-800 hover:text-gray-600 transition duration-300">Registrar</a>
        </div>
    </div>
    <div v-else class="bg-gray-50 text-black flex items-center justify-center" style="min-height: 100vh; min-width: 100vw;">
        <h1 class="text-3xl font-bold text-gray-800">Bem-vindo ao Teck!</h1>
        <a href="dashboard" class="text-2xl font-bold text-gray-800 hover:text-gray-600 transition duration-300">Dashboard</a>
    </div>
</template>
