<template>
  <AppLayout>
    <div class="p-6">
      <PageHeader title="Create Store" :button="{ text: 'Back to Stores', link: '/stores' }" />
      <form @submit.prevent="submitStore">
        <div>
          <label>Business</label>
          <select v-model="form.business_id" required>
            <option value="">Select</option>
            <option v-for="b in businesses" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
        </div>
        <div>
          <label>Branch</label>
          <select v-model="form.branch_id">
            <option value="">None</option>
            <option v-for="br in filteredBranches" :key="br.id" :value="br.id">{{ br.name }}</option>
          </select>
        </div>
        <div>
          <label>Name</label>
          <input v-model="form.name" required />
        </div>
        <div>
          <label>Address</label>
          <input v-model="form.address" />
        </div>
        <div>
          <label>Phone</label>
          <input v-model="form.phone" />
        </div>
        <div>
          <label>Status</label>
          <select v-model="form.status">
            <option :value="1">Active</option>
            <option :value="0">Inactive</option>
          </select>
        </div>
        <button type="submit">Save</button>
      </form>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
const props = defineProps({
  businesses: Array,
  branches: Array
});
const form = ref({
  business_id: '',
  branch_id: '',
  name: '',
  address: '',
  phone: '',
  status: 1
});
const filteredBranches = computed(() => {
  if (!form.value.business_id) return props.branches;
  return props.branches.filter(b => b.business_id == form.value.business_id);
});
const submitStore = () => {
  router.post('/stores', form.value);
};
</script> 