<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Edit Category" :button="{ text: 'Back to Categories', link: '/categories' }" />
      <form @submit.prevent="submitCategory">
        <input v-model="form.name" placeholder="Name" required />
        <textarea v-model="form.description" placeholder="Description"></textarea>
        <input v-model="form.parent_id" placeholder="Parent ID (optional)" />
        <input v-model="form.business_id" placeholder="Business ID" required />
        <button type="submit">Update</button>
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';

const form = ref({ /* pre-fill with category data from props */ });
const submitCategory = () => {
  Swal.fire('Updating category...');
  router.put(`/categories/${form.value.id}`, form.value);
};
</script>
<style scoped>
input, textarea { @apply border rounded px-2 py-1 w-full mb-2; }
button { @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700; }
</style>
