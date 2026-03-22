<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InfoTransferencia from '@/Components/InfoTransferencia.vue';
import { Head } from '@inertiajs/vue3';
import { onBeforeMount, ref } from 'vue';
import axios from 'axios';

const transferencias = ref([]);



onBeforeMount(async () => {
    await atualizarValores();
});

const atualizarValores = async () => { 
    await axios.get('/api/ultimas-transferencias', {
        withCredentials: true, // Certifique-se de enviar cookies para autenticação
    })
    .then(response => {
        transferencias.value = response.data.data || [];
    })
    .catch(error => {
        console.error('Erro ao obter as últimas transferências:', error); // Só para debug, nao deve ir a producao
    }); 
}

</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout @atualizar-valores="atualizarValores">
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-right">
                        <h1 class="inline text-xl"><b>Saldo Atual: </b></h1> {{ Intl.NumberFormat('pt-BR', {style: 'currency', currency: 'BRL'}).format($page.props.carteira?.valor_atual ?? 0) }}
                    </div>
                    <div class="mt-2 mb-10 mx-3 justify-items-center">
                        <h2 class="text-2xl font-semibold mb-4">Últimas Movimentações</h2>
                        <div v-if="transferencias.length > 0" class="space-y-3 lg:w-1/2 md:w-full sm:w-full">
                            <InfoTransferencia
                                v-for="(transf, index) in transferencias"
                                :key="index"
                                :valor="transf.valor"
                                :data="transf.created_at"
                                :descricao="transf.descricao"
                                :tipo="transf.tipo"
                                :de_user="transf.de_user ? transf.de_user.name : 'N/A'"
                                :para_user="transf.para_user ? transf.para_user.name : 'N/A'"
                            >

                        </div>
                        <div v-else class="text-gray-500">
                            Nenhuma movimentação feita na conta ainda. Invista aqui!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
