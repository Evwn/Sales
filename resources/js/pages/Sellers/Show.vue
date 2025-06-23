<template>
  <AppLayout>
    <Head title="Seller Details" />
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h2 class="text-2xl font-semibold mb-4">Seller Details</h2>
          <div>
            <p><strong>Name:</strong> {{ seller.name }}</p>
            <p><strong>Email:</strong> {{ seller.email }}</p>
            <p><strong>Branch:</strong> {{ branch?.name || 'N/A' }}</p>
            <p><strong>Business:</strong> {{ business?.name || 'N/A' }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  seller: Object,
  branch: Object,
  business: Object
})
</script> 
