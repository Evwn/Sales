<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';

const form = ref({ name: '', business_id: '', options: [] });
const submitModifier = () => {
  Swal.fire('Saving modifier...');
  router.post('/modifiers', form.value);
};
</script>
<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Create Modifier" :button="{ text: 'Back to Modifiers', link: '/modifiers' }" />
      <form @submit.prevent="submitModifier">
        <input v-model="form.name" placeholder="Name" required />
        <input v-model="form.business_id" placeholder="Business ID" required />
        <!-- Options section: add/remove option name and price -->
        <button type="submit">Save</button>
      </form>
    </div>
  </AppLayout>
</template>
<style scoped>
input { @apply border rounded px-2 py-1 w-full mb-2; }
button { @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700; }
</style>
