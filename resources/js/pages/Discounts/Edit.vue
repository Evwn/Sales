<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';

const form = ref({ /* pre-fill with discount data from props */ });
const submitDiscount = () => {
  Swal.fire('Updating discount...');
  router.put(`/discounts/${form.value.id}`, form.value);
};
</script>
<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Edit Discount" :button="{ text: 'Back to Discounts', link: '/discounts' }" />
      <form @submit.prevent="submitDiscount">
        <select v-model="form.type" required>
          <option value="">Type</option>
          <option value="percentage">Percentage</option>
          <option value="flat">Flat</option>
        </select>
        <input v-model="form.value" placeholder="Value" required />
        <input v-model="form.starts_at" type="date" placeholder="Start Date" required />
        <input v-model="form.ends_at" type="date" placeholder="End Date" required />
        <input v-model="form.business_id" placeholder="Business ID" required />
        <button type="submit">Update</button>
      </form>
    </div>
  </AppLayout>
</template>
<style scoped>
input, select { @apply border rounded px-2 py-1 w-full mb-2; }
button { @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700; }
</style>
