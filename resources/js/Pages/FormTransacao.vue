<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';

const opened = ref(false);
const errors = ref([]);

const form = ref({
    email: '',
    valor: '',
    processing: false,
});

const emit = defineEmits(['atualizarSaldo','close']);

function openModal() {
    opened.value = true;
}

function closeModal() {
    emit('atualizarSaldo');
    emit('close');
    form.value.email = '';
    form.value.valor = '';
    errors.value = [];
}

async function transferTo() {
    form.processing = true;
    await axios.post('/api/transferencia', {
        email: form.value.email,
        valor: form.value.valor,
    })
    .then(response => {
        form.processing = true;
        alert('Transferência realizada com sucesso!');
        errors.value = [];
        closeModal();
    })
    .catch((error) => {
        form.processing = true;
        if (error.response && error.response.data && error.response.data.errors) {
            errors.value = Object.values(error.response.data.errors).flat();
        }

        else {
            if(error.response.message) {
                errors.value = [error.response.message];
            } else {
                errors.value = ['Ocorreu um erro inesperado. Por favor, tente novamente.'];
            }
        }
    });
}
</script>
<template>
    <Modal :show="opened" @close="closeModal">
        <div class="p-6">
            <h2
                class="text-lg font-medium text-gray-900"
            >
                Transferência Bancaria
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Confira o email e saldo e faça a transferência bancária para o usuário desejado.
            </p>

            <div class="mt-6">
                <InputLabel
                    for="email"
                    value="email"
                    class="sr-only"
                />

                <TextInput
                    id="email"
                    ref="emailInput"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-3/4"
                    placeholder="Email"
                />

            </div>

            <div class="mt-6">
                <InputLabel
                    for="valor"
                    value="valor"
                    class="sr-only"
                />

                <TextInput
                    id="valor"
                    ref="valorInput"
                    v-model="form.valor"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Valor"
                />

            </div>
            <p v-for="message in errors" class="text-sm text-red-600">
                {{ message }}
            </p>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">
                    Cancelar
                </SecondaryButton>

                <PrimaryButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="transferTo"
                >
                    Transferir
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>