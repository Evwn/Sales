<template>
  <AppLayout title="Edit Modifier">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Edit Modifier</h1>
      <form @submit.prevent="submitModifier">
        <input v-model="form.name" placeholder="Name" required />
        <input v-model="form.business_id" placeholder="Business ID" required />
        <!-- Options section: add/remove option name and price -->
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

const form = ref({ /* pre-fill with modifier data from props */ });
const submitModifier = () => {
  Swal.fire('Updating modifier...');
  router.put(`/modifiers/${form.value.id}`, form.value);
};
</script>
<style scoped>
input { @apply border rounded px-2 py-1 w-full mb-2; }
button { @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700; }
</style> 