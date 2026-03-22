<style lang="css">
    .datatable select, .datatable input {
        border-radius: 4px;
        margin-left: 0.25rem !important;
        margin-right: 0.25rem !important;
    }
    
    .datatable table{
        border-radius: 4px;
    }

    table.dataTable thead th {
        background-color: #f9fafb;
        color: #374151;
        font-weight: 900;
    }
    .dt-layout-row.dt-layout-table{
        margin-top: 0.75rem !important;
    }

    table.dataTable tbody tr {
        background-color: #ffffff;
    }
    table.dataTable tbody tr:nth-child(even) {
        background-color: #f3f4f6;
    } 
    table.dataTable tbody tr:hover {
        background-color: #e5e7eb;
        cursor: pointer;
    }
    div.dt-layout-row:has(.dt-paging){
        display: flex !important;
        justify-content: center !important;
        margin-top: 1rem !important;
    }
    .dt-paging-button {
        background-color: #f9fafb;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 0.25rem 0.75rem;
        margin: 0 0.25rem;
    }
    .dt-paging-button.disabled {
        background-color: #b8b8b8;
        color: #374151;
        border: 1px solid #b8b8b8;
        cursor: not-allowed;
        border-radius: 4px;
        padding: 0.25rem 0.75rem;
        margin: 0 0.25rem;
    }
</style>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
 
DataTable.use(DataTablesCore);

let dataTableApi = null;
const filtros = {
    nome_usuario: '',
    tipo: '',
    data_inicio: '',
    data_fim: '',
};

const extratoColumns = [
    { title: 'Tipo', data: 'tipo', render: (data) => data === 'credito' ? 'Crédito' : 'Débito' },
    { title: 'Descrição', data: 'descricao', render: (data, type, row) => row.extrato ? row.extrato.descricao.slice(0, 30)+'...' : 'N/A' },
    { title: 'Usuário', data: '', render: (data, type, row) => row.tipo == 'debito' ? (row.para_user ? row.para_user.name : 'N/A') : (row.de_user ? row.de_user.name : 'N/A') },
    { title: 'Valor', data: 'valor', render: (data) => data ? Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(data) : '' },
    { title: 'Data', data: 'created_at', render: (data) => data ? new Date(data).toLocaleDateString('pt-BR') : '' },
];

async function filter() {
    const tipoEl = document.querySelector('.dt-filter-tipo');
    const dataInicioEl = document.querySelector('.dt-filter-data_inicio');
    const dataFimEl = document.querySelector('.dt-filter-data_fim');
    const usuarioEl = document.querySelector('.dt-search input');

    filtros.nome_usuario = usuarioEl ? usuarioEl.value : '';
    filtros.tipo = tipoEl ? tipoEl.value : '';
    filtros.data_inicio = dataInicioEl ? dataInicioEl.value : '';
    filtros.data_fim = dataFimEl ? dataFimEl.value : '';

    if (dataTableApi) {
        await extratoAjax({}, (result) => {
            dataTableApi.clear();
            dataTableApi.rows.add(result.data);
            dataTableApi.draw();
        });
    }
};

const tipos = `<select name="tipo" class="dt-filter-tipo" title="Tipo">
    <option value="">Selecione um tipo</option>
    <option value="credito">Crédito</option>
    <option value="debito">Débito</option>
</select>`;

const datas = `<input name="data_inicio" type="date" class="dt-filter-data_inicio" title="Data Início"/> <input name="data_fim" type="date" class="dt-filter-data_fim" title="Data Fim"/>`;

const options = {
    paging: true,
    searching: true,
    info: false,
    responsive: true,
    initComplete: function() {
        dataTableApi = this.api();
        dataTableApi.table().container().querySelector(".dt-search").style.display = 'inline';
        const select = document.createElement('div');
        select.classList.add('dt-custom-filter', 'inline');
        select.innerHTML = tipos + datas;
        dataTableApi.table().container().querySelector(".dt-search").parentNode.appendChild(select);

        const usuarioEl = dataTableApi.table().container().querySelector('.dt-search input');
        const tipoEl = dataTableApi.table().container().querySelector('.dt-filter-tipo');
        const dataInicioEl = dataTableApi.table().container().querySelector('.dt-filter-data_inicio');
        const dataFimEl = dataTableApi.table().container().querySelector('.dt-filter-data_fim');

        if (usuarioEl) usuarioEl.addEventListener('change', filter);
        if (usuarioEl) usuarioEl.addEventListener('input', filter);
        if (tipoEl) tipoEl.addEventListener('change', filter);
        if (dataInicioEl) dataInicioEl.addEventListener('change', filter);
        if (dataFimEl) dataFimEl.addEventListener('change', filter);
    },
    language: {
        emptyTable: "Nenhum registro encontrado",
        info: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 até 0 de 0 registros",
        infoFiltered: "(Filtrados de _MAX_ registros)",
        lengthMenu: "_MENU_ resultados por página",
        loadingRecords: "Carregando...",
        processing: "Processando...",
        zeroRecords: "Nenhum registro encontrado",
        search: "Pesquisar Usuário",
        oPaginate: {
            sNext: ">",
            sPrevious: "<",
            sFirst: "Primeira",
            sLast: "Última"
        },
    },
};

const extratoAjax = async (_data, callback) => {
    try {
        
        const response = await axios.get('/api/extrato-completo', {
            withCredentials: true,
            params: {
                nome_usuario: filtros.nome_usuario || undefined,
                tipo: filtros.tipo || undefined,
                data_inicio: filtros.data_inicio || undefined,
                data_fim: filtros.data_fim || undefined,
            },
        });

        callback({ data: response.data.data || [] });
    } catch (error) {
        console.error('Erro ao obter o extrato completo:', error); // Só para debug, nao deve ir a producao
        callback({ data: [] });
    }
};

const atualizarValores = async () => { await filter() }


</script>

<template>
    <Head title="Extrato" />
    <AuthenticatedLayout @atualizar-valores="atualizarValores">
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Extrato
            </h2>
        </template>
        
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-right">
                        <h1 class="inline text-xl"><b>Saldo Atual: </b></h1> {{ Intl.NumberFormat('pt-BR', {style: 'currency', currency: 'BRL'}).format($page.props.carteira?.valor_atual ?? 0) }}
                    </div>

                    <div class="mt-2 mb-10 mx-3">
                        <h2 class="text-2xl font-semibold mb-4 text-center">Extrato Completo</h2>
                        
                        <DataTable
                            :ajax="extratoAjax"
                            :columns="extratoColumns"
                            :options="options"
                            class="border-separate border border-gray-400">
                        </DataTable>
                        
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
