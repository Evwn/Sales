<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Edit Purchase" :button="{ text: 'Back to Purchases', link: '/purchases' }" />
      <form @submit.prevent="submitPurchase">
        <!-- form fields -->
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
const form = ref({});
const submitPurchase = () => {/* ... */};
</script>
<style scoped>
/* Custom styles for beautiful UI */
</style> 