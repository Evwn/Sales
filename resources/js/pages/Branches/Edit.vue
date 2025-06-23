<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { PropType } from 'vue';
import Swal from 'sweetalert2';
import BranchForm from './Form.vue';

const props = defineProps({
    business: {
        type: Object,
        required: true
    },
    branch: {
        type: Object,
        required: true
    },
    businesses: {
        type: Array as PropType<Array<{ id: number; name: string }>>,
        required: true,
        default: () => []
    },
    sellers: {
        type: Array as PropType<Array<{ id: number; name: string; email: string }>>,
        required: true,
        default: () => []
    }
});

const form = useForm({
    name: props.branch.name,
    address: props.branch.address,
    phone: props.branch.phone,
    business_id: props.business.id,
    seller_ids: props.branch.sellers.map(seller => seller.id)
});

const handleSubmit = async () => {
    try {
        // Show loading state
        Swal.fire({
            title: 'Updating Branch...',
            allowOutsideClick: false,
            backdrop: 'rgba(0,0,0,0.4)',
            timer: 2000,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Submit the form
        await form.put(`/businesses/${form.business_id}/branches/${props.branch.id}`, {
            onSuccess: () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Branch updated successfully',
                    timer: 2000,
                    showConfirmButton: false,
                    backdrop: 'rgba(0,0,0,0.4)'
                });
            },
            onError: (errors) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: Object.values(errors).join('\n'),
                    confirmButtonText: 'OK',
                    backdrop: 'rgba(0,0,0,0.4)'
                });
            }
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred',
            confirmButtonText: 'OK',
            backdrop: 'rgba(0,0,0,0.4)'
        });
    }
};
</script>

<template>
    <AppLayout title="Edit Branch">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Branch
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <BranchForm :branch="branch" :business="business" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
