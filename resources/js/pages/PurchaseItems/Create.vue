<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Create Purchase Item" :button="{ text: 'Back to Purchase Items', link: '/purchase-items' }" />
      <form @submit.prevent="submitPurchaseItem">
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
const submitPurchaseItem = () => {/* ... */};
</script>
<style scoped>
/* Custom styles for beautiful UI */
</style> 