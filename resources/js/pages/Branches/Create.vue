<template>
  <AppLayout title="Create Branch">
    <PageHeader title="Create Branch" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <BranchForm :business="business" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import BranchForm from './Form.vue';

defineProps({
  business: {
    type: Object,
    required: true,
  },
});
</script> 
